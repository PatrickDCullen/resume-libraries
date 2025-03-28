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
use function Laravel\Prompts\warning;
use function Laravel\Prompts\confirm;
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
        $projectsPath = $this->getProjectsPath();

        $disk = Storage::build([
            "driver" => "local",
            "root" => $projectsPath
        ]);

        $projects = collect($disk->directories("/"))->filter(function ($project) {
            // Remove this project from the projects array
            return $project !== Str::of(base_path())
                ->remove(Str::beforeLast(base_path(), "/") . "/")
                ->value();
        });

        $projects->each(function ($projectDir) use ($projectsPath) {
            // For now, don't worry about repetition - merging will come later
            note("Getting PHP and JavaScript dependencies for " . $projectDir);
            $phpService = new PhpService($projectsPath . "/" . $projectDir);
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

            $npmService = new NpmService($projectsPath . "/" . $projectDir);
            $packageJson = $npmService->getPackageJson();
            if ($packageJson) {
                $npmService->printLibraries();
                $npmService->printDevLibraries();
            }
            note("Done getting PHP and JavaScript dependencies for " . $projectDir);
        });

    }

    private function getProjectsPath()
    {
        $inferredProjectsPath = Str::of(base_path())
            ->remove("/" . Str::afterLast(base_path(), "/"))
            ->value();

        $confirmed = confirm(
            label: 'Is this the folder that contains your projects? ' . $inferredProjectsPath,
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
