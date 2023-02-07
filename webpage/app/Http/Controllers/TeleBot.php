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
        $message = json_encode($request->all(), true);
        $chat_id = $message['message']['chat']['id'];
        if (!TeleChat::where('chat_id', $chat_id)->exists()) {
            TeleChat::create([
                'chat_id' => $chat_id,
                'chat_type' => $message['message']['chat']['type'],
                'chat_title' => $message['message']['chat']['title'] ?? null,
                'chat_username' => $message['message']['chat']['username'] ?? null,
                'chat_first_name' => $message['message']['chat']['first_name'] ?? null,
                'chat_last_name' => $message['message']['chat']['last_name'] ?? null,
            ]);
            if ($message["entities"]["type"] == "bot_command" && strpos($message["text"], "/start")) {
            }
            $this->send_message($chat_id, 'Hallo, ich bin der SP Wahlbot. Ich halte dich über die Resultate von Kandis auf dem Laufenden. Schreibe /start um zu beginnen.');
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
}
