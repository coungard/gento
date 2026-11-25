# GIGACODE.md — Gento (Генераторы и запчасти)

## Project Overview

Laravel 13 application (PHP 8.4) with Filament 4 admin panel. E-commerce for generators and spare parts. Uses SQLite as the database, Tailwind CSS 4 for styling, Vite for frontend bundling.

## Tech Stack

- **Backend:** Laravel 13, PHP 8.4, Filament 4 (admin panel)
- **Frontend:** Vite 8, Tailwind CSS 4, Blade templates
- **Database:** SQLite (`database/database.sqlite`)
- **Testing:** PHPUnit 12, Faker
- **Styling:** Laravel Pint (code formatter)

## Commands

```bash
# Setup
composer setup

# Development
composer run dev          # starts Laravel dev server
npm run dev               # starts Vite dev server
npm run build             # builds assets for production

# Testing
composer run test         # runs PHPUnit with config clear
php artisan test          # direct test runner
php artisan test --filter=TestName   # single test

# Code formatting
vendor/bin/pint --dirty --format agent   # format changed PHP files

# Artisan
php artisan make:model Name -fcrs         # model + controller + migration + factory + seeder + resource + test
php artisan make:test NameTest             # feature test
php artisan route:list --except-vendor     # list routes
```

## Conventions

- **PHP:** PHP 8 constructor property promotion, explicit return types, curly braces for all control structures, TitleCase enums, PHPDoc blocks over inline comments.
- **Naming:** Descriptive names (`isRegisteredForDiscounts`, not `discount()`). Check sibling files before creating new ones.
- **Architecture:** Follow existing directory structure. Do not create new base folders without approval. Do not change dependencies without approval.
- **Testing:** Feature tests preferred over unit tests. Use factories + states for model creation. Run narrowest test set possible.
- **Frontend:** If changes don't appear in UI, run `npm run build` or ask user to run `npm run dev`.
- **Artisan:** Always pass `--no-interaction`. Use `php artisan make:` for scaffolding.
- **URLs:** Prefer named routes + `route()` function.
- **Pint:** Run `vendor/bin/pint --dirty --format agent` on any PHP file changes before finalizing.

## Laravel Boost MCP

This project uses Laravel Boost. Prefer Boost tools over raw alternatives:

- `search-docs` — before changes depending on Laravel APIs
- `database-query` / `database-schema` — for database work
- `browser-logs` — for frontend debugging
- `get-absolute-url` — before sharing URLs with the user
- `last-error` — for backend error details

## Project-Specific Notes

- Admin panel: Filament (accessible at `/admin`)
- Language: Russian (laravel-lang used for localization)
- Database: SQLite — no MySQL/PostgreSQL configuration needed
