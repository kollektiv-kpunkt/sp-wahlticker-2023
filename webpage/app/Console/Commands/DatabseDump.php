<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Console\Events\CommandStarting;

class DatabaseDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:dump';

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
    public function handle(CommandStarting $command)
    {
        if ($command->command != 'db:dump' && $command->command != 'migrate:fresh') {
            return Command::SUCCESS;
        }
        $filename = "backup-" . Carbon::now()->format('Y-m-d_H-i-s') . ".sql";
        // Create backup folder and set permission if not exist.
        $storageAt = storage_path() . "/app/db_dumps/";
        if(!File::exists($storageAt)) {
            File::makeDirectory($storageAt, 0755, true, true);
        }
        $command = "".env('DB_DUMP_PATH', 'mysqldump')." --user=" . env('DB_USERNAME') ." --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . "  | gzip > " . $storageAt . $filename;
        $returnVar = NULL;
        $output = NULL;
        exec($command, $output, $returnVar);
        return Command::SUCCESS;
    }
}
