<?php

declare(strict_types=1);

use ShakilAhmmed\BladeFileIconify\Factory;
use ShakilAhmmed\BladeFileIconify\Svg;

if (! function_exists('svg')) {
    function svg(string $name, $class = '', array $attributes = []): Svg
    {
        return app(Factory::class)->svg($name, $class, $attributes);
    }
}
