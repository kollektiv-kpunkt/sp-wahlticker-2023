<?php

namespace App\Console\Commands\Data;

use Illuminate\Console\Command;

class ImportMunicipalities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import-municipalities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file = file_get_contents(storage_path('tmp/wahlen_resultate_2023_02_12.json'));
        $data = json_decode($file, true);
        $municipalities = $data["kantone"][1]["vorlagen"][0]["gemeinden"];
        foreach ($municipalities as $municipality) {
            if ($municipality["geoLevelname"] == "Zürich") {
                continue;
            }
            $municipalityModel = new \App\Models\Municipality();
            $municipalityModel->id = $municipality["geoLevelnummer"];
            $municipalityModel->name = $municipality["geoLevelname"];
            $municipalityModel->constituency_id = $municipality["wahlkreisNummer"];
            $municipalityModel->save();
        }
        return Command::SUCCESS;
    }
}
