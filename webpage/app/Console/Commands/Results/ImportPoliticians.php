<?php

namespace App\Console\Commands\Results;

use Illuminate\Console\Command;
use App\Models\PoliticianResult;

class ImportPoliticians extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'results:import-politicians';

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
        $politicians = json_decode(file_get_contents(storage_path('app/wahlen_resultate_2023_02_12.json')), true)["kantone"][1]["vorlagen"][0]["resultat"]["kandidaten"];
        foreach ($politicians as $key => $politician) {
            $constituencyId = explode("_", $politician["kandidatNummer"])[0];
            $politicianId = explode("_", $politician["kandidatNummer"])[1];
            if (PoliticianResult::where("politicianId", $politicianId)->where("constituencyId", $constituencyId)->exists()) {
                $politicianResult = PoliticianResult::where("politicianId", $politicianId)->where("constituencyId", $constituencyId)->first();
            } else {
                $politicianResult = new PoliticianResult();
            }
            $politicianResult->fill([
                "politicianId" => $politicianId,
                "name" => $politician["vorname"] . " " . $politician["nachname"],
                "partyId" => "2023_" . $politician["listeNummer"],
                "constituencyId" => $constituencyId,
                "votes" => $politician["stimmen"],
                "initialPosition" => substr($politicianId, strlen($politician["listeNummer"])),
                "finalPosition" => $politician["rangInListeInWahlkreis"],
                "elected" => $politician["gewaehlt"] ?? false,
            ]);
            $politicianResult->save();
        }
        return Command::SUCCESS;
    }
}
