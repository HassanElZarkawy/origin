<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Origin\ModuleManager;

class ModuleListCommand extends Command
{
    protected $signature = 'module:list';

    protected $description = 'List all modules with their status';

    public function handle(ModuleManager $manager): int
    {
        $modules = $manager->allModules();

        if (empty($modules)) {
            $this->info('No modules found.');

            return self::SUCCESS;
        }

        $rows = array_map(function (string $module) use ($manager) {
            $enabled = $manager->isEnabled($module);
            $path = $manager->getModulePath($module);

            return [
                $module,
                $manager->getProviderClass($module),
                $enabled ? '<fg=green>Enabled</>' : '<fg=red>Disabled</>',
                $path,
            ];
        }, $modules);

        $this->table(
            ['Module', 'Provider', 'Status', 'Path'],
            $rows
        );

        return self::SUCCESS;
    }
}
