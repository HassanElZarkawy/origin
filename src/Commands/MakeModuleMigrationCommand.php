<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Origin\ModuleManager;

class MakeModuleMigrationCommand extends Command
{
    protected $signature = 'make:module:migration {module : The module name} {name : The migration name}';

    protected $description = 'Create a new migration in a module';

    public function __construct(
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(ModuleManager $manager): int
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::snake($this->argument('name'));

        if (! $manager->moduleExists($module)) {
            $this->error("Module [{$module}] does not exist.");

            return self::FAILURE;
        }

        $migrationPath = $manager->getModulePath($module).'/database/migrations';
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$name}.php";
        $filePath = $migrationPath.'/'.$fileName;

        $this->makeDirectory($migrationPath);

        $stub = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
PHP;

        $this->files->put($filePath, $stub);

        $this->info("Migration [{$name}] created in module [{$module}].");

        return self::SUCCESS;
    }

    protected function makeDirectory(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
    }
}
