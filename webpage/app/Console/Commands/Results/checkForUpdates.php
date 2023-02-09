<?php

namespace App\Console\Commands\Results;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ResultController;


class checkForUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'results:check-for-updates-kr';

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
        if (env("APP_ENV") == "local") {
            $this->info("Local environment detected.");
            $file = file_get_contents(storage_path("tmp/wahlen_resultate_2023_02_12.min.json"));
        } else {
            $file = Http::withOptions([
                'verify' => !(env("APP_ENV") == "local"),
            ])->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get(env("KR_FILE_URL"))->body();
        }
        $json = json_decode($file, true);
        if (get_option("kr_last_update") == $json["timestamp"] && env("APP_ENV") != "local") {
            $this->error("No update found.");
            exit;
        }
        $krResults = $json["kantone"][1]["vorlagen"][0];
        $rrResults = $json["kantone"][1]["vorlagen"][1];

        ResultController::handleKrMunicipalities($krResults["gemeinden"]);
        ResultController::handleKrParties($krResults["resultat"]["listen"]);
        ResultController::handleKrPoliticians($krResults["resultat"]["kandidaten"]);

        $this->info("Done.");
        return Command::SUCCESS;
    }
}
