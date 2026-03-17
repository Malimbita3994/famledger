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
