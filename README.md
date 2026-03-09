# famledger

Financial management System for family

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

## Production: Super Admin & mobile app

The mobile app logs in via the API at `https://famledger.laravel.cloud/api`. Create the super admin user **on the production server** (e.g. Laravel Cloud) so the app can sign in:

**On Laravel Cloud:** use the Cloud CLI or dashboard “Run command” and run:

```bash
php artisan famledger:create-super-admin --name="Super Administrator" --email=admin@famledger.com --password="SuperAdmin123!"
```

- **404 in browser at `/api`:** Normal. The API has no GET route for `/api`; the app uses `POST /api/login` and `GET /api/user`.
- **“These credentials do not match our records”:** The user doesn’t exist in the **production** database. Run the command above on production once.
