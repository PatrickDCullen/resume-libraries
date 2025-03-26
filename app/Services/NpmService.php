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

    public function getLibraries() : string
    {
        $packageJson = json_decode(file_get_contents($this->path . '/package.json'));

        $requiredNpmLibraries = collect($packageJson->dependencies)->keys();

        // TODO refactor common code with getDevLibraries to dedicated method?
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

    public function printDevLibraries() : void
    {
        $packageJson = json_decode(file_get_contents($this->path . '/package.json'));

        if (! property_exists($packageJson, 'devDependencies')) {
            warning("No required dev NPM libraries found.");
            return;
        }

        note("Found the following required dev NPM libraries:");
        info($this->getDevLibraries($packageJson));
        return;
    }

    public function getDevLibraries($packageJson) : string
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
