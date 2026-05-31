<?php

namespace Origin;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class ModuleServiceProvider extends ServiceProvider
{
    protected string $name = '';

    protected string $moduleName;

    protected array $depends = [];

    public function __construct(Application $app)
    {
        parent::__construct($app);

        if (empty($this->name)) {
            $className = new ReflectionClass($this)->getShortName();
            $this->moduleName = Str::beforeLast($className, 'ServiceProvider');
        } else {
            $this->moduleName = Str::studly($this->name);
        }
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerCommands();
        $this->registerFactories();
    }

    public function register(): void
    {
        $this->registerConfig();
    }

    public function moduleName(): string
    {
        return $this->moduleName;
    }

    public function modulePath(string $path = ''): string
    {
        $basePath = base_path(config('modular.path', 'modules'));
        $moduleRoot = $basePath.'/'.$this->moduleName();

        if ($path) {
            return $moduleRoot.'/'.ltrim($path, '/');
        }

        return $moduleRoot;
    }

    protected function registerRoutes(): void
    {
        $webRoutes = $this->modulePath('routes/web.php');

        if (file_exists($webRoutes)) {
            $this->app->make(Router::class)->middleware($this->routeMiddleware())
                ->prefix($this->routePrefix())
                ->group($webRoutes);
        }

        $apiRoutes = $this->modulePath('routes/api.php');

        if (file_exists($apiRoutes)) {
            $this->app->make(Router::class)->middleware($this->apiMiddleware())
                ->prefix($this->apiPrefix())
                ->group($apiRoutes);
        }
    }

    protected function registerMigrations(): void
    {
        $migrationPath = $this->modulePath('database/migrations');

        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    protected function registerViews(): void
    {
        $viewPath = $this->modulePath('resources/views');

        if (is_dir($viewPath)) {
            $this->loadViewsFrom($viewPath, $this->moduleName());
        }
    }

    protected function registerTranslations(): void
    {
        $langPath = $this->modulePath('resources/lang');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleName());
        }
    }

    protected function registerConfig(): void
    {
        $configPath = $this->modulePath('config');

        if (is_dir($configPath)) {
            $files = glob($configPath.'/*.php');

            foreach ($files as $file) {
                $key = basename($file, '.php');
                $this->mergeConfigFrom($file, $this->moduleName().'::'.$key);
            }
        }
    }

    protected function registerCommands(): void
    {
        $commandPath = $this->modulePath('Commands');

        if (! is_dir($commandPath)) {
            return;
        }

        $namespace = $this->getModuleNamespace();

        foreach (glob($commandPath.'/*.php') as $file) {
            $className = basename($file, '.php');
            $fqcn = "{$namespace}\\Commands\\{$className}";

            if (class_exists($fqcn) && is_subclass_of($fqcn, Command::class)) {
                Artisan::starting(function ($artisan) use ($fqcn) {
                    $artisan->resolve($fqcn);
                });
            }
        }
    }

    protected function registerFactories(): void
    {
        $factoryPath = $this->modulePath('database/factories');

        if (! is_dir($factoryPath)) {
            return;
        }

        if ($this->app->bound(Factory::class)) {
            $namespace = $this->getModuleNamespace();
            $this->app->make(Factory::class)->load($factoryPath);
        }
    }

    protected function routePrefix(): string
    {
        return config('modular.routes.web.prefix', '');
    }

    protected function routeMiddleware(): array
    {
        return config('modular.routes.web.middleware', ['web']);
    }

    protected function apiPrefix(): string
    {
        return config('modular.routes.api.prefix', 'api');
    }

    protected function apiMiddleware(): array
    {
        return config('modular.routes.api.middleware', ['api']);
    }

    protected function getModuleNamespace(): string
    {
        $baseNamespace = config('modular.namespace', 'Modules');

        return "{$baseNamespace}\\{$this->moduleName()}";
    }
}
