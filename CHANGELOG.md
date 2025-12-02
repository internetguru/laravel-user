# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [4.0.0-rc.1] - 2025-12-02

## [3.0.0] - 2025-11-14

_Stable release based on [3.0.0-rc.1]._

## [3.0.0-rc.1] - 2025-11-14

### Changed

- Use laravel-common v3 notifications.

## [2.9.6] - 2025-11-11

### Fixed

- Fix tests.

## [2.9.5] - 2025-11-11

### Fixed

- Fallback socialite user missing name to No name.

## [2.9.4] - 2025-11-11

### Fixed

- Redistribute user columns widths.

## [2.9.3] - 2025-11-06

### Fixed

- Fix missing To name when sending User notification.

## [2.9.2] - 2025-11-05

### Fixed

- Add missing WithSocialite trait.

## [2.9.1] - 2025-11-05

### Fixed

- Fix user seeder use.

## [2.9.0] - 2025-11-05

_Stable release based on [2.9.0-rc.1]._

## [2.9.0-rc.1] - 2025-11-05

### Added

- Add IgUserSeeder.

## [2.8.1] - 2025-10-19

### Fixed

- Fix success login redirect message lang.

## [2.8.0] - 2025-10-09

_Stable release based on [2.8.0-rc.1]._

## [2.8.0-rc.1] - 2025-10-09

### Changed

- Separate login and demo-login views and add demo-login title and description translations.

## [2.7.0] - 2025-10-02

_Stable release based on [2.7.0-rc.1]._

## [2.7.0-rc.1] - 2025-10-02

### Changed

- Login and logout preserving current language.

## [2.6.0] - 2025-10-01

_Stable release based on [2.6.0-rc.1]._

## [2.6.0-rc.1] - 2025-10-01

### Added

- Check `session.expire_on_close` and `session.lifetime` in `LaravelUserServiceProvider`.

## [2.5.10] - 2025-10-01

### Changed

- Update error message.

## [2.5.9] - 2025-09-24

### Added

- Add title to user menu icon.

## [2.5.8] - 2025-09-22

### Fixed

- Fix demo login.

## [2.5.7] - 2025-09-22

### Fixed

- Fix return void.

## [2.5.6] - 2025-09-22

### Fixed

- Fix login redirect in demo.

## [2.5.5] - 2025-09-19

### Fixed

- Fix plain text email newlines

## [2.5.4] - 2025-09-19

### Fixed

- Fix plan text email crlf

## [2.5.3] - 2025-09-19

### Added

- Add signature to emails

## [2.5.2] - 2025-09-19

### Fixed

- Fix email blades

## [2.5.1] - 2025-09-19

### Changed

- Simplify an error message

## [2.5.0] - 2025-09-18

_Stable release based on [2.5.0-rc.1]._

## [2.5.0-rc.1] - 2025-09-18

## [2.4.5] - 2025-09-08

### Fixed

- Fix user summary heading.

## [2.4.4] - 2025-09-04

### Fixed

- Make demo login use user success login redirect.

## [2.4.3] - 2025-09-03

### Fixed

- Fix calling providers().

## [2.4.2] - 2025-09-03

### Fixed

- Fix provider buttons.

## [2.4.1] - 2025-09-03

### Fixed

- Fix getting Provider.

## [2.4.0] - 2025-09-03

_Stable release based on [2.4.0-rc.1]._

## [2.4.0-rc.1] - 2025-09-03

### Changed

- Make Provider enum customizable.

## [2.3.4] - 2025-09-02

### Fixed

- Update one time login success message.

## [2.3.3] - 2025-09-02

### Fixed

- Change "Registration successful" to "Registered as :name".

## [2.3.2] - 2025-09-02

### Fixed

- Fix login and connect to first check connected user fallback to email user.

## [2.3.1] - 2025-09-02

### Fixed

- Add missing InteractWithQueue and SerializesModels to TokenAuthNotification.

## [2.3.0] - 2025-08-25

