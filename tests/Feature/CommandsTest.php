<?php

it('module:help displays all commands', function () {
    $this->artisan('module:help')
        ->expectsOutputToContain('Origin')
        ->expectsOutputToContain('make:module')
        ->assertSuccessful();
});

it('module:list command is registered', function () {
    $this->artisan('module:list')->assertSuccessful();
});

it('module:list shows message when no modules exist', function () {
    $this->artisan('module:list')
        ->expectsOutput('No modules found.')
        ->assertSuccessful();
});

it('make:module generates a module composer.json', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $composer = json_decode(file_get_contents(base_path('modules/Blog/composer.json')), true);

    expect($composer['name'])->toBe('modules/blog');
    expect($composer['type'])->toBe('origin-module');
    expect($composer['autoload']['psr-4'])->toHaveKey('Modules\\Blog\\');
});

it('make:module adds merge-plugin config to app composer.json', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $appComposer = json_decode(file_get_contents(base_path('composer.json')), true);

    expect($appComposer['extra']['merge-plugin']['include'])->toContain('modules/*/composer.json');
});

it('make:module command is registered', function () {
    $this->artisan('make:module', ['name' => 'Blog'])
        ->assertSuccessful();
});

it('make:module creates module directory structure', function () {
    $this->artisan('make:module', ['name' => 'Blog'])
        ->assertSuccessful();

    $modulesPath = base_path('modules/Blog');

    expect(is_dir($modulesPath))->toBeTrue();
    expect(is_dir($modulesPath.'/Providers'))->toBeTrue();
    expect(is_dir($modulesPath.'/Controllers'))->toBeTrue();
    expect(is_dir($modulesPath.'/Models'))->toBeTrue();
    expect(is_dir($modulesPath.'/Commands'))->toBeTrue();
    expect(is_dir($modulesPath.'/routes'))->toBeTrue();
    expect(is_dir($modulesPath.'/database/migrations'))->toBeTrue();
    expect(is_dir($modulesPath.'/database/seeders'))->toBeTrue();
    expect(is_dir($modulesPath.'/database/factories'))->toBeTrue();
    expect(is_dir($modulesPath.'/resources/views'))->toBeTrue();
    expect(is_dir($modulesPath.'/resources/lang'))->toBeTrue();
    expect(is_dir($modulesPath.'/config'))->toBeTrue();

    expect(file_exists($modulesPath.'/Providers/BlogServiceProvider.php'))->toBeTrue();
    expect(file_exists($modulesPath.'/routes/web.php'))->toBeTrue();
    expect(file_exists($modulesPath.'/routes/api.php'))->toBeTrue();
    expect(file_exists($modulesPath.'/composer.json'))->toBeTrue();
});

it('make:module generates service provider extending ModuleServiceProvider', function () {
    $this->artisan('make:module', ['name' => 'Blog'])
        ->assertSuccessful();

    $content = file_get_contents(base_path('modules/Blog/Providers/BlogServiceProvider.php'));

    expect($content)->toContain('extends ModuleServiceProvider');
    expect($content)->toContain('namespace Modules\\Blog\\Providers');
});

it('make:module fails when module already exists', function () {
    $this->artisan('make:module', ['name' => 'Blog'])
        ->assertSuccessful();

    $this->artisan('make:module', ['name' => 'Blog'])
        ->expectsOutput('Module [Blog] already exists.')
        ->assertFailed();
});

it('make:module converts name to studly case', function () {
    $this->artisan('make:module', ['name' => 'user-management'])
        ->assertSuccessful();

    expect(is_dir(base_path('modules/UserManagement')))->toBeTrue();
});

it('module:list shows created modules', function () {
    $this->artisan('make:module', ['name' => 'Blog'])
        ->assertSuccessful();

    $this->artisan('module:list')
        ->expectsOutputToContain('Blog')
        ->assertSuccessful();
});

it('module:enable fails for non-existent module', function () {
    $this->artisan('module:enable', ['module' => 'NonExistent'])
        ->expectsOutput('Module [NonExistent] does not exist.')
        ->assertFailed();
});

it('module:disable fails for non-existent module', function () {
    $this->artisan('module:disable', ['module' => 'NonExistent'])
        ->expectsOutput('Module [NonExistent] does not exist.')
        ->assertFailed();
});

it('module:migrate skips non-existent module', function () {
    $this->artisan('module:migrate', ['module' => 'NonExistent'])
        ->expectsOutput('Module [NonExistent] does not exist. Skipping.')
        ->assertSuccessful();
});

it('module:publish fails for non-existent module', function () {
    $this->artisan('module:publish', ['module' => 'NonExistent'])
        ->expectsOutput('Module [NonExistent] does not exist.')
        ->assertFailed();
});
