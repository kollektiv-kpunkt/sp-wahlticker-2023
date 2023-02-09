<?php

namespace App\Console\Commands\Results;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'results:clear-logs';

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
        $files = Storage::disk('logs')->files('result_logs');
        foreach ($files as $file) {
            Storage::disk('logs')->delete($file);
        }
        return Command::SUCCESS;
    }
}
