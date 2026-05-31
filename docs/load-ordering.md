# Module Load Ordering

By default, modules load in alphabetical order. Origin provides two mechanisms to control the order: a **global priority list** and **per-module dependencies**.

## Priority List

Add module names to the `priority` array in `config/modular.php`:

```php
'priority' => ['Users', 'Billing', 'Blog'],
```

Modules in this list load first, in the exact order specified. All other modules load alphabetically after them.

This is useful for modules that provide shared infrastructure (like a `Users` or `Core` module) that other modules depend on.

## Dependency-Based Ordering

Individual modules can declare their dependencies via the `$depends` property on their ServiceProvider:

```php
// modules/Blog/Providers/BlogServiceProvider.php
class BlogServiceProvider extends ModuleServiceProvider
{
    protected array $depends = ['Users'];
}
```

```php
// modules/Shop/Providers/ShopServiceProvider.php
class ShopServiceProvider extends ModuleServiceProvider
{
    protected array $depends = ['Users', 'Billing'];
}
```

With these declarations, Origin resolves the load order using a topological sort:

1. `Users` loads first (no dependencies)
2. `Billing` loads second (depends on `Users`)
3. `Blog` loads third (depends on `Users`)
4. `Shop` loads fourth (depends on `Users` and `Billing`)

Dependencies are parsed from the ServiceProvider file before instantiation — no need for the class to be autoloaded first.

## Circular Dependency Detection

If two or more modules depend on each other, Origin throws a `RuntimeException` at boot time:

```php
// BlogServiceProvider.php
protected array $depends = ['Shop'];

// ShopServiceProvider.php
protected array $depends = ['Blog'];
```

```
RuntimeException: Circular dependency detected involving module [Blog].
```

This prevents your application from entering an infinite loop during boot.

## Resolution Algorithm

The full resolution order in `ModuleManager::enabledModules()`:

1. Filter out modules in the `disabled` list
2. Separate into priority-listed and remaining modules
3. Sort priority modules by their position in the `priority` config
4. Sort remaining modules alphabetically
5. Merge both lists (priority first)
6. Run a topological sort respecting `$depends` declarations
7. Detect and throw on circular dependencies

## Example: Multi-Module Application

```php
// config/modular.php
'priority' => ['Core'],
'disabled' => ['LegacyImporter'],
```

```php
// CoreServiceProvider — no dependencies, loaded first via priority
class CoreServiceProvider extends ModuleServiceProvider {}

// UsersServiceProvider — depends on Core
class UsersServiceProvider extends ModuleServiceProvider
{
    protected array $depends = ['Core'];
}

// BlogServiceProvider — depends on Users (which depends on Core)
class BlogServiceProvider extends ModuleServiceProvider
{
    protected array $depends = ['Users'];
}

// ShopServiceProvider — depends on Users and Billing
class ShopServiceProvider extends ModuleServiceProvider
{
    protected array $depends = ['Users', 'Billing'];
}
```

Final load order:
1. `Core` (priority + no deps)
2. `Users` (depends on Core)
3. `Billing` (no deps, alphabetical)
4. `Blog` (depends on Users)
5. `Shop` (depends on Users + Billing)
