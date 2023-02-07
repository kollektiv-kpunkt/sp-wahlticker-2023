<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeleChat;
use Illuminate\Support\Facades\Storage;

class TeleBot extends Controller
{
    public function webhook(Request $request)
    {
        $update = json_decode($request->getContent(), true);
        Storage::disk('local')->put('telebot.json', json_encode($update, JSON_PRETTY_PRINT));
        exit;
        $chat_id = $update['message']['chat']['id'];
        $message = $update['message']['text'];
        if ($message == "/start") {
            $this->sendMessage($chat_id, "Hello, I am a bot");
        }
    }
}
