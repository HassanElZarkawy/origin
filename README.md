# Origin — Modular Architecture for Laravel

Origin is a Laravel package that brings modular architecture to your application. It provides convention-based auto-discovery of routes, migrations, views, translations, config, commands, and factories — with zero boilerplate per module.

## Requirements

- PHP 8.2+
- Laravel 11.x, 12.x, or 13.x

## Installation

```bash
composer require hassanelzarkawy/origin
```

Laravel's package auto-discovery registers the service provider automatically. No manual configuration needed.

Publish the config file:

```bash
php artisan vendor:publish --tag=modular-config
```

## Quick Start

```bash
# Create your first module
php artisan make:module Blog

# Install module dependencies (each module has its own composer.json)
composer update

# Generate module components
php artisan make:module:model Blog Post --migration
php artisan make:module:controller Blog PostController
php artisan make:module:seeder Blog PostSeeder

# Run module migrations
php artisan module:migrate Blog

# Check module status
php artisan module:status
```

## Documentation

| Page                                                   | Description                                                 |
|--------------------------------------------------------|-------------------------------------------------------------|
| [Configuration](docs/configuration.md)                 | All config options and what they do                         |
| [Creating Modules](docs/creating-modules.md)           | Module structure, make commands, and conventions            |
| [Auto-Discovery](docs/auto-discovery.md)               | How routes, migrations, views, etc. are wired automatically |
| [Console Commands](docs/console-commands.md)           | Complete reference for all artisan commands                 |
| [Module Load Ordering](docs/load-ordering.md)          | Priority and dependency-based module ordering               |
| [Enabling & Disabling Modules](docs/enable-disable.md) | Toggle modules on and off                                   |
| [Publishing Module Assets](docs/publishing.md)         | Publish views, config, and translations                     |
| [Removing Modules](docs/removing-modules.md)           | Clean removal of modules                                    |
| [Facade & API Reference](docs/api-reference.md)        | Programmatic usage via the Modular facade                   |
| [Testing](docs/testing.md)                             | Testing your modules                                        |

## How It Works

Origin uses [wikimedia/composer-merge-plugin](https://github.com/wikimedia/composer-merge-plugin) to integrate modules with Composer. Each module gets its own `composer.json`, and the merge plugin automatically includes `modules/*/composer.json` when you run `composer update`.

On every request, Laravel boots the `Origin\ModularServiceProvider`. This provider:

1. Scans the `modules/` directory for module directories
2. Filters out any disabled modules
3. Resolves the correct load order (respecting priority and dependencies)
4. Registers each module's `ServiceProvider` with Laravel

Each module's `ServiceProvider` extends `Origin\ModuleServiceProvider`, which auto-discovers and registers:

- **Routes** — `routes/web.php` and `routes/api.php`
- **Migrations** — `database/migrations/`
- **Views** — `resources/views/` (namespaced as `{module}::`)
- **Translations** — `resources/lang/`
- **Config** — `config/*.php`
- **Commands** — `Commands/`
- **Factories** — `database/factories/`

Everything is convention-based. If the file or directory exists, it's registered. If it doesn't, it's silently skipped.

## Module Directory Structure

```
modules/Blog/
├── Commands/                    # Auto-registered artisan commands
├── Controllers/                 # Module controllers
├── Models/                      # Eloquent models
├── Providers/
│   └── BlogServiceProvider.php  # Extends Origin\ModuleServiceProvider
├── routes/
│   ├── web.php                  # Web routes
│   └── api.php                  # API routes
├── database/
│   ├── migrations/              # Module migrations
│   ├── seeders/                 # Module seeders
│   └── factories/               # Model factories
├── resources/
│   ├── views/                   # Blade views (use as blog::*)
│   └── lang/                    # Translation files
├── config/                      # Module config files
└── composer.json                # Module dependencies (merged by composer-merge-plugin)
```

## License

MIT
