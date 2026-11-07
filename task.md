# Tasks — Telnet intern attendance API

Approved prototype scope for this repository. The lead sets up the Laravel application (T0). Essential and supporting tasks are written so each one can be pasted into a GitHub issue. Backlog tasks stay in this file until the lead promotes them.

Source: `software_requirements_specification.md`, plus decisions recorded on 7 Nov 2026. The stack in **Locked stack** was approved the same day.

## How to use this file

1. The lead completes T0 using the locked stack. Intern issues start after that. How to branch and open an issue is in `CONTRIBUTING.md`. The stack record is `docs/decisions/0001-prototype-stack.md`.
2. Open one GitHub issue per essential or supporting task. Paste that task's **Issue body**. Leave backlog tasks unopened.
3. An intern takes a single issue whose **Depends on** tasks are already merged.
4. The issue is done when every acceptance box is checked and the named tests pass.
5. Work outside the issue's scope is a new task, not an addition to the open one.

Suggested branch names: `feature/<task-id>-short-description` (example: `feature/T5-sync-pull`).

Suggested labels: `prototype`, plus `essential`, `supporting`, or `backlog`.

Tests are Pest feature tests. Run them with `php artisan test`.

The existing `.github/ISSUE_TEMPLATE` files describe a different product. Do not use them for these tasks until they are replaced.

## Demo the prototype must support

A reviewer can run this without the React Native app. HTTP calls stand in for the phone.

1. A visitor opens the public landing page and reads what the system is.
2. A manager signs in, creates a synthetic intern, sets a PIN, attaches a reference photo, and assigns a site whose radius starts at 100 meters.
3. Pull returns that intern and site to the simulated phone.
4. The phone pushes a check-in whose id is a UUID it chose. The manager sees that same UUID on the attendance board, with the time that terminal last synced.
5. The same push sent again does not create a second event.
6. A PIN check-in, a remote check-in outside the site radius, and a stubbed face score below 85% all appear on the review queue.
7. The manager overrides the out-of-bounds event. A notification row is stored. No SMS or WhatsApp message is sent.

**Done when:** that path runs locally against Postgres, the feature tests named in T11 pass, and the README documents the sample calls. The face scorer and the notification delivery are labeled as stubs in the UI and the README.

## Recorded decisions

| Topic | Decision |
| --- | --- |
| Repository | One Laravel application. React manager UI and the public landing page live in it |
| Mobile database | WatermelonDB stays on the phone. This API is the pull/push counterpart |
| Primary keys | UUID on every table. Ids created by the phone are stored unchanged |
| Sync transport | HTTP JSON, the contract WatermelonDB `synchronize()` speaks |
| Conflict handling | If the server row changed after `lastPulledAt`, reject the push. The phone pulls and retries. An identical replay is a no-op |
| Site radius | Stored per site. Default 100 meters. A manager can edit it |
| Roles | One manager role for the prototype. A separate admin role is backlog |
| Face match | Stub scorer in the prototype. A real provider is backlog |
| Messages | A queued job writes a notification row. Twilio, Infobip, and WhatsApp are backlog |
| Evidence bytes | Local disk for the prototype. S3 is backlog |
| Secrets in sync | PIN hashes, passwords, device tokens, and card keys are never part of a pull payload |
| Deletions | Prototype rule, closing a gap in the SRS: a push `deleted` id is a soft delete. A later pull returns that id in `deleted` |
| Sync auth before T16 | Routes require `SYNC_DEV_TOKEN`. T16 replaces that shared token with a per-terminal token |
| Framework | Laravel 13 on PHP 8.3 or newer |
| UI | Official React starter kit: Inertia 3, React 19, TypeScript, Tailwind 4, shadcn/ui. No Inertia server-side rendering |
| Auth | Fortify session cookies for managers. Public registration disabled. Sync uses bearer tokens, not the manager session |
| Manager account | The starter kit `users` table. UUID primary key. One row is one manager |
| Data stores | Postgres for records. Redis for the queue and cache. Local disk named `evidence` for files |
| Tests | Pest |
| Local runtime | PHP, Composer, and Node on the host. Postgres and Redis in Docker Compose |
| Code layout | One application. Inertia routes in `routes/web.php`. Sync routes in `routes/sync.php`. Domain actions in `app/Actions`. Jobs in `app/Jobs` |

## Locked stack

Approved for T0. Do not substitute Livewire, a separate React SPA, WorkOS, Laravel Sail, Horizon, or Sanctum.

| Piece | Choice |
| --- | --- |
| PHP | 8.3 or newer |
| Laravel | 13 |
| Create command | `laravel new atd-telnet-app --react --pest --database=pgsql --npm --no-boost` |
| Auth package | Fortify, which the React starter kit includes. Do not pass `--workos` |
| Manager table | `users`, with a UUID primary key |
| Tests | Pest |
| Postgres and Redis | Docker Compose. PHP stays on the host |

This repository is not empty. Run that command in a temporary directory, then bring the Laravel files into this repository. Keep `task.md`, `software_requirements_specification.md`, `LICENSE`, and `.github/ISSUE_TEMPLATE/`. Do not pass `--git` or `--github`. The project already has a Git history.

Before the first migrate, confirm the Postgres release is one Laravel 13's database documentation supports.

