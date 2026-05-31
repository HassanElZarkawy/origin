<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Origin\ModuleManager;

class MakeModuleSeederCommand extends Command
{
    protected $signature = 'make:module:seeder {module : The module name} {name : The seeder name}';

    protected $description = 'Create a new seeder in a module';

    public function __construct(
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(ModuleManager $manager): int
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));

        if (! $manager->moduleExists($module)) {
            $this->error("Module [{$module}] does not exist.");

            return self::FAILURE;
        }

        if (! Str::endsWith($name, 'Seeder')) {
            $name .= 'Seeder';
        }

        $seederPath = $manager->getModulePath($module).'/database/seeders';
        $filePath = $seederPath.'/'.$name.'.php';

        if ($this->files->exists($filePath)) {
            $this->error("Seeder [{$name}] already exists in module [{$module}].");

            return self::FAILURE;
        }

        $namespace = $this->getModuleNamespace($module).'\\Database\\Seeders';

        $stub = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Database\Seeder;

class {$name} extends Seeder
{
    public function run(): void
    {
        //
    }
}
PHP;

        $this->makeDirectory($seederPath);
        $this->files->put($filePath, $stub);

        $this->info("Seeder [{$name}] created in module [{$module}].");

        return self::SUCCESS;
    }

    protected function getModuleNamespace(string $module): string
    {
        return config('modular.namespace', 'Modules').'\\'.$module;
    }

    protected function makeDirectory(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
    }
}
