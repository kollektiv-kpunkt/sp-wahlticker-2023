<?php

namespace App\Console\Commands\Results;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class reset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'results:reset';

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
        $this->info("Resetted last update timestamp.");
        set_option("kr_last_update", 0);
        $this->info("Downloading new file...");
        $file = Http::withOptions([
            'verify' => !(env("APP_ENV") == "local"),
        ])->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get(env("KR_FILE_URL"))->body();
        $json = json_encode(json_decode($file, true), JSON_PRETTY_PRINT);
        file_put_contents(storage_path("tmp/wahlen_resultate_2023_02_12.json"), $file);

        $this->info("Truncating politicians...");
        DB::table("politician_results")->truncate();

        $this->info("Importing politicians...");
        \Artisan::call('results:import-politicians');

        $this->info("Done.");

        return Command::SUCCESS;
    }
}
