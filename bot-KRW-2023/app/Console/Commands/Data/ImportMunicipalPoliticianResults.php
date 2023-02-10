<?php

namespace App\Console\Commands\Data;

use Illuminate\Console\Command;

class ImportMunicipalPoliticianResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import-municipal-politician-results';

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
        $starttime = microtime(true);
        $file = file_get_contents(storage_path('tmp/wahlen_resultate_2023_02_12.json'));
        $data = json_decode($file, true);
        $municipalities = $data["kantone"][1]["vorlagen"][0]["gemeinden"];
        foreach ($municipalities as $municipality) {
            if ($municipality["geoLevelname"] == "Zürich") {
                continue;
            }
            foreach ($municipality["resultat"]["kandidaten"] as $politician) {
                $politicianResult = new \App\Models\PoliticianResult();
                $politicianResult->politician_id = "{$municipality["geoLevelnummer"]}_{$politician["kandidatNummer"]}";
                $politicianResultUpper = \App\Models\PoliticianResult::where("politician_id", $politician["kandidatNummer"])->first();
                $politicianResult->name = $politicianResultUpper->name;
                $politicianResult->party_id = $politicianResultUpper->party_id;
                $politicianResult->constituency_id = $politicianResultUpper->constituency_id;
                $politicianResult->initialPosition = $politicianResultUpper->initialPosition;
                $politicianResult->municipal = true;
                $politicianResult->municipality_id = $municipality["geoLevelnummer"];
                $politicianResult->save();
            }
        }
        $municipalities = $data["kantone"][1]["vorlagen"][0]["zaehlkreise"];
        foreach ($municipalities as $municipality) {
            if ($municipality["geoLevelname"] == "Zürich") {
                continue;
            }
            foreach ($municipality["resultat"]["kandidaten"] as $politician) {
                $politicianResult = new \App\Models\PoliticianResult();
                $politicianResult->politician_id = "{$municipality["geoLevelnummer"]}_{$politician["kandidatNummer"]}";
                $politicianResultUpper = \App\Models\PoliticianResult::where("politician_id", $politician["kandidatNummer"])->first();
                $politicianResult->name = $politicianResultUpper->name;
                $politicianResult->party_id = $politicianResultUpper->party_id;
                $politicianResult->constituency_id = $politicianResultUpper->constituency_id;
                $politicianResult->initialPosition = $politicianResultUpper->initialPosition;
                $politicianResult->municipal = true;
                $politicianResult->municipality_id = $municipality["geoLevelnummer"];
                $politicianResult->save();
            }
        }
        $endtime = microtime(true);
        $this->info("Time: " . ($endtime - $starttime));
        return Command::SUCCESS;
    }
}
