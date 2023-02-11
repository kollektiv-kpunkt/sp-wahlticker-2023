<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeleChat;
use App\Models\PoliticianResult;
use App\Models\PartyResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Party;
use App\Models\Constituency;
use App\Models\Municipality;
use App\Models\OpenReply;

class TeleBot extends Controller
{
    public $admin_commands = ["send_to_all"];
    public $commands_withouth_agrs = ["start", "list", "subscribe_parteien_all_kreise", "subscribe_parteien_gemeinden", "unsubscribe_all", "wahlkreise", "gemeinden", "parteien", "kandis"];
    public $commands_with_args_helper =
    [
        "help" => <<<EOD
        Zu welcher Art von Updates möchtest du Hilfe?
        Schreibe "Kandis" für Hilfe zu den Kandidat*innenupdates.
        Schreibe "Parteien" für Hilfe zu den Parteienupdates.
        Schreibe "Alle" für alle meine Befehle.
        EOD,
        "subscribe" => "Ok! Gib mir bitte den Namen der Kandidat*in, die du abonnieren möchtest wie er auf dem Wahlzettel steht.",
        "subscribe_nr" => "Ok! Gib mir bitte die Nummer der Kandidat*in, die du abonnieren möchtest. Sie besteht aus der Nummer des Wahlkreises, der Listennummer und ihrem Listenplatz.",
        "unsubscribe" => "Ok! Gib mir bitte die Nummer der Kandidat*in, die du deabonnieren möchtest. Sie besteht aus der Nummer des Wahlkreises, der Listennummer und ihrem Listenplatz. Nutze /find_kandi VORNAME NACHNAME um die Kandinummer zu finden.",
        "unsubscribe_parteien" => "Ok! Schreib mir bitte den Namen des Wahlkreis oder die Nummer der Gemeinde, von der du die Parteiresultate deabonnieren möchtest. (Schreib /gemeinden, um die Nummern der Gemeinden zu sehen.)",
        "subscribe_kandis_wahlkreis" => "Ok! Schreib mir bitte den Namen des Wahlkreises, von dem du die Kandidat*innen abonnieren möchtest. (Schreib /wahlkreise, um die Namen der Wahlkreise zu sehen.)",
        "subscribe_kandis_partei" => "Ok! Schreib mir bitte den Namen der Partei, von der du die Kandidat*innen abonnieren möchtest. (Schreib /parteien, um die Namen der Parteien zu sehen.)",
        "subscribe_kandis_partei_kreis" => "Ok! Schreib mir bitte den Namen der Partei und des Wahlkreises, von dem du die Kandidat*innen abonnieren möchtest. (Schreib /parteien, um die Namen der Parteien zu sehen. Schreib /wahlkreise, um die Namen der Wahlkreise zu sehen.)",
        "subscribe_kandi_gemeinde" => "Ok! Schreib mir bitte die Kandinummer und die Nummer der Gemeinde, von der du die Kandidat*innen abonnieren möchtest. (Schreib /gemeinden, um die Nummern der Gemeinden zu sehen. Nutze /find_kandi VORNAME NACHNAME um die Kandinummer zu finden.)",
        "subscribe_kandi_gemeinde_all" => "Ok! Schreib mir bitte die Kandinummer zu denen ich die Updates in allen Gemeinden schicken soll. (Nutze /find_kandi VORNAME NACHNAME um die Kandinummer zu finden.)",
        "subscribe_kandis_gemeinde" => "Ok! Schreib mir bitte die Nummer der Gemeinde, von der du die Kandidat*innenresultate abonnieren möchtest. (Schreib /gemeinden, um die Nummern der Gemeinden zu sehen.)",
        "find_kandi" => "Schreib mir bitte den Kandi-Namen wie er auf dem Wahlzettel steht",
        "subscribe_parteien_wahlkreis" => "Ok! Schreib mir bitte den Namen des Wahlkreises, von dem du die Parteiresultate abonnieren möchtest. (Schreib /wahlkreise, um die Namen der Wahlkreise zu sehen.)",
        "subscribe_parteien_gemeinde" => "Ok! Schreib mir bitte die Nummer der Gemeinde, von der du die Parteiresultate abonnieren möchtest. (Schreib /gemeinden, um die Nummern der Gemeinden zu sehen.)",
        "send_to_all" => "Please provide the message you want to send to all subscribers."
    ];

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
        if (!isset($message["entities"][0]["type"]) || $message["entities"][0]["type"] != "bot_command") {
            $this->handle_text_message($message["text"], $chat_id);
        } else {
            $this->handle_command($message);
        }
    }

    public function send_message($chat_id, $content)
    {
        $url = "https://api.telegram.org/bot" . env('TELE_BOT_TOKEN') . "/sendMessage?chat_id=" . $chat_id . "&text=" . urlencode($content) . "&parse_mode=HTML";
        try {
            $response = Http::get($url);
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }
        return $response;
    }

    public function maintenance($chat_id)
    {
        $message = <<<EOD
        Hoi 👋
        Ich bin derzeit im Wartungsmodus. Sorry, versuch es morgen früh noch einmal.
        EOD;
        $this->send_message($chat_id, $message);
    }

    public function handle_command($message)
    {
        $commandInfo = $this->determine_command($message["text"]);
        $command = $commandInfo["command"];
        $commandLenght = $commandInfo["length"];

        $chat_id = $message['chat']['id'];
        $content = substr(stripslashes($message['text']), $commandLenght + 1);
        if (method_exists($this, $command)) {
            if (in_array($command, $this->admin_commands) && env("ADMIN_CHAT_ID") != $chat_id) {
                $this->send_message($chat_id, "I am sorry but I can't let you do that. 😉");
                exit;
            }
            if (in_array($command, $this->commands_withouth_agrs)) {
                $this->$command($chat_id);
            } else if ($content == "") {
                $this->handle_empty_content($command, $chat_id, $this->commands_with_args_helper[$command]);
            } else {
                $this->$command($content, $chat_id);
            }
        } else {
            $this->send_message($chat_id, 'Sorry, ich kenne diesen Befehl nicht. Schreibe /help um zu sehen, was ich alles kann.');
        }
    }

    public function start($chat_id)
    {
        $message = <<<EOD
        Hoi 👋
        Ich bin der SP Wahlbot. Ich kann dir Updates über die Resultate von Kantonsratskandis bei den Kantonalen Wahlen in Zürich schicken.
        Schreibe /help um zu sehen, was ich alles kann.
        EOD;
        $this->send_message($chat_id, $message);
    }

    public function help($content, $chat_id)
    {
        $help_messages = [
            "kandis" => <<<EOD
            Hier sind meine Befehle zu Kandiresultaten:

            /subscribe VORNAME NACHNAME - Abonniere Updates für Kandis (z.B. /subscribe Peter Müller).

            /subscribe_nr KANDI_ID - Abonniere Updates für Kandis (z.B. /subscribe_nr 1_101).

            /unsubscribe KANDI_ID - Deabonniere Updates für Kandis (z.B. /unsubscribe 1_101).

            /subscribe_kandis_wahlkreis WAHLKREIS - Abonniere Updates für alle Kandis in einem Wahlkreis (z.B. /subscribe_kandis_wahlkreis Zürich 1&2).

            /subscribe_kandis_partei PARTEI - Abonniere Updates für alle Kandis einer Partei (z.B. /subscribe_kandis_partei SP).

            /subscribe_kandis_partei_kreis PARTEI WAHLKREIS - Abonniere Updates für alle Kandis einer Partei in einem Wahlkreis (z.B. /subscribe_kandis_partei_kreis SP Zürich 1&2).

            /subscribe_kandi_gemeinde KANDI_ID GEMEINDE_ID - Abonniere Updates für ein Kandiresultat in einer Gemeinde

            /subscribe_kandi_gemeinde_all KANDI_ID - Abonniere Updates für Kandiresultate in allen Gemeinden der Kandidat*in

            /subscribe_kandis_gemeinde GEMEINDE_ID - Abonniere Updates für alle Kandiresultate in einer Gemeinde

            /find_kandi VORNAME NACHNAME - Finde Kandis (z.B. /find_kandi Peter Müller).

            Für diese Wahlen ist es leider nicht möglich, Updates für alle Kandis in einer Gemeinde zu abonnieren. Sorry 🙈 mein Entwickler hatte schlicht keine Zeit mehr, das zu implementieren.
            EOD,
            "parteien" => <<<EOD
            Hier sind meine Befehle zu Parteiresultaten:

            /subscribe_parteien_wahlkreis WAHLKREIS - Abonniere Updates für alle Parteien in einem Wahlkreis (z.B. /subscribe_parteien_wahlkreis Zürich 1&2).

            /subscribe_parteien_gemeinde GEMEINDE - Abonniere Updates für alle Parteien in einer Gemeinde (z.B. /subscribe_parteien_gemeinde Zürich).

            /subscribe_parteien_all_kreise - Abonniere Updates für alle Parteiresultate auf Ebene der Wahlkreise im ganzen Kanton.

            /subscribe_parteien_gemeinden - Abonniere Updates für alle Parteiresultate auf Ebene der Gemeinden im ganzen Kanton.

            /unsubscribe_parteien WAHLKREIS|GEMEINDE_ID - Deabonniere Updates für Parteiresultate in einem Wahlkreis oder einer Gemeinde (z.B. /unsubscribe_parteien Zürich 1&2).
            EOD,
            "rest" => <<<EOD
            Ausserdem kann ich noch folgendes:

            /start - Starte den Bot

            /help - Zeige den Hilfe Screen

            /unsubscribe_all - Deabonniere Updates für alle Kandis und Parteiresultate.

            /list - Zeige alle abonnierten Kandis und Parteiresultate.

            /parteien - Zeige alle Parteien, die ich kenne.

            /wahlkreise - Zeige alle Wahlkreise, die ich kenne.

            /gemeinden - Zeige alle Gemeinden, die ich kenne.
            EOD
        ];
        $type = strtolower($content);
        if ($type == "alle") {
            $this->send_message($chat_id, $help_messages["kandis"]);
            sleep(1);
            $this->send_message($chat_id, $help_messages["parteien"]);
            sleep(1);
            $this->send_message($chat_id, $help_messages["rest"]);
            sleep(1);
            $this->send_message($chat_id, 'Wenn du fragen hast, schreib meinem Entwickler <a href="https://t.me/TimothyJOesch">@TimothyJOesch</a>');
        } else if (array_key_exists($type, $help_messages)) {
            $this->send_message($chat_id, $help_messages[$type]);
            sleep(1);
            $this->send_message($chat_id, $help_messages["rest"]);
            sleep(1);
            $this->send_message($chat_id, 'Wenn du fragen hast, schreib meinem Entwickler <a href="https://t.me/TimothyJOesch">@TimothyJOesch</a>');
        } else {
            $this->send_message($chat_id, "Sorry, diese Art von Hilfe kenne ich nicht. Schreibe /help um zu sehen, was ich alles kann.");
        }
    }

    public function list($chat_id)
    {
        $politicians = PoliticianResult::where("chats_interested", "like", "%{$chat_id}%")->get();
        $constituencyResults = PartyResult::where("chats_interested", "like", "%{$chat_id}%")->where("municipal", false)->get();
        $constituencies = [];
        foreach ($constituencyResults as $constituencyResult) {
            $constituencies[] = $constituencyResult->constituency->name;
        }
        $subscribedConstituencies = array_unique($constituencies);
        $municipalityResults = PartyResult::where("chats_interested", "like", "%{$chat_id}%")->where("municipal", true)->get();
        $municipalities = [];
        foreach ($municipalityResults as $municipalityResult) {
            $municipalities[] = $municipalityResult->municipality->name . " (" . $municipalityResult->municipality->id . ")";
        }
        $subscribedMunicipalities = array_unique($municipalities);
        if (count($politicians) == 0 && count($constituencies) == 0 && count($municipalities) == 0) {
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
                            $currentPolitician = $politicians[$j + $i * 10];
                            if ($currentPolitician->municipal) {
                                $text .= "{$currentPolitician->name}, Gemeinde {$currentPolitician->municipality->name}, {$currentPolitician->party->abbreviation} (entfernen mit /unsubscribe_{$currentPolitician->politician_id})\n";
                            } else {
                                $text .= "{$currentPolitician->name}, Wahlkreis {$currentPolitician->constituency->name}, {$currentPolitician->party->abbreviation} (entfernen mit /unsubscribe_{$currentPolitician->politician_id})\n";
                            }
                        } else {
                            break;
                        }
                    }
                    $this->send_message($chat_id, $text);
                }
            }
            if (count($subscribedConstituencies) > 0) {
                sleep(1);
                $this->send_message($chat_id, "Du hast die Parteiresultate aus folgenden Wahlkreisen abonniert:\n" . implode("\n", $subscribedConstituencies) . "\n(entfernen mit /unsubscribe_parteien Wahlkreisname)");
            }
            if (count($subscribedMunicipalities) > 0) {
                sleep(1);
                $this->send_message($chat_id, "Du hast die Parteiresultate aus folgenden Gemeinden abonniert (IDs in Klammern):\n" . implode("\n", $subscribedMunicipalities) . "\n(entfernen mit /unsubscribe_parteien Gemeinde_ID)");
            }
            return;
        }
    }

    public function subscribe($content, $chat_id)
    {
        $politician = PoliticianResult::where('name', 'LIKE', '%' . $content . '%')->where("municipal", false)->get();
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

            Die Kandinummer setzt sich aus der Wahlkreisnummer, der Listennummer und dem Listenplatz zusammen. <a href='https://www.zh.ch/de/politik-staat/wahlen-abstimmungen/kantons-regierungsratswahlen.html#-1097010600'>Die Wahlkreisnummern findest du hier</a>. Wenn es mehr als 10 Sitze auf der Liste gibt, ist die Platznummer immer zweistellig (z.B. 01, 02, 03 etc.). Für Lou Muster aus dem Wahlkreis Dietikon der Liste 20 auf Listenplatz 5 lautet die Listennummer "7_2005". Wenn du Lou also so suchen willst, schreib <b>/kandiNr_7_2005</b>.

            Schreib /help um zu sehen, was ich kann.
            EOD
            );
            return;
        } else {
            $this->send_message($chat_id, "Ich habe mehrere Kandis mit diesem Namen gefunden. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_nr($content, $chat_id)
    {
        $politician = PoliticianResult::where('politician_id', $content)->get();
        if ($politician && count($politician) > 0) {
            $politician = $politician->first();
            $added = $politician->addChatInterested($chat_id);
            if (!$added) {
                $this->send_message($chat_id, "{$politician->name} ist bereits auf deiner Liste. Falls du die Kandi von der Liste entfernen willst, schreibe /unsubscribe_{$politician->politician_id}.");
            } else {
                if ($politician->municipal) {
                    $this->send_message($chat_id, "Ich habe {$politician->name} von der Liste " . substr($politician->party_id, 5) . " ({$politician->party->name}) in der Gemeinde {$politician->municipality->name} gefunden. Ich werde dich zu diesen Kandiresultaten auf dem Laufenden halten.");
                } else {
                    $this->send_message($chat_id, "Ich habe {$politician->name} von der Liste " . substr($politician->party_id, 5) . " ({$politician->party->name}) im Wahlkreis {$politician->constituency->name} gefunden. Ich werde dich zu diesen Kandiresultaten auf dem Laufenden halten.");
                }
            }
            return;
        } else {
            $this->send_message($chat_id, <<<EOD
            Ich habe leider keinen Kandi mit dieser Kandinummer gefunden. Bitte prüfe kurz auf Tippfehler. Wenn du die Kandi über ihren Namen suchen willst, kannst du das mit dem Befehl <b>/kandi NAME</b> tun.

            Die Kandinummer setzt sich aus der Wahlkreisnummer, der Listennummer und dem Listenplatz zusammen. <a href='https://www.zh.ch/de/politik-staat/wahlen-abstimmungen/kantons-regierungsratswahlen.html#-1097010600'>Die Wahlkreisnummern findest du hier</a>. Wenn es mehr als 10 Sitze auf der Liste gibt, ist die Platznummer immer zweistellig (z.B. 01, 02, 03 etc.). Für Lou Muster aus dem Wahlkreis Dietikon der Liste 20 auf Listenplatz 5 lautet die Listennummer "7_2005". Wenn du Lou also so suchen willst, schreib <b>/kandiNr_7_2005</b>.

            Schreib /help um zu sehen, was ich kann.
            EOD
            );
            return;
        }
    }

    public function subscribe_kandis_wahlkreis($content, $chat_id)
    {
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

    public function subscribe_kandis_partei($content, $chat_id)
    {
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

    public function subscribe_kandis_partei_kreis($content, $chat_id)
    {
        $exploded = explode(" ", $content);
        if (count($exploded) < 2) {
            $this->send_message($chat_id, "Da ist etwas schief gelaufen. Um Kandis aus einem bestimmten Wahlkreis einer bestimmten Partei zu abonnieren, schreibe /subscribe_kandis_partei_kreis PARTEI WAHLKREIS.\nSchreib /parteien, damit ich dir alle Parteien, die ich verwende, anzeige.\nSchreib /wahlkreise, damit ich dir alle Wahlkreise, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
            return;
        }
        $partyAbbreviation = $exploded[0];
        unset($exploded[0]);
        $constituencyName = implode(" ", $exploded);
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

    public function subscribe_kandi_gemeinde($content, $chat_id)
    {
        $exploded = explode(" ", $content);
        $politician_id = $exploded[0];
        $municipality_id = $exploded[1];
        $politician = PoliticianResult::where('politician_id', "{$municipality_id}_{$politician_id}")->first();
        if ($politician) {
            $politician->addChatInterested($chat_id);
            $this->send_message($chat_id, "Ich habe den Kandidaten {$politician->name} aus der Gemeinde {$politician->municipality->name} gefunden. Ich werde dich zu den Resultaten dieses Kandidaten auf dem Laufenden halten.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keinen Kandidaten mit dieser Kandinummer in dieser Gemeinde gefunden. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_kandi_gemeinde_all($content, $chat_id)
    {
        $politician_id = $content;
        $politicians = PoliticianResult::where('politician_id', "LIKE", "%\_{$politician_id}")->where("municipal",true)->get();
        if ($politicians && $politicians->count() > 0) {
            foreach ($politicians as $politician) {
                $politician->addChatInterested($chat_id);
            }
            $this->send_message($chat_id, "Ich habe {$politicians->first()->name} aus dem Wahlkreis {$politicians->first()->constituency->name} gefunden. Ich werde dich zu den Kandiresultaten aus allen Gemeinden auf dem Laufenden halten.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keinen Kandidaten mit dieser Kandinummer in allen Gemeinden gefunden. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_kandis_gemeinde($content, $chat_id)
    {
        $municipality = Municipality::where('id', $content)->first();
        if ($municipality) {
            $politicians = PoliticianResult::where('municipality_id', $municipality->id)->get();
            foreach ($politicians as $politician) {
                $politician->addChatInterested($chat_id);
            }
            $this->send_message($chat_id, "Ich habe die Gemeinde {$municipality->name} gefunden. Ich werde dich zu den Kandis dieser Gemeinde auf dem Laufenden halten.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keine Gemeinde mit diesem Namen gefunden. Schreib /gemeinden, damit ich dir alle Gemeinden, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function find_kandi($content, $chat_id)
    {
        $politician = PoliticianResult::where('name', "LIKE", "%{$content}%")->first();
        if ($politician) {
            $this->send_message($chat_id, "Ich habe {$politician->name} aus dem Wahlkreis {$politician->constituency->name} gefunden.\n\nDie Nummer lautet <b>{$politician->politician_id}</b>.\n\nSchreib /subscribe_kandi_{$politician->politician_id} um über diese Resultate auf dem Laufenden gehalten zu werden.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keinen Kandidaten mit dieser Kandinummer gefunden. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_parteien_wahlkreis($content, $chat_id)
    {
        $constituency = Constituency::where('name', 'LIKE', '%' . $content . '%')->first();
        if ($constituency) {
            $parties = PartyResult::where('constituency_id', $constituency->id)->where("party_id", "LIKE", "2023_%")->where("municipal", false)->get();
            foreach ($parties as $party) {
                $party->addChatInterested($chat_id);
            }
            $this->send_message($chat_id, "Ich habe den Wahlkreis {$constituency->name} gefunden. Ich werde dich zu den Parteiresultaten dieses Wahlkreises auf dem Laufenden halten.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keinen Wahlkreis mit diesem Namen gefunden. Schreib /wahlkreise, damit ich dir alle Wahlkreise, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_parteien_gemeinde($content, $chat_id)
    {
        $municipality = Municipality::where('name', 'LIKE', '%' . $content . '%')->first();
        if ($municipality) {
            $partyResults = PartyResult::where('municipality_id', $municipality->id)->get();
            foreach ($partyResults as $partyResult) {
                $partyResult->addChatInterested($chat_id);
            }
            $this->send_message($chat_id, "Ich habe die Gemeinde {$municipality->name} gefunden. Ich werde dich zu den Parteiresultaten dieser Gemeinde auf dem Laufenden halten.");
            return;
        } else {
            $this->send_message($chat_id, "Ich habe leider keine Gemeinde mit diesem Namen gefunden. Schreib /gemeinden, damit ich dir alle Gemeinden, die ich verwende, anzeige. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function subscribe_parteien_all_kreise($chat_id)
    {
        $partyResults = PartyResult::where("party_id", "LIKE", "2023_%")->where("municipal", false)->get();
        foreach ($partyResults as $partyResult) {
            $partyResult->addChatInterested($chat_id);
        }
        $this->send_message($chat_id, "Ich werde dich zu allen Parteiresultaten auf Ebene der Wahlkreise auf dem Laufenden halten.");
        return;
    }

    public function subscribe_parteien_gemeinden($chat_id)
    {
        $partyResults = PartyResult::where("party_id", "LIKE", "2023_%")->where("municipal", true)->get();
        foreach ($partyResults as $partyResult) {
            $partyResult->addChatInterested($chat_id);
        }
        $this->send_message($chat_id, "Ich werde dich zu allen Parteiresultaten auf Ebene der Gemeinde auf dem Laufenden halten.");
        return;
    }

    public function unsubscribe($content, $chat_id)
    {
        $politicianConstituency = PoliticianResult::where('politician_id', $content)->where("chats_interested", "LIKE" , "%$chat_id%")->get();
        $politicianMunicipality = PoliticianResult::where('politician_id', "LIKE", "%\_$content")->where("chats_interested", "LIKE" , "%$chat_id%")->get();
        $politician = $politicianConstituency->merge($politicianMunicipality);
        if ($politician) {
            if (count($politician) > 1) {
                foreach ($politician as $politician) {
                    $removed = $politician->removeChatInterested($chat_id);
                }
                if ($removed) {
                    $this->send_message($chat_id, "Ich habe alle abonnierten Resultate von {$politician->name} von deiner Liste entfernt.");
                } else {
                    $this->send_message($chat_id, "Ich konnte keine Resultate von {$politician->name} in diesem Gebiet auf deiner Liste finden.");
                }
            } else if (count($politician) == 1) {
                $politician = $politician->first();
                $removed = $politician->removeChatInterested($chat_id);
                if ($removed) {
                    if ($politician->municipal) {
                        $this->send_message($chat_id, "Ich habe die Resultate von {$politician->name} in der Gemeinde {$politician->municipality->name} von deiner Liste entfernt.");
                    } else {
                        $this->send_message($chat_id, "Ich habe die Resultate von {$politician->name} im Wahlkreis {$politician->constituency->name} von deiner Liste entfernt.");
                    }
                } else {
                    $this->send_message($chat_id, "Ich konnte keine Resultate von {$politician->name} in diesem Gebiet auf deiner Liste finden.");
                }
            }
            return;
        } else if ($politician && count($politician) == 0) {
            $this->send_message($chat_id, <<<EOD
            Ich habe leider keinen Kandi mit dieser Kandinummer gefunden. Bitte prüfe kurz auf Tippfehler. Wenn du die Kandi über ihren Namen suchen willst, kannst du das mit dem Befehl <b>/kandi NAME</b> tun.

            Die Kandinummer setzt sich aus der Wahlkreisnummer, der Listennummer und dem Listenplatz zusammen. <a href='https://www.zh.ch/de/politik-staat/wahlen-abstimmungen/kantons-regierungsratswahlen.html#-1097010600'>Die Wahlkreisnummern findest du hier</a>. Wenn es mehr als 10 Sitze auf der Liste gibt, ist die Platznummer immer zweistellig (z.B. 01, 02, 03 etc.). Für Lou Muster aus dem Wahlkreis Dietikon der Liste 20 auf Listenplatz 5 lautet die Listennummer "7_2005". Wenn du Lou also so suchen willst, schreib <b>/kandiNr_7_2005</b>.

            Schreib /help um zu sehen, was ich kann.
            EOD
            );
            return;
        } else {
            $this->send_message($chat_id, "Huups, da ist wohl etwas schief gelaufen. Schreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function unsubscribe_parteien($content, $chat_id)
    {
        if (intval($content)) {
            $constituency = Municipality::where('id', $content)->first();
            $partyResult = PartyResult::where('municipality_id', $constituency->id)->get();
            $type = "Gemeinde";
        } else {
            $constituency = Constituency::where('name', "LIKE", "%{$content}%")->first();
            $partyResult = PartyResult::where('constituency_id', $constituency->id)->get();
            $type = "Wahlkreis";
        }
        if ($partyResult) {
            foreach ($partyResult as $party) {
                $removed = $party->removeChatInterested($chat_id);
            }
            if (!$removed) {
                $this->send_message($chat_id, "Du hast {$constituency->name} noch nicht auf deiner Liste der Parteiresultate.");
            } else {
                $this->send_message($chat_id, "Ich hab {$constituency->name} von deiner Liste der Parteiresultate entfernt.");
            }
            return;
        } else {
            $this->send_message($chat_id, "Huups, da ist wohl etwas schief gelaufen. Wenn du einer Partei in einem Wahlkreis entfolgen möchtest, schreib /unsubscribe_parteien WAHLKREIS|GEMEINDE_ID. \nSchreib /parteien, damit ich dir alle Parteien, die ich verwende, anzeige.\nSchreib /wahlkreise, damit ich dir alle Wahlkreise, die ich verwende, anzeige.\nSchreib /gemeinden damit ich dir die Gemeinden mit ihren IDs zeige.\nSchreib /help um zu sehen, was ich kann.");
            return;
        }
    }

    public function unsubscribe_all($chat_id)
    {
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

    public function wahlkreise($chat_id)
    {
        $constituencies = Constituency::all();
        $text = "Ich verwende folgende Wahlkreise:\n";
        foreach ($constituencies as $constituency) {
            $text .= $constituency->name . "\n";
        }
        $this->send_message($chat_id, $text);
        return;
    }

    public function parteien($chat_id)
    {
        $parties = Party::where("party_id", "LIKE", "2023_%")->get();
        $text = "Ich verwende folgende Parteikürzel:\n";
        foreach ($parties as $party) {
            $text .= "<b>{$party->abbreviation}</b> ({$party->name})" . "\n";
        }
        $this->send_message($chat_id, $text);
        return;
    }

    public function gemeinden($chat_id)
    {
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

    public function determine_command($text)
    {
        $commandInfo = [
            "command" => "",
            "length" => 0,
        ];
        $all_underlines = [];
        $pos_last = 0;
        while (($pos = strpos($text, "_", $pos_last)) !== FALSE) {
            $pos_last   = $pos + 1;
            $all_underlines[] = $pos;
        }
        if (count($all_underlines) == 0) {
            $commandInfo["command"] = substr($text, 1);
            $commandInfo["length"] = strlen($commandInfo["command"]);
        } else {
            foreach ($all_underlines as $underline) {
                if ($underline == 0) {
                    continue;
                }
                if (method_exists($this, substr($text, 1, $underline - 1))) {
                    $commandInfo["command"] = substr($text, 1, $underline - 1);
                    $commandInfo["length"] = $underline;
                } else {
                    continue;
                }
            }
        }
        if (method_exists($this, substr($text, 1))) {
            $commandInfo["command"] = substr($text, 1);
            $commandInfo["length"] = strlen($commandInfo["command"]);
        }
        if (method_exists($this, substr($text, 1, strpos($text, " ") - 1))) {
            $commandInfo["command"] = substr($text, 1, strpos($text, " ") - 1);
            $commandInfo["length"] = strpos($text, " ");
        }
        return $commandInfo;
    }

    public function handle_empty_content($command, $chat_id, $message_if_empty)
    {
        $openReply = OpenReply::create([
            'command' => $command,
            'tele_chat_id' => $chat_id,
        ]);
        $this->send_message($chat_id, $message_if_empty);
        return;
    }

    public function handle_text_message($text, $chat_id)
    {
        $openReply = OpenReply::where('tele_chat_id', $chat_id)->where("replied", false)->where("created_at", ">", Carbon::now()->subSeconds(60))->latest()->first();
        if (!$openReply) {
            $this->start($chat_id);
            return;
        }
        $command = $openReply->command;
        $openReply->replied = true;
        $openReply->save();
        $this->$command($text, $chat_id);
    }

    public function send_to_all($content, $chat_id)
    {
        if ($content == "YESIDO") {
            $chats = TeleChat::all();
            foreach ($chats as $chat) {
                $this->send_message($chat->chat_id, "Nachricht von @wahlbot:\n\n" . $content);
            }
            $this->send_message($chat_id, "Nachricht wurde an " . count($chats) . " Chats gesendet.");
            return;
        }
        OpenReply::create([
            'command' => "send_to_all {$content}",
            'tele_chat_id' => $chat_id,
        ]);
        $this->send_message($chat_id, "Are you sure you want to send the following message to " . count(TeleChat::all()) . " chats?");
        sleep(1);
        $this->send_message($chat_id, $content);
        sleep(1);
        $this->send_message($chat_id, "If so, type YESIDO");
    }
}
