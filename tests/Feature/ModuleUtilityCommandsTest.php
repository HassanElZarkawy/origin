<?php

it('module:remove deletes the module directory', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();
    expect(is_dir(base_path('modules/Blog')))->toBeTrue();

    $this->artisan('module:remove', ['module' => 'Blog', '--force' => true])
        ->assertSuccessful();

    expect(is_dir(base_path('modules/Blog')))->toBeFalse();
});

it('module:remove fails when module does not exist', function () {
    $this->artisan('module:remove', ['module' => 'NonExistent', '--force' => true])
        ->expectsOutput('Module [NonExistent] does not exist.')
        ->assertFailed();
});

it('module:remove cleans up merge-plugin config when no modules remain', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $composer = json_decode(file_get_contents(base_path('composer.json')), true);
    expect($composer['extra']['merge-plugin']['include'])->toContain('modules/*/composer.json');

    $this->artisan('module:remove', ['module' => 'Blog', '--force' => true])->assertSuccessful();

    $composer = json_decode(file_get_contents(base_path('composer.json')), true);
    expect(isset($composer['extra']['merge-plugin']))->toBeFalse();
});

it('module:status shows module information', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:model', ['module' => 'Blog', 'name' => 'Post'])->assertSuccessful();
    $this->artisan('make:module:controller', ['module' => 'Blog', 'name' => 'PostController'])->assertSuccessful();

    $this->artisan('module:status', ['module' => 'Blog'])
        ->expectsOutputToContain('Blog')
        ->assertSuccessful();
});

it('module:status shows all modules when no module specified', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();
    $this->artisan('make:module', ['name' => 'Shop'])->assertSuccessful();

    $this->artisan('module:status')
        ->expectsOutputToContain('Blog')
        ->expectsOutputToContain('Shop')
        ->assertSuccessful();
});

it('module:status reports non-existent module', function () {
    $this->artisan('module:status', ['module' => 'NonExistent'])
        ->expectsOutput('Module [NonExistent] does not exist.')
        ->assertSuccessful();
});

it('module:status shows no modules message when empty', function () {
    $this->artisan('module:status')
        ->expectsOutput('No modules found.')
        ->assertSuccessful();
});

it('module:seed shows message when no modules exist', function () {
    $this->artisan('module:seed')
        ->expectsOutput('No modules to seed.')
        ->assertSuccessful();
});

it('module:seed skips non-existent module', function () {
    $this->artisan('module:seed', ['module' => 'NonExistent'])
        ->expectsOutput('Module [NonExistent] does not exist. Skipping.')
        ->assertSuccessful();
});

it('module:migrate:rollback skips non-existent module', function () {
    $this->artisan('module:migrate:rollback', ['module' => 'NonExistent'])
        ->expectsOutput('Module [NonExistent] does not exist. Skipping.')
        ->assertSuccessful();
});

it('module:migrate:reset skips non-existent module', function () {
    $this->artisan('module:migrate:reset', ['module' => 'NonExistent'])
        ->expectsOutput('Module [NonExistent] does not exist. Skipping.')
        ->assertSuccessful();
});

it('module:migrate:fresh skips non-existent module', function () {
    $this->artisan('module:migrate:fresh', ['module' => 'NonExistent'])
        ->expectsOutput('Module [NonExistent] does not exist. Skipping.')
        ->assertSuccessful();
});
