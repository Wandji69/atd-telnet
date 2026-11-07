# 0001. Prototype stack

- Status: Accepted
- Date: 7 Nov 2026

## Context

The attendance system must serve a React manager UI and a public landing page from the same Laravel application that the phone will sync with. Interns will share this repository. The SRS sketched several services. This prototype keeps those responsibilities in one application.

## Decision

- PHP 8.3 or newer, Laravel 13.
- Official React starter kit: Inertia 3, React 19, TypeScript, Tailwind 4, and shadcn/ui.
- Fortify session authentication for managers. Public registration is disabled. The manager account is the `users` table with a UUID primary key.
- Sync endpoints use bearer tokens. They do not use the manager session.
- Postgres stores records. Redis stores the queue and the cache. Files for evidence use a local disk named `evidence`.
- Pest runs the tests.
- Local PHP, Composer, and Node. Postgres 16 and Redis 7 run in Docker Compose.
- Inertia pages use `routes/web.php`. Sync will use `routes/sync.php`. Domain actions live in `app/Actions`. Jobs live in `app/Jobs`.

WorkOS, a separate React SPA, Livewire, Laravel Sail, Horizon, Sanctum, and Inertia server-side rendering are out of this prototype.

## Consequences

- T0 creates the application from `laravel new atd-telnet-app --react --pest --database=pgsql --npm --no-boost` in a temporary directory, then moves it here.
- A UUID supplied by the phone is stored unchanged. The application generates a UUID only when the caller did not supply one.
- Framework tables that Laravel owns, such as `cache`, `jobs`, and `sessions`, keep the starter kit's key types except where a column points at `users.id`. That column is a UUID.
- `task.md` is the source for intern issues. This decision is the source for the stack.
