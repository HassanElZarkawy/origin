<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Origin\ModuleManager;

class ModuleDisableCommand extends Command
{
    protected $signature = 'module:disable {module : The module to disable}';

    protected $description = 'Disable a module';

    public function handle(ModuleManager $manager, Filesystem $files): int
    {
        $module = Str()->studly($this->argument('module'));

        if (! $manager->moduleExists($module)) {
            $this->error("Module [{$module}] does not exist.");

            return self::FAILURE;
        }

        if (! $manager->isEnabled($module)) {
            $this->info("Module [{$module}] is already disabled.");

            return self::SUCCESS;
        }

        $this->updateConfig($module, $files);
        $this->info("Module [{$module}] has been disabled.");

        return self::SUCCESS;
    }

    protected function updateConfig(string $module, Filesystem $files): void
    {
        $configPath = config_path('modular.php');

        if ($files->exists($configPath)) {
            $content = $files->get($configPath);

            if (! str_contains($content, "'{$module}'") && ! str_contains($content, "\"{$module}\"")) {
                $content = preg_replace(
                    "/('disabled'\s*=>\s*\[)/",
                    "$1\n        '{$module}',",
                    $content
                );
                $files->put($configPath, $content);
            }
        }

        $disabled = config('modular.disabled', []);

        if (! in_array($module, $disabled)) {
            $disabled[] = $module;
            config(['modular.disabled' => $disabled]);
        }
    }
}
