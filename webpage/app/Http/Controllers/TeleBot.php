<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeleChat;
use App\Models\PoliticianResult;
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
        $url = "https://api.telegram.org/bot" . env('TELE_BOT_TOKEN') . "/sendMessage?chat_id=" . $chat_id . "&text=" . urlencode($content) . "&parse_mode=HTML";
        try {
            $response = Http::get($url);
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }
        return $response;
    }

    public function handle_command($message) {
        $command = substr(stripslashes($message['text']), $message['entities'][0]['offset'], $message['entities'][0]['length']);
        switch ($command) {
            case '/start':
                $this->send_message($message['chat']['id'],
<<<EOD
Hoi 👋
Ich bin der SP Wahlbot. Ich kann dir Updates über die Resultate von Kandis im Kantonsratswahlkampf von Zürich schicken. Schreib mir <b>/kandi Vorname Nachname</b> und ich halte dich zu dieser*diesem Kandi auf dem Laufenden.
EOD
);
                break;
            case "/kandi":
                $kandiname = substr($message["text"], $message["entities"][0]["length"] + 1);
                $politician = PoliticianResult::where('name', 'LIKE', '%' . $kandiname . '%')->get();
                if ($politician && count($politician) == 1) {
                    $politician = $politician->first();
                    $politician->chats_interested = array_merge($politician->chats_interested ?? [], [$message['chat']['id']]);
                    $politician->save();
                    $this->send_message($message['chat']['id'], "Ich habe {$politician->name} von der Liste " . substr($politician->partyId, 5) . " ({$politician->party->name}) gefunden. Ich werde dich zu dieser*diesem Kandi auf dem Laufenden halten.");
                    return;
                } else if ($politician && count($politician) == 0) {
                    $this->send_message($message['chat']['id'], "Ich habe leider keinen Kandi mit diesem Namen gefunden. Schreib /help um zu sehen, was ich kann.");
                    return;
                } else {
                    $this->send_message($message['chat']['id'], "Ich habe mehrere Kandis mit diesem Namen gefunden. Schreib /help um zu sehen, was ich kann.");
                    return;
                }
                break;
            case '/help':
                $this->send_message($message['chat']['id'], 'Ich bin der SP Wahlbot. Ich halte dich über die Resultate von Kandis auf dem Laufenden. Schreibe /start um zu beginnen.');
                break;
            default:
                $this->send_message($message['chat']['id'], 'Ich kenne diesen Befehl nicht. Schreibe /help um zu sehen, was ich kann.');
                break;
        }
    }
}
