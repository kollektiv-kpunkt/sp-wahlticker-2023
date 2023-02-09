<?php

namespace App\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class DefaultData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:default-data';

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
        $this->info("Seeding default data...");
        $files = File::allFiles(storage_path("app/defaultData"));
        foreach ($files as $file) {
            $this->info("Seeding " . $file->getFilename());
            $sql = File::get($file->getPathname());
            DB::unprepared($sql);
        }
        $this->info("Seeding Politicians...");
        \Artisan::call("results:import-politicians");
        $this->info("Seeding default data finished.");
        return Command::SUCCESS;
    }
}
