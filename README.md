# Internet Guru Laravel User

Internet Guru Laravel User is a library that provides seamless integration with various social authentication providers. It stores the user's social identity in the database and allows the user to link multiple social identities to a single account. It also provides PIN-based login via email and a full user management UI.

| Branch  | Status | Code Coverage |
| :------------- | :------------- | :------------- |
| Main | ![tests](https://github.com/internetguru/laravel-user/actions/workflows/test.yml/badge.svg?branch=main) | ![coverage](https://raw.githubusercontent.com/internetguru/laravel-user/refs/heads/badges/main-coverage.svg) |
| Staging | ![tests](https://github.com/internetguru/laravel-user/actions/workflows/test.yml/badge.svg?branch=staging) | ![coverage](https://raw.githubusercontent.com/internetguru/laravel-user/refs/heads/badges/staging-coverage.svg) |
| Dev | ![tests](https://github.com/internetguru/laravel-user/actions/workflows/test.yml/badge.svg?branch=dev) | ![coverage](https://raw.githubusercontent.com/internetguru/laravel-user/refs/heads/badges/dev-coverage.svg) |

## Table of Contents

- [Features and Terminology](#features-and-terminology)
- [Installation](#installation)
- [Configuration](#configuration)
- [Roles](#roles)
- [Socialite Providers](#socialite-providers)
- [Demo Mode](#demo-mode)
- [PIN Login](#pin-login)
- [Language and Locale](#language-and-locale)
- [User Management](#user-management)
- [Blade Components](#blade-components)
- [User Preferences](#user-preferences)
- [Association History](#association-history)
- [User Policy](#user-policy)
- [IgUserSeeder](#iguserseeder)
- [Publishing](#publishing)
- [E2E Tests](#e2e-tests)
- [License & Commercial Terms](#license--commercial-terms)

## Features and Terminology

- **Account** – application user account.
- **Identity** – provider & provider_user_id.
- **Register** – create a new account with a new identity linked to it.
- **Connect** – link a new identity to the current account.
- **Disconnect** – unlink an existing identity from the current account.
- **Transfer** – unlink an existing identity from one account and link it to the current one.
- **Automatic account** – an account created programmatically, e.g. during data import, that has never been logged into; `created_by` equals its own `id` and `logged_at` is `null`.

## Installation

1. Install the package via Composer:

    ```sh
    composer require internetguru/laravel-user
    ```

2. Publish and run the migration files:

    ```sh
    php artisan vendor:publish --provider="InternetGuru\LaravelUser\LaravelUserServiceProvider" --tag="ig-user:migrations"
    php artisan migrate
    ```

3. Set required session configuration in `config/session.php`:

    ```php
    'expire_on_close' => true,
    'lifetime' => 120,
    ```

    The service provider throws an exception in debug mode or logs a warning in production if these values are not set correctly.

## Configuration

### Environment Variables

| Variable | Default | Description |
|---|---|---|
| `GOOGLE_CLIENT_ID` | — | Google OAuth client ID |
| `GOOGLE_CLIENT_SECRET` | — | Google OAuth client secret |
| `GOOGLE_REDIRECT_URI` | — | Google OAuth redirect URI |
| `SEZNAM_CLIENT_ID` | — | Seznam OAuth client ID |
| `SEZNAM_CLIENT_SECRET` | — | Seznam OAuth client secret |
| `SEZNAM_REDIRECT_URI` | — | Seznam OAuth redirect URI |
| `AUTH_DEMO` | `false` | Enable demo login — no password, user selected from list |
| `LANG_DOMAINS` | `""` | Comma-separated `lang:domain` pairs, e.g. `cs:example.cz,da:example.dk` |

### Disabling a Provider

Set `enabled` to `false` in `config/services.php` for any provider to hide it from all login/connect buttons:

```php
'google' => [
    'enabled' => false,
    ...
],
```

## Roles

The package ships with a five-level `Role` enum. Applications can override `User::roles()` to return a custom enum.

| Role | Level | Icon |
|---|---|---|
| `CUSTOMER` | 10 | `fa-user` |
| `OPERATOR` | 20 | `fa-user-nurse` |
| `AUDITOR` | 30 | `fa-user-shield` |
| `MANAGER` | 40 | `fa-user-tie` |
| `ADMIN` | 50 | `fa-user-gear` |

Each case exposes `level()`, `icon()`, and `translation()` methods.

### Dynamic Role Checks

The `User` model provides magic `is{Role}()` and `is{Role}Plus()` methods based on the configured roles enum:

```php
$user->isAdmin();         // true if role === ADMIN
$user->isManagerPlus();   // true if role level >= MANAGER level
$user->isOperatorPlus();  // true if role level >= OPERATOR level
```

### Role Helpers

```php
User::roles()::cases();        // all Role cases
User::publicRolesArray();      // all cases except ADMIN
User::roleOptions();           // [['id' => 'manager', 'name' => 'Manager'], ...]
```

## Socialite Providers

Built-in providers: **Google** and **Seznam**. Any other Socialite-compatible provider can be added by the application.

The `socialite.action` route accepts a `provider` and an `action`: `login`, `loginAndConnect`, `register`, `connect`, or `disconnect`. Use `socialite.callback` for the OAuth return URL.

### Routes

| Route name | URI | Description |
|---|---|---|
| `login` | GET `/login` | Unified login page |
| `logout` | GET `/logout` | Log out |
| `pin-login.form` | POST `/pin-login/send` | Send PIN email |
| `pin-login.verify` | GET `/pin-login/verify` | Show PIN entry form |
| `pin-login.verify.submit` | POST `/pin-login/verify` | Verify PIN — throttled: 5 per 10 minutes |
| `socialite.action` | GET `/socialite/{provider}/{action}` | Redirect to provider |
| `socialite.callback` | GET `/socialite/{provider}/{action}/callback` | Handle provider callback |
| `users.index` | GET `/users` | User list — managers and above |
| `users.show` | GET `/users/{user}` | User detail |
| `users.update` | POST `/users/{user}` | Update name, email, phone, or role |

### Usage Examples

```blade
{{-- Socialite login/register/connect buttons --}}
<x-ig-user::buttons action="login" :showRemember="true" />
<x-ig-user::buttons action="login" :showRemember="false" />
<x-ig-user::buttons action="register" />
<x-ig-user::buttons action="connect" />

{{-- Direct disconnect link --}}
<a href="{{ route('socialite.action', [
    'provider' => InternetGuru\LaravelUser\Enums\Provider::GOOGLE,
    'action' => InternetGuru\LaravelUser\Enums\ProviderAction::DISCONNECT,
]) }}">Disconnect Google</a>
```

## Demo Mode

Set `AUTH_DEMO=true` to enable demo login. The login page switches to `login-demo` view, which lists all non-automatic users sorted by role from highest to lowest for one-click login — no password required. Useful for staging environments.

```env
AUTH_DEMO=true
```

The list is provided by `User::getDemoUsers()`, which returns all non-automatic users sorted by role level descending.

## PIN Login

PIN login allows users to authenticate with a 6-digit PIN sent to their email address. The PIN is prefixed with `IG-` in the UI, for example `IG-123456`.

### Flow

1. User submits their email on the unified `/login` page.
2. The server sends a PIN email and redirects to `/pin-login/verify`.
3. User enters the PIN in the 6-box input; a hidden field assembles the full value.
4. On success, the user is logged in and redirected.

### Settings

| Setting | Value |
|---|---|
| PIN lifetime | 10 minutes |
| Resend throttle | 1 minute |
| Verify throttle | 5 attempts per 10 minutes |
| PIN format | `IG-` prefix + 6-digit numeric code |

### Send Form Options

- **Remember me** – persists the session beyond the browser close.
- **Create account if not found** – when checked, a new account is created for unknown emails.

### reCAPTCHA

The PIN send form is protected by reCAPTCHA v3. Ensure `laravel-common` reCAPTCHA is configured.

## Language and Locale

The `SetAppLocale` middleware, registered automatically in the `web` group, handles language detection and persistence.

### Priority Order

1. Explicit `?lang=` query parameter.
2. Authenticated user's `lang` column.
3. Session-stored locale.
4. Browser `Accept-Language` header — Slovak falls back to Czech if Czech is configured.
5. `app.locale` config default.

### Lang Domains

Map languages to dedicated domains via `LANG_DOMAINS`:

```env
LANG_DOMAINS=cs:example.cz,da:example.dk
```

- Requests on a lang domain always enforce that domain's language.
- When a user switches to a language that has a dedicated domain, they are redirected there.
- When a user switches to a language without a dedicated domain while on a lang domain, they are redirected to `app.www`.

Language is saved to the authenticated user's `lang` column on every explicit change.

## User Management

The package provides a user list at `/users` and a user detail page at `/users/{user}`, built on `laravel-model-browser`. Access is controlled by `UserPolicy`.

Users can update their own `name`, `email`, `phone`, and `role` via POST to `/users/{user}`. Role changes are subject to the `setRole` policy.

### Automatic Accounts

An account is considered _automatic_ when `created_by === id` and `logged_at IS NULL`. These accounts are hidden from `User::summary()` and `getDemoUsers()`. When a user registers via PIN login with the "create account" option, reusing an existing automatic account converts it to a regular account.

## Blade Components

### `<x-ig-user::buttons>`

Renders socialite provider buttons for a given action.

| Prop | Default | Description |
|---|---|---|
| `providers` | `User::providers()::enabledCases()` | List of enabled providers |
| `action` | `ProviderAction::LOGIN` | Action: login, register, connect |
| `prev_url` | `User::getPreviousUrl()` | URL to redirect to after auth |
| `showRemember` | `false` | Show "Remember me" checkbox |
| `disabled` | `false` | Disable all buttons |

### `<x-ig-user::user-menu>`

Dropdown menu for authenticated users showing name, role icon, link to user detail, and logout. Shows a login link for guests.

### `<x-ig-user::pin-input>`

Alpine.js-powered 6-box PIN input with paste, backspace, and arrow key support.

| Prop | Default | Description |
|---|---|---|
| `name` | `'pin'` | Hidden input field name |
| `length` | `6` | Number of digit boxes |
| `prefix` | `'IG-'` | Visual prefix label |

## User Preferences

The `user_preferences` table provides a simple key-value store per user.

```php
$user->setPreference('theme', 'dark');
$theme = $user->getPreference('theme', 'light'); // 'dark'
```

## Association History

The `User` model uses the `AssociationHistory` trait from `laravel-common`. Changes to the following fields are tracked automatically:

`name`, `email`, `phone`, `role`, `lang`, `socialite`

The history is displayed on the user detail page, visible to managers and above.

## User Policy

| Gate | Description |
|---|---|
| `crud` | User can edit themselves; admins can edit all; managers can edit users with lower/equal roles |
| `viewAny` | Managers and above can view the user list |
| `administrate` | Managers and above |
| `setRole` | Admins can set any role; managers can set roles up to their own level |

Publish the default policy to customise it:

```sh
php artisan vendor:publish --provider="InternetGuru\LaravelUser\LaravelUserServiceProvider" --tag="ig-user:policies"
```

## IgUserSeeder

The package ships with `IgUserSeeder` for seeding the Internet Guru team accounts with Google/Seznam socialites and `ADMIN` role. Use it in your `DatabaseSeeder`:

```php
$this->call(\InternetGuru\LaravelUser\Database\Seeders\IgUserSeeder::class);
```

## Publishing

| Tag | Destination | Description |
|---|---|---|
| `ig-user:migrations` | `database/migrations/` | Database migrations |
| `ig-user:translations` | `lang/vendor/ig-user/` | Language files — cs, en, da |
| `ig-user:views` | `resources/views/vendor/ig-user/` | Blade views |
| `ig-user:policies` | `app/Policies/` | UserPolicy |

```sh
php artisan vendor:publish --provider="InternetGuru\LaravelUser\LaravelUserServiceProvider" --tag="ig-user:translations"
php artisan vendor:publish --provider="InternetGuru\LaravelUser\LaravelUserServiceProvider" --tag="ig-user:views"
```

## E2E Tests

The package includes Playwright E2E tests via `laravel-common` test helpers. Register them in your Playwright config:

```js
import { registerUserTests } from 'path/to/laravel-user/e2e';

registerUserTests(test, { languages: ['en', 'cs'], demo: true });
```

### Options

| Option | Type | Description |
|---|---|---|
| `languages` | `string[]` | Languages to test — login/logout per language |
| `demo` | `boolean` | Include demo login flow tests |

## License & Commercial Terms

### License

Copyright © 2026 **Internet Guru**

This software is licensed under the [Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA 4.0)](http://creativecommons.org/licenses/by-nc-sa/4.0/) license.

> **Disclaimer:** This software is provided "as is", without warranty of any kind, express or implied. In no event shall the authors or copyright holders be liable for any claim, damages or other liability.

### Commercial Use

The standard CC BY-NC-SA license prohibits commercial use. If you wish to use this software in a commercial environment or product, we offer **flexible commercial licenses** tailored to:

* Your company size.
* The nature of your project.
* Your specific integration needs.

**Note:** In many instances, especially for startups or small-scale tools, this may result in no fees being charged at all. Please contact us to obtain written permission or a commercial agreement.

**Contact for Licensing:** [info@internetguru.io](mailto:info@internetguru.io)

### Professional Services

Are you looking to get the most out of this project? We are available for:

* **Custom Development:** Tailoring the software to your specific requirements.
* **Integration & Support:** Helping your team implement and maintain the solution.
* **Training & Workshops:** Seminars and hands-on workshops for your developers.

Reach out to us at [info@internetguru.io](mailto:info@internetguru.io) — we are more than happy to assist you!
