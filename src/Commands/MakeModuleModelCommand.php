<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Origin\ModuleManager;

class MakeModuleModelCommand extends Command
{
    protected $signature = 'make:module:model {module : The module name} {name : The model name} {--m|migration : Create a new migration file for the model}';

    protected $description = 'Create a new model in a module';

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

        $path = $manager->getModulePath($module).'/Models/'.$name.'.php';

        if ($this->files->exists($path)) {
            $this->error("Model [{$name}] already exists in module [{$module}].");

            return self::FAILURE;
        }

        $namespace = $this->getModuleNamespace($module);

        $stub = <<<PHP
<?php

namespace {$namespace}\Models;

use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    protected \$guarded = [];
}
PHP;

        $this->makeDirectory(dirname($path));
        $this->files->put($path, $stub);

        $this->info("Model [{$name}] created in module [{$module}].");

        if ($this->option('migration')) {
            $this->createMigration($manager, $module, $name);
        }

        return self::SUCCESS;
    }

    protected function createMigration(ModuleManager $manager, string $module, string $model): void
    {
        $table = Str::snake(Str::pluralStudly($model));
        $migrationName = 'create_'.$table.'_table';
        $managerPath = $manager->getModulePath($module).'/database/migrations';

        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$migrationName}.php";
        $filePath = $managerPath.'/'.$fileName;

        $this->makeDirectory($managerPath);

        $namespace = $this->getModuleNamespace($module);

        $stub = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;

        $this->files->put($filePath, $stub);

        $this->info("Migration [{$migrationName}] created in module [{$module}].");
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
