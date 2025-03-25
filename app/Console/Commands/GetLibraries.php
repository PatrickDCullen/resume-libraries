<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;

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
        $path = text(
            label: 'Enter the absolute path of the project containing the composer.json file you would like to parse.',
            placeholder: base_path(),
            default: base_path(),
            hint: 'Not sure? Navigate to the project in your terminal and use the pwd command.'
        );

        // PHP dependencies
        $composerJson = json_decode(file_get_contents($path . '/composer.json'));
        $requiredPhpLibraries = collect($composerJson->require)->keys()->all();
        $requiredDevPhpLibraries = collect($composerJson->{'require-dev'})->keys()->all();

        $requiredPhpLibrariesString = implode(', ', $requiredPhpLibraries);
        $requiredDevPhpLibrariesString = implode(', ', $requiredDevPhpLibraries);
        note("Found the following required PHP libraries:");
        info($requiredPhpLibrariesString);
        note("Found the following required dev PHP libraries:");
        info($requiredDevPhpLibrariesString);

        // NPM dependencies
        $packageJson = json_decode(file_get_contents($path . '/package.json'));
        $requiredNpmLibraries = collect($packageJson->dependencies)->keys()->all();
        // TODO add validation to ensure devDependencies key exists
        // $requiredDevNpmLibraries = collect($packageJson->devDependencies)->keys()->all();

        $requiredNpmLibrariesString = implode(', ', $requiredNpmLibraries);
        // $requiredDevNpmLibrariesString = implode(', ', $requiredDevPhpLibraries);
        note("Found the following required NPM libraries:");
        info($requiredNpmLibrariesString);
        // note("Found the following required dev NPM libraries:");
        // info($requiredDevNpmLibrariesString);
    }
}
