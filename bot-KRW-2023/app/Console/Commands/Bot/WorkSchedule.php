<?php

namespace App\Console\Commands\Bot;

use Illuminate\Console\Command;
use App\Http\Controllers\TeleBot;

class WorkSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:work-schedule';

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
        $scheduledMessages = \App\Models\ScheduledMessage::where("sent", false)->take(60)->get();
        $teleBot = new TeleBot();
        foreach ($scheduledMessages as $scheduledMessage) {
            $teleBot->send_message($scheduledMessage->tele_chat_id, $scheduledMessage->content);
            $scheduledMessage->sent = true;
            $scheduledMessage->status = "sent";
            $scheduledMessage->sent_at = now();
            $scheduledMessage->save();
            usleep(500000);
        }
        return Command::SUCCESS;
    }
}
