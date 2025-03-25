<?php

namespace App\Services;

use function Laravel\Prompts\note;

class NpmService
{
    public function __construct(protected string $path){
    }

    public function getLibraries() : string
    {
        $packageJson = json_decode(file_get_contents($this->path . '/package.json'));
        $requiredNpmLibraries = collect($packageJson->dependencies)->keys()->all();
        return implode(', ', $requiredNpmLibraries);
    }

    public function printDevLibraries() : void
    {
        $packageJson = json_decode(file_get_contents($this->path . '/package.json'));

        if (! property_exists($packageJson, 'devDependencies')) {
            note("No required dev NPM libraries found.");
            return;
        }

        $requiredNpmDevLibraries = $this->getDevLibraries($packageJson);

        note("Found the following required dev NPM libraries:");
        info($requiredNpmDevLibraries);
        return;
    }

    public function getDevLibraries($packageJson) : string
    {
        $requiredNpmDevLibraries = collect($packageJson->devDependencies)->keys()->all();
        return implode(', ', $requiredNpmDevLibraries);
    }
}
