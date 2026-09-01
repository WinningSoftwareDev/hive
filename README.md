# Hive

<p>
<img src="https://img.shields.io/badge/Version-4.1.0-blue" alt="Version 4.1.0">
<img src="https://img.shields.io/badge/License-GPL--3.0--or--later-40adbc" alt="License GPL-3.0-or-later">
<img src="https://img.shields.io/badge/Symfony-8.0.13-black?logo=symfony" alt="Symfony 8.0.13">
</p>

A **highly opinionated**, production-ready Symfony 8 starter template designed for rapid application development with 
a modern frontend stack.

## 🚀 Features

* **Core:** Symfony 8 + Doctrine ORM/DBAL.
* **Templating:** [Latte](https://latte.nette.org/) — with custom Vite integration for **Instant Live-Reload**.
* **Frontend:** Vue.js 3, Vite, TailwindCSS, TypeScript, and SCSS.
* **Auth System:** Full "out-of-the-box" flow (Login, Registration, Password Reset, Email Verification, OAuth).
* **Email Builder:** A database-driven utility for generating templated HTML emails.
* **Quality Gates:** Pre-configured PHPStan (Max Level), PHP-CS-Fixer, and ESLint.

![index.png](screenshots/index.png)
![authentication.png](screenshots/authentication.png)
![error-page.png](screenshots/error-page.png)
![health-checks.png](screenshots/health-checks.png)

## 🛠 Installation
### Create your project
```bash
composer create-project winningsoftware/hive my-project
```

This will download the Hive project to a new `my-project` directory inside your current working directory. 

This also runs the installer as part of the post create project command which creates the required directories, copies 
the relevant config files, cleans up unnecessary files and runs `composer install` - so you don't need to run this 
manually.
    
### Configure your environment
Update your `.env` file to suit your application/development environment. See the "Environment Configuration" section
below.

### Install JavaScript dependencies
```bash
npm install
```

### Database initialisation
Check the script at `data/setup.sql` to see what SQL will be executed by the following command:

```bash
php bin/console app:database:setup
# Or manually import data/setup.sql
```

## ⚙️ Environment Configuration

Before running the app, update your `.env` file with the following key variables:

| Variable                     | Default / Example         | Description                                               |
|:-----------------------------|:--------------------------|:----------------------------------------------------------|
| `APP_NAME`                   | `My App`                  | The name of your application                              |
| `DEFAULT_URI`                | `http://localhost`        | Used for generating absolute URLs and OAuth redirect URLs |
| `DB_HOST`                    | `localhost`               | Database host (use `mysql` if using Docker)               |
| `DB_PORT`                    | `3306`                    | Database port                                             |
| `DB_USER`                    | `root`                    | Database user                                             |
| `DB_PASSWORD`                | `docker`                  | Database password                                         |
| `ADMIN_USER`                 | `admin@my-app.com`        | Default admin email (created by `app:database:setup`)     |
| `ADMIN_PASSWORD`             | `noodlepot`               | Default admin password                                    |
| `MAILER_DSN`                 | `smtp://mailcatcher:1025` | SMTP connection string                                    |
| `MAIL_FROM_NAME`             | —                         | Sender name for outgoing emails                           |
| `MAIL_FROM_ADDRESS`          | `info@my-app.com`         | Sender address for outgoing emails                        |
| `VITE_SERVER_PORT`           | `3000`                    | Vite dev server port                                      |
| `VITE_LOCAL_PORT`            | `3000`                    | Local port mapped to Vite server (e.g. in Docker)         |
| `USE_HMR`                    | `false`                   | Set to `true` to enable Vite Hot Module Replacement       |
| `OAUTH_GITHUB_CLIENT_ID`     | —                         | GitHub OAuth App client ID                                |
| `OAUTH_GITHUB_CLIENT_SECRET` | —                         | GitHub OAuth App client secret                            |
| `OAUTH_GOOGLE_CLIENT_ID`     | —                         | Google OAuth 2.0 client ID                                |
| `OAUTH_GOOGLE_CLIENT_SECRET` | —                         | Google OAuth 2.0 client secret                            |

> **Note**: The OAuth redirect URL is constructed from your `DEFAULT_URI` env var. Ensure `DEFAULT_URI` uses the correct 
> scheme (`https` in production) so the redirect URL matches what you registered with the provider.

### Obtaining OAuth Credentials

#### GitHub
1. Go to **Settings > Developer settings > [OAuth Apps](https://github.com/settings/developers)** and click **New OAuth App**.
2. Fill in:
   - **Application name:** Your app name
   - **Homepage URL:** Your app's URL (e.g. `https://my-app.com`)
   - **Authorization callback URL:** `https://my-app.com/connect/github/check`
3. Click **Register application**.
4. Copy the **Client ID** and generate a **Client Secret**. Set them as `OAUTH_GITHUB_CLIENT_ID` and `OAUTH_GITHUB_CLIENT_SECRET` in your `.env`.

#### Google
1. Go to the [Google Cloud Console](https://console.cloud.google.com/), create or select a project.
2. Navigate to **APIs & Services > [Credentials](https://console.cloud.google.com/apis/credentials)**.
3. Click **Create Credentials > OAuth client ID**.
4. Set **Application type** to **Web application**, add your callback URL under **Authorized redirect URIs**: `https://my-app.com/connect/google/check`.
5. Click **Create**. Copy the **Client ID** and **Client Secret**. Set them as `OAUTH_GOOGLE_CLIENT_ID` and `OAUTH_GOOGLE_CLIENT_SECRET` in your `.env`.

## 💻 Development Workflow
This template supports both local and containerized development. Choose the method that fits your environment.

### 1. Running the Project (Local)
The simplest way to get started is using PHP's built-in server:
* **Terminal A (PHP):** `php -S localhost:8000 -t public/`
* **Terminal B (Vite):** `npm run vite:dev`
* **Config:** Set `USE_HMR=true` and `DEFAULT_URI=http://localhost` in your `.env`.

### 2. Docker Setup (Recommended)
For a production-like environment, I recommend using **Docker**. A standard setup should include:
* **PHP-FPM** (8.4+)
* **Server (Nginx/Apache/Caddy etc)**
* **MySQL 8.0+**
* **Mailcatcher/Mailtrap** (for local email testing)

> If using a custom Docker setup with an HTTPS reverse proxy, use the commented-out `server` block in `vite.config.ts` 
> to map your certificates and enable `wss` for HMR. You will need to manually copy the certificate and key from your 
> reverse proxy container into the referenced locations in your PHP container.

### 3. Hot Module Replacement (HMR)
HMR is configured for both Vue/CSS assets and Latte templates.
* **Local Dev:** The default `server` block in `vite.config.ts` works out of the box.
* **Containerized Dev:** If running over plain HTTP in Docker, ensure `server.https` is `false` and `server.hmr.protocol` 
is set to `ws`.

### 4. Quality Control
Run these commands before committing to maintain high standards:

| Tool             | Command          | Description                      |
|:-----------------|:-----------------|:---------------------------------|
| **PHPStan**      | `composer stan`  | Static analysis (Level: Max)     |
| **PHP-CS-Fixer** | `composer cs`    | Check coding standards (dry-run) |
| **PHPUnit**      | `composer test`  | Run unit tests                   |
| **ESLint**       | `npm run lint`   | Lint Vue and TypeScript files    |
| **Prettier**     | `npm run format` | Format asset files               |

## 📂 Project Structure
This template uses a modular, domain-oriented structure. The `src/` directory handles PHP logic, while `assets/scripts/` 
organizes Vue components and frontend utilities into feature-based plugins.

```text
src/
├── Administration/ # Module: Admin panel (Vue SPA) — controllers & API
├── Application/    # Domain: Where you build your app logic
├── Authentication/ # Module: Self-contained User & Security logic
├── Core/           # Internal: Base classes and framework extensions
└── Kernel.php

assets/
├── scripts/
│   ├── Core/       # Frontend utilities (ComponentLoader, FlashHandler)
│   ├── Plugin/     
│   │   ├── AppCore/         # Homepage UI (IntroCard and sub-components)
│   │   └── AuthCore/        # Authentication UI components
│   └── app.ts      # Main JS entry point
└── styles/         # CSS and Tailwind directives
```

## 🗄 Database Schema

This project uses a **multi-schema approach** to maintain strict separation between the Authentication system and other 
areas of your application.

The `php bin/console app:database:setup` command executes `data/setup.sql`, which creates the following:

* **`Authentication` Schema**: Contains `tblUser`, `tblEmailVerificationToken`, `tblPasswordResetToken`, `tblRole`, 
  `tblPermission`, `tblRolePermission`, `tblUserRole`, and `tblUserOauth`.
* **`Core` Schema**: Contains `ublEmailType` and `ublOauthProvider` (look-up tables).

> **IMPORTANT**: Before running the setup command, review `data/setup.sql`. If your database user does not have `CREATE SCHEMA` 
> permissions, or if you prefer a different database setup, you will need to modify this script and the corresponding 
> Doctrine Entity mappings. You'll need to expose the relevant port (3000 by default) from your PHP container.

## 📧 Email System

The built-in `EmailBuilder` utility allows you to generate emails by referencing a "Handle" stored in the `Core.ublEmailType` table.

**Example Usage:**
```php
$email = $this->emailBuilder->getEmail(
    EmailType::VERIFY_EMAIL_ADDRESS,
    $user->getEmail(),
    [
        'verificationUrl' => $this->generateUrl('authenticate_verify_email', ['token' => $token]),
    ]
);
$this->mailer->send($email);
```

## 🛣 Available Routes

| Namespace              | Name                                     | Method       | Path                                      |
|:-----------------------|------------------------------------------|--------------|-------------------------------------------|
| **App\Application**    | `app_index`                              | `GET`        | `/`                                       |
| **App\Authentication** | `authenticate`                           | `GET`        | `/authenticate`                           |
| **App\Authentication** | `authenticate_request_password_reset`    | `GET`,`POST` | `/authenticate/password-reset`            |
| **App\Authentication** | `authenticate_password_reset`            | `GET`,`POST` | `/authenticate/password-reset/reset`      |
| **App\Authentication** | `authenticate_login`                     | `GET`,`POST` | `/authenticate/login`                     |
| **App\Authentication** | `authenticate_logout`                    | `GET`        | `/authenticate/logout`                    |
| **App\Authentication** | `authenticate_get_logged_in_user`        | `GET`        | `/authenticate/current-user`              |
| **App\Authentication** | `authenticate_register`                  | `POST`       | `/authenticate/register`                  |
| **App\Authentication** | `authenticate_verify_email`              | `GET`        | `/authenticate/verify`                    |
| **App\Authentication** | `authenticate_resend_verification_email` | `POST`       | `/authenticate/resend-verification-email` |
| **App\Authentication** | `user_account`                           | `GET`        | `/user/account`                           |
| **App\Authentication** | `connect_oauth_start`                    | `GET`        | `/connect/{service}`                      |
| **App\Authentication** | `connect_github_check`                   | `GET`        | `/connect/github/check`                   |
| **App\Authentication** | `connect_google_check`                   | `GET`        | `/connect/google/check`                   |
| **App\Administration** | `admin_entry`                            | `ANY`        | `/admin/{vue_routing}`                    |
| **Liip Monitor**       | `liip_monitor_health_interface`          | `ANY`        | `/monitor/health/`                        |

> Run `php bin/console debug:router` for the full list of included endpoints.

## 🔧 Customizing

* **Homepage:** The main entry point is defined in `src/Application/IndexController.php` with the template at 
`templates/application/index.latte`. This template renders a single `IntroCard` Vue component, which acts as a wrapper 
for various internal components. You can find and modify these in `assets/scripts/Plugin/AppCore`.
* **Authentication:** All backend logic resides in `src/Authentication`. This includes Login, Registration, Password 
Reset, and Email Verification.
* **Auth UI:** For the frontend side of the built-in system, this project uses Vue components found in the 
`assets/scripts/Plugin/AuthCore` directory.
* **OAuth Providers:** To add or remove OAuth sign-in options, update the `Core.ublOauthProvider` seed data in `data/setup.sql`, create a `KnpU\OAuth2ClientBundle` client config in `config/packages/knpu_oauth2_client.yaml`, register the callback route in `config/routes.yaml`, and add the provider entry to the `OAUTH_PROVIDERS` array in `assets/scripts/Plugin/AuthCore/Constant/OauthProvider.ts`.
* **Styles:** Global styles, Tailwind directives, and SCSS variables are managed in `assets/styles/app.css`.

## 🐳 Docker & Advanced Setup

The recommended approach for a production-like environment is a containerized setup (PHP, Nginx, MySQL, and Mailcatcher).

If you are using a custom Docker setup, ensure you update the `server` block in `vite.config.ts` to use the commented out 
section. If you're using plain HTTP inside your containers, remove `server.https` and set `server.hmr.protocol` to `ws`.