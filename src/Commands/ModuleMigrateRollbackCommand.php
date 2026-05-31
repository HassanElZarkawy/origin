<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Origin\ModuleManager;

class ModuleMigrateRollbackCommand extends Command
{
    protected $signature = 'module:migrate:rollback
        {module? : The module to rollback}
        {--step=1 : Number of migrations to rollback}
        {--force : Force the operation to run in production}';

    protected $description = 'Rollback module migrations';

    public function handle(ModuleManager $manager): int
    {
        $module = $this->argument('module');

        if ($module) {
            $modules = [Str::studly($module)];
        } else {
            $modules = $manager->enabledModules();
        }

        foreach ($modules as $moduleName) {
            if (! $manager->moduleExists($moduleName)) {
                $this->warn("Module [{$moduleName}] does not exist. Skipping.");

                continue;
            }

            $migrationPath = $this->relativeMigrationPath($manager, $moduleName);

            if (! is_dir(base_path($migrationPath))) {
                $this->line("No migrations found for [{$moduleName}]. Skipping.");

                continue;
            }

            $this->info("Rolling back module [{$moduleName}]...");

            $this->call('migrate:rollback', [
                '--path' => $migrationPath,
                '--step' => $this->option('step'),
                '--force' => $this->option('force'),
            ]);
        }

        return self::SUCCESS;
    }

    protected function relativeMigrationPath(ModuleManager $manager, string $module): string
    {
        $basePath = base_path();
        $fullPath = $manager->getModulePath($module).'/database/migrations';

        return ltrim(str_replace($basePath, '', $fullPath), '/');
    }
}