## Prototype schema

T0 creates `users`. T1 implements every other table. Sync responses expose the safe columns only.

WatermelonDB's default id generator does not emit UUIDs. The mobile app must send canonical UUID strings (`8-4-4-4-12` hex). This API rejects anything else.

| Table | Sync | Purpose |
| --- | --- | --- |
| `users` | Server only | Manager accounts. Created by the React starter kit and converted to UUID in T0 |
| `devices` | Server only | Terminal identity and `last_synced_at`. Token hash arrives in T16 |
| `interns` | Pull | Profile. `pin_hash` and the raw photo file stay off the wire |
| `sites` | Pull | Name, latitude, longitude, `radius_meters` (default 100) |
| `assignments` | Pull | Intern assigned to a site |
| `cards` | Pull | Status, offline log count, last status time, token version, revocation, re-issue flag. No key material |
| `attendance_events` | Push | Terminal or PIN check-in. Client-generated UUID |
| `remote_check_ins` | Push | GPS, cell-tower reference, timestamp, selfie key, geofence result |
| `evidence` | Push | Photo metadata and object key. File bytes stay out of Postgres |
| `review_decisions` | Server only | Override or dismiss |
| `fraud_flags` | Server only | Match score, scorer name (`stub` or a later provider) |
| `notifications` | Server only | Stored dispatch. No external send in the prototype |

Every table has a UUID primary key, `created_at`, and `updated_at`. Synced tables also have nullable `deleted_at`.

Card and attendance status values: `CHECKED_IN`, `ON_BREAK`, `CHECKED_OUT`.

## Task index

