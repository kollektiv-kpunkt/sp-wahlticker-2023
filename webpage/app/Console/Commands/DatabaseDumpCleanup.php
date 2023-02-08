<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DatabaseDumpCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:cleanup-dumps';

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
        $files = Storage::disk("local")->allFiles("db_dumps");

        foreach ($files as $file) {
            $time = Storage::disk('local')->lastModified($file);
            $fileModifiedDateTime = Carbon::parse($time);

            if (Carbon::now()->gt($fileModifiedDateTime->addDays(7))) {
                Storage::disk("local")->delete($file);
            }

            if (File::exists(public_path('storage/' . $file))) {
                File::delete(public_path('storage/' . $file));
            }
        }
        return Command::SUCCESS;
    }
}
