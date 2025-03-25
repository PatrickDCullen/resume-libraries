<?php

namespace App\Services;

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

    public function getDevLibraries() : string
    {
        $packageJson = json_decode(file_get_contents($this->path . '/package.json'));
        $requiredNpmDevLibraries = collect($packageJson->devDependencies)->keys()->all();
        return implode(', ', $requiredNpmDevLibraries);
    }
}
