<?php

namespace App\Services;

class PhpService
{
    // Takes the absolute path to a given project
    public function __construct(protected string $path){
    }

    public function getLibraries() : string
    {
        $composerJson = json_decode(file_get_contents($this->path . '/composer.json'));
        $requiredPhpLibraries = collect($composerJson->require)->keys()->all();
        return implode(', ', $requiredPhpLibraries);
    }

    public function getDevLibraries() : string
    {
        $composerJson = json_decode(file_get_contents($this->path . '/composer.json'));
        $requiredPhpDevLibraries = collect($composerJson->{'require-dev'})->keys()->all();
        return implode(', ', $requiredPhpDevLibraries);
    }
}
