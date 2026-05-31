<?php

it('make:module:model creates a model in the module', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:model', ['module' => 'Blog', 'name' => 'Post'])
        ->expectsOutput('Model [Post] created in module [Blog].')
        ->assertSuccessful();

    $path = base_path('modules/Blog/Models/Post.php');
    expect(file_exists($path))->toBeTrue();
    expect(file_get_contents($path))->toContain('namespace Modules\Blog\Models');
    expect(file_get_contents($path))->toContain('class Post extends Model');
});

it('make:module:model fails when module does not exist', function () {
    $this->artisan('make:module:model', ['module' => 'NonExistent', 'name' => 'Post'])
        ->expectsOutput('Module [NonExistent] does not exist.')
        ->assertFailed();
});

it('make:module:model fails when model already exists', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:model', ['module' => 'Blog', 'name' => 'Post'])->assertSuccessful();
    $this->artisan('make:module:model', ['module' => 'Blog', 'name' => 'Post'])
        ->expectsOutput('Model [Post] already exists in module [Blog].')
        ->assertFailed();
});

it('make:module:model creates a migration with --migration flag', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:model', ['module' => 'Blog', 'name' => 'Post', '--migration' => true])
        ->assertSuccessful();

    expect(file_exists(base_path('modules/Blog/Models/Post.php')))->toBeTrue();

    $migrations = glob(base_path('modules/Blog/database/migrations/*_create_posts_table.php'));
    expect($migrations)->toHaveCount(1);

    $content = file_get_contents($migrations[0]);
    expect($content)->toContain("Schema::create('posts'");
});
