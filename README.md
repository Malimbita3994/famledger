# famledger

Financial management System for family

## Testing

```bash
composer test
# or
php artisan test
```

Feature tests use **SQLite in-memory** (`phpunit.xml`). Enable the PHP extensions `pdo_sqlite` and `sqlite3`, or override `DB_*` in `phpunit.xml` / `.env.testing` for MySQL/PostgreSQL.

## Code style

```bash
composer run format
```

Uses [Laravel Pint](https://laravel.com/docs/pint) (`pint.json` preset: `laravel`).

## Optimizing for production

To improve load times in production, run:

```bash
composer run optimize
```

This caches config, routes, views, and events. After code or config changes, run:

```bash
composer run optimize:clear
```

Then `composer run optimize` again. Use `composer install --optimize-autoloader` (or `-o`) when deploying to optimize the class autoloader.