| Task | Owner | Track | Depends on | Requirements |
| --- | --- | --- | --- | --- |
| [T0](#t0--application-skeleton) | Lead | Setup | Locked stack | UR-01, UR-02, NFR-BE-02 |
| [T1](#t1--uuid-schema) | Intern | Essential | T0 | UR-02 |
| [T2](#t2--manager-authentication) | Intern | Essential | T0, T1 | UI-02 |
| [T3](#t3--public-landing-page) | Intern | Essential | T0 | UI-01 |
| [T4](#t4--enrollment) | Intern | Essential | T2, T1 | Demo setup, FR-BE-06 |
| [T5](#t5--watermelondb-pull) | Intern | Essential | T1 | UR-03, FR-BE-06 |
| [T6](#t6--watermelondb-push) | Intern | Essential | T5 | FR-BE-06, FR-BE-07, FR-BE-09 |
| [T7](#t7--attendance-status) | Intern | Essential | T6 | FR-BE-01 |
| [T8](#t8--pin-fallback) | Intern | Essential | T6, T4 | FR-BE-03 |
| [T9](#t9--geofence) | Intern | Essential | T6, T4 | FR-BE-04, FR-BE-05 |
| [T10](#t10--manager-board-and-review) | Intern | Essential | T2, T7, T8, T9 | UI-02 |
| [T11](#t11--demo-fixture-and-tests) | Intern | Essential | T3, T4, T10 | UR-03, definition of done |
| [T12](#t12--card-reissue-list) | Intern | Supporting | T6, T10 | FR-BE-08, UI-03, EC-02 |
| [T13](#t13--face-match-stub) | Intern | Supporting | T6, T10 | FR-BE-02, EC-05 |
| [T14](#t14--evidence-files) | Intern | Supporting | T4 | NFR-BE-03 |
| [T15](#t15--notification-record) | Intern | Supporting | T13 | NFR-BE-02, EC-05 |
| [T16](#t16--terminal-tokens) | Intern | Supporting | T5 | UR-03 |
| [T17](#t17--real-face-provider) | — | Backlog | T13 | FR-BE-02 |
| [T18](#t18--live-sms-and-whatsapp) | — | Backlog | T15 | NFR-BE-02 |
| [T19](#t19--activity-tracking) | — | Backlog | Requirements still missing | §2.1, §6.2 |
| [T20](#t20--separate-admin-role) | — | Backlog | T2 | EC-02 |
| [T21](#t21--spoof-and-clock-payloads) | — | Backlog | Payload still undefined | EC-03, EC-04 |
| [T22](#t22--card-key-distribution) | — | Backlog | Security design | FR-3.1.1, NFR-5.1 |
| [T23](#t23--s3-and-encryption-at-rest) | — | Backlog | T14 | NFR-BE-03, NFR-5.1 |
| [T24](#t24--grpc-transport) | — | Backlog | None planned | §2.1 diagram |

## Out of scope for every task

React Native, WatermelonDB on the device, NFC radio, on-device liveness, battery management, the 450 ms on-device budget, and certificate pinning. Those belong to the mobile project.

---

## T0 — Application skeleton

- **Owner:** Lead. Do not open this as an intern issue.
- **Track:** Setup
- **Depends on:** The locked stack above.
- **Requirements:** UR-01, UR-02, NFR-BE-02

### Outcome

A local Laravel 13 application boots against Postgres and Redis, renders the React starter kit through Inertia, and stores manager accounts in `users` with UUID primary keys.

### Scope

- Install PHP 8.3 or newer, Composer, Node, and Docker.
- Create the app with `laravel new atd-telnet-app --react --pest --database=pgsql --npm --no-boost` in a temporary directory, then move it into this repository without deleting the files named in **Locked stack**.
- Add a Docker Compose file that runs Postgres and Redis only.
- Point `DB_*` at Postgres and `QUEUE_CONNECTION` and `CACHE_STORE` at Redis.
- Remove `Features::registration()` from `config/fortify.php`, and remove the frontend links to the registration routes so the React build still succeeds.
- Change the `users` migration and `User` model to a UUID primary key before the first migrate. Use a base model later tables can extend. Generate a UUID only when the caller did not supply one, so a phone-created id can be stored unchanged.
- Add an `evidence` local disk in `config/filesystems.php`.
- Add `.env.example` names and safe placeholders for `SYNC_DEV_TOKEN` and `FACE_MATCH_STUB=true`.
- Keep Laravel's vendor, storage keys, and `.env` out of Git.
- Document the local boot commands in `README.md`, including Compose, migrate, and `composer run dev`.

### Non-goals

- Domain tables other than `users`. Sync, enrollment, and the landing copy are T1 through T11.
- Inertia server-side rendering, Laravel Sail, Horizon, Sanctum, and WorkOS.
- Choosing a face vendor, an SMS vendor, or a cloud host.
- Committing secrets.

### Acceptance criteria

- [ ] `composer.json` requires PHP `^8.3` and `laravel/framework` `^13.0`.
- [ ] `README.md` lists the commands that start Compose, install dependencies, migrate, and run the app.
- [ ] A browser can open the starter-kit React page through a Laravel route.
- [ ] `GET /register` is unavailable.
- [ ] A request reaches Postgres, and a queued job can reach Redis.
- [ ] `users.id` is a UUID. Creating a user without an id stores a new UUID. Creating a user with a supplied UUID keeps that UUID.
- [ ] `.env.example` contains names and placeholders only, including `SYNC_DEV_TOKEN` and `FACE_MATCH_STUB`.

### Tests

Migrate against the Compose Postgres and save one `users` row with a chosen UUID. `php artisan test` runs the starter-kit Pest tests. Record both commands in the README.

---

## T1 — UUID schema

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T0
- **Requirements:** UR-02
- **Suggested title:** `[T1] Add UUID schema for attendance sync`

### Issue body

#### Problem

The phone creates attendance ids before the server has seen them. Every table needs a UUID primary key, and synced rows need a deletion timestamp so a later pull can report deletes.

## Outcome

Migrations, models, and factories exist for every prototype table except `users`. No HTTP behavior yet.

#### Scope

- Add the tables listed under **Prototype schema**, leaving `users` as T0 created it.
- UUID primary keys on every table. Foreign keys point at those UUIDs.
- Synced tables include nullable `deleted_at`.
- `sites.radius_meters` defaults to 100.
- `cards.status` defaults to `CHECKED_OUT`.
- Factories produce synthetic rows only.

#### Non-goals

- Sync endpoints, authentication, file uploads, and UI.
- Storing AES keys, PIN plaintext, or photo bytes in Postgres.

#### Acceptance criteria

- [ ] Migrating a fresh database creates every prototype table. `users` is still the manager account from T0.
- [ ] Every primary key column is a UUID, including `users.id`. None of the tables use an auto-increment id.
- [ ] A factory can persist one row of each table.
- [ ] `sites.radius_meters` defaults to 100 when the caller omits it.
- [ ] Models reject a non-UUID primary key.

#### Tests

A migration test or a feature test that runs migrations on a fresh database and asserts the primary key type and the site default.

---

## T2 — Manager authentication

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T0, T1
- **Requirements:** UI-02
- **Suggested title:** `[T2] Add manager sign-in`

### Issue body

#### Problem

The manager UI needs an authenticated session. Visitors must not reach manager screens.

#### Outcome

A manager can sign in and sign out. The session belongs to a row in `users`.

#### Scope

- Use the Fortify sign-in and sign-out screens from T0. Do not add a second authentication package.
- Keep public registration disabled.
- Password stored with Laravel's password hasher.
- A local seeder for one synthetic manager in `users`, documented in the README. The password lives in `.env.example` as a placeholder the developer replaces locally.
- Guest requests to manager routes redirect to sign-in.

#### Non-goals

- A new registration flow, a custom password-reset mailer, and a separate admin role (T20). Fortify's remaining starter-kit features can stay as T0 left them.
- Sync authentication (T5 uses `SYNC_DEV_TOKEN`; T16 replaces it).

#### Acceptance criteria

- [ ] A manager with valid credentials reaches a signed-in page.
- [ ] A wrong password stays on the sign-in screen and shows an error.
- [ ] A signed-out request to a manager route redirects to sign-in.
- [ ] The password is stored hashed. The hash is never sent to the browser.

#### Tests

Feature tests for a successful sign-in, a failed sign-in, and a guest redirect.

---

## T3 — Public landing page

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T0
- **Requirements:** UI-01
- **Suggested title:** `[T3] Add the public landing page`

### Issue body

#### Problem

Visitors need a public page that explains the system and leads managers to sign-in.

#### Outcome

`/` renders a React page with the copy below and a link to manager sign-in. No account is required.

#### Copy to use

- **Title:** Telnet intern attendance
- **Lead:** Attendance for interns that keeps working through power cuts and network outages.
- **Body:** Terminals record check-ins on the device and sync them when a connection returns. Managers see those events here, including the last time each terminal synced. A missing card uses a PIN and a photo. A remote check-in must fall inside the intern's assigned site.
- **Action:** Manager sign-in

#### Scope

- One public React page at `/`.
- The sign-in link points at the T2 route. If T2 is not merged yet, link to the path T2 will own and name that path in the pull request.

#### Non-goals

- Marketing CMS, brand imagery, and localization.
- Any data from the database.

#### Acceptance criteria

- [ ] `/` returns the page with no session.
- [ ] The page contains the title, lead, and body above.
- [ ] The page links to manager sign-in.

#### Tests

A feature test that guests can load `/` and see the title.

---

## T4 — Enrollment

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T2, T1
- **Requirements:** Demo setup, FR-BE-06
- **Suggested title:** `[T4] Let a manager enroll an intern and a site`

### Issue body

#### Problem

The SRS does not define how an intern, a PIN, a reference photo, or a site gets into the system. The demo cannot start until a manager can create them.

#### Outcome

A signed-in manager can create a synthetic intern, set a 6-digit PIN, store a reference photo on the local disk, create a site, and assign the intern to that site.

#### Scope

- Manager screens for intern, site, and assignment.
- PIN validated as exactly 6 digits, then stored only as `pin_hash`.
- Reference photo saved on a local Laravel disk named `evidence`. The intern row stores the object key.
- Site form includes name, latitude, longitude, and radius. Radius defaults to 100 meters.
- Assignment links one intern to one site.

#### Non-goals

- Check-in, sync, and geofence evaluation (T6, T9).
- S3 (T23).
- The generalized evidence metadata table behavior for check-in snapshots (T14). T4 may write the reference file and the intern's object key only.

#### Acceptance criteria

- [ ] A signed-in manager can create an intern, a site, and an assignment.
- [ ] A PIN that is not 6 digits is rejected and is not stored.
- [ ] The database contains `pin_hash` and does not contain the PIN.
- [ ] The reference file is on the `evidence` disk, and `interns.reference_photo_key` points at it.
- [ ] Omitting the radius stores 100.
- [ ] A signed-out visitor cannot open these screens.

#### Tests

Feature tests for create-intern, invalid PIN, default radius, and the guest redirect.

---

## T5 — WatermelonDB pull

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T1
- **Requirements:** UR-03, FR-BE-06
- **Suggested title:** `[T5] Implement WatermelonDB pull`

### Issue body

#### Problem

The phone has to fetch intern profiles, sites, assignments, and card state that changed since its last pull.

#### Outcome

`POST /api/sync/pull` speaks the WatermelonDB pull contract for `interns`, `sites`, `assignments`, and `cards`.

#### Request and response

Request JSON: `lastPulledAt` (milliseconds, or `null` on the first pull) and an optional `device_id` UUID.

Response JSON:

```json
{
  "changes": {
    "interns": { "created": [], "updated": [], "deleted": [] },
    "sites": { "created": [], "updated": [], "deleted": [] },
    "assignments": { "created": [], "updated": [], "deleted": [] },
    "cards": { "created": [], "updated": [], "deleted": [] }
  },
  "timestamp": 0
}
```

`timestamp` is the server time in milliseconds. The phone stores it as the next `lastPulledAt`.

#### Scope

- First pull (`lastPulledAt` null) returns current rows in `created` and omits soft-deleted rows.
- Later pulls return rows created after that time in `created`, rows updated after that time in `updated`, and ids whose `deleted_at` is after that time in `deleted`.
- A row is in only one of those arrays.
- Intern payloads omit `pin_hash` and any file bytes. Card payloads omit key material.
- The route requires header `Authorization: Bearer <SYNC_DEV_TOKEN>`.
- When `device_id` matches a device row, set `devices.last_synced_at`.

#### Non-goals

- Push (T6).
- Per-terminal tokens (T16).
- Key distribution (T22).

#### Acceptance criteria

- [ ] A pull with `lastPulledAt: null` returns the existing intern, site, assignment, and card in `created`.
- [ ] A pull after that timestamp returns only later changes.
- [ ] A soft-deleted row appears in `deleted` and not in `created` or `updated`.
- [ ] Intern JSON has no `pin_hash`.
- [ ] A missing or wrong bearer token receives 401.
- [ ] `timestamp` is an integer number of milliseconds.

#### Tests

Feature tests for the first pull, an incremental pull, a deletion, the forbidden field, and the unauthorized request.

---

## T6 — WatermelonDB push

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T5
- **Requirements:** FR-BE-06, FR-BE-07, FR-BE-09
- **Suggested title:** `[T6] Implement WatermelonDB push`

### Issue body

#### Problem

The phone pushes attendance events, remote check-ins, and photo metadata that were stored offline. A retry after a dropped connection must not duplicate them. A push that overlaps a newer server change must be rejected.

#### Outcome

`POST /api/sync/push` accepts WatermelonDB push bodies for `attendance_events`, `remote_check_ins`, and `evidence`, using the client's UUIDs.

#### Request

```json
{
  "device_id": "uuid",
  "lastPulledAt": 0,
  "changes": {
    "attendance_events": { "created": [], "updated": [], "deleted": [] },
    "remote_check_ins": { "created": [], "updated": [], "deleted": [] },
    "evidence": { "created": [], "updated": [], "deleted": [] }
  }
}
```

The route uses the same `SYNC_DEV_TOKEN` bearer token as pull.

#### Prototype rules

- Persist the client `id` unchanged. The server must not issue a replacement id.
- Reject a body whose ids are not canonical UUIDs.
- Identical replay of a `created` record: respond 200 and do not insert a second row.
- If any pushed row was updated on the server after `lastPulledAt`, respond 409 and apply none of the changes in that request.
- `deleted` sets `deleted_at` and leaves the row in place.
- Update `devices.last_synced_at` on success.
- A batch of many rows is accepted in one request. Processing may run in the request for the prototype. Do not call SMS or a face vendor here.

#### Non-goals

- Status transition rules (T7), PIN verification (T8), and geofence math (T9). Store the fields those tasks will interpret.
- File bytes (T14). `evidence` stores metadata and an object key.

#### Acceptance criteria

- [ ] A created attendance event is stored under the UUID from the body.
- [ ] Sending that same body again leaves a single row.
- [ ] A push whose server row changed after `lastPulledAt` returns 409 and writes nothing from that body.
- [ ] A `deleted` id sets `deleted_at`. The next pull lists that id under `deleted`.
- [ ] A non-UUID id returns 422.
- [ ] A wrong bearer token returns 401.
- [ ] `devices.last_synced_at` moves forward after a successful push.

#### Tests

Feature tests for insert, replay, conflict rollback, soft delete, invalid id, and unauthorized access.

---

## T7 — Attendance status

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T6
- **Requirements:** FR-BE-01
- **Suggested title:** `[T7] Enforce attendance status transitions`

### Issue body

#### Problem

A terminal check-in has to move the intern's card among `CHECKED_IN`, `ON_BREAK`, and `CHECKED_OUT`. Illegal jumps must fail without partially writing the batch.

#### Outcome

Pushing an `attendance_events` row with `kind` of `terminal` updates the matching `cards` row when the transition is legal.

#### Legal transitions

| From | To |
| --- | --- |
| `CHECKED_OUT` | `CHECKED_IN` |
| `CHECKED_IN` | `ON_BREAK` or `CHECKED_OUT` |
| `ON_BREAK` | `CHECKED_IN` or `CHECKED_OUT` |

Any other jump rejects the push with 422 and writes nothing from that request.

On success, set `cards.status`, `cards.last_status_at` from the event's `occurred_at`, and increment `cards.offline_log_count` by one.

#### Scope

- Validate transitions inside the push path from T6.
- Apply the card update in the same transaction as the event insert.

#### Non-goals

- NFC writes on the card. The phone does that.
- PIN and remote events. T8 and T9 own those kinds.

#### Acceptance criteria

- [ ] `CHECKED_OUT` to `CHECKED_IN` updates the card and stores the event.
- [ ] `CHECKED_IN` to `ON_BREAK` and `ON_BREAK` to `CHECKED_OUT` succeed.
- [ ] `CHECKED_OUT` to `ON_BREAK` returns 422 and leaves the card and the event table unchanged.
- [ ] `offline_log_count` increases by one on each accepted terminal event.
- [ ] The event's UUID is the one the client sent.

#### Tests

Feature tests for one legal transition, one illegal transition, and the counter.

---

## T8 — PIN fallback

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T6, T4
- **Requirements:** FR-BE-03
- **Suggested title:** `[T8] Accept PIN fallback check-ins`

### Issue body

#### Problem

An intern without a card must check in with a 6-digit PIN and a photo. That entry is always waiting for a manager.

#### Outcome

A push of `attendance_events` with `kind` of `pin` verifies the PIN, requires evidence, stores the event, and sets `review_status` to `pending`.

#### Scope

- The event includes `intern_id`, `pin`, `occurred_at`, and an evidence object key.
- Compare `pin` with `interns.pin_hash`.
- Reject a missing photo key, a missing PIN, or a PIN that does not match. Write nothing from that request.
- On success, store the event with `review_status` `pending`. Do not store the PIN.
- Do not change card status on a PIN event. The manager review in T10 is what acknowledges it.

#### Non-goals

- The review screen (T10).
- Face comparison (T13).

#### Acceptance criteria

- [ ] A matching PIN and a photo key store one event flagged `pending`.
- [ ] A wrong PIN returns 422 and stores no event.
- [ ] A matching PIN with no photo key returns 422 and stores no event.
- [ ] The stored row and the HTTP response contain no PIN and no PIN hash.
- [ ] The card status is unchanged.

#### Tests

Feature tests for success, wrong PIN, missing photo, and the absence of the PIN in the database row.

---

## T9 — Geofence

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T6, T4
- **Requirements:** FR-BE-04, FR-BE-05
- **Suggested title:** `[T9] Enforce the site geofence on remote check-ins`

### Issue body

#### Problem

A remote check-in must include a GPS point, a cell-tower reference, a timestamp, and a selfie. Points outside the assigned site's radius stay held until a manager overrides them.

#### Outcome

Pushing a `remote_check_ins` row either accepts it or holds it for review. Distance uses the haversine formula and the site's `radius_meters`.

#### Scope

- Require `intern_id`, `latitude`, `longitude`, `cell_tower_ref` (non-empty string), `occurred_at`, and a photo object key.
- Load the intern's assignment and site. Missing assignment rejects the push.
- Store `distance_meters` and `site_id` on the row.
- Distance less than or equal to `radius_meters`: `review_status` is `none`.
- Distance greater than `radius_meters`: `review_status` is `pending`. The row must not be treated as a normal accepted check-in by the manager board (T10 reads this status).
- Reject incomplete payloads with 422 and write nothing.

#### Non-goals

- Mock-location detection and cell-tower triangulation. The phone does those. This task stores the cell-tower reference and does not judge it.
- The override action (T10).
- A live map vendor.

#### Acceptance criteria

- [ ] A point inside the radius is stored with `review_status` `none` and a `distance_meters` value.
- [ ] A point outside the radius is stored with `review_status` `pending`.
- [ ] A payload missing GPS, cell-tower reference, timestamp, or photo key returns 422 and stores nothing.
- [ ] An intern with no assignment returns 422.
- [ ] Changing the site radius changes which points are held.

#### Tests

Feature tests for inside, outside, a missing field, and a radius edit. Use fixed coordinates so the distance is deterministic.

---

## T10 — Manager board and review

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T2, T7, T8, T9
- **Requirements:** UI-02
- **Suggested title:** `[T10] Add the manager attendance board and review queue`

### Issue body

#### Problem

Managers need to see synced attendance and act on PIN entries and out-of-bounds check-ins. The board must show when a terminal last synced, because an offline terminal is absent from the server until then.

#### Outcome

A signed-in manager sees an attendance board, a review queue, and an override action.

#### Scope

- Board lists attendance events and accepted remote check-ins, including the client UUID, intern, kind, status, and `occurred_at`.
- Each device that has synced shows `last_synced_at`.
- Review queue lists PIN events and remote check-ins whose `review_status` is `pending`.
- Override sets that row's `review_status` to `overridden`, writes a `review_decisions` row with `user_id` set to the signed-in `users.id` and action `override`, and records an optional reason.
- Held remote check-ins appear as held, not as normal check-ins, until the override.
- Empty board and empty queue have explicit empty states.

#### Non-goals

- Face-match rows (T13) and card re-issue rows (T12). Leave a clear place in the queue data for those later sources.
- Live updates over websockets. A page load shows the server's current data.

#### Acceptance criteria

- [ ] After a terminal push, the board shows that event's client UUID.
- [ ] The board shows the device's `last_synced_at`.
- [ ] A pending PIN event and a pending remote check-in appear on the queue.
- [ ] An accepted in-radius remote check-in appears on the board and not on the queue.
- [ ] Override changes `review_status` to `overridden` and writes a `review_decisions` row for the signed-in manager.
- [ ] A signed-out visitor cannot load the board or post an override.

#### Tests

Feature tests for the board contents, the queue split, override persistence, and the guest redirect.

---

## T11 — Demo fixture and tests

- **Owner:** Intern
- **Track:** Essential
- **Depends on:** T3, T4, T10
- **Requirements:** UR-03, definition of done
- **Suggested title:** `[T11] Cover the demo path with a sync fixture and tests`

### Issue body

#### Problem

Reviewers need one documented path that proves the prototype without the mobile app.

#### Outcome

A feature test and a README section perform the approved demo: enroll, pull, push, replay, PIN, out-of-bounds hold, and override.

#### Scope

- One feature test that walks the demo steps against the app.
- README section with the sample HTTP calls, the expected UUIDs, and the manager clicks.
- Synthetic data only. Names and photos generated for the test.
- The face-stub step and the notification row are included only if T13 and T15 have merged. Otherwise the README says those steps arrive with T13 and T15.

#### Non-goals

- The React Native client.
- Production seed data.

#### Acceptance criteria

- [ ] The feature test creates an intern and a site, pulls them, pushes a client UUID, and asserts that UUID on the manager board.
- [ ] The test pushes the same body again and still finds one row.
- [ ] The test covers a pending PIN event, an out-of-bounds remote check-in, and an override.
- [ ] The README steps match the test.
- [ ] The README states that facial matching and message sending are outside this demo until their tasks merge.

#### Tests

The new feature test is the deliverable. It must pass in the project's test command from T0.

---

## T12 — Card re-issue list

- **Owner:** Intern
- **Track:** Supporting
- **Depends on:** T6, T10
- **Requirements:** FR-BE-08, UI-03, EC-02
- **Suggested title:** `[T12] Show cards that need re-issue`

### Issue body

#### Problem

When a terminal reports a card crypto failure, a manager has to see that card and mark it for re-issue. The phone already fell back to PIN on the device.

#### Outcome

A pushed card update can set `needs_reissue`. The manager UI lists those cards.

#### Scope

- Accept a card field `needs_reissue` on push for the `cards` collection, or a dedicated boolean on a card update inside the existing push endpoint. Document the chosen JSON shape in the pull request.
- Manager list of cards where `needs_reissue` is true, showing the intern and `last_status_at`.
- The manager role performs this. There is no separate admin account in the prototype.

#### Non-goals

- Writing new AES keys to a physical card (T22, and the mobile app).
- Automatic PIN fallback. T8 already accepts the PIN event the phone sends beside this flag.

#### Acceptance criteria

- [ ] A sync that marks a card `needs_reissue` stores the flag.
- [ ] The manager sees that card on the re-issue list after sync.
- [ ] A card with the flag false is absent from the list.
- [ ] The list is limited to signed-in managers.

#### Tests

Feature tests for the flag round-trip and the manager list.

---

## T13 — Face-match stub

- **Owner:** Intern
- **Track:** Supporting
- **Depends on:** T6, T10
- **Requirements:** FR-BE-02, EC-05
- **Suggested title:** `[T13] Flag low face scores with a stub scorer`

### Issue body

#### Problem

The SRS asks for a facial comparison after sync, and a score below 85% must reach the manager. The prototype has no face vendor.

#### Outcome

A stub scorer writes a `fraud_flags` row. Scores below 85 create a pending review item. The UI says the scorer is a stub.

#### Prototype rule

The stub runs only when `FACE_MATCH_STUB=true`. In that mode, push metadata may include `stub_match_score` (a number from 0 to 100). The stub stores that number. When the field is absent, the stub stores 100 and does not flag the event.

When `FACE_MATCH_STUB` is false, `stub_match_score` is ignored and no score is invented.

#### Scope

- After a successful push that includes evidence, queue a job that writes `fraud_flags` with `scorer` `stub`.
- Score below 85 sets the related event `review_status` to `pending`.
- The review queue from T10 shows these rows and the score.
- The queue label includes the word `Stub`.

#### Non-goals

- Calling a real vision API (T17).
- On-device liveness.

#### Acceptance criteria

- [ ] With the stub enabled, `stub_match_score` 70 creates a fraud flag and a pending review row.
- [ ] With the stub enabled and no score in the payload, the flag score is 100 and the event is not moved to pending solely by this task.
- [ ] With the stub disabled, `stub_match_score` does not create a flag.
- [ ] The manager queue shows `Stub` next to the score.
- [ ] The HTTP push response returns before the job finishes. The flag exists after the queue worker runs.

#### Tests

Feature tests for the three stub cases, plus a test that runs the queued job.

---

## T14 — Evidence files

- **Owner:** Intern
- **Track:** Supporting
- **Depends on:** T4
- **Requirements:** NFR-BE-03
- **Suggested title:** `[T14] Store check-in photos on the local evidence disk`

### Issue body

#### Problem

Check-in photos are evidence. The database should keep the key and the metadata. The bytes should live on a disk, so a later task can point that disk at S3.

#### Outcome

`evidence` rows reference files on the local `evidence` disk. Postgres does not contain image bytes.

#### Scope

- Accept a local upload path used by the demo client, or a multipart upload on a manager/demo route, and write the file to the `evidence` disk.
- Persist `evidence.object_key`, `intern_id`, `attendance_event_id` or `remote_check_in_id`, and `captured_at`.
- Share the disk T4 already uses for reference photos.
- Document the disk name in `.env.example`.

#### Non-goals

- S3, encryption at rest, and retention deletion (T23).
- Serving photos on a public URL. Manager access goes through an authenticated route.

#### Acceptance criteria

- [ ] Uploading a fixture image creates a file on the `evidence` disk and a row whose object key matches.
- [ ] The image bytes are not stored in a database column.
- [ ] A signed-out request cannot download the file.
- [ ] A signed-in manager can open the file for an intern they can already see.

#### Tests

Feature tests for store, the empty database blob check, guest denial, and manager download.

---

## T15 — Notification record

- **Owner:** Intern
- **Track:** Supporting
- **Depends on:** T13
- **Requirements:** NFR-BE-02, EC-05
- **Suggested title:** `[T15] Store a notification row when a face flag is raised`

### Issue body

#### Problem

A score below 85% is supposed to alert a manager. The prototype must not call SMS or WhatsApp providers.

#### Outcome

The fraud job queues a second step that inserts a `notifications` row. The manager can see that a notification was recorded.

#### Scope

- On a new fraud flag below 85, write one notification with channel `log`, a payload that includes the intern id, the score, and the flag id, and status `stored`.
- Show the stored notification on the related review item.
- Log the same payload through the application log.

#### Non-goals

- Twilio, Infobip, WhatsApp, email, and retries against a provider (T18).

#### Acceptance criteria

- [ ] A stub score of 70 produces one `notifications` row after the worker runs.
- [ ] A stub score of 100 produces no notification row.
- [ ] The row's channel is `log` and its payload contains the score.
- [ ] No outbound HTTP call is required for the test to pass.
- [ ] The review item shows that a notification was stored.

#### Tests

Feature tests for the below-threshold row, the above-threshold absence, and the review item text.

---

## T16 — Terminal tokens

- **Owner:** Intern
- **Track:** Supporting
- **Depends on:** T5
- **Requirements:** UR-03
- **Suggested title:** `[T16] Authenticate sync with a per-terminal token`

### Issue body

#### Problem

A single shared `SYNC_DEV_TOKEN` is enough for local development. Each terminal needs its own credential before this API is used by more than one device.

#### Outcome

A manager can issue a token for a device. Pull and push accept that device token. The shared dev token no longer authorizes sync outside the test environment.

#### Scope

- Store only a hash of the device token on `devices`.
- A manager action issues a token once and shows the plaintext a single time.
- Sync requests authenticate with the device token and must send the matching `device_id`.
- `SYNC_DEV_TOKEN` remains valid only when `APP_ENV=testing`, so the existing tests keep working.

#### Non-goals

- Certificate pinning, which is a mobile concern.
- Rotating card encryption keys (T22).

#### Acceptance criteria

- [ ] A newly issued token can pull and push for its `device_id`.
- [ ] That token is rejected for a different `device_id`.
- [ ] The plaintext token is not stored in the database.
- [ ] With `APP_ENV` other than `testing`, `SYNC_DEV_TOKEN` returns 401.
- [ ] Existing sync tests still pass in the test environment.

#### Tests

Feature tests for a valid device token, a mismatched device id, and rejection of the shared token when the environment is not `testing`.

---

## T17 — Real face provider

- **Owner:** Unassigned
- **Track:** Backlog. Do not open.
- **Depends on:** T13, and a lead decision naming the provider and the data-processing terms.
- **Requirements:** FR-BE-02, EC-05

Replace the stub scorer with a real comparison against the reference photo. Keep the 85% threshold, the review queue, and the notification hook. The stub must stay available in tests. Blocked until the lead names the vendor and confirms that synthetic images are still the only data allowed in development.

---

## T18 — Live SMS and WhatsApp

- **Owner:** Unassigned
- **Track:** Backlog. Do not open.
- **Depends on:** T15, and a lead decision naming Twilio, Infobip, or another provider.
- **Requirements:** NFR-BE-02

Send the stored notification through the chosen provider from a queue worker. Keep the `notifications` row as the audit of what was sent. Blocked until the lead names the provider and a non-production sender identity.

---

## T19 — Activity tracking

- **Owner:** Unassigned
- **Track:** Backlog. Do not open.
- **Depends on:** A written definition of an activity, who logs it, and what the manager should see.
- **Requirements:** Named in SRS §2.1 and §6.2. No functional requirement exists.

Do not invent the feature in an issue. The lead adds the requirement before this task can be scoped.

---

## T20 — Separate admin role

- **Owner:** Unassigned
- **Track:** Backlog. Do not open.
- **Depends on:** T2
- **Requirements:** EC-02

Split card re-issue and other admin actions from the manager role once the lead defines the permission list. The prototype uses one manager role on purpose.

---

## T21 — Spoof and clock payloads

- **Owner:** Unassigned
- **Track:** Backlog. Do not open.
- **Depends on:** A defined payload for mock-location flags and terminal clock skew.
- **Requirements:** EC-03, EC-04

The SRS places mock-location checks and the monotonic clock on the device. This API can store or reject those signals only after the mobile contract defines the fields. Do not guess the payload in an issue.

---

## T22 — Card key distribution

- **Owner:** Unassigned
- **Track:** Backlog. Do not open.
- **Depends on:** A security design for AES key delivery.
- **Requirements:** FR-3.1.1, NFR-5.1

NFC reads and writes stay on the phone. This task would define how a terminal receives key material. That material must stay out of the WatermelonDB pull. Blocked on an explicit security design from the lead.

---

## T23 — S3 and encryption at rest

- **Owner:** Unassigned
- **Track:** Backlog. Do not open.
- **Depends on:** T14, and a lead decision on the bucket and the encryption approach.
- **Requirements:** NFR-BE-03, NFR-5.1

Point the `evidence` disk at S3-compatible storage and define encryption for stored photos. Local disk remains valid for development.

---

## T24 — gRPC transport

- **Owner:** Unassigned
- **Track:** Backlog. Do not open.
- **Depends on:** None planned.
- **Requirements:** The architecture diagram mentions gRPC. The prototype speaks HTTP JSON because that is the WatermelonDB sync contract.

Revisit only if a client actually requires gRPC. Do not add a second sync transport beside the HTTP endpoints.

## Traceability

| Requirement | Tasks |
| --- | --- |
| UR-01 | T0, T3, T10 |
| UR-02 | T0, T1, T6 |
| UR-03 | T5, T6, T11, T16 |
| FR-BE-01 | T7 |
| FR-BE-02, EC-05 | T13, T15, T17 |
| FR-BE-03 | T8, T10 |
| FR-BE-04, FR-BE-05 | T9, T10 |
| FR-BE-06 | T4, T5, T6 |
| FR-BE-07, EC-06 | T6 |
| FR-BE-08, EC-02 | T12, T20 |
| FR-BE-09, EC-01 | T6 |
| UI-01 | T3 |
| UI-02 | T2, T10 |
| UI-03 | T12 |
| NFR-BE-02 | T0, T15, T18 |
| NFR-BE-03 | T4, T14, T23 |
| EC-03, EC-04 | T21 |
| FR-3.1.1 keys, NFR-5.1 card crypto | T22. Transit TLS is a deployment concern for the lead |
| Activity service | T19, blocked |
| Edge NFC, liveness, power, 450 ms budget | Out of scope |
