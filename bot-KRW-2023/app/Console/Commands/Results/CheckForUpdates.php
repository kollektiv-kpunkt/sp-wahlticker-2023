<?php

namespace App\Console\Commands\Results;

use Illuminate\Console\Command;
use App\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CheckForUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'results:check-for-updates';

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
        ob_start();
        echo PHP_EOL;
        echo " ************************************************************************************** " . PHP_EOL;
        echo " *                                                                                    * " . PHP_EOL;
        $now = Carbon::now()->format("Y-m-d_H:i:s");
        echo " ********************* Log for Result update: {$now} ********************* " . PHP_EOL;
        echo " *                                                                                    * " . PHP_EOL;
        echo " ************************************************************************************** " . PHP_EOL;
        echo PHP_EOL;
        $resultController = new ResultController();
        $resultController->check_for_updates();
        echo PHP_EOL;
        echo " ************************************************************************************** " . PHP_EOL;
        echo " *                                                                                    * " . PHP_EOL;
        echo " ****************** End of Log for Result update {$now} ****************** " . PHP_EOL;
        echo " *                                                                                    * " . PHP_EOL;
        echo " ************************************************************************************** " . PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL;
        $output = ob_get_clean();
        Storage::disk('logs')->prepend("result_logs/results-{$now}.log", $output);

        return Command::SUCCESS;
    }
}
