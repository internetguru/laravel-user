<?php

namespace InternetGuru\LaravelUser\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\RedirectResponse;
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

    public function sendPinLogin(?string $redirectTo = null): RedirectResponse
    {
        // If PIN already exists and newer than 1 minute then throttle
        if ($this->pinLoginRecord && $this->pinLoginRecord->updated_at->diffInMinutes() < 1) {
            return back()->withErrors(__('ig-user::pin_login.wait'));
        }

        $pinLogin = $this->pinLoginRecord()->updateOrCreate([
            'user_id' => $this->id,
        ], [
            'pin' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => now()->addMinutes(10),
        ]);
        User::sendPinLoginNotification($pinLogin);

        return redirect()->to('/')->with('success', __('ig-user::pin_login.sent') . Helpers::getEmailClientLink());
    }

    public static function sendPinLoginNotification(PinLoginModel $pinLogin): void
    {
        $notification = new PinLoginNotification($pinLogin);
        $notification->locale(app()->getLocale());
        $pinLogin->user->notify($notification);
    }

    public static function formatPin(string $pin): string
    {
        return self::PIN_PREFIX . $pin;
    }

    public static function pinLogin(string $pin): RedirectResponse
    {
        [, $backUrl] = User::getAuthSessions();

        // Strip prefix and non-digits
        $pin = preg_replace('/[^0-9]/', '', $pin);

        $pinLogin = PinLoginModel::where('pin', $pin)
            ->where('expires_at', '>', now())
            ->first();

        if (! $pinLogin) {
            // Check if there's an expired PIN matching
            $expired = PinLoginModel::where('pin', $pin)
                ->where('expires_at', '<=', now())
                ->exists();

            $error = $expired
                ? __('ig-user::pin_login.expired_pin')
                : __('ig-user::pin_login.invalid_pin');

            return redirect()->to($backUrl)->withErrors($error);
        }

        $user = $pinLogin->user;
        $pinLogin->delete();
        auth()->login($user);
        User::authenticated(auth()->user());

        return User::successLoginRedirect($user);
    }
}
