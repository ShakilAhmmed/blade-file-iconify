<?php

namespace ShakilAhmmed\BladeFileIconify\Console;

use Brick\VarExporter\VarExporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use October\Rain\Config\DataWriter\Rewrite;

class SaveIconsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:save-icons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It Will Pull All Icons from https://github.com/iconify/icon-sets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get('https://api.github.com/repos/iconify/icon-sets/contents/json');
        $config = require __DIR__ . '/../../config/blade-icons.php';
        if ($response->successful()) {
            $files = $response->json();
            foreach ($files as $file) {
                $filename = $file['download_url'];
                $iconsType = $file['name'] ? explode('.', $file['name'])[0] : 'default';
                $prefix = Str::replace('-', '', Str::singular($iconsType));
                $config['sets'][$iconsType] = [
                    'path' => '',
                    'prefix' => $prefix
                ];
                $this->info("Fetching Icons from $filename");
                try {
                    $fileResponse = Http::timeout(300)->retry(3)->get($filename);
                    if ($fileResponse->successful()) {
                        $icons = $fileResponse->json()['icons'] ?? [];
                        foreach ($icons as $key => $icon) {
                            $svgPath = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 512 512" fill="currentColor">';
                            $svgPath .= $icon['body'];
                            $svgPath .= '</svg>';
                            $folderPath = __DIR__ . '/../../resources/svg/' . $iconsType;
                            $configFolderPath = 'resources/blade-file-iconify/svg/' . $iconsType;
                            $config['sets'][$iconsType]['path'] = $configFolderPath;
                            if (!file_exists($folderPath)) {
                                mkdir($folderPath);
                            }
                            $filePath = $folderPath . '/' . $key . '.svg';
                            if (!File::exists($filePath)) {
                                file_put_contents($filePath, $svgPath);
                                $this->info("You can use <x-$prefix-$key/>");
                            }
                        }
                    }
                } catch (\Exception  $exception) {
                    $this->error($exception->getMessage());
                }
                sleep(5);
            }
            $newConfig = VarExporter::export($config, VarExporter::ADD_RETURN);
            \File::put(__DIR__ . '/../../config/blade-icons.php', "<?php\n\n" . $newConfig);
        } else {
            $this->error('Failed to fetch icons from the repository.');
        }
    }
}
