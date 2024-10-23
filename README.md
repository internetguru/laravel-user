# Internet Guru Laravel Socialite

Internet Guru Laravel Socialite is a library that provides seamless integration with various social authentication providers. It stores the user's social identity in the database and allows the user to link multiple social identities to a single account. It also provides temporary login link to the user's email address.

## Features and terminology

- **Account** – application user account.
- **Identity** – provider & provider_user_id.
- **Register** – create a new account with a new identity linked to it.
- **Connect** – link a new identity to the current account.
- **Disconnect** – unlink an existing identity from the current account.
- **Transfer** – unlink an existing identity from one account and link it to the current one.

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
