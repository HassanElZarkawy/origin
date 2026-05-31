# Enabling & Disabling Modules

Modules can be toggled on and off without deleting them. A disabled module is completely excluded from the Laravel bootstrap — its ServiceProvider is never registered.

## Via Artisan

```bash
# Disable a module
php artisan module:disable Blog

# Enable it again
php artisan module:enable Blog
```

## Via Config

Edit `config/modular.php`:

```php
'disabled' => ['Blog', 'LegacyShop'],
```

## What Happens When a Module Is Disabled

When a module is in the `disabled` list:

- Its `ServiceProvider` is **not registered** with Laravel
- Its **routes** are not loaded (no URL conflicts)
- Its **migrations** are not registered
- Its **views** are not namespaced
- Its **translations** are not loaded
- Its **config** is not merged
- Its **commands** are not registered
- Its **factories** are not loaded

The module directory and files still exist on disk — nothing is deleted. The module is simply invisible to Laravel.

## Other Modules That Depend on a Disabled Module

If module `Blog` declares `protected array $depends = ['Users']` and `Users` is disabled, the dependency is silently ignored during the topological sort. Only enabled modules participate in dependency resolution.

If you want stricter behavior (failing when a dependency is disabled), you can check for it in your module's ServiceProvider:

```php
public function register(): void
{
    parent::register();

    $manager = app(ModuleManager::class);

    if (! $manager->isEnabled('Users')) {
        throw new \RuntimeException('Blog module requires the Users module to be enabled.');
    }
}
```
