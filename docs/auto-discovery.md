# Auto-Discovery

When a module's `ServiceProvider` extends `Origin\ModuleServiceProvider`, the `boot()` method automatically registers resources based on file existence. If a file or directory doesn't exist, it's silently skipped — zero overhead.

## What Gets Auto-Discovered

### Routes

**Files:** `routes/web.php`, `routes/api.php`

```php
// modules/Blog/routes/web.php
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/blog', [PostController::class, 'index']);
    Route::get('/blog/{id}', [PostController::class, 'show']);
});
```

```php
// modules/Blog/routes/api.php
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    Route::get('/api/posts', [PostController::class, 'apiIndex']);
});
```

Web routes are wrapped in the `web` middleware group (or whatever is configured in `config/modular.routes.web`). API routes are wrapped in the `api` middleware group and prefixed with `/api` by default.

### Migrations

**Directory:** `database/migrations/`

Any migration files in this directory are automatically registered with Laravel. You can run them with:

```bash
php artisan module:migrate Blog
```

Or run all modules' migrations at once:

```bash
php artisan module:migrate
```

### Views

**Directory:** `resources/views/`

Views are namespaced using the module name (lowercase). For a module named `Blog`:

```php
// In a controller
return view('blog::posts.index', ['posts' => $posts]);

// In a Blade template
@include('blog::partials.sidebar')
```

### Translations

**Directory:** `resources/lang/`

Translations are namespaced the same way:

```php
// In code
echo __('blog::messages.welcome');

// In Blade
{{ __('blog::messages.title') }}
```

### Config

**Directory:** `config/`

Each PHP file in the config directory is merged into Laravel's config with a namespaced key:

```php
// modules/Blog/config/posts.php
return [
    'per_page' => 15,
];

// Access it:
config('Blog::posts.per_page'); // 15
```

### Commands

**Directory:** `Commands/`

Any artisan command classes placed in this directory are automatically registered:

```php
// modules/Blog/Commands/CleanupPosts.php
namespace Modules\Blog\Commands;

use Illuminate\Console\Command;

class CleanupPosts extends Command
{
    protected $signature = 'blog:cleanup';
    
    public function handle(): int
    {
        // ...
        return self::SUCCESS;
    }
}
```

This command is immediately available as `php artisan blog:cleanup`.

### Factories

**Directory:** `database/factories/`

Model factories are automatically loaded so they can be used in seeders and tests.

## Overriding Auto-Discovery

Every auto-discovery method on `ModuleServiceProvider` is `protected`. You can override any of them in your module's ServiceProvider:

```php
class BlogServiceProvider extends ModuleServiceProvider
{
    protected function registerRoutes(): void
    {
        // Custom route registration logic
        Route::prefix('blog')
            ->middleware(['web', 'auth'])
            ->group($this->modulePath('routes/web.php'));
    }
}
```

Available override methods:

| Method | What it registers |
|---|---|
| `registerRoutes()` | `routes/web.php` and `routes/api.php` |
| `registerMigrations()` | `database/migrations/` |
| `registerViews()` | `resources/views/` |
| `registerTranslations()` | `resources/lang/` |
| `registerConfig()` | `config/*.php` |
| `registerCommands()` | `Commands/*.php` |
| `registerFactories()` | `database/factories/` |

## The `modulePath()` Helper

Your module's ServiceProvider has a `modulePath()` method that resolves paths relative to the module root:

```php
// In BlogServiceProvider
$this->modulePath();                    // /path/to/project/modules/Blog
$this->modulePath('routes/web.php');    // /path/to/project/modules/Blog/routes/web.php
$this->modulePath('database/migrations'); // /path/to/project/modules/Blog/database/migrations
```

This respects the `path` and `namespace` config, so it works correctly regardless of where your modules live.

## Module Name Resolution

The module name is resolved from the ServiceProvider class name:

- `BlogServiceProvider` → `Blog`
- `UserManagementServiceProvider` → `UserManagement`

You can set it explicitly if needed:

```php
class MyCustomProvider extends ModuleServiceProvider
{
    protected string $name = 'Blog';
}
```
