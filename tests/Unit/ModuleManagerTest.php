<?php

use Origin\ModuleManager;

it('can list all modules', function () {
    $manager = resolve(ModuleManager::class);

    expect($manager->allModules())->toBeArray();
});

it('returns empty array when modules directory does not exist', function () {
    $manager = resolve(ModuleManager::class);

    expect($manager->allModules())->toBe([]);
});

it('can check if a module is enabled', function () {
    $manager = resolve(ModuleManager::class);

    expect($manager->isEnabled('NonExistent'))->toBeTrue();
});

it('can disable and enable modules', function () {
    $manager = resolve(ModuleManager::class);

    $manager->disable('Blog');
    expect($manager->isEnabled('Blog'))->toBeFalse();

    $manager->enable('Blog');
    expect($manager->isEnabled('Blog'))->toBeTrue();
});

it('generates correct provider class name', function () {
    $manager = resolve(ModuleManager::class);

    expect($manager->getProviderClass('Blog'))->toBe('Modules\\Blog\\Providers\\BlogServiceProvider');
});

it('returns correct modules path', function () {
    $manager = resolve(ModuleManager::class);

    expect($manager->getModulesPath())->toBe(base_path('modules'));
});

it('returns correct module path for a specific module', function () {
    $manager = resolve(ModuleManager::class);

    expect($manager->getModulePath('Blog'))->toBe(base_path('modules/Blog'));
});

it('reports module does not exist when directory missing', function () {
    $manager = resolve(ModuleManager::class);

    expect($manager->moduleExists('Blog'))->toBeFalse();
});

it('sorts modules by priority config', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();
    $this->artisan('make:module', ['name' => 'Shop'])->assertSuccessful();
    $this->artisan('make:module', ['name' => 'Users'])->assertSuccessful();

    config(['modular.priority' => ['Users', 'Blog']]);

    $manager = resolve(ModuleManager::class);
    $modules = $manager->enabledModules();

    $usersIndex = array_search('Users', $modules);
    $blogIndex = array_search('Blog', $modules);
    $shopIndex = array_search('Shop', $modules);

    expect($usersIndex)->toBeLessThan($blogIndex);
    expect($usersIndex)->toBeLessThan($shopIndex);
    expect($blogIndex)->toBeLessThan($shopIndex);
});

it('reads depends property from module service provider', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();
    $this->artisan('make:module', ['name' => 'Users'])->assertSuccessful();

    $this->addDependsToProvider('Blog', ['Users']);

    $manager = resolve(ModuleManager::class);

    expect($manager->getModuleDependencies('Blog'))->toBe(['Users']);
});

it('returns empty dependencies when no depends property', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $manager = resolve(ModuleManager::class);

    expect($manager->getModuleDependencies('Blog'))->toBe([]);
});

it('respects module dependencies in load order', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();
    $this->artisan('make:module', ['name' => 'Users'])->assertSuccessful();

    $this->addDependsToProvider('Blog', ['Users']);

    $manager = resolve(ModuleManager::class);
    $modules = $manager->enabledModules();

    $usersIndex = array_search('Users', $modules);
    $blogIndex = array_search('Blog', $modules);

    expect($usersIndex)->toBeLessThan($blogIndex);
});

it('detects circular dependencies', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();
    $this->artisan('make:module', ['name' => 'Users'])->assertSuccessful();

    $this->addDependsToProvider('Blog', ['Users']);
    $this->addDependsToProvider('Users', ['Blog']);

    $manager = resolve(ModuleManager::class);

    expect(fn () => $manager->enabledModules())
        ->toThrow(RuntimeException::class);
});
