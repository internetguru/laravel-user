<?php

namespace InternetGuru\LaravelUser\Traits;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use InternetGuru\LaravelCommon\Support\Helpers;
use InternetGuru\LaravelUser\Models\PinLogin as PinLoginModel;
use InternetGuru\LaravelUser\Notifications\PinLoginNotification;

trait PinLogin
{
    public const PIN_PREFIX = 'IG-';

    public function pinLoginRecord(): HasOne
    {
        return $this->hasOne(PinLoginModel::class);
    }

    /**
     * Issue a PIN for the given email address and send it out.
     *
     * A PIN that is still valid is kept: the sign-in form only leads to its
     * verification, and a resend mails the same PIN again.
     *
     * No user account is created here: a registration stays pending
     * (pin_logins.user_id is null) until the PIN proves the email is real.
     */
    public static function sendPinLogin(string $email, bool $remember = false, bool $register = false, bool $resend = false): RedirectResponse
    {
        session()->put(PinLoginModel::SESSION_KEY, $email);
        $pinLogin = PinLoginModel::where('email', $email)->first();
        $validPin = $pinLogin && $pinLogin->expires_at->isFuture();

        if ($validPin && ! $resend) {
            return redirect()->route('pin-login.verify');
        }

        // If PIN already exists and newer than 1 minute then throttle
        if ($pinLogin && $pinLogin->updated_at->diffInMinutes() < 1) {
            return redirect()->route('pin-login.verify')
                ->withErrors(__('ig-user::pin_login.wait'));
        }

        if ($validPin) {
            $pinLogin->touch();

            return self::notifyPinSent($pinLogin);
        }

        $userId = User::where('email', $email)->value('id');

        $attributes = [
            'user_id' => $userId,
            'pin' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => now()->addMinutes(10),
        ];

        if (! $resend) {
            $attributes['remember'] = $remember;
            // Without an account behind the email the PIN can only lead to a registration
            $attributes['register'] = $register || ! $userId;
        }

        return self::notifyPinSent(PinLoginModel::updateOrCreate(['email' => $email], $attributes));
    }

    private static function notifyPinSent(PinLoginModel $pinLogin): RedirectResponse
    {
        User::sendPinLoginNotification($pinLogin);

        return redirect()->route('pin-login.verify')
            ->with('success', __('ig-user::pin_login.sent') . Helpers::getEmailClientLink());
    }

    public static function sendPinLoginNotification(PinLoginModel $pinLogin): void
    {
        $notification = new PinLoginNotification($pinLogin);
        $notification->locale(app()->getLocale());

        // Pending registrations have no user model to notify yet
        if ($pinLogin->user) {
            $pinLogin->user->notify($notification);

            return;
        }

        Notification::route('mail', $pinLogin->email)->notify($notification);
    }

    public static function formatPin(string $pin): string
    {
        return self::PIN_PREFIX . $pin;
    }

    public static function pinLogin(string $pin, ?string $email = null): RedirectResponse
    {
        // Strip prefix and non-digits
        $pin = preg_replace('/[^0-9]/', '', $pin);

        // A PIN is valid only for the address it was sent to
        $pinLogin = $email === null ? null : PinLoginModel::where('pin', $pin)
            ->where('email', Str::lower($email))
            ->where('expires_at', '>', now())
            ->first();

        if (! $pinLogin) {
            logger()->warning('Invalid PIN login attempt', ['user_id' => User::getByEmail($email)?->id]);

            return redirect()->route('pin-login.verify')
                ->withErrors(__('ig-user::pin_login.invalid'));
        }

        $user = $pinLogin->user;
        $remember = $pinLogin->remember;
        $register = $pinLogin->register;
        $pinEmail = $pinLogin->email;
        $pinLogin->delete();
        session()->forget(PinLoginModel::SESSION_KEY);

        // The identity is confirmed only now, so this is where the account is created
        if (! $user) {
            if (! $register) {
                return redirect()->route('login')
                    ->withErrors(__('ig-user::messages.login.notfound'));
            }

            $user = User::registerUser(User::nameFromEmail($pinEmail), $pinEmail);
            event(new Registered($user));
        }

        auth()->login($user, $remember);
        User::authenticated($user);

        return User::successLoginRedirect($user);
    }
}
