<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeleChat;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class TeleBot extends Controller
{
    public function webhook(Request $request)
    {
        $message = $request->all()['message'];
        if (!isset($message['chat']['id'])) {
            $this->send_message("543376720", 'Something went wrong.');
            return;
        }
        $chat_id = $message['chat']['id'];
        if (!TeleChat::where('chat_id', $chat_id)->exists()) {
            TeleChat::create([
                'chat_id' => $chat_id,
                'chat_type' => $message['chat']['type'],
                'chat_title' => $message['chat']['title'] ?? null,
                'chat_username' => $message['chat']['username'] ?? null,
                'chat_first_name' => $message['chat']['first_name'] ?? null,
                'chat_last_name' => $message['chat']['last_name'] ?? null,
            ]);
        }
        if (!isset($message["entities"])) {
            $this->send_message($chat_id, 'Hallo, ich bin der SP Wahlbot. Ich halte dich über die Resultate von Kandis auf dem Laufenden. Schreibe /start um zu beginnen.');
        } else {
            $this->handle_command($message);
        }
    }

    public function send_message($chat_id, $content) {
        $url = "https://api.telegram.org/bot" . env('TELE_BOT_TOKEN') . "/sendMessage?chat_id=" . $chat_id . "&text=" . urlencode($content);
        try {
            $response = Http::get($url);
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }
        return $response;
    }

    public function handle_command($message) {
        $command = substr(stripslashes($message['text']), $message['entities'][0]['offset'], $message['entities'][0]['length']);

        $this->send_message($message['chat']['id'], 'Command: ' . $command);
    }
}
