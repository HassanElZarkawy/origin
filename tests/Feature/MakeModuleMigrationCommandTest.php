<?php

it('make:module:migration creates a migration in the module', function () {
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    $this->artisan('make:module:migration', ['module' => 'Blog', 'name' => 'add_slug_to_posts_table'])
        ->expectsOutput('Migration [add_slug_to_posts_table] created in module [Blog].')
        ->assertSuccessful();

    $migrations = glob(base_path('modules/Blog/database/migrations/*_add_slug_to_posts_table.php'));
    expect($migrations)->toHaveCount(1);

    $content = file_get_contents($migrations[0]);
    expect($content)->toContain('public function up()');
    expect($content)->toContain('public function down()');
});

it('make:module:migration fails when module does not exist', function () {
    $this->artisan('make:module:migration', ['module' => 'NonExistent', 'name' => 'test'])
        ->expectsOutput('Module [NonExistent] does not exist.')
        ->assertFailed();
});
