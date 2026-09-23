# Laravel User (internetguru/laravel-user)

Accounts, sign-in (Google, Seznam, e-mailed PIN, demo), roles, locale and the user management screens. The full reference is `vendor/internetguru/laravel-user/README.md`.

## Accounts and roles

- The application's `App\Models\User` extends `InternetGuru\LaravelUser\Models\User`. Put app-specific relations and methods there, never in the package.
- Roles are the five-level `InternetGuru\LaravelUser\Enums\Role`: `CUSTOMER` 10, `OPERATOR` 20, `SUPERVISOR` 30, `MANAGER` 40, `ADMIN` 50. An application may swap the enum through `User::roles()`, so reach cases with `User::roles()::MANAGER` rather than hard-coding the package enum.
- Check roles with the magic methods `$user->isManager()` (exactly) and `$user->isManagerPlus()` (that level or higher). Compare levels, never role names. `User::publicRolesArray()` leaves out `ADMIN`, and `User::roleOptions()` feeds a select.
- `Role` implements laravel-common's `HasLabel`: show a role with `$role->toLabelHtml()`. In a user list use `formatUserRoleLabel`; `formatUserRole` stays plain text for sorting and export. Don't add an application-side role formatter.
- Authorization goes through policies. `UserPolicy` provides `crud`, `viewAny`, `administrate`, `setRole`, `merge` and `viewRoleList`; publish it (`--tag=ig-user:policies`) only to change it.
- `/role-list` documents every policy ability per role automatically. Name a new ability with the flat translation key `'{Policy}@{ability}'` in `lang/<locale>/role-list.php`, e.g. `'MachinePolicy@manage' => 'Manage machines'`.

## Sign-in and locale

- Routes: `login`, `logout`, `pin-login.*`, `socialite.action` / `socialite.callback`, `users.index`, `users.show`, `users.update`, `role-list`.
- `AUTH_LOGIN_ENABLED=false` turns every way in into a 404 while keeping the routes registered. `AUTH_DEMO=true` lists users for one-click sign-in. `AUTH_MERGE_ENABLED=true` allows merging accounts.
- An *automatic account* (`created_by === id`, `logged_at` null) was created by the system and never signed in to; demo lists and `User::summary()` leave it out.
- The `SetAppLocale` middleware picks the locale from `?lang=`, the user's `lang` column, the session, `Accept-Language`, then `app.locale`. `LANG_DOMAINS` maps languages to domains. Don't set the locale by hand in controllers.
- The package requires `session.expire_on_close = true` and `session.lifetime = 120`, and throws in debug mode otherwise.

## Views and assets

- Components: `<x-ig-user::buttons action="login|register|connect" />`, `<x-ig-user::user-menu />`, `<x-ig-user::pin-input />`, `<x-ig-user::system-notice />`.
- Views are namespaced `ig-user::`, overridden in `resources/views/vendor/ig-user`. Translations are overridden in `lang/vendor/ig-user`.
- Sass partials come from `ig::user/…` (variables, socialites, user-detail, pin-input, pin-login, role-list), and JS from `import 'ig::user-js'`, which registers the `mergeSearch` Alpine component.
- Per-user settings: `$user->setPreference($key, $value)` and `$user->getPreference($key, $default)`.

## Tests

- Create accounts with the application's `UserFactory` and its role state (e.g. `User::factory()->withRole(User::roles()::OPERATOR)->create()`), not by setting the `role` column by hand.
