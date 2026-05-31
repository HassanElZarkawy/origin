<?php

namespace Origin;

use Illuminate\Support\ServiceProvider;
use Origin\Commands\MakeModuleCommand;
use Origin\Commands\MakeModuleControllerCommand;
use Origin\Commands\MakeModuleMigrationCommand;
use Origin\Commands\MakeModuleModelCommand;
use Origin\Commands\MakeModuleSeederCommand;
use Origin\Commands\ModuleDisableCommand;
use Origin\Commands\ModuleEnableCommand;
use Origin\Commands\ModuleHelpCommand;
use Origin\Commands\ModuleListCommand;
use Origin\Commands\ModuleMigrateCommand;
use Origin\Commands\ModuleMigrateFreshCommand;
use Origin\Commands\ModuleMigrateResetCommand;
use Origin\Commands\ModuleMigrateRollbackCommand;
use Origin\Commands\ModulePublishCommand;
use Origin\Commands\ModuleRemoveCommand;
use Origin\Commands\ModuleSeedCommand;
use Origin\Commands\ModuleStatusCommand;

class ModularServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/modular.php',
            'modular'
        );

        $this->app->singleton(ModuleManager::class, function ($app) {
            return new ModuleManager($app['files'], $app['config']);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
                MakeModuleControllerCommand::class,
                MakeModuleModelCommand::class,
                MakeModuleMigrationCommand::class,
                MakeModuleSeederCommand::class,
                ModuleHelpCommand::class,
                ModuleListCommand::class,
                ModuleMigrateCommand::class,
                ModuleMigrateRollbackCommand::class,
                ModuleMigrateResetCommand::class,
                ModuleMigrateFreshCommand::class,
                ModuleSeedCommand::class,
                ModuleEnableCommand::class,
                ModuleDisableCommand::class,
                ModulePublishCommand::class,
                ModuleRemoveCommand::class,
                ModuleStatusCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/modular.php' => config_path('modular.php'),
            ], 'modular-config');
        }

        $this->registerModuleProviders();
    }

    protected function registerModuleProviders(): void
    {
        $manager = $this->app->make(ModuleManager::class);

        foreach ($manager->enabledModules() as $module) {
            $provider = $manager->getProviderClass($module);

            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }
}
