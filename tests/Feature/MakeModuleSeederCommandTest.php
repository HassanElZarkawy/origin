<?php

it('make:module:seeder creates a seeder in the module', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:seeder', ['module' => 'Blog', 'name' => 'PostSeeder'])
        ->expectsOutput('Seeder [PostSeeder] created in module [Blog].')
        ->assertSuccessful();

    $path = base_path('modules/Blog/database/seeders/PostSeeder.php');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('namespace Modules\Blog\Database\Seeders');
    expect($content)->toContain('class PostSeeder extends Seeder');
});

it('make:module:seeder appends Seeder suffix if missing', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:seeder', ['module' => 'Blog', 'name' => 'Post'])
        ->assertSuccessful();

    expect(file_exists(base_path('modules/Blog/database/seeders/PostSeeder.php')))->toBeTrue();
});

it('make:module:seeder fails when module does not exist', function () {
    $this->artisan('make:module:seeder', ['module' => 'NonExistent', 'name' => 'PostSeeder'])
        ->expectsOutput('Module [NonExistent] does not exist.')
        ->assertFailed();
});
