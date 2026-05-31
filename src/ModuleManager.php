<?php

namespace Origin;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleManager
{
    public function __construct(
        protected Filesystem $files,
        protected $config
    ) {}

    public function allModules(): array
    {
        $path = $this->getModulesPath();

        if (! $this->files->isDirectory($path)) {
            return [];
        }

        return collect($this->files->directories($path))
            ->map(fn (string $dir) => basename($dir))
            ->values()
            ->all();
    }

    public function enabledModules(): array
    {
        $disabled = $this->config->get('modular.disabled', []);

        $modules = array_values(
            array_filter($this->allModules(), fn (string $module) => ! in_array($module, $disabled))
        );

        return $this->resolveLoadOrder($modules);
    }

    public function resolveLoadOrder(array $modules): array
    {
        $priority = $this->config->get('modular.priority', []);
        $dependencies = $this->resolveDependencies($modules);

        $prioritized = array_values(array_filter($modules, fn (string $m) => in_array($m, $priority)));
        $remaining = array_values(array_filter($modules, fn (string $m) => ! in_array($m, $priority)));

        usort($prioritized, fn (string $a, string $b) => $this->sortByPriority($priority, $a, $b));
        sort($remaining);

        $ordered = array_merge($prioritized, $remaining);

        return $this->topologicalSort($ordered, $dependencies);
    }

    public function getModuleDependencies(string $module): array
    {
        $providerPath = $this->getModulePath($module).'/Providers/'.$module.'ServiceProvider.php';

        if (! $this->files->exists($providerPath)) {
            return [];
        }

        $content = $this->files->get($providerPath);

        if (! preg_match('/protected\s+array\s+\$depends\s*=\s*\[([^\]]*)\]/', $content, $matches)) {
            return [];
        }

        $items = explode(',', $matches[1]);

        return array_values(array_filter(array_map(function (string $item) {
            $item = trim($item, " \t\n\r\0\x0B'\"");

            return $item !== '' ? Str::studly($item) : null;
        }, $items)));
    }

    protected function resolveDependencies(array $modules): array
    {
        $dependencies = [];

        foreach ($modules as $module) {
            $dependencies[$module] = array_values(
                array_filter($this->getModuleDependencies($module), fn (string $dep) => in_array($dep, $modules))
            );
        }

        return $dependencies;
    }

    protected function sortByPriority(array $priority, string $a, string $b): int
    {
        $aIndex = array_search($a, $priority);
        $bIndex = array_search($b, $priority);

        return $aIndex <=> $bIndex;
    }

    protected function topologicalSort(array $modules, array $dependencies): array
    {
        $sorted = [];
        $visited = [];
        $visiting = [];

        $visit = function (string $module) use (&$visit, &$sorted, &$visited, &$visiting, $dependencies) {
            if (isset($visited[$module])) {
                return;
            }

            if (isset($visiting[$module])) {
                throw new \RuntimeException("Circular dependency detected involving module [{$module}].");
            }

            $visiting[$module] = true;

            foreach ($dependencies[$module] ?? [] as $dependency) {
                $visit($dependency);
            }

            unset($visiting[$module]);
            $visited[$module] = true;
            $sorted[] = $module;
        };

        foreach ($modules as $module) {
            $visit($module);
        }

        return $sorted;
    }

    public function disabledModules(): array
    {
        return array_values(
            array_filter($this->allModules(), fn (string $module) => ! $this->isEnabled($module))
        );
    }

    public function isEnabled(string $module): bool
    {
        return ! in_array($module, $this->config->get('modular.disabled', []));
    }

    public function enable(string $module): void
    {
        $disabled = $this->config->get('modular.disabled', []);
        $key = array_search($module, $disabled);

        if ($key !== false) {
            unset($disabled[$key]);
            $this->config->set('modular.disabled', array_values($disabled));
        }
    }

    public function disable(string $module): void
    {
        $disabled = $this->config->get('modular.disabled', []);

        if (! in_array($module, $disabled)) {
            $disabled[] = $module;
            $this->config->set('modular.disabled', $disabled);
        }
    }

    public function getProviderClass(string $module): string
    {
        $namespace = $this->config->get('modular.namespace', 'Modules');

        return "{$namespace}\\{$module}\\Providers\\{$module}ServiceProvider";
    }

    public function getModulePath(string $module): string
    {
        return $this->getModulesPath().'/'.$module;
    }

    public function getModulesPath(): string
    {
        return base_path($this->config->get('modular.path', 'modules'));
    }

    public function moduleExists(string $module): bool
    {
        return $this->files->isDirectory($this->getModulePath($module));
    }
}
