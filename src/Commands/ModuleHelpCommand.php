<?php

namespace Origin\Commands;

use Illuminate\Console\Command;

class ModuleHelpCommand extends Command
{
    protected $signature = 'module:help';

    protected $description = 'Display all available module commands with descriptions';

    protected array $commands = [
        [
            'command' => 'make:module {name}',
            'description' => 'Create a new module with full directory structure',
            'details' => 'Scaffolds Providers, Controllers, Models, routes, database, resources, and config. Registers PSR-4 autoloading in composer.json.',
        ],
        [
            'command' => 'make:module:controller {module} {name}',
            'description' => 'Create a controller in a module',
            'details' => 'Use --api to generate an API resource controller with index, store, show, update, destroy methods.',
        ],
        [
            'command' => 'make:module:model {module} {name}',
            'description' => 'Create a model in a module',
            'details' => 'Use -m or --migration to also generate a create table migration.',
        ],
        [
            'command' => 'make:module:migration {module} {name}',
            'description' => 'Create a migration in a module',
            'details' => 'Creates a timestamped migration file in the module\'s database/migrations directory.',
        ],
        [
            'command' => 'make:module:seeder {module} {name}',
            'description' => 'Create a seeder in a module',
            'details' => 'Creates a seeder class in the module\'s database/seeders directory.',
        ],
        [
            'command' => 'module:list',
            'description' => 'List all modules with their status',
            'details' => 'Shows a table with module name, service provider, enabled/disabled status, and path.',
        ],
        [
            'command' => 'module:status {module?}',
            'description' => 'Show detailed status for one or all modules',
            'details' => 'Displays path, namespace, provider, asset counts (routes, migrations, seeders, models, controllers, views, translations, commands, config), and registered routes.',
        ],
        [
            'command' => 'module:migrate {module?} --seed',
            'description' => 'Run migrations for a module or all enabled modules',
            'details' => 'Use --seed to run seeders after migration. Pass a module name to migrate a single module.',
        ],
        [
            'command' => 'module:migrate:rollback {module?} --step=1',
            'description' => 'Rollback module migrations',
            'details' => 'Use --step to control how many batches to rollback. Defaults to 1.',
        ],
        [
            'command' => 'module:migrate:reset {module?}',
            'description' => 'Rollback all module migrations',
            'details' => 'Drops all module tables by reversing all migrations.',
        ],
        [
            'command' => 'module:migrate:fresh {module?} --seed',
            'description' => 'Drop all tables and re-run module migrations',
            'details' => 'Use --seed to run seeders after fresh migration.',
        ],
        [
            'command' => 'module:seed {module?} --class=',
            'description' => 'Run seeders for a module or all enabled modules',
            'details' => 'Use --class to run a specific seeder. Without a module argument, seeds all enabled modules.',
        ],
        [
            'command' => 'module:enable {module}',
            'description' => 'Enable a module',
            'details' => 'Removes the module from the disabled list in config. The module\'s routes, migrations, views, etc. will be loaded on next request.',
        ],
        [
            'command' => 'module:disable {module}',
            'description' => 'Disable a module',
            'details' => 'Adds the module to the disabled list. All module features (routes, views, etc.) stop loading.',
        ],
        [
            'command' => 'module:publish {module} --tag= --force',
            'description' => 'Publish a module\'s assets to the app',
            'details' => 'Valid tags: config, views, translations, or all (default). Use --force to overwrite existing files.',
        ],
        [
            'command' => 'module:remove {module} --force',
            'description' => 'Remove a module entirely',
            'details' => 'Deletes the module directory and cleans up composer.json. Also removes orphaned dependencies not needed by other modules.',
        ],
    ];

    public function handle(): int
    {
        $this->line('<fg=white;options=bold>Origin — Modular Architecture for Laravel</>');
        $this->newLine();
        $this->line('All available module commands:');
        $this->newLine();

        $lastGroup = '';

        foreach ($this->commands as $command) {
            $isMake = str_starts_with($command['command'], 'make:');

            $group = $isMake ? 'make' : 'module';
            if ($group !== $lastGroup) {
                $lastGroup = $group;
                $this->newLine();
                $this->line('  <fg=yellow;options=bold>'.($isMake ? 'Generators' : 'Utilities').'</>');
                $this->newLine();
            }

            $this->line("  <fg=green>php artisan {$command['command']}</>");
            $this->line("    <fg=gray>{$command['description']}</>");
            $this->line("    <fg=gray>{$command['details']}</>");
            $this->newLine();
        }

        $this->line('  <fg=cyan>Tip:</> Run <fg=white>php artisan module:help</> anytime to see this reference.');
        $this->newLine();
        $this->line('  <fg=cyan>Dependencies:</> Each module has its own <fg=white>composer.json</>.');
        $this->line('  The merge plugin auto-includes <fg=white>modules/*/composer.json</>.');
        $this->line('  Just run <fg=white>composer update</> to install module dependencies.');
        $this->newLine();

        return self::SUCCESS;
    }
}
