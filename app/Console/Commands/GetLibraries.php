<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\PhpService;
use App\Services\NpmService;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\confirm;

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
    protected $description = 'Get the PHP and NPM libraries used in a project.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectsPath = $this->getProjectsPath();

        // Loop through each folder in the projects path
        $disk = Storage::build([
            "driver" => "local",
            "root" => $projectsPath
        ]);

        $projects = collect($disk->directories("/"));

        // dd($projects);

        // Call the php service on each of them
        $projects->each(function ($projectPath) {
            $phpService = new PhpService($projectPath);
            note("Found the following required PHP libraries:");
            info($phpService->getLibraries());
            note("Found the following required dev PHP libraries:");
            info($phpService->getDevLibraries());
        });
        // For now, don't worry about repetition

        // $phpService = new PhpService($projectPath);
        // note("Found the following required PHP libraries:");
        // info($phpService->getLibraries());
        // note("Found the following required dev PHP libraries:");
        // info($phpService->getDevLibraries());

        $npmService = new NpmService($projectPath);
        note("Found the following required NPM libraries, ordered by downloads over the last year:");
        info($npmService->getLibraries());
        $npmService->printDevLibraries();
    }

    private function getProjectsPath()
    {
        $inferredProjectsPath = Str::of(base_path())
            ->remove("/" . Str::afterLast(base_path(), "/"))
            ->value();

        $confirmed = confirm(
            label: 'Confirm whether this is the folder that contains your projects: ' . $inferredProjectsPath,
            default: true,
            yes: 'Yes',
            no: 'No, let me change it',
            hint: 'Not sure? Navigate to the project directory in your terminal and use the pwd command.'
        );

        $projectsPath = $inferredProjectsPath;

        if (! $confirmed) {
            $projectsPath = text(
                label: 'Enter the absolute path of the folder that contains your projects: ',
                placeholder: $inferredProjectsPath,
                default: $inferredProjectsPath,
                hint: 'Not sure? Navigate to the project directory in your terminal and use the pwd command.'
            );
        }

        return $projectsPath;
    }
}
