<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Origin\ModuleManager;

class ModuleStatusCommand extends Command
{
    protected $signature = 'module:status {module? : The module to inspect}';

    protected $description = 'Show detailed status for modules';

    public function handle(ModuleManager $manager): int
    {
        $module = $this->argument('module');

        if ($module) {
            $this->showModuleStatus($manager, Str::studly($module));
        } else {
            $modules = $manager->allModules();

            if (empty($modules)) {
                $this->info('No modules found.');

                return self::SUCCESS;
            }

            foreach ($modules as $moduleName) {
                $this->showModuleStatus($manager, $moduleName);
                $this->newLine();
            }
        }

        return self::SUCCESS;
    }

    protected function showModuleStatus(ModuleManager $manager, string $module): void
    {
        if (! $manager->moduleExists($module)) {
            $this->error("Module [{$module}] does not exist.");

            return;
        }

        $enabled = $manager->isEnabled($module);
        $path = $manager->getModulePath($module);
        $namespace = config('modular.namespace', 'Modules').'\\'.$module;

        $status = $enabled ? '<fg=green>Enabled</>' : '<fg=red>Disabled</>';

        $this->line("<fg=white;options=bold>{$module}</> — {$status}");
        $this->line("  Path:       {$path}");
        $this->line("  Namespace:  {$namespace}");
        $this->line('  Provider:   '.$manager->getProviderClass($module));

        $this->newLine();

        $this->line('  <fg=white;options=bold>Assets:</>');
        $this->line('    Routes:       '.$this->fileStatus($path.'/routes/web.php').' (web)  '.$this->fileStatus($path.'/routes/api.php').' (api)');
        $this->line('    Migrations:   '.$this->countStatus($path.'/database/migrations', 'migration'));
        $this->line('    Seeders:      '.$this->countStatus($path.'/database/seeders', 'seeder'));
        $this->line('    Models:       '.$this->countStatus($path.'/Models', 'model'));
        $this->line('    Controllers:  '.$this->countStatus($path.'/Controllers', 'controller'));
        $this->line('    Views:        '.$this->countStatus($path.'/resources/views', 'view'));
        $this->line('    Translations: '.$this->countStatus($path.'/resources/lang', 'translation file'));
        $this->line('    Commands:     '.$this->countStatus($path.'/Commands', 'command'));
        $this->line('    Config:       '.$this->countStatus($path.'/config', 'config file'));

        $routes = $this->getModuleRoutes($module);
        if (! empty($routes)) {
            $this->newLine();
            $this->line('  <fg=white;options=bold>Registered Routes:</>');

            foreach ($routes as $route) {
                $methods = implode('|', $route['methods']);
                $this->line("    <fg=cyan>{$methods}</> {$route['uri']}");
            }
        }
    }

    protected function fileStatus(string $path): string
    {
        return file_exists($path) ? '<fg=green>✓</>' : '<fg=red>✗</>';
    }

    protected function countStatus(string $path, string $label): string
    {
        if (! is_dir($path)) {
            return '<fg=red>none</>';
        }

        $count = count(glob($path.'/*'));

        if ($count === 0) {
            return '<fg=yellow>empty</>';
        }

        return "<fg=green>{$count}</> {$label}".($count !== 1 ? 's' : '');
    }

    protected function getModuleRoutes(string $module): array
    {
        $routes = [];

        try {
            foreach ($this->laravel->make(Router::class)->getRoutes() as $route) {
                $action = $route->getAction('uses');

                if (is_string($action) && str_starts_with($action, config('modular.namespace', 'Modules').'\\'.$module.'\\')) {
                    $routes[] = [
                        'methods' => $route->methods(),
                        'uri' => $route->uri(),
                    ];
                }

                if (! is_string($action) && $action instanceof \Closure) {
                    $reflection = new \ReflectionFunction($action);
                    $closureFile = $reflection->getFileName();

                    if ($closureFile && str_contains($closureFile, "modules/{$module}/")) {
                        $routes[] = [
                            'methods' => $route->methods(),
                            'uri' => $route->uri(),
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return $routes;
    }
}
