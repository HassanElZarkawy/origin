# Configuration

After publishing the config file, you'll find it at `config/modular.php`:

```php
return [
    'path' => 'modules',
    'namespace' => 'Modules',
    'disabled' => [],
    'priority' => [],
    'routes' => [
        'web' => [
            'prefix' => '',
            'middleware' => ['web'],
        ],
        'api' => [
            'prefix' => 'api',
            'middleware' => ['api'],
        ],
    ],
];
```

## Reference

### `path`

**Type:** `string`  
**Default:** `'modules'`

The directory (relative to your project root) where modules live.

```php
'path' => 'app/Modules',
```

### `namespace`

**Type:** `string`  
**Default:** `'Modules'`

The root PSR-4 namespace for all modules. When you create a module called `Blog`, its namespace becomes `Modules\Blog`.

```php
'namespace' => 'App\\Modules',
```

This changes the generated service provider namespace and the autoloading entry in each module's `composer.json`.

### `disabled`

**Type:** `array`  
**Default:** `[]`

An array of module names to exclude from loading. Disabled modules are completely skipped — their service providers are not registered, so no routes, migrations, views, or any other resources are loaded.

```php
'disabled' => ['LegacyBlog', 'OldShop'],
```

You can manage this list via artisan commands:

```bash
php artisan module:disable Blog
php artisan module:enable Blog
```

### `priority`

**Type:** `array`  
**Default:** `[]`

An array of module names that should load first, in the given order. Modules not in this list load alphabetically after the prioritized ones.

```php
'priority' => ['Users', 'Billing'],
```

See [Module Load Ordering](load-ordering.md) for more details including dependency-based ordering.

### `routes.web`

**Type:** `array`

Default route configuration for all modules' `routes/web.php`:

- `prefix` — URL prefix applied to all web routes (default: `''`)
- `middleware` — Middleware group applied to all web routes (default: `['web']`)

```php
'routes' => [
    'web' => [
        'prefix' => '',
        'middleware' => ['web', 'auth'],
    ],
],
```

### `routes.api`

**Type:** `array`

Default route configuration for all modules' `routes/api.php`:

- `prefix` — URL prefix applied to all API routes (default: `'api'`)
- `middleware` — Middleware group applied to all API routes (default: `['api']`)

```php
'routes' => [
    'api' => [
        'prefix' => 'api/v1',
        'middleware' => ['api'],
    ],
],
```

## Per-Module Route Overrides

Individual modules can override the default route settings by overriding methods on their ServiceProvider:

```php
class BlogServiceProvider extends ModuleServiceProvider
{
    protected function routePrefix(): string
    {
        return 'blog';
    }

    protected function routeMiddleware(): array
    {
        return ['web', 'auth'];
    }

    protected function apiPrefix(): string
    {
        return 'api/blog';
    }

    protected function apiMiddleware(): array
    {
        return ['api', 'auth:api'];
    }
}
```

This gives the Blog module its own route prefix and middleware, independent of the global config.
