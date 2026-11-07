# Contributing

This repository is the Telnet intern attendance API and the React manager UI. The mobile app is a separate project.

Read `README.md` to boot the app, `task.md` for the work, and `software_requirements_specification.md` for the original requirements. The locked stack is recorded in `docs/decisions/0001-prototype-stack.md`.

## Pick up a task

1. Wait until T0 is on `main`. The lead opens the intern issues. Do not open a task that is not already an issue.
2. Choose one issue whose **Depends on** tasks are already merged. Comment that you are taking it.
3. Update `main`, then create `feature/<task-id>-short-description`. Example: `feature/T5-sync-pull`.
4. Implement only that issue. Pages live in `resources/js/pages`. Sync routes will live in `routes/sync.php`. Domain rules live in `app/Actions`.
5. Add the tests named in the issue. Run `php artisan test`.
6. Open a pull request into `main`. Include the task id, the requirement ids, the test command, and screenshots for a UI change. End the description with `Closes #<issue number>`.

Commit messages start with the task id: `T5 Add WatermelonDB pull`.

A change the issue does not list belongs in a new task, not on the open pull request. Leave backlog tasks T17–T24 unopened.

## Issues

Paste the **Issue body** from `task.md`. The GitHub form also accepts that paste. Do not use any older template. Those described a different product.

Labels are `prototype` plus `essential` or `supporting`.

## Review

The lead reviews and merges. Do not commit directly to `main`.

## Local data

Use the local Docker Compose database and the placeholder values in `.env.example`. Do not commit `.env`. Do not load real intern records, real photos, or production keys.
