<?php

namespace App\Console\Commands\Results;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Rogervila\ArrayDiffMultidimensional;
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
        if (get_option("kr_last_update") == $json["timestamp"] ) {
            $this->error("No update found.");
            exit;
        }

        $localFile = file_get_contents(storage_path("app/wahlen_resultate_2023_02_12.min.json"));
        $localJson = json_decode($localFile, true);

        $differences = ArrayDiffMultidimensional::strictComparison($localJson, $json);
        $this->info("Found " . count($differences) . " differences.");
        $this->info("Updating local file...");
        // file_put_contents(storage_path("app/wahlen_resultate_2023_02_12.min.json"), $file);

        if (isset($differences["kantone"][1]["vorlagen"][0]["wahlkreise"])) {
            $this->info("Found changes in wahlkreise.");
            $resultController = new ResultController();
            $resultController->handleResultChangeKR($differences["kantone"][1]["vorlagen"][0]["wahlkreise"], $json["kantone"][1]["vorlagen"][0]["wahlkreise"]);
        } else {
            $this->info("No changes in wahlkreise.");
        }
        // $this->info("Updating last update timestamp...");
        // set_option("kr_last_update", $json["timestamp"]);
        $this->info("Done.");
        return Command::SUCCESS;
    }
}