_Stable release based on [2.3.0-rc.1]._

## [2.3.0-rc.1] - 2025-08-25

### Added

- Add danish translation.

## [2.2.5] - 2025-08-18

### Fixed

- Fix login and connect.

## [2.2.4] - 2025-08-18

### Fixed

- Fix socialite login and connect.

## [2.2.3] - 2025-08-17

### Fixed

- Do not show error when already connected on login.

## [2.2.2] - 2025-08-17

### Changed

- Show success login message in login and connect instead of connected message.

## [2.2.1] - 2025-08-17

### Fixed

- Return to prev url instead of back after login and connect.

## [2.2.0] - 2025-08-17

_Stable release based on [2.2.0-rc.1]._

## [2.2.0-rc.1] - 2025-08-17

### Changed

- Change socialite `login` to `loginAndConnect`.

## [2.1.1] - 2025-07-29

### Fixed

- Fix success login redir types.

## [2.1.0] - 2025-07-29

_Stable release based on [2.1.0-rc.1]._

## [2.1.0-rc.1] - 2025-07-29

### Changed

- Separate `successLoginRedirect` to be customizable in `User` model.

## [2.0.6] - 2025-07-24

### Fixed

- Fix one time login return back with input.

## [2.0.5] - 2025-06-26

### Fixed

- Revert last hotfix.

## [2.0.4] - 2025-06-26

### Fixed

- Do not save lang if system is readonly.

## [2.0.3] - 2025-05-28

### Fixed

- Fix missing email footer.

## [2.0.2] - 2025-05-20

### Fixed

- Fix one time login email subject.

## [2.0.1] - 2025-05-15

### Fixed

- Fix laravel common requirement version to ^2

## [2.0.0] - 2025-05-15

_Stable release based on [2.0.0-rc.1]._

## [2.0.0-rc.1] - 2025-05-15

### Changed

- Update `laravel-common` version to `^2`.
- Use `IgMailMessage` in notifications.

## [1.0.0] - 2025-05-09

_Stable release based on [1.0.0-rc.1]._

## [1.0.0-rc.1] - 2025-05-09

### Added

- Use `internetguru/laravel-common` recaptcha.

### Changed

- Change `laravel-common` version to `^1`.
- Change `laravel-model-browser` version to `^1`.

## [0.16.1] - 2025-05-08

### Fixed

- Fix Provider check services config.

## [0.16.0] - 2025-05-08

_Stable release based on [0.16.0-rc.1]._

## [0.16.0-rc.1] - 2025-05-08

## Added

- Allow disabling a provider in the configuration by setting `enabled` to `false`.

## [0.15.1] - 2025-04-25

### Fixed

- Change laravel-common and laravel-model-browser version to `^0`.

## [0.15.0] - 2025-04-17

_Stable release based on [0.15.0-rc.1]._

## [0.15.0-rc.1] - 2025-04-17

### Changed

- Update laravel-common version to `^0.13`.

## [0.14.1] - 2025-04-16

### Fixed

- Fix model browser version to `^0.12`.

## [0.14.0] - 2025-04-16

_Stable release based on [0.14.0-rc.1]._

## [0.14.0-rc.1] - 2025-04-16

### Changed

- Update laravel-common version to `^0.12`.
- Update laravel-model-browser version to `^0.10`.


## [0.13.0] - 2025-04-16

_Stable release based on [0.13.0-rc.1]._

## [0.13.0-rc.1] - 2025-04-16

### Changed

- Update laravel-common version to `^0.11`.
- Update laravel-model-browser version to `^0.9`.

## [0.12.3] - 2025-04-15

### Fixed

- Disable editable on login forms.

## [0.12.2] - 2025-04-11

### Fixed

- Update laravel-common version to `^0.10`.

## [0.12.1] - 2025-04-09

### Fixed

- Update laravel-common version to `^0.9`.

## [0.12.0] - 2025-04-09

_Stable release based on [0.12.0-rc.1]._

