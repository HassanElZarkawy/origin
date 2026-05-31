<?php

namespace Origin\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Origin\ModuleManager;

class ModulePublishCommand extends Command
{
    protected $signature = 'module:publish
        {module : The module to publish}
        {--tag= : The tag to publish (config, views, translations, or all)}
        {--force : Overwrite existing files}';

    protected $description = 'Publish a module\'s assets (views, config, translations)';

    protected array $tagMethods = [
        'config' => 'publishConfig',
        'views' => 'publishViews',
        'translations' => 'publishTranslations',
    ];

    public function handle(ModuleManager $manager): int
    {
        $module = Str()->studly($this->argument('module'));

        if (! $manager->moduleExists($module)) {
            $this->error("Module [{$module}] does not exist.");

            return self::FAILURE;
        }

        $tag = $this->option('tag');
        $force = $this->option('force');

        if ($tag && $tag !== 'all') {
            if (! isset($this->tagMethods[$tag])) {
                $this->error("Invalid tag [{$tag}]. Valid tags: ".implode(', ', array_keys($this->tagMethods)));

                return self::FAILURE;
            }

            $this->{$this->tagMethods[$tag]}($manager, $module, $force);

            return self::SUCCESS;
        }

        foreach ($this->tagMethods as $method) {
            $this->$method($manager, $module, $force);
        }

        $this->info("Module [{$module}] assets published successfully.");

        return self::SUCCESS;
    }

    protected function publishConfig(ModuleManager $manager, string $module, bool $force): void
    {
        $configPath = $manager->getModulePath($module).'/config';

        if (! is_dir($configPath)) {
            return;
        }

        $destination = config_path($module);

        $this->laravel->make(Filesystem::class)->copyDirectory($configPath, $destination, $force ? true : false);

        $this->info("  Published config for [{$module}].");
    }

    protected function publishViews(ModuleManager $manager, string $module, bool $force): void
    {
        $viewPath = $manager->getModulePath($module).'/resources/views';

        if (! is_dir($viewPath)) {
            return;
        }

        $destination = resource_path('views/vendor/'.Str()->lower($module));

        $this->laravel->make(Filesystem::class)->copyDirectory($viewPath, $destination, $force ? true : false);

        $this->info("  Published views for [{$module}].");
    }

    protected function publishTranslations(ModuleManager $manager, string $module, bool $force): void
    {
        $langPath = $manager->getModulePath($module).'/resources/lang';

        if (! is_dir($langPath)) {
            return;
        }

        $destination = lang_path('vendor/'.Str()->lower($module));

        $this->laravel->make(Filesystem::class)->copyDirectory($langPath, $destination, $force ? true : false);

        $this->info("  Published translations for [{$module}].");
    }
}
