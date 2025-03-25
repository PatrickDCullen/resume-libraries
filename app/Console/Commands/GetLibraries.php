<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;

class GetLibraries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-libraries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = text('Enter the absolute path of the composer.json file you would like to parse.');

        // Stub out the path for now

        // Take in the whole file, parse the json, find the require key, and return all the keys in that key

        // String
        $absolutePath = base_path('composer.json');
        // json encoded string (serialized)
        $jsonEncodedString = file_get_contents($absolutePath);
        // PHP value object containing composer.json
        $composerDotJson = json_decode($jsonEncodedString);
        // PHP array of REQUIRED libraries (doesn't include dev)
        $libraries = collect($composerDotJson->require)->keys()->all();

        $librariesString = implode(', ', $libraries);
        info("Found the following libraries:");
        info($librariesString);

    }
}
