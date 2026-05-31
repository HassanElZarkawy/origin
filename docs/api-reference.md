# Facade & API Reference

## The `Modular` Facade

The `Modular` facade provides access to the `ModuleManager` instance:

```php
use Origin\Facades\Modular;
```

### Available Methods

#### `Modular::allModules(): array`

Returns all module names found in the modules directory, regardless of enabled/disabled state.

```php
$modules = Modular::allModules();
// ['Blog', 'LegacyShop', 'Users']
```

#### `Modular::enabledModules(): array`

Returns module names that are not disabled, sorted by priority and dependencies.

```php
$modules = Modular::enabledModules();
// ['Users', 'Blog', 'Shop']
```

#### `Modular::disabledModules(): array`

Returns module names that are in the disabled list.

```php
$modules = Modular::disabledModules();
// ['LegacyShop']
```

#### `Modular::isEnabled(string $module): bool`

Check if a specific module is enabled.

```php
if (Modular::isEnabled('Blog')) {
    // Blog module is active
}
```

#### `Modular::enable(string $module): void`

Enable a module at runtime (does not persist to config file).

```php
Modular::enable('Blog');
```

#### `Modular::disable(string $module): void`

Disable a module at runtime (does not persist to config file).

```php
Modular::disable('Blog');
```

## Direct `ModuleManager` Usage

You can also resolve the `ModuleManager` from the container:

```php
use Origin\ModuleManager;

$manager = app(ModuleManager::class);
```

### Additional Methods

These methods are available on the `ModuleManager` instance but are not exposed via the facade:

#### `$manager->getProviderClass(string $module): string`

Returns the fully-qualified class name of a module's ServiceProvider.

```php
$manager->getProviderClass('Blog');
// 'Modules\Blog\Providers\BlogServiceProvider'
```

#### `$manager->getModulePath(string $module): string`

Returns the absolute filesystem path for a module.

```php
$manager->getModulePath('Blog');
// '/home/user/project/modules/Blog'
```

#### `$manager->getModulesPath(): string`

Returns the absolute path to the modules root directory.

```php
$manager->getModulesPath();
// '/home/user/project/modules'
```

#### `$manager->moduleExists(string $module): bool`

Checks if a module directory exists on disk.

```php
$manager->moduleExists('Blog'); // true
$manager->moduleExists('Foo');  // false
```

#### `$manager->resolveLoadOrder(array $modules): array`

Resolves the load order for a given array of module names, respecting priority config and dependencies.

```php
$ordered = $manager->resolveLoadOrder(['Blog', 'Users', 'Shop']);
```

#### `$manager->getModuleDependencies(string $module): array`

Returns the declared dependencies for a module by parsing its ServiceProvider file.

```php
$deps = $manager->getModuleDependencies('Blog');
// ['Users']
```

## `ModuleServiceProvider` API

Your module's ServiceProvider extends `Origin\ModuleServiceProvider`, which provides these methods:

### `moduleName(): string`

Returns the resolved module name.

```php
// In BlogServiceProvider
$this->moduleName(); // 'Blog'
```

### `modulePath(string $path = ''): string`

Returns the absolute path to the module root, or a subpath within it.

```php
$this->modulePath();                      // /path/to/project/modules/Blog
$this->modulePath('routes/web.php');      // /path/to/project/modules/Blog/routes/web.php
$this->modulePath('database/migrations'); // /path/to/project/modules/Blog/database/migrations
```

### Overridable Methods

| Method | Returns | Purpose |
|---|---|---|
| `routePrefix(): string` | URL prefix for web routes | Override to customize web route prefix |
| `routeMiddleware(): array` | Middleware for web routes | Override to add/customize web middleware |
| `apiPrefix(): string` | URL prefix for API routes | Override to customize API route prefix |
| `apiMiddleware(): array` | Middleware for API routes | Override to add/customize API middleware |

### Properties

| Property | Type | Default | Purpose |
|---|---|---|---|
| `$name` | `string` | `''` | Explicit module name (overrides auto-detection) |
| `$depends` | `array` | `[]` | Module names this module depends on |
