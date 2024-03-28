<?php

declare(strict_types=1);

namespace ShakilAhmmed\BladeFileIconify\Console;

use ShakilAhmmed\BladeFileIconify\Factory;
use ShakilAhmmed\BladeFileIconify\IconsManifest;
use Illuminate\Console\Command;

final class CacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'icons:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover icon sets and generate a manifest file';

    public function handle(Factory $factory, IconsManifest $manifest): int
    {
        $manifest->write($factory->all());

        $this->info('Blade icons manifest file generated successfully!');

        return 0;
    }
}
