<?php

namespace App\Console\Commands\Bot;

use Illuminate\Console\Command;
use DefStudio\Telegraph\Models\TelegraphBot;

class SetWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:setWebhook';

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
        $bot = TelegraphBot::find(1);
        $bot->registerWebhook()->send();
        return Command::SUCCESS;
    }
}
