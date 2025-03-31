<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class PhpService
{
    // Takes the absolute path to a given project
    public function __construct(protected string $path) {}

    public function composerJsonExists(): bool
    {
        return file_exists($this->path.'/composer.json');
    }

    public function outputLibraries($projectDir)
    {
        if ($this->composerJsonExists()) {
            spin(
                message: 'Getting data from Packagist API...',
                callback: function () use ($projectDir) {
                    $this->getLibraries($projectDir);
                }
            );

            note('Found the following required dev PHP libraries for '.$projectDir.':');
            info($this->getDevLibraries());
        } else {
            warning('No composer.json found, skipping.');
        }
    }

    public function getLibraries($projectDir): void
    {
        $composerJson = json_decode(file_get_contents($this->path.'/composer.json'));
        $requiredPhpLibraries = collect($composerJson->require)->keys();

        $sortedLibraries = collect();
        $requiredPhpLibraries->each(function ($library) use ($sortedLibraries) {
            $downloads = Http::get("https://packagist.org/packages/{$library}/stats.json")->json(
                'downloads.monthly'
            );

            $libraryWithDownloads = ['library' => $library, 'downloads' => $downloads];
            if (! is_null($libraryWithDownloads['downloads'])) {
                $sortedLibraries->prepend($libraryWithDownloads);
            }
        });

        $sortedLibraries = $sortedLibraries->sortBy(function (array $package) {
            return $package['downloads'] * -1;
        });

        $librariesByDownloads = $sortedLibraries->pluck('library');

        note('Found the following required PHP libraries for '.$projectDir.':');
        info(implode(', ', $librariesByDownloads->toArray()));

    }

    public function getDevLibraries(): string
    {
        $composerJson = json_decode(file_get_contents($this->path.'/composer.json'));
        $requiredPhpDevLibraries = collect($composerJson->{'require-dev'})->keys();

        $sortedLibraries = collect();
        $requiredPhpDevLibraries->each(function ($library) use ($sortedLibraries) {
            $downloads = Http::get("https://packagist.org/packages/{$library}/stats.json")->json(
                'downloads.monthly'
            );

            $libraryWithDownloads = ['library' => $library, 'downloads' => $downloads];
            if (! is_null($libraryWithDownloads['downloads'])) {
                $sortedLibraries->prepend($libraryWithDownloads);
            }
        });

        $sortedLibraries = $sortedLibraries->sortBy(function (array $package) {
            return $package['downloads'] * -1;
        });

        $librariesByDownloads = $sortedLibraries->pluck('library');

        return implode(', ', $requiredPhpDevLibraries->toArray());
    }
}
