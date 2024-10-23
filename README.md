# Internet Guru Laravel Socialite

Internet Guru Laravel Socialite is a library that provides seamless integration with various social authentication providers.

## Installation

1. Install the package via Composer:

    ```sh
    composer require internetguru/laravel-socialite
    ```

2. Publish the migration files:

    ```sh
    php artisan vendor:publish --provider="InternetGuru\LaravelSocialite\SocialiteServiceProvider" --tag="migrations"
    ```

3. Run the migrations:

    ```sh
    php artisan migrate
    ```

## Configuration

Add your social authentication credentials to your `.env` file:

```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=your-google-redirect-uri

FACEBOOK_CLIENT_ID=your-facebook-client-id
FACEBOOK_CLIENT_SECRET=your-facebook-client-secret
FACEBOOK_REDIRECT_URI=your-facebook-redirect-uri

SEZNAM_CLIENT_ID=your-seznam-client-id
SEZNAM_CLIENT_SECRET=your-seznam-client-secret
SEZNAM_REDIRECT_URI=your-seznam-redirect-uri
```

## Usage Examples

```blade
<x-socialite::buttons action="login" :showRemember="true" />
<x-socialite::buttons action="login" :showRemember="false" />
<x-socialite::buttons action="register"/>
<x-socialite::buttons action="connect"/>

<a href="{{ route('socialite.action', [
    'provider' => InternetGuru\LaravelSocialite\Enums\Provider::GOOGLE,
    'action' => InternetGuru\LaravelSocialite\Enums\ProviderAction::DISCONNECT,
]) }}">Disconnect Google</a>
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
