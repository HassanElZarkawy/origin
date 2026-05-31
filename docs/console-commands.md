# Console Commands

Run `php artisan module:help` anytime to see the full command reference.

## Generators

### `make:module {name}`

Create a new module with the full directory structure.

```bash
php artisan make:module Blog
php artisan make:module user-management    # Creates UserManagement
```

This also:
- Generates a `composer.json` inside the module with PSR-4 autoloading
- Configures the `wikimedia/composer-merge-plugin` in your app's `composer.json` (on first module creation)
- Runs `composer dump-autoload`

### `make:module:controller {module} {name}`

Create a controller inside a module.

```bash
php artisan make:module:controller Blog PostController
php artisan make:module:controller Blog PostController --api
```

| Flag | Description |
|---|---|
| `--api` | Generate an API resource controller with CRUD methods |

The `Controller` suffix is appended automatically if omitted.

### `make:module:model {module} {name}`

Create an Eloquent model inside a module.

```bash
php artisan make:module:model Blog Post
php artisan make:module:model Blog Post -m
```

| Flag | Description |
|---|---|
| `-m`, `--migration` | Also generate a `create_{table}_table` migration |

### `make:module:migration {module} {name}`

Create a migration file inside a module.

```bash
php artisan make:module:migration Blog add_slug_to_posts_table
```

The file is created at `modules/Blog/database/migrations/{timestamp}_add_slug_to_posts_table.php`.

### `make:module:seeder {module} {name}`

Create a seeder class inside a module.

```bash
php artisan make:module:seeder Blog PostSeeder
```

The `Seeder` suffix is appended automatically if omitted.

## Listing & Status

### `module:list`

List all modules in a table format with their provider, status, and path.

```bash
php artisan module:list
```

Output:

```
+--------+--------------------------------------------+---------+------------------+
| Module | Provider                                   | Status  | Path             |
+--------+--------------------------------------------+---------+------------------+
| Blog   | Modules\Blog\Providers\BlogServiceProvider | Enabled | modules/Blog     |
| Shop   | Modules\Shop\Providers\ShopServiceProvider | Disabled| modules/Shop     |
+--------+--------------------------------------------+---------+------------------+
```

### `module:status {module?}`

Show detailed status for one or all modules. Includes asset counts, route info, and dependency information.

```bash
php artisan module:status          # All modules
php artisan module:status Blog     # Single module
```

Output includes:
- Path, namespace, and provider class
- Route file status (web/api)
- Counts for migrations, seeders, models, controllers, views, translations, commands, and config files
- Registered routes matching the module

### `module:help`

Display all available module commands with descriptions and usage tips.

```bash
php artisan module:help
```

## Migrations

### `module:migrate {module?}`

Run migrations for a specific module or all enabled modules.

```bash
php artisan module:migrate           # All enabled modules
php artisan module:migrate Blog      # Single module
```

| Flag | Description |
|---|---|
| `--seed` | Run module seeders after migration |
| `--force` | Force the operation to run in production |

### `module:migrate:rollback {module?}`

Rollback the last batch of module migrations.

```bash
php artisan module:migrate:rollback
php artisan module:migrate:rollback Blog
```

| Flag | Description |
|---|---|
| `--step=1` | Number of migration batches to rollback |
| `--force` | Force the operation to run in production |

### `module:migrate:reset {module?}`

Rollback all module migrations (reverse order).

```bash
php artisan module:migrate:reset
php artisan module:migrate:reset Blog --force
```

### `module:migrate:fresh {module?}`

Drop all tables and re-run all module migrations from scratch.

```bash
php artisan module:migrate:fresh
php artisan module:migrate:fresh Blog --seed
```

| Flag | Description |
|---|---|
| `--seed` | Run module seeders after fresh migration |
| `--force` | Force the operation to run in production |

## Seeders

### `module:seed {module?}`

Run seeders for a specific module or all enabled modules.

```bash
php artisan module:seed              # All enabled modules
php artisan module:seed Blog         # Single module
```

| Flag | Description |
|---|---|
| `--class=PostSeeder` | Run a specific seeder class |
| `--force` | Force the operation to run in production |

## Enable & Disable

### `module:enable {module}`

Enable a disabled module. Removes it from the `disabled` config list.

```bash
php artisan module:enable Blog
```

### `module:disable {module}`

Disable a module. Adds it to the `disabled` config list. The module's routes, views, migrations, and all other resources stop loading.

```bash
php artisan module:disable Blog
```

## Publishing

### `module:publish {module}`

Publish a module's assets to the main application so they can be overridden.

```bash
php artisan module:publish Blog
```

| Flag | Description |
|---|---|
| `--tag=views` | Publish only views |
| `--tag=config` | Publish only config |
| `--tag=translations` | Publish only translations |
| `--tag=all` | Publish everything (default) |
| `--force` | Overwrite existing published files |

Published locations:

| Asset | Published To |
|---|---|
| Views | `resources/views/vendor/{module}/` |
| Translations | `lang/vendor/{module}/` |
| Config | `config/{module}/` |

## Removal

### `module:remove {module}`

Remove a module entirely — deletes the directory, cleans up the merge-plugin configuration in `composer.json` (when no modules remain), and removes the disabled config entry.

```bash
php artisan module:remove Blog
php artisan module:remove Blog --force    # Skip confirmation
```

| Flag | Description |
|---|---|
| `--force` | Remove without confirmation prompt |
