<?php

it('make:module:controller creates a controller in the module', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:controller', ['module' => 'Blog', 'name' => 'PostController'])
        ->expectsOutput('Controller [PostController] created in module [Blog].')
        ->assertSuccessful();

    $path = base_path('modules/Blog/Controllers/PostController.php');
    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toContain('namespace Modules\Blog\Controllers');
    expect(file_get_contents($path))->toContain('class PostController extends Controller');
});

it('make:module:controller appends Controller suffix if missing', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:controller', ['module' => 'Blog', 'name' => 'Post'])
        ->assertSuccessful();

    expect(file_exists(base_path('modules/Blog/Controllers/PostController.php')))->toBeTrue();
});

it('make:module:controller creates an API controller with --api flag', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:controller', ['module' => 'Blog', 'name' => 'PostController', '--api' => true])
        ->assertSuccessful();

    $content = file_get_contents(base_path('modules/Blog/Controllers/PostController.php'));
    expect($content)->toContain('public function index()');
    expect($content)->toContain('public function store()');
    expect($content)->toContain('public function show($id)');
    expect($content)->toContain('public function update($id)');
    expect($content)->toContain('public function destroy($id)');
});

it('make:module:controller fails when module does not exist', function () {
    $this->artisan('make:module:controller', ['module' => 'NonExistent', 'name' => 'PostController'])
        ->expectsOutput('Module [NonExistent] does not exist.')
        ->assertFailed();
});

it('make:module:controller fails when controller already exists', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:controller', ['module' => 'Blog', 'name' => 'PostController'])->assertSuccessful();
    $this->artisan('make:module:controller', ['module' => 'Blog', 'name' => 'PostController'])
        ->expectsOutput('Controller [PostController] already exists in module [Blog].')
        ->assertFailed();
});
