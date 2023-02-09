<?php

namespace App\Console\Commands\Data;

use Illuminate\Console\Command;

class ImportConstituencyPartyResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import-constituency-party-results';

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
        $constituencies = $data["kantone"][1]["vorlagen"][0]["wahlkreise"];
        foreach ($constituencies as $constituency) {
            foreach ($constituency["resultat"]["listen"] as $party) {
                $partyResult = new \App\Models\PartyResult();
                $partyResult->party_id = "2023_" . $party["listeNummer"];
                $partyResult->constituency_id = $constituency["wahlkreisNummer"];
                $partyResult->municipal = false;
                $partyResult->municipality_id = null;
                $partyResult->save();
            }
        }
        return Command::SUCCESS;
    }
}
