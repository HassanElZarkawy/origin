<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Origin\ModuleManager;

class ModuleMigrateCommand extends Command
{
    protected $signature = 'module:migrate
        {module? : The module to migrate}
        {--seed : Run module seeders after migration}
        {--force : Force the operation to run in production}';

    protected $description = 'Run migrations for a specific module or all enabled modules';

    public function handle(ModuleManager $manager): int
    {
        $module = $this->argument('module');

        if ($module) {
            $modules = [Str()->studly($module)];
        } else {
            $modules = $manager->enabledModules();
        }

        if (empty($modules)) {
            $this->info('No modules to migrate.');

            return self::SUCCESS;
        }

        foreach ($modules as $moduleName) {
            if (! $manager->moduleExists($moduleName)) {
                $this->warn("Module [{$moduleName}] does not exist. Skipping.");

                continue;
            }

            $migrationPath = $manager->getModulePath($moduleName).'/database/migrations';

            if (! is_dir($migrationPath)) {
                $this->line("No migrations found for [{$moduleName}]. Skipping.");

                continue;
            }

            $this->info("Migrating module [{$moduleName}]...");

            $this->call('migrate', [
                '--path' => $this->relativeMigrationPath($manager, $moduleName),
                '--force' => $this->option('force'),
            ]);

            if ($this->option('seed')) {
                $this->runModuleSeeders($manager, $moduleName);
            }
        }

        return self::SUCCESS;
    }

    protected function relativeMigrationPath(ModuleManager $manager, string $module): string
    {
        $basePath = base_path();
        $fullPath = $manager->getModulePath($module).'/database/migrations';

        return ltrim(str_replace($basePath, '', $fullPath), '/');
    }

    protected function runModuleSeeders(ModuleManager $manager, string $module): void
    {
        $seederPath = $manager->getModulePath($module).'/database/seeders';

        if (! is_dir($seederPath)) {
            return;
        }

        $namespace = config('modular.namespace', 'Modules');

        foreach (glob($seederPath.'/*.php') as $file) {
            $className = basename($file, '.php');
            $fqcn = "{$namespace}\\{$module}\\Database\\Seeders\\{$className}";

            if (class_exists($fqcn)) {
                $this->info("  Seeding: {$className}");
                $this->call('db:seed', [
                    '--class' => $fqcn,
                    '--force' => $this->option('force'),
                ]);
            }
        }
    }
}
