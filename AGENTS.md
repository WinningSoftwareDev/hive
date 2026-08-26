# AGENTS.md — Hive

## Project Overview

A highly opinionated, production-ready Symfony 8 project template. Use it to bootstrap new Symfony applications:

```bash
composer create-project winningsoftware/hive my-project
```

- **License**: GPL-3.0-or-later
- **PHP**: >=8.4
- **Backend**: Symfony 8.0, Doctrine ORM 3.x, Latte templating
- **Frontend**: Vue 3, Vite 8, TailwindCSS v4, TypeScript, SCSS

---

## Project Structure

```
src/
├── Application/       # Scaffold your app logic here
├── Authentication/    # Self-contained user & security module
├── Administration/    # Admin panel (Vue SPA) — controllers + API
├── Core/              # Base classes, framework extensions, utilities
└── Kernel.php

assets/
├── scripts/
│   ├── app.ts                    # Public site entry (ComponentLoader + FlashHandler)
│   ├── admin.ts                  # Admin SPA entry (Vue Router app)
│   ├── Core/                     # ComponentLoader, FlashHandler, interfaces
│   ├── Plugin/AppCore/           # Homepage Vue components
│   ├── Plugin/AuthCore/          # Auth UI forms & pages
│   └── Administration/           # Admin SPA pages & components
└── styles/app.css                # Tailwind config + CSS variables

templates/            # Latte templates
├── Core/             # Layouts, headers, footers, error pages, emails
├── Authentication/   # Auth pages (login, register, password reset)
└── Administration/   # Admin shell layout

config/               # Symfony config (services, packages, routes)
data/setup.sql        # Database schema + seed data
```

---

## Key Conventions

### PHP
- **All files** begin with `declare(strict_types=1)`.
- **PHP 8 features**: Attributes (Doctrine/Symfony routing), constructor property promotion, `readonly` classes, `match` expressions, typed constants, arrow functions.
- **Type hints**: Every parameter and return value is explicitly typed. No bare `mixed`.
- **Entity column naming** (Hungarian-style): `int` for integers, `str` for strings, `bol` for booleans, `dtm` for datetimes (e.g. `intUserId`, `strEmail`, `bolActive`, `dtmCreated`). Follow this convention.
- **Multi-schema Doctrine**: Entities are mapped to `Authentication` or `Core` schemas via `#[ORM\Table(schema: '...')]`.
- **Entities** extend `App\Core\Entity\AbstractBaseEntity` (a `#[MappedSuperclass]` with lifecycle callbacks for `createdAt` / `updatedAt`). Do NOT use Hungarian notation for 
entity properties, only SQL columns use Hungarian notation.
- **Repositories** extend `Doctrine\ORM\EntityRepository` — **never** `ServiceEntityRepository`. Use `@extends EntityRepository<EntityType>` PHPDoc for type safety.
- **Controllers** extend `App\Core\Controller\AbstractApplicationController` (which extends `AbstractLatteController` for Latte rendering).
- **Routes**: Attribute-based with `#[Route(path: '...', name: '...', methods: [...])]`. Imported by namespace in `config/routes.yaml`.
- **Services**: Constructor injection. Use `readonly class` for stateless services.
- **DTOs**: Plain classes with Symfony validation attributes (`#[Assert\NotBlank]`, `#[Assert\Email]`, etc.) and getter/setter methods.
- **Forms**: Extend `Symfony\Component\Form\AbstractType`, build fields in `buildForm()`.

### Frontend
- **Vue in Latte**: Components are auto-mounted via `ComponentLoader.watch()`. Register new components in `Plugin/*/_components.ts` and use custom element tags in Latte: `<ComponentName prop-name="value" />`.
- **Admin SPA**: Separate entry (`admin.ts`) — a full Vue Router app mounted at `#admin-root`.
- **Styling**: TailwindCSS v4 via `@use 'tailwindcss'`.

---

## Commands

| Tool                | Command                                             | Description                |
|---------------------|-----------------------------------------------------|----------------------------|
| PHPStan (level max) | `composer stan`                                     | Static analysis            |
| PHP-CS-Fixer        | `composer cs`                                       | Code style check (dry-run) |
| ESLint              | `npm run lint`                                      | Lint Vue/TypeScript        |
| Vite build          | `npm run vite:build`                                | Production frontend build  |
| Vite dev server     | `npm run vite:dev`                                  | HMR-enabled dev server     |
| Database setup      | `php bin/console app:database:setup`                | Create schemas + seed data |
| Cache clear         | `php bin/console cache:clear --env=prod --no-debug` |                            |

---

## Database

- **Multi-schema**: `Authentication` (users, roles, permissions, tokens) and `Core` (email types).
- **No Doctrine Migrations**: This project NEVER uses Doctrine migrations. Schema changes are written manually in SQL. 
New SQL files should be created in `data/{YYYY}/{mm}/YYYY-mm-dd-{i}.sql`.
- **Setup**: Run `php bin/console app:database:setup` which executes `data/setup.sql`, creates default roles 
(`ROLE_USER`, `ROLE_ADMIN`), and seeds an admin user from `ADMIN_USER` / `ADMIN_PASSWORD` env vars.
- **Deviations**: If your DB user lacks `CREATE SCHEMA` permissions, modify `data/setup.sql` and corresponding entity 
schema mappings.

---

## Important Patterns

- Use `$this->renderTemplate('Module/template', [...])` — **not** Twig. Templates are `.latte` files.
- The **EmailBuilder** service (`App\Core\Controller\EmailBuilder`) generates `Symfony\Component\Mime\Email` from 
`Core.ublEmailType` DB records. Add new email types as rows in that table and reference them by handle constant.
- **HMR**: Toggle via `USE_HMR` env var. When enabled, Vite serves assets with hot reload. Config is in `vite.config.ts`.
- **Forms return JSON**: Controllers return `JsonResponse` with `{success, errors?, redirect?}` for XHR/AJAX submissions.
- **Post-install cleanup**: `composer create-project` runs `app:install`, copies `.env.template`, creates cache dirs, 
and removes scaffolding files.

---

## Environment

Key variables in `.env`:

| Variable                      | Purpose                               |
|-------------------------------|---------------------------------------|
| `APP_NAME`                    | Application name                      |
| `DEFAULT_URI`                 | Base URL for absolute link generation |
| `DB_HOST/PORT/USER/PASSWORD`  | Database connection                   |
| `MAILER_DSN`                  | SMTP connection string                |
| `MAIL_FROM_NAME/ADDRESS`      | Default sender                        |
| `ADMIN_USER/PASSWORD`         | Default admin credentials             |
| `USE_HMR`                     | Toggle Vite HMR                       |
| `VITE_SERVER_PORT/LOCAL_PORT` | Vite dev server ports                 |

---

## CI/CD

GitHub Workflow (`.github/workflows/CI.yml`):

1. **PHP 8.4**: `composer install` → `composer cs` → `composer stan`
2. **TypeScript**: `npm install` → `npm run lint`
