<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Origin\ModuleManager;

class ModuleRemoveCommand extends Command
{
    protected $signature = 'module:remove {module : The module to remove} {--force : Force removal without confirmation}';

    protected $description = 'Remove a module entirely (directory, autoload entry, config)';

    public function handle(ModuleManager $manager, Filesystem $files): int
    {
        $module = Str::studly($this->argument('module'));

        if (! $manager->moduleExists($module)) {
            $this->error("Module [{$module}] does not exist.");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Are you sure you want to remove module [{$module}]? This cannot be undone.")) {
            $this->info('Module removal cancelled.');

            return self::SUCCESS;
        }

        $modulePath = $manager->getModulePath($module);

        $this->removeModuleDependencies($module, $manager, $files);

        $files->deleteDirectory($modulePath);
        $this->info("Deleted module directory [{$modulePath}].");

        $this->removeMergePluginIfEmpty($manager, $files);
        $this->removeFromDisabledConfig($module, $files);

        $this->info("Module [{$module}] has been removed.");
        $this->warn("Run 'composer update' to clean up removed dependencies.");

        return self::SUCCESS;
    }

    protected function removeModuleDependencies(string $module, ModuleManager $manager, Filesystem $files): void
    {
        $moduleComposerPath = $manager->getModulePath($module).'/composer.json';

        if (! $files->exists($moduleComposerPath)) {
            return;
        }

        $moduleComposer = json_decode($files->get($moduleComposerPath), true);

        $modulePackages = array_keys(array_filter(
            array_merge(
                (array) ($moduleComposer['require'] ?? []),
                (array) ($moduleComposer['require-dev'] ?? [])
            ),
            fn (string $p) => ! str_starts_with($p, 'php') && ! str_starts_with($p, 'ext-'),
            ARRAY_FILTER_USE_KEY
        ));

        if (empty($modulePackages)) {
            return;
        }

        $appComposerPath = base_path('composer.json');

        if (! $files->exists($appComposerPath)) {
            return;
        }

        $appComposer = json_decode($files->get($appComposerPath), true);
        $changed = false;

        foreach ($modulePackages as $package) {
            if (isset($appComposer['require'][$package])) {
                if (! $this->isPackageNeededByOtherModules($package, $module, $manager, $files)) {
                    unset($appComposer['require'][$package]);
                    $changed = true;
                    $this->info("Removed package [{$package}] (only needed by [{$module}]).");
                } else {
                    $this->line("Keeping package [{$package}] (needed by other modules).");
                }
            }
        }

        if ($changed) {
            $files->put(
                $appComposerPath,
                json_encode($appComposer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n"
            );
        }
    }

    protected function removeMergePluginIfEmpty(ModuleManager $manager, Filesystem $files): void
    {
        if (! empty($manager->allModules())) {
            return;
        }

        $composerPath = base_path('composer.json');

        if (! $files->exists($composerPath)) {
            return;
        }

        $composer = json_decode($files->get($composerPath), true);

        if (isset($composer['extra']['merge-plugin'])) {
            unset($composer['extra']['merge-plugin']);

            if (empty($composer['extra'])) {
                unset($composer['extra']);
            }

            $files->put(
                $composerPath,
                json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n"
            );

            $this->info('Removed merge-plugin config (no modules remaining).');
        }
    }

    protected function isPackageNeededByOtherModules(string $package, string $excludedModule, ModuleManager $manager, Filesystem $files): bool
    {
        foreach ($manager->allModules() as $module) {
            if ($module === $excludedModule) {
                continue;
            }

            $moduleComposerPath = $manager->getModulePath($module).'/composer.json';

            if (! $files->exists($moduleComposerPath)) {
                continue;
            }

            $moduleComposer = json_decode($files->get($moduleComposerPath), true);

            $requirements = array_keys(array_merge(
                (array) ($moduleComposer['require'] ?? []),
                (array) ($moduleComposer['require-dev'] ?? [])
            ));

            if (in_array($package, $requirements)) {
                return true;
            }
        }

        return false;
    }

    protected function removeFromDisabledConfig(string $module, Filesystem $files): void
    {
        $configPath = config_path('modular.php');

        if (! $files->exists($configPath)) {
            return;
        }

        $content = $files->get($configPath);

        if (str_contains($content, "'{$module}'") || str_contains($content, "\"{$module}\"")) {
            $content = preg_replace("/\s*['\"]".preg_quote($module, '/')."['\"][,\s]*/", '', $content);
            $files->put($configPath, $content);
        }
    }
}
