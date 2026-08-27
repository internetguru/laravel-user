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
     * No user account is created here: a registration stays pending
     * (pin_logins.user_id is null) until the PIN proves the email is real.
     */
    public static function sendPinLogin(string $email, bool $remember = false, bool $register = false, bool $resend = false): RedirectResponse
    {
        $pinLogin = PinLoginModel::where('email', $email)->first();

        // If PIN already exists and newer than 1 minute then throttle
        if ($pinLogin && $pinLogin->updated_at->diffInMinutes() < 1) {
            return redirect()->route('pin-login.verify', ['email' => $email])
                ->withErrors(__('ig-user::pin_login.wait'));
        }

        $userId = User::where('email', $email)->value('id');

        $attributes = [
            'user_id' => $userId,
            'pin' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => now()->addMinutes(10),
        ];

        if (! $resend) {
            $attributes['remember'] = $remember;
            // Without an account behind the email the PIN can only lead to a registration
            $attributes['register'] = $register || ! $userId;
        }

        $pinLogin = PinLoginModel::updateOrCreate(['email' => $email], $attributes);
        User::sendPinLoginNotification($pinLogin);

        return redirect()->route('pin-login.verify', ['email' => $email])
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

        $verifyParams = $email ? ['email' => $email] : [];

        $pinLogin = PinLoginModel::where('pin', $pin)
            ->where('expires_at', '>', now())
            ->first();

        if (! $pinLogin) {
            logger()->warning('Invalid PIN login attempt', ['pin' => $pin, 'email' => $email]);

            return redirect()->route('pin-login.verify', $verifyParams)
                ->withErrors(__('ig-user::pin_login.invalid'));
        }

        $user = $pinLogin->user;
        $remember = $pinLogin->remember;
        $register = $pinLogin->register;
        $pinEmail = $pinLogin->email;
        $pinLogin->delete();

        // The identity is confirmed only now, so this is where the account is created
        if (! $user) {
            if (! $register) {
                return redirect()->route('pin-login.verify', $verifyParams)
                    ->withErrors(__('ig-user::messages.login.notfound'));
            }

            $user = User::registerUser(Str::before($pinEmail, '@'), $pinEmail);
            event(new Registered($user));
        }

        auth()->login($user, $remember);
        User::authenticated(auth()->user());

        return User::successLoginRedirect($user);
    }
}
