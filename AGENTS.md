# AGENTS.md

Instructions for coding agents working in this repository.

## Project Snapshot

- Framework: Laravel 12 (`laravel/framework ^12.0`)
- PHP: `^8.2`
- Frontend build: Vite 7 + Tailwind CSS 4
- JS helper: Axios bootstrap in `resources/js/bootstrap.js`
- Testing: PHPUnit 11 via `php artisan test`
- PHP formatter: Laravel Pint (`laravel/pint`)

## Rule Files Check (Cursor/Copilot)

Checked at repo root and recursively:

- `.cursorrules`: not found
- `.cursor/rules/`: not found
- `.github/copilot-instructions.md`: not found

If those files are added later, treat them as authoritative and update this file.

## Setup Commands

Run from repository root:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
```

One-command setup path from Composer:

```bash
composer run setup
```

## Build / Dev Commands

```bash
# Full dev stack (artisan serve, queue listener, pail logs, vite)
composer run dev

# Frontend dev only
npm run dev

# Frontend production build
npm run build

# Backend dev only
php artisan serve
```

Notes:

- `composer run setup` also runs `npm run build`.
- Vite config listens on `0.0.0.0:5173` with custom HMR host detection.

## Lint / Format Commands

No ESLint/Prettier/PHPStan config files were found.
Use Pint as canonical formatter for PHP files:

```bash
# Auto-format PHP files
./vendor/bin/pint

# Check-only mode (fails if formatting needed)
./vendor/bin/pint --test
```

## Test Commands

Primary command:

```bash
composer test
```

`composer test` runs:

1. `php artisan config:clear --ansi`
2. `php artisan test`

Direct test commands:

```bash
# Run all tests
php artisan test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Run tests matching a class name
php artisan test --filter=ExampleTest

# Run one test method
php artisan test --filter=test_the_application_returns_a_successful_response

# Run specific suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

Low-level PHPUnit alternative:

```bash
./vendor/bin/phpunit tests/Feature/ExampleTest.php
```

## Test Environment Notes

- `phpunit.xml` sets `APP_ENV=testing`.
- Tests use SQLite in-memory:
  - `DB_CONNECTION=sqlite`
  - `DB_DATABASE=:memory:`
- Do not depend on local MySQL data in tests.

## Code Style and Conventions

### Formatting

- Follow `.editorconfig`:
  - UTF-8, LF, final newline
  - 4-space indentation (YAML uses 2)
  - trim trailing whitespace (except Markdown)
- Match existing style in touched files.
- Avoid unrelated reformatting.

### Imports

- Keep imports at top via `use` statements.
- Prefer imports over inline fully-qualified class names.
- Remove unused imports during edits.
- Alias imports only when needed for clarity/conflicts.

### Types and signatures

- Add scalar/return type hints where practical.
- Use typed properties in classes when possible.
- Use enum types for domain values (see `app/Enums`).
- Keep model casts in sync with column types (`$casts`).

### Naming

- Classes/enums: `PascalCase`
- Methods/properties/variables: `camelCase`
- Constants: `UPPER_SNAKE_CASE`
- Database columns/tables: `snake_case`
- Route names follow existing dotted style, e.g. `admin.inventory.logs.index`

### Validation

- Validate all request input.
- Prefer `FormRequest` for complex rules.
- Use `Rule::enum(...)` for enum-backed fields.
- Keep validation rules/messages explicit and user-facing.

### Error handling and logging

- Use `DB::transaction(...)` for multi-write operations.
- Use locking (`lockForUpdate`) for race-prone flows.
- Catch exceptions at HTTP/service boundaries when needed.
- Log actionable context; do not log secrets or sensitive PII.

### Architecture

- Keep controllers thin; place business logic in services/models.
- Reuse relationships/scopes/constants to avoid duplicated logic.
- Keep migrations forward-only; do not rewrite historical migrations.
- When schema changes, update validation, casts, and domain constants together.

## Practical Agent Workflow

- Inspect nearby files for established patterns before editing.
- Keep diffs focused and reviewable.
- Run the smallest command set that verifies your changes.
- For backend logic changes: run targeted tests first, then broader tests if needed.
- For frontend/build changes: run `npm run build`.

## Quick Command Reference

```bash
composer run setup
composer run dev
npm run build
./vendor/bin/pint --test
composer test
php artisan test tests/Feature/ExampleTest.php
php artisan test --filter=ExampleTest
php artisan test --filter=test_the_application_returns_a_successful_response
```
