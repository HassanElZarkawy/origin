<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : The name of the module}';

    protected $description = 'Create a new module';

    public function __construct(
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $path = base_path(config('modular.path', 'modules')).'/'.$name;

        if ($this->files->isDirectory($path)) {
            $this->error("Module [{$name}] already exists.");

            return self::FAILURE;
        }

        $this->createModuleStructure($path, $name);
        $this->registerMergePlugin();

        $this->info("Module [{$name}] created successfully.");
        $this->info("Run 'composer update' to resolve module dependencies and autoload.");

        return self::SUCCESS;
    }

    protected function createModuleStructure(string $path, string $name): void
    {
        $namespace = config('modular.namespace', 'Modules');

        $directories = [
            '',
            'Commands',
            'Controllers',
            'Models',
            'Providers',
            'routes',
            'database/migrations',
            'database/seeders',
            'database/factories',
            'resources/views',
            'resources/lang',
            'config',
        ];

        foreach ($directories as $directory) {
            $this->files->makeDirectory(
                $path.'/'.$directory,
                0755,
                true,
                true
            );
        }

        $providerContent = <<<PHP
<?php

namespace {$namespace}\\{$name}\\Providers;

use Origin\ModuleServiceProvider;

class {$name}ServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        parent::register();
    }

    public function boot(): void
    {
        parent::boot();
    }
}
PHP;

        $this->files->put(
            $path.'/Providers/'.$name.'ServiceProvider.php',
            $providerContent
        );

        $webRoutesContent = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    //
});
PHP;

        $apiRoutesContent = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    //
});
PHP;

        $this->files->put($path.'/routes/web.php', $webRoutesContent);
        $this->files->put($path.'/routes/api.php', $apiRoutesContent);

        $this->createModuleComposerJson($path, $name);
    }

    protected function createModuleComposerJson(string $path, string $name): void
    {
        $namespace = config('modular.namespace', 'Modules');
        $moduleKey = strtolower($namespace).'/'.strtolower($name);

        $composer = [
            'name' => $moduleKey,
            'description' => "{$name} module",
            'type' => 'origin-module',
            'require' => new \stdClass,
            'autoload' => [
                'psr-4' => [
                    "{$namespace}\\{$name}\\" => '',
                ],
            ],
        ];

        $this->files->put(
            $path.'/composer.json',
            json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n"
        );
    }

    protected function registerMergePlugin(): void
    {
        $composerPath = base_path('composer.json');
        $modulePath = config('modular.path', 'modules');

        if (! $this->files->exists($composerPath)) {
            $this->warn('composer.json not found. You will need to manually configure the merge plugin.');

            return;
        }

        $composer = json_decode($this->files->get($composerPath), true);

        if (! isset($composer['extra']['merge-plugin'])) {
            $composer['extra']['merge-plugin'] = [];
        }

        $pattern = "{$modulePath}/*/composer.json";

        if (! in_array($pattern, $composer['extra']['merge-plugin']['include'] ?? [])) {
            $composer['extra']['merge-plugin']['include'] = array_values(
                array_unique(array_merge(
                    $composer['extra']['merge-plugin']['include'] ?? [],
                    [$pattern]
                ))
            );

            $this->files->put(
                $composerPath,
                json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n"
            );

            $this->info('Added merge-plugin config to composer.json.');
        }

        $this->runComposerUpdate();
    }

    protected function runComposerUpdate(): void
    {
        $composer = $this->findComposer();

        $command = array_merge($composer, ['update', '--no-interaction']);

        $this->info('Running composer update...');

        try {
            $process = (new Process($command, base_path()))
                ->setTimeout(300);

            $process->run(function (string $type, string $buffer) {
                $this->output->write($buffer);
            });

            if (! $process->isSuccessful()) {
                $this->warn('Composer update encountered errors. Check the output above.');
            }
        } catch (\Throwable $e) {
            $this->warn('Could not run composer update automatically.');
            $this->warn("Please run 'composer update' manually.");
        }
    }

    protected function findComposer(): array
    {
        $composerPath = base_path('composer.phar');

        if (file_exists($composerPath)) {
            return [PHP_BINARY, $composerPath];
        }

        return ['composer'];
    }
}
