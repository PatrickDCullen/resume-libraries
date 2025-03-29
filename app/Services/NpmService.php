<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use function Laravel\Prompts\note;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class NpmService
{
    public function __construct(protected string $path){
    }

    public function getPackageJson()
    {
        $packageJson = null;
        try {
            $packageJson = json_decode(file_get_contents($this->path . '/package.json'));
        } catch (\ErrorException) {
            warning("No package.json found, skipping.");
        }
        return $packageJson;
    }

    public function printLibraries() : void
    {
        $packageJson = json_decode(file_get_contents($this->path . '/package.json'));

        if (! property_exists($packageJson, 'dependencies')) {
            warning("No required NPM libraries found.");
            return;
        }

        note("Found the following required NPM libraries, ordered by downloads over the last year:");
        info($this->getLibraries($packageJson));
        return;
    }

    public function printDevLibraries() : void
    {
        $packageJson = json_decode(file_get_contents($this->path . '/package.json'));

        if (! property_exists($packageJson, 'devDependencies')) {
            warning("No required dev NPM libraries found.");
            return;
        }

        note("Found the following required dev NPM libraries, ordered by downloads over the last year:");
        info($this->getDevLibraries($packageJson));
        return;
    }

    private function getLibraries($packageJson) : string
    {
        $requiredNpmLibraries = collect($packageJson->dependencies)->keys();

        $sortedPackages = collect();

        $requiredNpmLibraries->each(function ($package) use ($sortedPackages) {
            $packageWithDownloads = Http::get(
                "https://api.npmjs.org/downloads/point/last-year/" . $package
            )->json();

            $sortedPackages = $sortedPackages->prepend($packageWithDownloads);
        });

        $sortedPackages = $sortedPackages->sortBy(function (array $package) {
            return $package["downloads"] * -1;
        });

        $packagesListByDownloads = $sortedPackages->pluck('package');

        return implode(', ', $packagesListByDownloads->toArray());
    }


    private function getDevLibraries($packageJson) : string
    {
        $requiredNpmDevLibraries = collect($packageJson->devDependencies)->keys();

        $sortedPackages = collect();

        $requiredNpmDevLibraries->each(function ($package) use ($sortedPackages) {
            $packageWithDownloads = Http::get(
                "https://api.npmjs.org/downloads/point/last-year/" . $package
            )->json();

            $sortedPackages = $sortedPackages->prepend($packageWithDownloads);
        });

        $sortedPackages = $sortedPackages->sortBy(function (array $package) {
            return $package["downloads"] * -1;
        });

        $packagesListByDownloads = $sortedPackages->pluck('package');

        return implode(', ', $packagesListByDownloads->toArray());
    }
}
