<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProjectsService;
use App\Services\PhpService;
use App\Services\NpmService;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\spin;

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
    protected $description = 'Get the PHP and NPM libraries used in each project in a projects directory.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectsService = new ProjectsService;
        $projectsService->getProjectDirectories()->each(function ($projectDir) use ($projectsService) {
            // For now, don't worry about repetition - merging will come later
            note("Getting PHP and JavaScript dependencies for " . $projectDir);
            $phpService = new PhpService($projectsService->getProjectsPath() . "/" . $projectDir);
            if ($phpService->composerJsonExists()) {
                spin(
                    message: 'Getting data from Packagist API...',
                    callback: function () use($projectDir, $phpService) {
                        $phpService->getLibraries($projectDir);
                    }
                );

                note("Found the following required dev PHP libraries for " . $projectDir . ":");
                info($phpService->getDevLibraries());
            } else {
                warning("No composer.json found, skipping.");
            }

            $npmService = new NpmService($projectsService->getProjectsPath() . "/" . $projectDir);
            $packageJson = $npmService->getPackageJson();
            if ($packageJson) {
                $npmService->printLibraries();
                $npmService->printDevLibraries();
            }
            note("Done getting PHP and JavaScript dependencies for " . $projectDir);
        });

    }
}
