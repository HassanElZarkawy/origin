<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Origin\ModuleManager;

class ModuleEnableCommand extends Command
{
    protected $signature = 'module:enable {module : The module to enable}';

    protected $description = 'Enable a module';

    public function handle(ModuleManager $manager, Filesystem $files): int
    {
        $module = Str()->studly($this->argument('module'));

        if (! $manager->moduleExists($module)) {
            $this->error("Module [{$module}] does not exist.");

            return self::FAILURE;
        }

        if ($manager->isEnabled($module)) {
            $this->info("Module [{$module}] is already enabled.");

            return self::SUCCESS;
        }

        $this->updateConfig($module, $files);
        $this->info("Module [{$module}] has been enabled.");

        return self::SUCCESS;
    }

    protected function updateConfig(string $module, Filesystem $files): void
    {
        $configPath = config_path('modular.php');

        if ($files->exists($configPath)) {
            $config = $files->get($configPath);
            $config = preg_replace(
                "/(['\"]".preg_quote($module, '/')."['\"])/",
                '',
                $config
            );
            $files->put($configPath, $config);
        }

        $disabled = config('modular.disabled', []);
        $key = array_search($module, $disabled);

        if ($key !== false) {
            unset($disabled[$key]);
            config(['modular.disabled' => array_values($disabled)]);
        }
    }
}
