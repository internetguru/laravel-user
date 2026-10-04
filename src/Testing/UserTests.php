<?php

namespace InternetGuru\LaravelUser\Testing;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use InternetGuru\LaravelUser\Models\PinLogin;
use InternetGuru\LaravelUser\Notifications\PinLoginNotification;

/**
 * Pest tests every application built on laravel-user should pass. Register the HTTP tests from a feature test file
 * and the browser tests from a file in tests/Browser:
 *
 *     UserTests::register(demo: false);
 *     UserTests::registerBrowser();
 */
class UserTests
{
    public static function register(bool $demo = false): void
    {
        describe('laravel-user login', function () use ($demo) {
            it('renders the login form', function () use ($demo) {
                $response = $this->get(route('login'))->assertOk()->assertSee('section-login', false);

                $demo
                    ? $response->assertSee('<select', false)->assertSee('name="email"', false)
                    : $response->assertSee('type="email"', false)->assertSee('id="remember_check"', false)->assertSee('id="register_check"', false);
            });

            it('redirects the registration page and guests on a protected page to the login', function () {
                $this->get('/register')->assertRedirect(route('login'));
                $this->get(route('users.index'))->assertRedirect(route('login'));
            });

            it('sends a signed-in user away from the login', function () {
                $this->actingAs(User::factory()->create())->get(route('login'))->assertRedirect();
            });

            if ($demo) {
                it('signs in a demo user picked from the list', function () {
                    $user = User::factory()->withRole(User::roles()::MANAGER)->create();

                    $this->post(route('login'), ['email' => $user->email])->assertRedirect();

                    $this->assertAuthenticatedAs($user);
                });
            } else {
                it('refuses an unknown address unless it registers', function () {
                    $this->from(route('login'))->post(route('pin-login.form'), ['email' => 'unknown@example.com'])
                        ->assertRedirect(route('login'))
                        ->assertSessionHasErrors();

                    $this->post(route('pin-login.form'), ['email' => 'unknown@example.com', 'register' => '1'])
                        ->assertRedirect(route('pin-login.verify', ['email' => 'unknown@example.com']));
                });

                it('e-mails the PIN with a link to the verification page', function () {
                    Notification::fake();

                    $this->post(route('pin-login.form'), ['email' => 'new@example.com', 'register' => '1']);

                    Notification::assertSentOnDemand(PinLoginNotification::class, function (PinLoginNotification $notification, array $channels, object $notifiable) {
                        $mail = (string) $notification->toMail($notifiable)->render();

                        return preg_match('/' . preg_quote(User::PIN_PREFIX, '/') . '\d{6}/', $mail)
                            && str_contains($mail, route('pin-login.verify', ['email' => 'new@example.com']));
                    });
                });
            }
        });

        describe('laravel-user access', function () {
            it('lists users for a manager and refuses a customer', function () {
                $this->actingAs(User::factory()->withRole(User::roles()::MANAGER)->create())
                    ->get(route('users.index'))->assertOk()->assertSee('section-user-list', false);

                $this->actingAs(User::factory()->withRole(User::roles()::CUSTOMER)->create())
                    ->get(route('users.index'))->assertForbidden();
            });

            it('shows a user detail to a manager', function () {
                $user = User::factory()->create();

                $this->actingAs(User::factory()->withRole(User::roles()::MANAGER)->create())
                    ->get(route('users.show', $user))
                    ->assertOk()
                    ->assertSee('section-user-detail', false)
                    ->assertSee($user->email);
            });
        });
    }

    public static function registerBrowser(): void
    {
        describe('laravel-user PIN login', function () {
            it('signs in once the e-mailed PIN is typed digit by digit', function () {
                $user = User::factory()->create(['logged_at' => null]);

                $page = UserTests::sendPin($user->email)
                    ->typeSlowly('.pin-input-box:first-child', PinLogin::where('email', $user->email)->value('pin'));

                UserTests::leaveVerification($page)
                    ->assertPathIsNot('/pin-login/verify')
                    ->assertNoJavascriptErrors();

                expect($user->refresh()->logged_at)->not->toBeNull();
            });

            it('creates the account of a registering address once its PIN is verified', function () {
                $page = UserTests::sendPin('new@example.com', register: true)
                    ->typeSlowly('.pin-input-box:first-child', PinLogin::where('email', 'new@example.com')->value('pin'));

                UserTests::leaveVerification($page)->assertPathIsNot('/pin-login/verify');

                expect(User::where('email', 'new@example.com')->exists())->toBeTrue();
            });

            it('keeps a wrong PIN on the verification page with an error', function () {
                $user = User::factory()->create(['logged_at' => null]);

                $page = UserTests::sendPin($user->email)->typeSlowly('.pin-input-box:first-child', '000000');

                UserTests::waitFor($page, fn () => $page->script('document.querySelector(\'[data-testid="system-message-danger"]\') !== null'))
                    ->assertPathIs('/pin-login/verify')
                    ->assertVisible('@system-message-danger');

                expect($user->refresh()->logged_at)->toBeNull();
            });
        });

        describe('laravel-user detail', function () {
            it('lets a manager rename a user in place', function () {
                $user = User::factory()->create(['name' => 'Old Name']);
                $this->actingAs(User::factory()->withRole(User::roles()::MANAGER)->create());

                visit(route('users.show', $user))
                    ->click('.section-user-detail dt:first-of-type button')
                    ->type('.section-user-detail input[name="name"]', 'New Name')
                    ->click('.section-user-detail form:has(input[name="name"]) button[type="submit"]')
                    ->assertVisible('@system-message-success')
                    ->assertSeeIn('.section-user-detail', 'New Name')
                    ->assertNoJavascriptErrors();

                expect($user->refresh()->name)->toBe('New Name');
            });
        });
    }

    /**
     * Wait for the PIN input to submit itself and the browser to leave the verification page.
     */
    public static function leaveVerification(mixed $page): mixed
    {
        return self::waitFor($page, fn () => parse_url($page->url(), PHP_URL_PATH) !== '/pin-login/verify');
    }

    /**
     * Poll through the browser's own wait, which keeps the in-process server answering, unlike sleep().
     */
    public static function waitFor(mixed $page, callable $condition, float $seconds = 5): mixed
    {
        for ($waited = 0; $waited < $seconds && ! $condition(); $waited += 0.1) {
            $page->wait(0.1);
        }

        return $page;
    }

    /**
     * Ask for a PIN on the login page and land on the verification page.
     */
    public static function sendPin(string $email, bool $register = false): mixed
    {
        Notification::fake();

        $page = visit(route('login', $register ? ['register' => 'true'] : []))
            ->type('.section-login input[name="email"]', $email)
            ->click('.section-login [data-testid="submit-button"]')
            ->assertPathIs('/pin-login/verify')
            ->assertVisible('.pin-input');

        // Alpine focuses the first PIN box 50 ms after it starts, which moves the cursor back if typing has begun
        return self::waitFor($page, fn () => $page->script('(() => { if (! document.querySelector(".pin-input")?._x_dataStack) return false; window.pinReadyAt ??= performance.now(); return performance.now() - window.pinReadyAt > 100; })()'));
    }
}
