<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class ProjectsService
{
    private $projectsPath;

    public function __construct()
    {
        $this->projectsPath = $this->getProjectsPathInput();
    }

    public function getProjectDirectories()
    {
        $projectsDirectory = Storage::build([
            'driver' => 'local',
            'root' => $this->projectsPath,
        ]);

        return collect($projectsDirectory->directories('/'))->filter(function ($project) {
            // Remove this project from the projects array
            return $project !== Str::of(base_path())
                ->remove(Str::beforeLast(base_path(), '/').'/')
                ->value();
        });
    }

    public function getProjectsPath()
    {
        return $this->projectsPath;
    }

    private function getProjectsPathInput()
    {
        $inferredProjectsPath = Str::of(base_path())
            ->remove('/'.Str::afterLast(base_path(), '/'))
            ->value();

        $confirmed = confirm(
            label: 'Is this the folder that contains your projects? '.$inferredProjectsPath,
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
