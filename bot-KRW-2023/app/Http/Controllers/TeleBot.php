<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeleChat;
use App\Models\PoliticianResult;
use App\Models\PartyResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\Party;
use App\Models\Constituency;
use App\Models\Municipality;

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
        if (env("BOT_ENV") === "maintenance") {
            $this->maintenance($chat_id);
            return;
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

    public function maintenance($chat_id) {
        $message = <<<EOD
        Hoi 👋
        Ich bin derzeit im Wartungsmodus. Sorry, versuch es morgen früh noch einmal.
        EOD;
        $this->send_message($chat_id, $message);
    }

    public function handle_command($message) {
        $firstColon = strpos($message["text"], ":");
        $firstSpace = strpos($message["text"], " ");
        if ((($firstColon < $firstSpace) || $firstSpace == false) && $firstColon != false) {
            $commandLenght = $firstColon;
        } else if ($firstSpace !== false) {
            $commandLenght = $firstSpace;
        } else {
            $commandLenght = $message["entities"][0]["length"];
        }

        $command = substr(stripslashes($message['text']), $message['entities'][0]['offset'] + 1, $commandLenght - 1);
        $chat_id = $message['chat']['id'];
        $content = substr(stripslashes($message['text']), $commandLenght + 1);
        if (method_exists($this, $command)) {
            $this->$command($content, $chat_id);
        } else {
            $this->send_message($chat_id, 'Sorry, ich kenne diesen Befehl nicht. Schreibe /help um zu sehen, was ich alles kann.');
        }
    }

    public function start($content, $chat_id) {
        $message = <<<EOD
        Hoi 👋
        Ich bin der SP Wahlbot. Ich kann dir Updates über die Resultate von Kantonsratskandis bei den Kantonalen Wahlen in Zürich schicken.
        Schreibe /help um zu sehen, was ich alles kann.
        EOD;
        $this->send_message($chat_id, $message);
    }

    public function help($content, $chat_id) {
        $message = <<<EOD
        Hier sind meine Befehle:

        /start - Starte den Bot

        /help - Zeige diese Hilfe

        /subscribe VORNAME NACHNAME - Abonniere Updates für Kandis (z.B. /subscribe Peter Müller).

        /subscribe_nr KANDI_ID - Abonniere Updates für Kandis (z.B. /subscribe_nr 1_101).

        /unsubscribe KANDI_ID - Deabonniere Updates für Kandis (z.B. /unsubscribe 1_101).

        /unsubscribe_parteien WAHLKREIS|GEMEINDE_ID - Deabonniere Updates für Parteien in einem Wahlkreis (z.B. /unsubscribe_parteien Zürich 1&2).

        /unsubscribe_all - Deabonniere Updates für alle Kandis und Parteien.

        /list - Zeige alle abonnierten Kandis.

        /subscribe_kandis_wahlkreis WAHLKREIS - Abonniere Updates für alle Kandis in einem Wahlkreis (z.B. /subscribe_kandis_wahlkreis Zürich 1&2).

        /subscribe_kandis_partei PARTEI - Abonniere Updates für alle Kandis einer Partei (z.B. /subscribe_kandis_partei SP).

        /subscribe_kandis_patei_wahlkreis PARTEI WAHLKREIS - Abonniere Updates für alle Kandis einer Partei in einem Wahlkreis (z.B. /subscribe_kandis_patei_wahlkreis SP Zürich 1&2).

        /subscribe_parteien_wahlkreis WAHLKREIS - Abonniere Updates für alle Parteien in einem Wahlkreis (z.B. /subscribe_parteien_wahlkreis Zürich 1&2).

        /subscribe_parteien_gemeinde GEMEINDE - Abonniere Updates für alle Parteien in einer Gemeinde (z.B. /subscribe_parteien_gemeinde Zürich).

        /parteien - Zeige alle Parteien.

        /wahlkreise - Zeige alle Wahlkreise.

        /gemeinden - Zeige alle Gemeinden.

        Wenn du fragen hast, schreib meinem Entwickler <a href="https://t.me/TimothyJOesch">@TimothyJOesch</a>.
        EOD;
        $this->send_message($chat_id, $message);
    }

    public function subscribe($content, $chat_id) {
        $politician = PoliticianResult::where('name', 'LIKE', '%' . $content . '%')->get();
        if ($politician && count($politician) == 1) {
            $politician = $politician->first();
            $added = $politician->addChatInterested($chat_id);
            if (!$added) {
                $this->send_message($chat_id, "{$politician->name} ist bereits auf deiner Liste. Falls du die Kandi von der Liste entfernen willst, schreibe /unsubscribe_{$politician->politician_id}.");
            } else {
                $this->send_message($chat_id, "Ich habe {$politician->name} von der Liste " . substr($politician->party_id, 5) . " ({$politician->party->name}) gefunden. Ich werde dich zu dieser*diesem Kandi auf dem Laufenden halten.");
            }
            return;
        } else if ($politician && count($politician) == 0) {
            $this->send_message($chat_id, <<<EOD
            Ich habe leider keinen Kandi mit diesem Namen gefunden. Bitte prüfe kurz auf Tippfehler. Wenn du die Kandi über ihre Kandinummer suchen willst, kannst du das mit dem Befehl <b>/kandiNr KANDINUMMER</b> tun.

            Die Kandinummer setzt sich aus der Wahlkreisnummer, der Listennummer und dem Listenplatz zusammen. <a href='https://www.zh.ch/de/politik-staat/wahlen-abstimmungen/kantons-regierungsratswahlen.html#-1097010600'>Die Wahlkreisnummern findest du hier</a>. Wenn es mehr als 10 Sitze auf der Liste gibt, ist die Platznummer immer zweistellig (z.B. 01, 02, 03 etc.). Für Lou Muster aus dem Wahlkreis Dietikon der Liste 20 auf Listenplatz 5 lautet die Listennummer "7_2005". Wenn du Lou also so suchen willst, schreib <b>/kandiNr:7_2005</b>.

            Schreib /help um zu sehen, was ich kann.
            EOD
            );
            return;
        } else {
            $this->send_message($chat_id, "Ich habe mehrere Kandis mit diesem Namen gefunden. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_nr($content, $chat_id) {
        $politician = PoliticianResult::where('politician_id', $content)->get();
        if ($politician && count($politician) == 1) {
            $politician = $politician->first();
            $added = $politician->addChatInterested($chat_id);
            if (!$added) {
                $this->send_message($chat_id, "{$politician->name} ist bereits auf deiner Liste. Falls du die Kandi von der Liste entfernen willst, schreibe /unsubscribe_{$politician->politician_id}.");
            } else {
                $this->send_message($chat_id, "Ich habe {$politician->name} von der Liste " . substr($politician->party_id, 5) . " ({$politician->party->name}) gefunden. Ich werde dich zu dieser*diesem Kandi auf dem Laufenden halten.");
            }
            return;
        } else if ($politician && count($politician) == 0) {
            $this->send_message($chat_id, <<<EOD
            Ich habe leider keinen Kandi mit dieser Kandinummer gefunden. Bitte prüfe kurz auf Tippfehler. Wenn du die Kandi über ihren Namen suchen willst, kannst du das mit dem Befehl <b>/kandi NAME</b> tun.

            Die Kandinummer setzt sich aus der Wahlkreisnummer, der Listennummer und dem Listenplatz zusammen. <a href='https://www.zh.ch/de/politik-staat/wahlen-abstimmungen/kantons-regierungsratswahlen.html#-1097010600'>Die Wahlkreisnummern findest du hier</a>. Wenn es mehr als 10 Sitze auf der Liste gibt, ist die Platznummer immer zweistellig (z.B. 01, 02, 03 etc.). Für Lou Muster aus dem Wahlkreis Dietikon der Liste 20 auf Listenplatz 5 lautet die Listennummer "7_2005". Wenn du Lou also so suchen willst, schreib <b>/kandiNr:7_2005</b>.

            Schreib /help um zu sehen, was ich kann.
            EOD
            );
            return;
        } else {
            $this->send_message($chat_id, "Huups, da ist wohl etwas schief gelaufen. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function unsubscribe($content, $chat_id) {
        $politician = PoliticianResult::where('politician_id', $content)->get();
        if ($politician && count($politician) == 1) {
            $politician = $politician->first();
            $removed = $politician->removeChatInterested($chat_id);
            if (!$removed) {
                $this->send_message($chat_id, "Du hast {$politician->name} noch nicht auf deiner Liste. Falls du die Kandi hinzufügen willst, schreibe /subscribeNr:{$politician->politician_id}.");
            } else {
                $this->send_message($chat_id, "Ich habe {$politician->name} von deiner Liste entfernt.");
            }
            return;
        } else if ($politician && count($politician) == 0) {
            $this->send_message($chat_id, <<<EOD
            Ich habe leider keinen Kandi mit dieser Kandinummer gefunden. Bitte prüfe kurz auf Tippfehler. Wenn du die Kandi über ihren Namen suchen willst, kannst du das mit dem Befehl <b>/kandi NAME</b> tun.

            Die Kandinummer setzt sich aus der Wahlkreisnummer, der Listennummer und dem Listenplatz zusammen. <a href='https://www.zh.ch/de/politik-staat/wahlen-abstimmungen/kantons-regierungsratswahlen.html#-1097010600'>Die Wahlkreisnummern findest du hier</a>. Wenn es mehr als 10 Sitze auf der Liste gibt, ist die Platznummer immer zweistellig (z.B. 01, 02, 03 etc.). Für Lou Muster aus dem Wahlkreis Dietikon der Liste 20 auf Listenplatz 5 lautet die Listennummer "7_2005". Wenn du Lou also so suchen willst, schreib <b>/kandiNr:7_2005</b>.

            Schreib /help um zu sehen, was ich kann.
            EOD
            );
            return;
        } else {
            $this->send_message($chat_id, "Huups, da ist wohl etwas schief gelaufen. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function unsubscribe_parteien($content, $chat_id) {
        if (intval($content)) {
            $constituency = Municipality::where('id', $content)->first();
            $partyResult = PartyResult::where('municipality_id', $constituency->id)->get();
            $type = "Gemeinde";
        } else {
            $constituency = Constituency::where('name', "LIKE", "%{$constituency_name}%")->first();
            $partyResult = PartyResult::where('constituency_id', $constituency->id)->get();
            $type = "Wahlkreis";
        }
        if ($partyResult) {
            foreach ($partyResult as $party) {
                $removed = $party->removeChatInterested($chat_id);
            }
            if (!$removed) {
                $this->send_message($chat_id, "Du hast {$constituency->name} noch nicht auf deiner Liste.");
            } else {
                $this->send_message($chat_id, "Ich hab {$constituency->name} von deiner Liste entfernt.");
            }
            return;
        } else {
            $this->send_message($chat_id, "Huups, da ist wohl etwas schief gelaufen. Wenn du einer Partei in einem Wahlkreis entfolgen möchtest, schreib /unsubscribe_partei WAHLKREIS|GEMEINDE_ID. \nSchreib /parteien, damit ich dir alle Parteien, die ich verwende, anzeige.\nSchreib /wahlkreise, damit ich dir alle Wahlkreise, die ich verwende, anzeige.\nSchreib /gemeinden damit ich dir die Gemeinden mit ihren IDs zeige.\nSchreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function unsubscribe_all($content, $chat_id) {
        $politicians = PoliticianResult::where("chats_interested", "like", "%{$chat_id}%")->get();
        $parties = PartyResult::where("chats_interested", "like", "%{$chat_id}%")->get();
        if ($politicians && count($politicians) > 0 || $parties && count($parties) > 0) {
            foreach ($politicians as $politician) {
                $politician->removeChatInterested($chat_id);
            }
            foreach ($parties as $party) {
                $party->removeChatInterested($chat_id);
            }
            $this->send_message($chat_id, "Ich habe alle Kandis und Parteien von deiner Liste entfernt.");
            return;
        } else {
            $this->send_message($chat_id, "Du hast noch keine Kandis oder Parteien auf deiner Liste. Falls du eine Kandi hinzufügen willst, schreibe /subscribe VORNAME NACHNAME. Schreibe /help um zu sehen, was ich sonst noch kann.");
            return;
        }
    }

    public function list($content, $chat_id) {
        $politicians = PoliticianResult::where("chats_interested", "like", "%{$chat_id}%")->get();
        $parties = PartyResult::where("chats_interested", "like", "%{$chat_id}%")->get();
        if (count($politicians) == 0 && count($parties) == 0) {
            $this->send_message($chat_id, "Du hast noch keine Kandis oder Parteien auf deiner Liste. Füge welche hinzu, indem du /subscribe oder /subscribeNr verwendest. Schreib /help um zu sehen, was ich kann.");
            return;
        } else {
            if (count($politicians) > 0) {
                $this->send_message($chat_id, "Du hast folgende Kandis auf deiner Liste:");
                $max = ceil(count($politicians) / 10);
                $i = 0;
                $j = 0;
                for ($i; $i < $max; $i++) {
                    sleep(1);
                    $text = "";
                    for ($j = 0; $j < 10; $j++) {
                        if (isset($politicians[$j + $i * 10])) {
                            $text .= $politicians[$j + $i * 10]->name . ", " . $politicians[$j + $i * 10]->party->abbreviation . " (entfernen mit /unsubscribe:{$politicians[$j + $i * 10]->politician_id})\n";
                        } else {
                            break;
                        }
                    }
                    $this->send_message($chat_id, $text);
                }
            }
            if (count($parties) > 0) {
                $this->send_message($chat_id, "Du hast folgende Parteien auf deiner Liste:");
                $max = ceil(count($parties) / 10);
                $i = 0;
                $j = 0;
                for ($i; $i < $max; $i++) {
                    sleep(1);
                    $text = "";
                    for ($j = 0; $j < 10; $j++) {
                        if (isset($parties[$j + $i * 10])) {
                            if ($parties[$j + $i * 10]->municipal) {
                                $text .= $parties[$j + $i * 10]->party->abbreviation . " Gemeinde {$parties[$j + $i * 10]->municipality->name} (entfernen mit /unsubscribe_parteien:{$parties[$j + $i * 10]->municipality->id})\n";
                            } else {
                                $text .= $parties[$j + $i * 10]->party->abbreviation . " Wahlkreis {$parties[$j + $i * 10]->constituency->name} (entfernen mit /unsubscribe_parteien:{$parties[$j + $i * 10]->constituency->name})\n";
                            }
                        } else {
                            break;
                        }
                    }
                    $this->send_message($chat_id, $text);
                }
            }
            return;
        }
    }

    public function subscribe_kandis_wahlkreis($content, $chat_id) {
        $constituency = Constituency::where('name', 'LIKE', '%' . $content . '%')->first();
        if ($constituency) {
            $politicians = PoliticianResult::where('constituency_id', $constituency->id)->get();
            foreach ($politicians as $politician) {
                $politician->addChatInterested($chat_id);
            }
            $this->send_message($chat_id, "Ich habe den Wahlkreis {$constituency->name} gefunden. Ich werde dich zu den Kandis dieses Wahlkreises auf dem Laufenden halten.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keinen Wahlkreis mit diesem Namen gefunden. Schreib /wahlkreise, damit ich dir alle Wahlkreise, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_kandis_partei($content, $chat_id) {
        $party = Party::where('abbreviation', 'LIKE', '%' . $content . '%')->first();
        if ($party) {
            $politicians = PoliticianResult::where('party_id', "2023_" . $party->id)->get();
            foreach ($politicians as $politician) {
                $politician->addChatInterested($chat_id);
            }
            $this->send_message($chat_id, "Ich habe die Partei {$party->name} gefunden. Ich werde dich zu den Kandis dieser Partei auf dem Laufenden halten.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keine Partei mit diesem Namen gefunden. Schreib /parteien, damit ich dir alle Parteien, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_kandis_partei_wahlkreis($content, $chat_id) {
        $exploded = explode(" ", $content);
        if (count($exploded) != 2) {
            $this->send_message($chat_id, "Da ist etwas schief gelaufen. Um Kandis aus einem bestimmten Wahlkreis einer bestimmten Partei zu abonnieren, schreibe /subscribe_kandis_partei_wahlkreis PARTEI WAHLKREIS.\nSchreib /parteien, damit ich dir alle Parteien, die ich verwende, anzeige.\nSchreib /wahlkreise, damit ich dir alle Wahlkreise, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
            return;
        }
        $partyAbbreviation = explode(" ", $content)[0];
        $constituencyName = explode(" ", $content)[1];
        $party = Party::where('abbreviation', 'LIKE', '%' . $partyAbbreviation . '%')->first();
        $constituency = Constituency::where('name', 'LIKE', '%' . $constituencyName . '%')->first();
        if ($party && $constituency) {
            $politicians = PoliticianResult::where('party_id', "2023_" . $party->id)->where('constituency_id', $constituency->id)->get();
            foreach ($politicians as $politician) {
                $politician->addChatInterested($chat_id);
            }
            $this->send_message($chat_id, "Ich habe die Partei {$party->name} und den Wahlkreis {$constituency->name} gefunden. Ich werde dich zu den Kandis dieser Partei in diesem Wahlkreis auf dem Laufenden halten.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keine Partei oder keinen Wahlkreis mit diesem Namen gefunden. Schreib /parteien, damit ich dir alle Parteien, die ich verwende, anzeige. Schreib /wahlkreise, damit ich dir alle Wahlkreise, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_parteien_wahlkreis($content, $chat_id) {
        $constituency = Constituency::where('name', 'LIKE', '%' . $content . '%')->first();
        if ($constituency) {
            $parties = PartyResult::where('constituency_id', $constituency->id)->where("party_id", "LIKE", "2023_%")->where("municipal", false)->get();
            foreach ($parties as $party) {
                $party->addChatInterested($chat_id);
            }
            $this->send_message($chat_id, "Ich habe den Wahlkreis {$constituency->name} gefunden. Ich werde dich zu den Parteien dieses Wahlkreises auf dem Laufenden halten.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keinen Wahlkreis mit diesem Namen gefunden. Schreib /wahlkreise, damit ich dir alle Wahlkreise, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_parteien_gemeinde($content, $chat_id) {
        $municipality = Municipality::where('name', 'LIKE', '%' . $content . '%')->first();
        if ($municipality) {
            $partyResults = PartyResult::where('municipality_id', $municipality->id)->get();
            foreach ($partyResults as $partyResult) {
                $partyResult->addChatInterested($chat_id);
            }
            $this->send_message($chat_id, "Ich habe die Gemeinde {$municipality->name} gefunden. Ich werde dich zu den Parteien dieser Gemeinde auf dem Laufenden halten.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keine Gemeinde mit diesem Namen gefunden. Schreib /gemeinden, damit ich dir alle Gemeinden, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function wahlkreise($content, $chat_id) {
        $constituencies = Constituency::all();
        $text = "Ich verwende folgende Wahlkreise:\n";
        foreach ($constituencies as $constituency) {
            $text .= $constituency->name . "\n";
        }
        $this->send_message($chat_id, $text);
        return;
    }

    public function parteien($content, $chat_id) {
        $parties = Party::where("party_id", "LIKE", "2023_%")->get();
        $text = "Ich verwende folgende Parteikürzel:\n";
        foreach ($parties as $party) {
            $text .= "<b>{$party->abbreviation}</b> ({$party->name})" . "\n";
        }
        $this->send_message($chat_id, $text);
        return;
    }

    public function gemeinden($content, $chat_id) {
        $municipalities = Municipality::orderBy('name', 'asc')->get();
        $text = "Ich verwende folgende Gemeinden:\n";
        $this->send_message($chat_id, $text);
        $max = ceil(count($municipalities) / 50);
        $i = 0;
        $j = 0;
        for ($i; $i < $max; $i++) {
            sleep(1);
            $text = "";
            for ($j = 0; $j < 50; $j++) {
                if (isset($municipalities[$j + $i * 50])) {
                    $text .= "{$municipalities[$j + $i * 50]->name} (BfS-Nr: {$municipalities[$j + $i * 50]->id})\n";
                } else {
                    break;
                }
            }
            $this->send_message($chat_id, $text);
        }
        return;
    }
}
