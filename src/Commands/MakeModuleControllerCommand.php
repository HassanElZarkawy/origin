<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Origin\ModuleManager;

class MakeModuleControllerCommand extends Command
{
    protected $signature = 'make:module:controller {module : The module name} {name : The controller name} {--api : Generate an API controller}';

    protected $description = 'Create a new controller in a module';

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

        if (! Str::endsWith($name, 'Controller')) {
            $name .= 'Controller';
        }

        $path = $manager->getModulePath($module).'/Controllers/'.$name.'.php';

        if ($this->files->exists($path)) {
            $this->error("Controller [{$name}] already exists in module [{$module}].");

            return self::FAILURE;
        }

        $namespace = $this->getModuleNamespace($module);
        $stub = $this->option('api') ? $this->apiStub($namespace, $name) : $this->webStub($namespace, $name);

        $this->makeDirectory(dirname($path));
        $this->files->put($path, $stub);

        $this->info("Controller [{$name}] created in module [{$module}].");

        return self::SUCCESS;
    }

    protected function webStub(string $namespace, string $name): string
    {
        return <<<PHP
<?php

namespace {$namespace}\Controllers;

use Illuminate\Routing\Controller;

class {$name} extends Controller
{
    public function __invoke()
    {
        //
    }
}
PHP;
    }

    protected function apiStub(string $namespace, string $name): string
    {
        return <<<STUB
<?php

namespace {$namespace}\Controllers;

use Illuminate\Routing\Controller;

class {$name} extends Controller
{
    public function index()
    {
        //
    }

    public function store()
    {
        //
    }

    public function show(\$id)
    {
        //
    }

    public function update(\$id)
    {
        //
    }

    public function destroy(\$id)
    {
        //
    }
}
STUB;
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
