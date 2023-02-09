<?php

namespace App\Console\Commands\Data;

use Illuminate\Console\Command;

class ImportPartyResultsMunicipal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import-municipal-party-results';

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
            foreach ($municipality["resultat"]["listen"] as $party) {
                $partyResult = new \App\Models\PartyResult();
                $partyResult->party_id = "2023_" . $party["listeNummer"];
                $partyResult->constituency_id = $municipality["wahlkreisNummer"];
                $partyResult->municipal = true;
                $partyResult->municipality_id = $municipality["geoLevelnummer"];
                $partyResult->save();
            }
        }

        return Command::SUCCESS;
    }
}
