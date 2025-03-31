<?php

namespace App\Console\Commands;

use App\Services\NpmService;
use App\Services\PhpService;
use App\Services\ProjectsService;
use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

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
    protected $description = "Get the PHP and NPM libraries used in each project in a project's directory.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectsService = new ProjectsService;
        $projectsService->getProjectDirectories()->each(function ($projectDir) use ($projectsService) {
            // For now, don't worry about repetition - merging will come later
            note('Getting PHP and JavaScript dependencies for '.$projectDir);
            $projectAbsolutePath = $projectsService->getProjectsPath().'/'.$projectDir;

            $phpService = new PhpService($projectAbsolutePath);
            $phpService->outputLibraries();

            $npmService = new NpmService($projectAbsolutePath);
            $npmService->outputLibraries();

            note('Done getting PHP and JavaScript dependencies for '.$projectDir);
        });

    }
}
