# Creating Modules

## The `make:module` Command

```bash
php artisan make:module Blog
```

This creates the full module structure, generates a module `composer.json` with PSR-4 autoloading, and configures the [composer-merge-plugin](https://github.com/wikimedia/composer-merge-plugin) in your app's `composer.json` so module dependencies are automatically discovered:

```
modules/Blog/
├── Commands/
├── Controllers/
├── Models/
├── Providers/
│   └── BlogServiceProvider.php
├── routes/
│   ├── web.php
│   └── api.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   └── lang/
├── config/
└── composer.json              # Module dependencies (merged by composer-merge-plugin)
```

The module name is automatically converted to `StudlyCase`:

```bash
php artisan make:module user-management
# Creates modules/UserManagement/
```

### What gets generated

**BlogServiceProvider.php** — the module's service provider:

```php
<?php

namespace Modules\Blog\Providers;

use Origin\ModuleServiceProvider;

class BlogServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        parent::register();
    }

    public function boot(): void
    {
        parent::boot();
    }
}
```

Both `register()` and `boot()` call their parent, which triggers auto-discovery. You can add your own logic before or after the `parent::` calls.

**routes/web.php** and **routes/api.php** — starter route files with the basic middleware groups pre-configured.

**composer.json** — each module gets its own composer file for managing dependencies:

```json
{
    "name": "modules/blog",
    "type": "origin-module",
    "autoload": {
        "psr-4": {
            "Modules\\Blog\\": ""
        }
    },
    "require": {}
}
```

### Installing Module Dependencies

Origin uses `wikimedia/composer-merge-plugin` to merge module dependencies into your application. When you create your first module, the merge plugin configuration is added to your app's `composer.json`:

```json
{
    "extra": {
        "merge-plugin": {
            "include": [
                "modules/*/composer.json"
            ]
        }
    }
}
```

To add a dependency to a module, edit its `composer.json` and add the package to `require`:

```json
{
    "require": {
        "fakerphp/faker": "^1.23"
    }
}
```

Then run:

```bash
composer update
```

The merge plugin automatically discovers all `modules/*/composer.json` files and merges their dependencies with your application's dependencies.

## Generator Commands

### Create a Controller

```bash
php artisan make:module:controller Blog PostController
```

Creates `modules/Blog/Controllers/PostController.php`:

```php
namespace Modules\Blog\Controllers;

use Illuminate\Routing\Controller;

class PostController extends Controller
{
    public function __invoke()
    {
        //
    }
}
```

The `Controller` suffix is automatically appended if omitted:

```bash
php artisan make:module:controller Blog Post
# Creates PostController.php
```

#### API Controller

Use the `--api` flag for a resource controller with CRUD methods:

```bash
php artisan make:module:controller Blog PostController --api
```

Generates `index()`, `store()`, `show($id)`, `update($id)`, and `destroy($id)`.

### Create a Model

```bash
php artisan make:module:model Blog Post
```

Creates `modules/Blog/Models/Post.php`:

```php
namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];
}
```

#### With Migration

Use `--migration` (or `-m`) to also generate a `create_posts_table` migration:

```bash
php artisan make:module:model Blog Post --migration
```

Creates both `Models/Post.php` and `database/migrations/{timestamp}_create_posts_table.php` with the schema builder stub.

### Create a Migration

```bash
php artisan make:module:migration Blog add_slug_to_posts_table
```

Creates a timestamped migration file at `modules/Blog/database/migrations/`.

### Create a Seeder

```bash
php artisan make:module:seeder Blog PostSeeder
```

Creates `modules/Blog/database/seeders/PostSeeder.php`:

```php
namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        //
    }
}
```

The `Seeder` suffix is automatically appended if omitted.

## Module Naming Conventions

| Module Name | Directory | Namespace |
|---|---|---|
| `Blog` | `modules/Blog/` | `Modules\Blog\` |
| `user-management` | `modules/UserManagement/` | `Modules\UserManagement\` |
| `shop_catalog` | `modules/ShopCatalog/` | `Modules\ShopCatalog\` |

The name is always converted to `StudlyCase`.
