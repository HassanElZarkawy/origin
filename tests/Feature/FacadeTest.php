<?php

use Origin\Facades\Modular;

it('can access facade', function () {
    expect(class_exists(Modular::class))->toBeTrue();
});
