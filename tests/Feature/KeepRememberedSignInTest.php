<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeepRememberedSignInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.demo' => false, 'auth.providers.users.model' => User::class]);
        $this->app['router']->middleware(['web', 'auth'])->get('/machines', fn () => 'machines')->name('machines.index');
        $this->app['router']->middleware('web')->post('/machines', fn () => response()->json(['saved' => true]));
        $this->app['router']->middleware('web')->get('/page', fn () => response('<p>' . implode(' ', (array) session('warning', [])) . '</p>'));
        $this->app['router']->middleware('web')->get('/plain', fn () => response('<p>No messages here</p>'));
        $this->app['router']->middleware('web')->get('/data', fn () => response()->json(['ok' => true]));
        $this->app['router']->middleware('web')->get('/away', fn () => redirect('/page'));
    }

    public function test_a_remembered_return_renews_the_cookie_for_400_days()
    {
        $user = $this->rememberedUser();

        $response = $this->withCookie($this->recallerName(), $this->recaller($user))->get(route('login'));

        $this->assertAuthenticatedAs($user);
        $cookie = $response->getCookie($this->recallerName(), false);
        $this->assertNotNull($cookie);
        $this->assertGreaterThan(now()->addDays(399)->timestamp, $cookie->getExpiresTime());
    }

    public function test_a_remembered_return_through_a_post_such_as_a_livewire_update_renews_the_cookie()
    {
        $user = $this->rememberedUser();

        $response = $this->withCookie($this->recallerName(), $this->recaller($user))->post('/machines');

        $this->assertAuthenticatedAs($user);
        $cookie = $response->getCookie($this->recallerName(), false);
        $this->assertNotNull($cookie);
        $this->assertFalse($cookie->isCleared());
    }

    public function test_an_ended_sign_in_shows_the_notice_on_the_page_and_removes_the_cookie()
    {
        $user = $this->rememberedUser();

        $response = $this->withCookie($this->recallerName(), $this->recaller($user, str_repeat('b', 60)))->get('/page');

        $this->assertGuest();
        $response->assertSee(__('ig-user::auth.signed-out', ['url' => route('login')]), false);
        $this->assertTrue($response->getCookie($this->recallerName(), false)->isCleared());
    }

    public function test_an_old_format_cookie_is_recalled_and_renewed_in_the_current_format()
    {
        $user = $this->rememberedUser();

        $response = $this->withCookie($this->recallerName(), $user->getAuthIdentifier() . '|' . $user->getRememberToken() . '|')->get('/plain');

        $this->assertAuthenticatedAs($user);
        $this->assertSame($this->recaller($user), $response->getCookie($this->recallerName())->getValue());
    }

    public function test_the_notice_joins_a_warning_the_application_flashed()
    {
        $user = $this->rememberedUser();

        $this->withSession(['warning' => 'App warning'])
            ->withCookie($this->recallerName(), $this->recaller($user, str_repeat('b', 60)))
            ->get('/page')
            ->assertSee('App warning')
            ->assertSee(__('ig-user::auth.signed-out', ['url' => route('login')]), false);
    }

    public function test_a_page_without_the_notice_keeps_the_cookie_for_the_next_page()
    {
        $user = $this->rememberedUser();

        $response = $this->withCookie($this->recallerName(), $this->recaller($user, str_repeat('b', 60)))->get('/plain');

        $this->assertNull($response->getCookie($this->recallerName(), false));
    }

    public function test_a_json_response_keeps_the_cookie_for_the_next_page()
    {
        $user = $this->rememberedUser();

        $response = $this->withCookie($this->recallerName(), $this->recaller($user, str_repeat('b', 60)))->get('/data');

        $this->assertNull($response->getCookie($this->recallerName(), false));
    }

    public function test_with_sign_in_disabled_the_cookie_is_removed_without_a_notice()
    {
        config(['ig-user.login' => false]);
        $user = $this->rememberedUser();

        $response = $this->withCookie($this->recallerName(), $this->recaller($user, str_repeat('b', 60)))->get('/page');

        $response->assertSee('<p></p>', false);
        $this->assertTrue($response->getCookie($this->recallerName(), false)->isCleared());
    }

    public function test_an_ended_sign_in_keeps_the_cookie_through_a_redirect_to_the_sign_in_page()
    {
        $user = $this->rememberedUser();

        $response = $this->withCookie($this->recallerName(), $this->recaller($user, str_repeat('b', 60)))->get('/away');

        $response->assertRedirect('/page');
        $this->assertNull($response->getCookie($this->recallerName(), false));
    }

    public function test_a_post_with_an_ended_sign_in_keeps_the_cookie_for_the_next_page()
    {
        $user = $this->rememberedUser();

        $response = $this->withCookie($this->recallerName(), $this->recaller($user, str_repeat('b', 60)))->post('/machines');

        $this->assertNull($response->getCookie($this->recallerName(), false));
    }

    public function test_a_guest_without_the_cookie_gets_no_notice()
    {
        $this->get('/page')->assertSee('<p></p>', false);
    }

    private function rememberedUser(): User
    {
        $user = User::factory()->create();
        $user->setRememberToken(str_repeat('a', 60));
        $user->save();

        return $user;
    }

    private function recaller(User $user, ?string $token = null): string
    {
        return $user->getAuthIdentifier() . '|' . ($token ?? $user->getRememberToken()) . '|' . auth()->guard()->hashPasswordForCookie($user->getAuthPassword());
    }

    private function recallerName(): string
    {
        return auth()->guard()->getRecallerName();
    }
}
