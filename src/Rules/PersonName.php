<?php

namespace InternetGuru\LaravelUser\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class PersonName implements ValidationRule
{
    private const MIN_ALPHANUMERICS = 2;

    public static function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $name = Str::squish($value);

        return (bool) preg_match("/^[\p{L}\p{N}][\p{L}\p{M}\p{N} .'\-]*$/u", $name)
            && self::hasEnoughAlphanumerics($name);
    }

    /**
     * A readable name made of the email local part, e.g. "j.k.rowling85+shop@x.com" gives "J. K. Rowling".
     */
    public static function fromEmail(string $email): string
    {
        $localPart = preg_replace('/\+.*/', '', Str::before($email, '@'));
        $name = self::clean($localPart);

        $withoutTrailingDigits = rtrim(preg_replace('/[\p{N}\s]+$/u', '', $name));
        if (self::hasEnoughAlphanumerics($withoutTrailingDigits)) {
            $name = $withoutTrailingDigits;
        }

        if (self::hasEnoughAlphanumerics($name)) {
            return Str::title($name);
        }

        $domainWithoutTld = Str::beforeLast(Str::after($email, '@'), '.');

        return Str::title(self::clean($localPart . $domainWithoutTld));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid($value)) {
            $fail(__('ig-user::user.name.invalid'));
        }
    }

    /**
     * Keeps a period after a standalone word of one or two letters (an initial or abbreviation) and turns any other separator into a space.
     */
    private static function clean(string $value): string
    {
        $value = preg_replace('/(?<![\p{L}\p{N}])(\p{L}{1,2})\./u', "$1\x01 ", $value);
        $value = preg_replace('/[^\p{L}\p{N}\x01]+/u', ' ', $value);

        return Str::squish(str_replace("\x01", '.', $value));
    }

    private static function hasEnoughAlphanumerics(string $value): bool
    {
        return preg_match_all('/[\p{L}\p{N}]/u', $value) >= self::MIN_ALPHANUMERICS;
    }
}
