<?php

namespace Origin\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Origin\Facades\Modular;
use Origin\ModularServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ModularServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Modular' => Modular::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app->make(Repository::class)->set('modular.path', 'modules');
        $app->make(Repository::class)->set('modular.namespace', 'Modules');
    }

    protected function tearDown(): void
    {
        $modulesPath = base_path('modules');

        if (is_dir($modulesPath)) {
            $files = $this->app->make(Filesystem::class);
            $files->deleteDirectory($modulesPath);
        }

        parent::tearDown();
    }

    protected function addDependsToProvider(string $module, array $depends): void
    {
        $path = base_path("modules/{$module}/Providers/{$module}ServiceProvider.php");
        $content = file_get_contents($path);

        $dependsString = "'".implode("', '", $depends)."'";
        $inject = "{\n    protected array \$depends = [{$dependsString}];\n";

        $content = str_replace(
            "{\n    public function register",
            $inject.'    public function register',
            $content
        );

        file_put_contents($path, $content);
    }
}
