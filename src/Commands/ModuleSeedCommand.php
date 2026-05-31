<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Origin\ModuleManager;

class ModuleSeedCommand extends Command
{
    protected $signature = 'module:seed
        {module? : The module to seed}
        {--class= : The specific seeder class to run}
        {--force : Force the operation to run in production}';

    protected $description = 'Run seeders for a specific module or all enabled modules';

    public function handle(ModuleManager $manager): int
    {
        $module = $this->argument('module');

        if ($module) {
            $modules = [Str::studly($module)];
        } else {
            $modules = $manager->enabledModules();
        }

        if (empty($modules)) {
            $this->info('No modules to seed.');

            return self::SUCCESS;
        }

        foreach ($modules as $moduleName) {
            if (! $manager->moduleExists($moduleName)) {
                $this->warn("Module [{$moduleName}] does not exist. Skipping.");

                continue;
            }

            $seederPath = $manager->getModulePath($moduleName).'/database/seeders';

            if (! is_dir($seederPath)) {
                $this->line("No seeders found for [{$moduleName}]. Skipping.");

                continue;
            }

            $this->info("Seeding module [{$moduleName}]...");

            if ($class = $this->option('class')) {
                $this->runSeeder($moduleName, $class);
            } else {
                $this->runAllSeeders($manager, $moduleName);
            }
        }

        return self::SUCCESS;
    }

    protected function runSeeder(string $module, string $class): void
    {
        $namespace = config('modular.namespace', 'Modules');

        if (! Str::endsWith($class, 'Seeder')) {
            $class .= 'Seeder';
        }

        $fqcn = "{$namespace}\\{$module}\\Database\\Seeders\\{$class}";

        if (! class_exists($fqcn)) {
            $this->warn("Seeder [{$class}] not found in module [{$module}].");

            return;
        }

        $this->call('db:seed', [
            '--class' => $fqcn,
            '--force' => $this->option('force'),
        ]);
    }

    protected function runAllSeeders(ModuleManager $manager, string $module): void
    {
        $seederPath = $manager->getModulePath($module).'/database/seeders';
        $namespace = config('modular.namespace', 'Modules');

        foreach (glob($seederPath.'/*.php') as $file) {
            $className = basename($file, '.php');
            $fqcn = "{$namespace}\\{$module}\\Database\\Seeders\\{$className}";

            if (class_exists($fqcn)) {
                $this->info("  Running: {$className}");
                $this->call('db:seed', [
                    '--class' => $fqcn,
                    '--force' => $this->option('force'),
                ]);
            }
        }
    }
}
