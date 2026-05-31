# Publishing Module Assets

Origin allows you to publish a module's views, config, and translations to the main Laravel application. This lets users override module resources without modifying the module itself.

## Basic Usage

```bash
# Publish all assets from the Blog module
php artisan module:publish Blog
```

## Publishing Specific Assets

Use the `--tag` flag to publish only one type of asset:

```bash
php artisan module:publish Blog --tag=views
php artisan module:publish Blog --tag=config
php artisan module:publish Blog --tag=translations
```

## Overwriting Existing Files

Add `--force` to overwrite previously published files:

```bash
php artisan module:publish Blog --tag=views --force
```

## Where Assets Are Published

| Asset Type | Source | Published To |
|---|---|---|
| Views | `modules/Blog/resources/views/` | `resources/views/vendor/blog/` |
| Translations | `modules/Blog/resources/lang/` | `lang/vendor/blog/` |
| Config | `modules/Blog/config/` | `config/blog/` |

## How Overrides Work

After publishing, Laravel's standard override mechanism takes over. For example, if you published Blog's views:

- Laravel first looks in `resources/views/vendor/blog/posts/index.blade.php`
- Falls back to `modules/Blog/resources/views/posts/index.blade.php`

This means users can customize any view while still receiving updates to views they haven't overridden.
