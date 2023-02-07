<?php

namespace App\Console\Commands\Bot;

use Illuminate\Console\Command;
use App\Http\Controllers\TeleBot;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:test';

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
        $bot = new TeleBot();
        $bot->send_message("-874264018", 'Test');
        return Command::SUCCESS;
    }
}
