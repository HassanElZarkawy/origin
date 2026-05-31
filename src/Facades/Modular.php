<?php

namespace Origin\Facades;

use Illuminate\Support\Facades\Facade;
use Origin\ModuleManager;

/**
 * @method static \Origin\ModuleManager modules()
 * @method static array enabledModules()
 * @method static array allModules()
 * @method static array disabledModules()
 * @method static bool isEnabled(string $module)
 * @method static void enable(string $module)
 * @method static void disable(string $module)
 */
class Modular extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ModuleManager::class;
    }
}
