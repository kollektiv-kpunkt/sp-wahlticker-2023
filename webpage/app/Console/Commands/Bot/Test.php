<?php

namespace App\Console\Commands\Bot;
use DefStudio\Telegraph\Models\TelegraphChat;


use Illuminate\Console\Command;

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
    protected $description = 'Testing the telegram bot here';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        return Command::SUCCESS;
    }
}
