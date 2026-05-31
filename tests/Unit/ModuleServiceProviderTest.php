<?php

use Illuminate\Support\Str;
use Origin\ModuleServiceProvider;

it('derives module name from service provider class name when no explicit name', function () {
    $provider = new class($this->app) extends ModuleServiceProvider {};

    $name = $provider->moduleName();

    expect($name)->toBeString();
    expect($name)->not->toBeEmpty();
    expect(Str::endsWith($name, 'ServiceProvider'))->toBeFalse();
});

it('uses explicit name property when set', function () {
    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'Blog';
    };

    expect($provider->moduleName())->toBe('Blog');
});

it('returns correct module path based on config', function () {
    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'Blog';
    };

    $path = $provider->modulePath();

    expect($path)->toBe(base_path('modules/Blog'));
});

it('appends subpath to module path', function () {
    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'Blog';
    };

    expect($provider->modulePath('routes/web.php'))->toBe(base_path('modules/Blog/routes/web.php'));
});

it('does not crash when views directory does not exist', function () {
    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'NonExistent';
    };

    $provider->boot();

    expect(true)->toBeTrue();
});

it('does not crash when routes directory does not exist', function () {
    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'NonExistent';
    };

    $provider->boot();

    expect(true)->toBeTrue();
});

it('resolves module namespace from config', function () {
    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'Blog';
    };

    $method = new ReflectionMethod($provider, 'getModuleNamespace');
    $method->setAccessible(true);

    expect($method->invoke($provider))->toBe('Modules\\Blog');
});

it('uses custom namespace from config', function () {
    config(['modular.namespace' => 'App\\Modules']);

    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'Blog';
    };

    $method = new ReflectionMethod($provider, 'getModuleNamespace');
    $method->setAccessible(true);

    expect($method->invoke($provider))->toBe('App\\Modules\\Blog');
});

it('returns route prefix from config', function () {
    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'Blog';
    };

    $method = new ReflectionMethod($provider, 'routePrefix');
    $method->setAccessible(true);

    expect($method->invoke($provider))->toBe('');
});

it('returns route middleware from config', function () {
    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'Blog';
    };

    $method = new ReflectionMethod($provider, 'routeMiddleware');
    $method->setAccessible(true);

    expect($method->invoke($provider))->toBe(['web']);
});

it('returns api prefix from config', function () {
    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'Blog';
    };

    $method = new ReflectionMethod($provider, 'apiPrefix');
    $method->setAccessible(true);

    expect($method->invoke($provider))->toBe('api');
});

it('returns api middleware from config', function () {
    $provider = new class($this->app) extends ModuleServiceProvider
    {
        protected string $name = 'Blog';
    };

    $method = new ReflectionMethod($provider, 'apiMiddleware');
    $method->setAccessible(true);

    expect($method->invoke($provider))->toBe(['api']);
});
