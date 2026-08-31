# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to 
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.0.1] - 2026-08-31
### Removed
- Removed lingering Font Awesome key prompt from installer, no longer used (can be added optionally under 
`FONT_AWESOME_KEY` environment variable).

## [4.0.0] - 2026-08-27
### Added
- Added PHPUnit tests and partial coverage of existing service classes
- Added Prettier for JS formatting

### Changed
- Updated `ci.yml` to separate steps into separate processes and add unit testing/format check to the pipeline
- Rebranded the project from "Symfony Base" to "Hive"
- Reworked default DB schema
- All tables have a consistent `intId` primary key.
- All tables (join tables excluded) now have `dtmCreated`, `dtmUpdated` columns
- Consistent `ubl` schemas, all now extend `AbstractLookupEntity`

### Fixed
- Fixed styling issue in admin panel causing the header to be compressed when the page content is taller than the 
viewport height

### Removed
- Removed some unnecessary Vue components 

## [3.1.1] - 2026-06-06
### Fixed
- Added FontAwesome import to `admin.ts`

## [3.1.0] - 2026-06-06
### Added
- Added FontAwesome `.npmrc.template` with installer step to prompt for auth token, copy to `.npmrc`, and delete template
- Added installer prompt for FontAwesome Kit ID, replacing development package in `package.json` and `vite.config.ts`

## [3.0.0] - 2026-06-06
### Added
- Added OAuth GitHub/Google authentication via `knpu/oauth2-client-bundle`
- Added `Core.ublOauthProvider` table and entity for provider definitions
- Added `Authentication.tblUserOauth` join table for multi-provider user linking
- Added `OAuthAuthenticator` handling OAuth callback, user lookup, and account linking
- Added OAuth sign-in buttons with icon/label to the authentication page
- Added TypeScript `OAUTH_PROVIDERS` constants driving button rendering
- Added `liip/monitor-bundle` for built-in health checks
- Default home page now includes a link to the health checks
- Default home page now includes a link to the admin panel for logged in admin users

### Changed
- Replaced custom health check controller action with Liip Monitor `doctrine_dbal` check
- Updated `.php-cs-fixer.php` to exclude `config/reference.php`
- Change capitalisation of the `DEFAULT_URI` environment variable in the installer

### Removed
- Removed custom `checkDatabaseConnection` action from `HealthCheckController`
- Removed custom `HealthCheckController` and Vue components

## [2.3.6] - 2026-05-23
### Added
- Add new release workflow

## [2.3.5] - 2026-05-23
### Added
- Added new GitHub CI workflow
- Add an `AGENTS.md` for base project information

### Changed
- Updated PHP version requirements to 8.4+

### Removed
- Remove BitBucket pipeline script

## [2.3.4] - 2026-05-23
### Changed
- Updated the repository link in the welcome template

## [2.3.3] - 2026-05-22
### Fixed
- Hotfix for invalid arguments in doctrine config on project install

## [2.3.2] - 2026-05-21
### Fixed
- Hotfix for invalid arguments in doctrine config on project install

## [2.3.1] - 2026-05-21
### Changed
- Updated all required Symfony packages to 8.0+

## [2.3.0] - 2026-04-08
### Added
- Added default admin user creation to the database setup command, using new environment variables
- Added additional output to the installer command to reflect new admin user credentials
- Added new admin UI including controllers, API and Vue components

### Changed
- Code improvements in `Installer.php`

## [2.2.0] - 2026-04-02
### Added
- Added CSRF protection to all existing forms
- Added resend verification email button and functionality
- Added a new `dtmVerified` column to the verification entity 
- Implemented time limit between token requests

### Changed
- Renamed `VerificationToken` to `EmailVerificationToken` to be more explicit
- No longer deletes the verification token on successful verification, marks `dtmVerified` instead (for better traceability)

## [2.1.0] - 2026-03-30
### Added
- Added `Role` and `Permission` entities to support authorisation checks
- Added new controller and endpoint for user account overview: `UserAccountController` and `/user/account`
- Added support for static image assets in Vue/TS; will now build any explicitly imported images to `public/assets/images` + 
  ensured image loading works correctly for both `vite:dev` and `vite:build`

### Changed
- Improved the styling of the default error page
- Reworked index to use components for logged in/logged-out user cards

## [2.0.2] - 2026-03-28
### Changed
- Updated default Favicon
- Updated default icon logo displayed on app index

### Fixed
- Fixed login/registration form submissions when database is not yet configured
- Fix to ensure that `vite.config.template.ts` is deleted on correctly app install

## [2.0.1] - 2026-03-28
### Added
- Added new `VITE_SERVER_PORT` and `VITE_LOCAL_PORT` environmental variables

### Fixed
- Fixed critical path issue in `Installer.php`

## [2.0.0] - 2026-03-28
### Added
- Added Vue.js
- Added HMR support
- Added initial database setup command `bin/console app:database:setup`
- Added `ErrorController` and custom error pages
- Added `CHANGELOG` for tracking changes to the project
- Added ESLint and configured for TypeScript/Vue
- Added a Bitbucket pipeline script including PHP Stan, PHP CS Fixer and ESLint steps

### Changed
- Swapped Webpack for Vite
- Refactored database setup SQL to allow processing with PHP
- Renamed `Auth` schema to `Authentication`
- Renamed '_Core' schema to 'Core'
- Refactored Authentication system to use Vue
- Improved default styling

### Removed
- Removed Webpack
- Removed unnecessary custom CSS

---

Versions 1.0.0 and below are not included here since this CHANGELOG was created in a later version.