## [0.12.0-rc.1] - 2025-04-09

### Added

- Configure user list column widths.

### Changed

- Update laravel-model-browser to `^0.8`.

## [0.11.0] - 2025-04-07

_Stable release based on [0.11.0-rc.1]._

## [0.11.0-rc.1] - 2025-04-07

### Changed

- Update laravel-common to `^0.8`.

## [0.10.6] - 2025-04-07

### Fixed

- Fix routes are not in web middleware group.

## [0.10.5] - 2025-04-03

### Fixed

- Fix redirect to previous page after login.

## [0.10.4] - 2025-04-01

### Fixed

- Update laravel-common to `^0.7`.

## [0.10.3] - 2025-03-24

### Fixed

- Fix getting role in registerUser method.

## [0.10.2] - 2025-03-14

### Fixed

- Adjust connect identity header styles.

## [0.10.1] - 2025-03-14

### Fixed

- Do not format user emails.

## [0.10.0] - 2025-03-14

_Stable release based on [0.10.0-rc.1]._

## [0.10.0-rc.1] - 2025-03-14

### Changed

- Use laravel-common base view instead of own.

## [0.9.1] - 2025-03-13

### Fixed

- Fix missed debug.

## [0.9.0] - 2025-03-13

_Stable release based on [0.9.0-rc.1]._

## [0.9.0-rc.1] - 2025-03-13

### Changed

- Use email templates from `laravel-common`.

## [0.8.0] - 2025-03-13

_Stable release based on [0.8.0-rc.1]._

## [0.8.0-rc.1] - 2025-03-13

### Added

- Add `editable-skip` class to user detail forms.
- Show provider name next to user name in user detail.

### Changed

- Update translations.

### Fixed

- Fix missing registratioín success message.

### Removed

- Remove `primary` after user name in user detail.

## [0.7.0] - 2025-03-04

_Stable release based on [0.7.0-rc.1]._

## [0.7.0-rc.1] - 2025-03-04

### Added

- Add custom unique email validation message.
- Add custom user auth error.

### Changed

- Update translations.
- Do not show user role next to user name in user dropdown menu.
- Make user dropdown menu wider with max width.

## [0.6.0] - 2025-03-04

_Stable release based on [0.6.0-rc.1]._

## [0.6.0-rc.1] - 2025-03-04

### Changed

- Update laravel-model-browser version to `0.6`.

## [0.5.0] - 2025-02-27

_Stable release based on [0.5.0-rc.1]._

## [0.5.0-rc.1] - 2025-02-27

### Changed

- Update laravel-model-browser version to `0.5`.

## [0.4.5] - 2025-02-27

### Fixed

- Fix user detail translation.

## [0.4.4] - 2025-02-27

### Fixed

- Update user detail edit translations.

## [0.4.3] - 2025-02-26

### Fixed

- Fix saving user role validation.

## [0.4.2] - 2025-02-26

### Fixed

- Fix user role select translations.

## [0.4.1] - 2025-02-26

### Fixed

- Fix role select to be dynamic.

## [0.4.1] - 2025-02-26

## [0.4.0] - 2025-02-24

_Stable release based on [0.4.0-rc.1]._

## [0.4.0-rc.1] - 2025-02-24

### Changed

- Update laravel-model-browser version to `0.4`.

## [0.3.0] - 2025-02-23

_Stable release based on [0.3.0-rc.1]._

## [0.3.0-rc.1] - 2025-02-23

### Changed

- Update laravel-model-browser version to `0.3`.

## [0.2.5] - 2025-02-19

### Fixed

- Fix default user list sort.

## [0.2.4] - 2025-02-19

### Fixed

- Add missing user filter.

## [0.2.3] - 2025-02-19

### Fixed

- Fix default role translations.

## [0.2.2] - 2025-02-19

### Fixed

- Fix ig require versions.

## [0.2.1] - 2025-02-19

### Fixed

- Add minimum stability dev.

## [0.2.0] - 2025-02-19

