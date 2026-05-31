# Removing Modules

## The `module:remove` Command

```bash
php artisan module:remove Blog
```

This will ask for confirmation before proceeding. Use `--force` to skip the prompt:

```bash
php artisan module:remove Blog --force
```

## What Gets Cleaned Up

`module:remove` performs the following:

1. **Deletes the module directory** — Removes `modules/Blog/` and all its contents
2. **Cleans up merge-plugin config** — If this was the last module, removes the `merge-plugin` section from your app's `composer.json` `extra` block
3. **Removes orphaned dependencies** — Strips packages from your app's `require` that were only needed by the removed module
4. **Cleans up disabled config** — Removes the module name from the `disabled` array if present

## After Removal

Run `composer dump-autoload` to regenerate the classmap:

```bash
composer dump-autoload
```

This ensures PHP no longer tries to resolve classes from the removed module's namespace.

## Important Notes

- **Database data is not affected.** If the module had migrations that ran, the database tables and data remain. To drop them, run `module:migrate:reset` before removing the module.
- **Published assets are not removed.** If you published views or config with `module:publish`, those files remain in `resources/views/vendor/blog/`, `config/blog/`, etc. Delete them manually if needed.
- **Route cache should be cleared** after removing a module:

```bash
php artisan route:clear
php artisan config:clear
```
