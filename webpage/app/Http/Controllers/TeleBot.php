<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeleChat;
use App\Models\PoliticianResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\Party;
use App\Models\Constituency;

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
                    $added = $politician->addChatInterested($message['chat']['id']);
                    if (!$added) {
                        $this->send_message($message['chat']['id'], "{$politician->name} ist bereits auf deiner Liste. Falls du die Kandi von der Liste entfernen willst, schreibe /entferneKandi {$politician->politicianId}.");
                    } else {
                        $this->send_message($message['chat']['id'], "Ich habe {$politician->name} von der Liste " . substr($politician->partyId, 5) . " ({$politician->party->name}) gefunden. Ich werde dich zu dieser*diesem Kandi auf dem Laufenden halten.");
                    }
                    return;
                } else if ($politician && count($politician) == 0) {
                    $this->send_message($message['chat']['id'],
<<<EOD
Ich habe leider keinen Kandi mit diesem Namen gefunden. Bitte prüfe kurz auf Tippfehler. Wenn du die Kandi über ihre Kandinummer suchen willst, kannst du das mit dem Befehl <b>/kandiNr KANDINUMMER</b> tun.

Die Kandinummer setzt sich aus der Wahlkreisnummer, der Listennummer und dem Listenplatz zusammen. <a href='https://www.zh.ch/de/politik-staat/wahlen-abstimmungen/kantons-regierungsratswahlen.html#-1097010600'>Die Wahlkreisnummern findest du hier</a>. Wenn es mehr als 10 Sitze auf der Liste gibt, ist die Platznummer immer zweistellig (z.B. 01, 02, 03 etc.). Für Lou Muster aus dem Wahlkreis Dietikon der Liste 20 auf Listenplatz 5 lautet die Listennummer "7_2005". Wenn du Lou also so suchen willst, schreib <b>/kandiNr 7_205</b>.

Schreib /help um zu sehen, was ich kann.
EOD
);
                    return;
                } else {
                    $this->send_message($message['chat']['id'], "Ich habe mehrere Kandis mit diesem Namen gefunden. Schreib /help um zu sehen, was ich kann.");
                    return;
                }
                break;

            case "/kandiNr":
                $kandiNr = substr($message["text"], $message["entities"][0]["length"] + 1);
                $politician = PoliticianResult::where('politicianId', $kandiNr)->get();
                if ($politician && count($politician) == 1) {
                    $politician = $politician->first();
                    $added = $politician->addChatInterested($message['chat']['id']);
                    if (!$added) {
                        $this->send_message($message['chat']['id'], "{$politician->name} ist bereits auf deiner Liste. Falls du die Kandi von der Liste entfernen willst, schreibe /entferneKandi {$politician->politicianId}.");
                    } else {
                        $this->send_message($message['chat']['id'], "Ich habe {$politician->name} von der Liste " . substr($politician->partyId, 5) . " ({$politician->party->name}) gefunden. Ich werde dich zu dieser*diesem Kandi auf dem Laufenden halten.");
                    }
                    return;
                } else if ($politician && count($politician) == 0) {
                    $this->send_message($message['chat']['id'], "Ich habe leider keinen Kandi mit dieser Kandinummer gefunden. Schreib /help um zu sehen, was ich kann.");
                    return;
                }
                break;

            case "/kandiListe":
                $listeName = substr($message["text"], $message["entities"][0]["length"] + 1);
                $party = Party::where('abbreviation', 'LIKE', '%' . $listeName . '%')->first();
                if ($party) {
                    $politicians = PoliticianResult::where('partyId', "2023_" . $party->id)->get();
                    foreach ($politicians as $politician) {
                        $politician->addChatInterested($message['chat']['id']);
                    }
                    $this->send_message($message['chat']['id'], "Ich habe die Liste {$party->name} gefunden. Ich werde dich zu den Kandis dieser Liste auf dem Laufenden halten.");
                    return;
                } else {
                    $this->send_message($message['chat']['id'], "Ich habe leider keine Liste mit diesem Namen gefunden. Schreib /listen, damit ich dir alle Parteikürzel, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
                    return;
                }
                break;

            case "/listen":
                $parties = Party::where("partyId", "LIKE", "2023_%")->get();
                $messageText = "Ich verwende folgende Parteikürzel:\n";
                $partyAbbreviations = [];
                foreach ($parties as $party) {
                    $partyAbbreviations[] = "- <b>" . $party->abbreviation . "</b> (" . $party->name . ")\n";
                }
                $messageText .= implode("", $partyAbbreviations);
                $this->send_message($message['chat']['id'], $messageText);
                return;
                break;

            case "/kandiWahlkreis":
                $constituencyName = substr($message["text"], $message["entities"][0]["length"] + 1);
                $constituency = Constituency::where('name', 'LIKE', '%' . $constituencyName . '%')->first();
                if ($constituency) {
                    $politicians = PoliticianResult::where('constituencyId', $constituency->id)->get();
                    foreach ($politicians as $politician) {
                        $politician->addChatInterested($message['chat']['id']);
                    }
                    $this->send_message($message['chat']['id'], "Ich habe den Wahlkreis {$constituency->name} gefunden. Ich werde dich zu den Kandis dieses Wahlkreises auf dem Laufenden halten.");
                    return;
                } else {
                    $this->send_message($message['chat']['id'], "Ich habe leider keinen Wahlkreis mit diesem Namen gefunden. Schreib /wahlkreise, damit ich dir alle Wahlkreise, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
                    return;
                }

            case "/wahlkreise":
                $constituencies = Constituency::all();
                $constituencyNames = [];
                foreach ($constituencies as $constituency) {
                    $constituencyNames[] = "- " . $constituency->name;
                }
                $this->send_message($message['chat']['id'], "Ich verwende folgende Wahlkreise:\n" . implode("\n", $constituencyNames));
                return;

            case "/kandiListeWahlkreis":
                $partyAbbreviation = explode(" ", substr($message["text"], $message["entities"][0]["length"] + 1))[0];
                $constituencyName = explode(" ", substr($message["text"], $message["entities"][0]["length"] + 1))[1];
                $party = Party::where('abbreviation', 'LIKE', '%' . $partyAbbreviation . '%')->first();
                $constituency = Constituency::where('name', 'LIKE', '%' . $constituencyName . '%')->first();
                if (!$party || !$constituency) {
                    $this->send_message($message['chat']['id'], "Ich habe leider keine Liste mit diesem Namen oder keinen Wahlkreis mit diesem Namen gefunden. Schreib /listen, damit ich dir alle Parteikürzel, die ich verwende, anzeige. Schreib /wahlkreise, damit ich dir alle Wahlkreise, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
                    return;
                }
                $politicians = PoliticianResult::where('partyId', "2023_" . $party->id)->where('id', $constituency->id)->get();
                foreach ($politicians as $politician) {
                    $politician->addChatInterested($message['chat']['id']);
                }
                $this->send_message($message['chat']['id'], "Ich habe die Liste {$party->name} im Wahlkreis {$constituency->name} gefunden. Ich werde dich zu den Kandis dieser Liste auf dem Laufenden halten.");
                return;
                break;


            case "/entferneKandi":
                $kandiNr = substr($message["text"], $message["entities"][0]["length"] + 1);
                $politician = PoliticianResult::where('politicianId', $kandiNr)->get();
                if ($politician && count($politician) == 1) {
                    $politician = $politician->first();
                    $removed = $politician->removeChatInterested($message['chat']['id']);
                    if (!$removed) {
                        $this->send_message($message['chat']['id'], "{$politician->name} ist nicht auf deiner Liste. Falls du die Kandi hinzufügen willst, schreibe /kandiNr {$politician->politicianId}.");
                    } else {
                        $this->send_message($message['chat']['id'], "Ich habe {$politician->name} von deiner Liste entfernt.");
                    }
                    return;
                } else if ($politician && count($politician) == 0) {
                    $this->send_message($message['chat']['id'], "Ich habe leider keinen Kandi mit dieser Kandinummer gefunden. Schreib /help um zu sehen, was ich kann.");
                    return;
                }
                break;

            case "/entferneAlle":
                $politicians = PoliticianResult::where('chats_interested', "LIKE", "%{$message['chat']['id']}%")->get();
                if (count($politicians) == 0) {
                    $this->send_message($message['chat']['id'], "Du hast noch keine Kandis auf deiner Liste. Füge welche hinzu, indem du /kandi oder /kandiNr verwendest. Schreib /help um zu sehen, was ich kann.");
                    return;
                } else {
                    foreach ($politicians as $politician) {
                        $politician->removeChatInterested($message['chat']['id']);
                    }
                    $this->send_message($message['chat']['id'], "Ich habe alle Kandis von deiner Liste entfernt.");
                    return;
                }
                break;

            case "/meineKandis":
                $politicians = PoliticianResult::where('chats_interested', "LIKE", "%{$message['chat']['id']}%")->get();
                if (count($politicians) == 0) {
                    $this->send_message($message['chat']['id'], "Du hast noch keine Kandis auf deiner Liste. Füge welche hinzu, indem du /kandi oder /kandiNr verwendest. Schreib /help um zu sehen, was ich kann.");
                    return;
                } else {
                    $this->send_message($message['chat']['id'], "Du hast folgende Kandis auf deiner Liste:");
                    $max = ceil(count($politicians) / 10);
                    $i = 0;
                    $j = 0;
                    for ($i; $i < $max; $i++) {
                        sleep(1);
                        $text = "";
                        for ($j = 0; $j < 10; $j++) {
                            if (isset($politicians[$j + $i * 10])) {
                                $text .= $politicians[$j + $i * 10]->name . ", " . $politicians[$j + $i * 10]->party->abbreviation . " (entfernen mit /entferneKandi {$politicians[$j + $i * 10]->politicianId})\n";
                            } else {
                                break;
                            }
                        }
                        $this->send_message($message['chat']['id'], $text);
                    }
                    return;
                }
                break;

            case "/yourCreator":
                if (!get_option("creator")) {
                    set_option("creator", $message['from']['id']);
                    $this->send_message($message['chat']['id'], "Du wurdest als Admin der App " . env("APP_NAME") . " registriert.");

                } else {
                    $this->send_message($message['chat']['id'], "Mein Creator ist " . get_option("creator"));
                }
                return;


            case "/registerChannel":
                if ($message["from"]["id"] == get_option("creator")) {
                    set_option("telegram_channel_id", $message['chat']['id']);
                    $this->send_message($message['chat']['id'], "Ich habe diesen Channel als Wahlchannel registriert.");
                } else {
                    $this->send_message($message['chat']['id'], "Du bist nicht berechtigt, diesen Befehl auszuführen.");
                }
                return;
                break;

            case "/echoChannel":
                $this->send_message($message['chat']['id'], "Folgende Channel-ID ist registriert: " . get_option("telegram_channel_id"));
                return;
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