_Stable release based on [0.2.0-rc.1]._

## [0.2.0-rc.1] - 2025-02-19

## [0.1.2] - 2024-11-07

### Fixed

- Rename laravel-auth to laravel-user.

## [0.1.1] - 2024-11-06

### Fixed

- Update composer.

## [0.1.0] - 2024-10-07

_Stable release based on [0.1.0-rc.1]._

## [0.1.0-rc.1] - 2024-10-07

## [0.0.0] - 2024-10-07

### Added

- New changelog file.

[4.0.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v3.0.0
[3.0.0]: https://https://github.com/internetguru/laravel-user/compare/v2.9.6...v3.0.0
[3.0.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v2.9.6
[2.9.6]: https://https://github.com/internetguru/laravel-user/compare/v2.9.5...v2.9.6
[2.9.5]: https://https://github.com/internetguru/laravel-user/compare/v2.9.4...v2.9.5
[2.9.4]: https://https://github.com/internetguru/laravel-user/compare/v2.9.3...v2.9.4
[2.9.3]: https://https://github.com/internetguru/laravel-user/compare/v2.9.2...v2.9.3
[2.9.2]: https://https://github.com/internetguru/laravel-user/compare/v2.9.1...v2.9.2
[2.9.1]: https://https://github.com/internetguru/laravel-user/compare/v2.9.0...v2.9.1
[2.9.0]: https://https://github.com/internetguru/laravel-user/compare/v2.8.1...v2.9.0
[2.9.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v2.8.1
[2.8.1]: https://https://github.com/internetguru/laravel-user/compare/v2.8.0...v2.8.1
[2.8.0]: https://https://github.com/internetguru/laravel-user/compare/v2.7.0...v2.8.0
[2.8.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v2.7.0
[2.7.0]: https://https://github.com/internetguru/laravel-user/compare/v2.6.0...v2.7.0
[2.7.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v2.6.0
[2.6.0]: https://https://github.com/internetguru/laravel-user/compare/v2.5.10...v2.6.0
[2.6.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v2.5.10
[2.5.10]: https://https://github.com/internetguru/laravel-user/compare/v2.5.9...v2.5.10
[2.5.9]: https://https://github.com/internetguru/laravel-user/compare/v2.5.8...v2.5.9
[2.5.8]: https://https://github.com/internetguru/laravel-user/compare/v2.5.7...v2.5.8
[2.5.7]: https://https://github.com/internetguru/laravel-user/compare/v2.5.6...v2.5.7
[2.5.6]: https://https://github.com/internetguru/laravel-user/compare/v2.5.5...v2.5.6
[2.5.5]: https://https://github.com/internetguru/laravel-user/compare/v2.5.4...v2.5.5
[2.5.4]: https://https://github.com/internetguru/laravel-user/compare/v2.5.3...v2.5.4
[2.5.3]: https://https://github.com/internetguru/laravel-user/compare/v2.5.2...v2.5.3
[2.5.2]: https://https://github.com/internetguru/laravel-user/compare/v2.5.1...v2.5.2
[2.5.1]: https://https://github.com/internetguru/laravel-user/compare/v2.5.0...v2.5.1
[2.5.0]: https://https://github.com/internetguru/laravel-user/compare/v2.4.5...v2.5.0
[2.5.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v2.4.5
[2.4.5]: https://https://github.com/internetguru/laravel-user/compare/v2.4.4...v2.4.5
[2.4.4]: https://https://github.com/internetguru/laravel-user/compare/v2.4.3...v2.4.4
[2.4.3]: https://https://github.com/internetguru/laravel-user/compare/v2.4.2...v2.4.3
[2.4.2]: https://https://github.com/internetguru/laravel-user/compare/v2.4.1...v2.4.2
[2.4.1]: https://https://github.com/internetguru/laravel-user/compare/v2.4.0...v2.4.1
[2.4.0]: https://https://github.com/internetguru/laravel-user/compare/v2.3.4...v2.4.0
[2.4.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v2.3.4
[2.3.4]: https://https://github.com/internetguru/laravel-user/compare/v2.3.3...v2.3.4
[2.3.3]: https://https://github.com/internetguru/laravel-user/compare/v2.3.2...v2.3.3
[2.3.2]: https://https://github.com/internetguru/laravel-user/compare/v2.3.1...v2.3.2
[2.3.1]: https://https://github.com/internetguru/laravel-user/compare/v2.3.0...v2.3.1
[2.3.0]: https://https://github.com/internetguru/laravel-user/compare/v2.2.5...v2.3.0
[2.3.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v2.2.5
[2.2.5]: https://https://github.com/internetguru/laravel-user/compare/v2.2.4...v2.2.5
[2.2.4]: https://https://github.com/internetguru/laravel-user/compare/v2.2.3...v2.2.4
[2.2.3]: https://https://github.com/internetguru/laravel-user/compare/v2.2.2...v2.2.3
[2.2.2]: https://https://github.com/internetguru/laravel-user/compare/v2.2.1...v2.2.2
[2.2.1]: https://https://github.com/internetguru/laravel-user/compare/v2.2.0...v2.2.1
[2.2.0]: https://https://github.com/internetguru/laravel-user/compare/v2.1.1...v2.2.0
[2.2.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v2.1.1
[2.1.1]: https://https://github.com/internetguru/laravel-user/compare/v2.1.0...v2.1.1
[2.1.0]: https://https://github.com/internetguru/laravel-user/compare/v2.0.6...v2.1.0
[2.1.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v2.0.6
[2.0.6]: https://https://github.com/internetguru/laravel-user/compare/v2.0.5...v2.0.6
[2.0.5]: https://https://github.com/internetguru/laravel-user/compare/v2.0.4...v2.0.5
[2.0.4]: https://https://github.com/internetguru/laravel-user/compare/v2.0.3...v2.0.4
[2.0.3]: https://https://github.com/internetguru/laravel-user/compare/v2.0.2...v2.0.3
[2.0.2]: https://https://github.com/internetguru/laravel-user/compare/v2.0.1...v2.0.2
[2.0.1]: https://https://github.com/internetguru/laravel-user/compare/v2.0.0...v2.0.1
[2.0.0]: https://https://github.com/internetguru/laravel-user/compare/v1.0.0...v2.0.0
[2.0.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v1.0.0
[1.0.0]: https://https://github.com/internetguru/laravel-user/compare/v0.16.1...v1.0.0
[1.0.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.16.1
[0.16.1]: https://https://github.com/internetguru/laravel-user/compare/v0.16.0...v0.16.1
[0.16.0]: https://https://github.com/internetguru/laravel-user/compare/v0.15.1...v0.16.0
[0.16.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.15.1
[0.15.1]: https://https://github.com/internetguru/laravel-user/compare/v0.15.0...v0.15.1
[0.15.0]: https://https://github.com/internetguru/laravel-user/compare/v0.14.1...v0.15.0
[0.15.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.14.1
[0.14.1]: https://https://github.com/internetguru/laravel-user/compare/v0.14.0...v0.14.1
[0.14.0]: https://https://github.com/internetguru/laravel-user/compare/v0.13.0...v0.14.0
[0.14.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.13.0
[0.13.0]: https://https://github.com/internetguru/laravel-user/compare/v0.12.3...v0.13.0
[0.13.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.12.3
[0.12.3]: https://https://github.com/internetguru/laravel-user/compare/v0.12.2...v0.12.3
[0.12.2]: https://https://github.com/internetguru/laravel-user/compare/v0.12.1...v0.12.2
[0.12.1]: https://https://github.com/internetguru/laravel-user/compare/v0.12.0...v0.12.1
[0.12.0]: https://https://github.com/internetguru/laravel-user/compare/v0.11.0...v0.12.0
[0.12.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.11.0
[0.11.0]: https://https://github.com/internetguru/laravel-user/compare/v0.10.6...v0.11.0
[0.11.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.10.6
[0.10.6]: https://https://github.com/internetguru/laravel-user/compare/v0.10.5...v0.10.6
[0.10.5]: https://https://github.com/internetguru/laravel-user/compare/v0.10.4...v0.10.5
[0.10.4]: https://https://github.com/internetguru/laravel-user/compare/v0.10.3...v0.10.4
[0.10.3]: https://https://github.com/internetguru/laravel-user/compare/v0.10.2...v0.10.3
[0.10.2]: https://https://github.com/internetguru/laravel-user/compare/v0.10.1...v0.10.2
[0.10.1]: https://https://github.com/internetguru/laravel-user/compare/v0.10.0...v0.10.1
[0.10.0]: https://https://github.com/internetguru/laravel-user/compare/v0.9.1...v0.10.0
[0.10.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.9.1
[0.9.1]: https://https://github.com/internetguru/laravel-user/compare/v0.9.0...v0.9.1
[0.9.0]: https://https://github.com/internetguru/laravel-user/compare/v0.8.0...v0.9.0
[0.9.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.8.0
[0.8.0]: https://https://github.com/internetguru/laravel-user/compare/v0.7.0...v0.8.0
[0.8.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.7.0
[0.7.0]: https://https://github.com/internetguru/laravel-user/compare/v0.6.0...v0.7.0
[0.7.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.6.0
[0.6.0]: https://https://github.com/internetguru/laravel-user/compare/v0.5.0...v0.6.0
[0.6.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.5.0
[0.5.0]: https://https://github.com/internetguru/laravel-user/compare/v0.4.5...v0.5.0
[0.5.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.4.5
[0.4.5]: https://https://github.com/internetguru/laravel-user/compare/v0.4.4...v0.4.5
[0.4.4]: https://https://github.com/internetguru/laravel-user/compare/v0.4.3...v0.4.4
[0.4.3]: https://https://github.com/internetguru/laravel-user/compare/v0.4.2...v0.4.3
[0.4.2]: https://https://github.com/internetguru/laravel-user/compare/v0.4.1...v0.4.2
[0.4.1]: https://https://github.com/internetguru/laravel-user/compare/v0.4.0...v0.4.1
[0.4.0]: https://https://github.com/internetguru/laravel-user/compare/v0.3.0...v0.4.0
[0.4.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.3.0
[0.3.0]: https://https://github.com/internetguru/laravel-user/compare/v0.2.5...v0.3.0
[0.3.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.2.5
[0.2.5]: https://https://github.com/internetguru/laravel-user/compare/v0.2.4...v0.2.5
[0.2.4]: https://https://github.com/internetguru/laravel-user/compare/v0.2.3...v0.2.4
[0.2.3]: https://https://github.com/internetguru/laravel-user/compare/v0.2.2...v0.2.3
[0.2.2]: https://https://github.com/internetguru/laravel-user/compare/v0.2.1...v0.2.2
[0.2.1]: https://https://github.com/internetguru/laravel-user/compare/v0.2.0...v0.2.1
[0.2.0]: https://https://github.com/internetguru/laravel-user/compare/v0.1.2...v0.2.0
[0.2.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.1.2
[0.1.0]: https://https://github.com/internetguru/laravel-user/compare/v0.0.0...v0.1.0
[0.1.0-rc.1]: https://github.com/internetguru/laravel-user/releases/tag/v0.0.0
[0.1.2]: https://https://github.com/internetguru/laravel-user/compare/v0.1.1...v0.1.2
[0.1.1]: https://https://github.com/internetguru/laravel-user/compare/v0.1.0...v0.1.1
[0.1.0]: https://https://github.com/internetguru/laravel-socialite/compare/v0.0.0...v0.1.0
[0.1.0-rc.1]: https://github.com/internetguru/laravel-socialite/releases/tag/v0.0.0
[0.0.0]: git log v0.0.0
