# Telnet intern attendance

Laravel 13 API and React manager UI for off-grid intern attendance. The phone app is a separate project. Work is tracked in `task.md`. Contributors follow `CONTRIBUTING.md`.

## Requirements

- PHP 8.3 or newer, with the `pdo_pgsql` extension
- Composer
- Node 24 and npm
- Docker, for Postgres and Redis

This machine does not have the PHP Redis extension. The app uses the Predis client instead.

## Local setup

```bash
docker compose up -d
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
# set MANAGER_PASSWORD in .env first, see "Manager sign-in" below
php artisan db:seed
composer run dev
```

The app is at `http://localhost:8000`.

`docker compose` starts Postgres 16 on port 5432 and Redis 7 on port 6379. The database name, user, and password in `.env.example` match that Compose file. They are local placeholders. Replace `SYNC_DEV_TOKEN` before you share a machine.

`composer run dev` starts the PHP server, the queue listener, and Vite together.

## Manager sign-in

Managers sign in at `/login` with Laravel Fortify. Public registration is off, so the only way to get an account is the local seeder.

1. In your own `.env`, replace the `MANAGER_PASSWORD=change-me` placeholder with a password only you know. `MANAGER_EMAIL` and `MANAGER_NAME` can stay as they are. Keep the password in double quotes, for example `MANAGER_PASSWORD="my-password"`. Without quotes, a `#` or a space cuts the password short.
2. Run `php artisan db:seed` (or `php artisan db:seed --class=ManagerSeeder`).
3. Sign in at `http://localhost:8000/login` with `MANAGER_EMAIL` and your password.

The seeder creates one synthetic manager in `users`. Running it again updates that same account. It refuses to run in production, and it refuses the `change-me` placeholder. Passwords are stored with Laravel's hasher and are never sent to the browser. Do not commit `.env`.

Routes that need a signed-in manager use the `auth` middleware. A signed-out request to one of them redirects to `/login`.

## Checks

```bash
php artisan test
```

Tests use an in-memory SQLite database. The running app uses Postgres.

## Layout

- `routes/web.php` — Inertia pages
- `app/Models/Model.php` — base model for later UUID tables
- `app/Models/User.php` — manager account
- `docs/decisions/0001-prototype-stack.md` — locked stack
