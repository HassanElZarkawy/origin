# Testing

## Running the Package Tests

```bash
composer test
```

This runs the full Pest test suite. For coverage:

```bash
composer test-coverage
```

## Testing Modules in Your Application

When writing tests for your Laravel application that uses Origin modules, modules are loaded automatically via the service provider — just like in production. No special test setup is needed.

### Example: Testing a Module Route

```php
// tests/Feature/BlogTest.php

test('blog index page loads', function () {
    $response = $this->get('/blog');

    $response->assertStatus(200);
});
```

### Example: Testing with Module Models

```php
// tests/Feature/PostTest.php

use Modules\Blog\Models\Post;

test('can create a post', function () {
    $post = Post::factory()->create();

    expect($post)->toBeInstanceOf(Post::class);
});
```

### Checking Module State in Tests

```php
use Origin\Facades\Modular;

test('blog module is enabled', function () {
    expect(Modular::isEnabled('Blog'))->toBeTrue();
});

test('all modules load in correct order', function () {
    $modules = Modular::enabledModules();

    $usersIndex = array_search('Users', $modules);
    $blogIndex = array_search('Blog', $modules);

    expect($usersIndex)->toBeLessThan($blogIndex);
});
```

## Testing the Origin Package Itself

The Origin package uses [Orchestra Testbench](https://packages.tools/testbench.html) for isolated package testing with [Pest](https://pestphp.com/).

### Test Structure

```
tests/
├── Pest.php                              # Test bootstrap
├── TestCase.php                          # Base test case (Orchestra Testbench)
├── Unit/
│   ├── ModuleManagerTest.php             # ModuleManager unit tests
│   └── ModuleServiceProviderTest.php     # ModuleServiceProvider unit tests
└── Feature/
    ├── CommandsTest.php                  # Core command tests
    ├── MakeModuleControllerCommandTest.php
    ├── MakeModuleModelCommandTest.php
    ├── MakeModuleMigrationCommandTest.php
    ├── MakeModuleSeederCommandTest.php
    ├── ModuleUtilityCommandsTest.php     # Remove, status, seed, migrate commands
    └── FacadeTest.php                    # Facade tests
```

### Base TestCase

All tests extend `Origin\Tests\TestCase`, which:

- Registers `ModularServiceProvider` with Orchestra Testbench
- Sets up the `Modular` facade alias
- Configures the modules path and namespace
- Cleans up the `modules/` directory after each test

### Writing New Tests

```php
it('does something with a module', function () {
    // Create a module
    $this->artisan('make:module', ['name' => 'Blog'])->assertSuccessful();

    // Test against it
    $path = base_path('modules/Blog');
    expect(is_dir($path))->toBeTrue();
});
```

The `TestCase` automatically cleans up created modules in `tearDown()`, so tests don't interfere with each other.
