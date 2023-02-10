<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PartyResult;
use App\Models\PoliticianResult;
use App\Models\CountedMunicipality;
use App\Models\ScheduledMessage;

class ResultController extends Controller
{
    public function check_for_updates() {
        if (env("FILE_LOCATION") == "local") {
            $file = file_get_contents(storage_path("tmp/wahlen_resultate_2023_02_12.json"));
        } else {
            $file = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get(env("FILE_URL"))->body();
        }
        $json = json_decode($file, true);
        if (get_option("kr_last_update") == $json["timestamp"] && env("APP_ENV") != "local") {
            exit;
        }
        if (env("APP_ENV") != "local") {
            set_option("kr_last_update", $json["timestamp"]);
        }
        $krResults = $json["kantone"][1]["vorlagen"][0];
        unset($krResults["gemeinden"][151]);
        $krResults["gemeinden"] = array_values($krResults["gemeinden"]);
        $gemeinden = array_merge($krResults["gemeinden"], $krResults["zaehlkreise"]);
        foreach($krResults["wahlkreise"] as $constituency) {
            if ($constituency["resultat"]["gebietAusgezaehlt"] == false) {
                continue;
            }
            $this->handle_kr_party_constituency($constituency);
            $this->handle_kr_politicians_constituency($constituency);
        }
        foreach($gemeinden as $municipality) {
            if ($municipality["resultat"]["gebietAusgezaehlt"] == false) {
                continue;
            }
            $this->handle_kr_party_municipality($municipality);
            $this->handle_kr_politicians_municipality($municipality);
        }
    }

    public function handle_kr_party_municipality($municipality)
    {
        if (CountedMunicipality::where("municipality_id", $municipality["geoLevelnummer"])->where("type", "municipalParty")->first() == null) {
            CountedMunicipality::create([
                "municipality_id" => $municipality["geoLevelnummer"],
                "type" => "municipalParty",
            ]);
        } else {
            return;
        }
        $message_identifier = "municipality_{$municipality["geoLevelnummer"]}_parties";
        $chats_interested = PartyResult::where("municipality_id", $municipality["geoLevelnummer"])->where("municipal", true)->where("party_id", "LIKE", "2023_%")->first()->chats_interested;
        $scheduledMessages = [];
        foreach($chats_interested as $chat_interested) {
            $scheduledMessages[] = ScheduledMessage::firstOrNew([
                "tele_chat_id" => $chat_interested,
                "message_identifier" => $message_identifier,
                "content" => "Die Gemeinde {$municipality["geoLevelname"]} hat ihre Ergebnisse veröffentlicht. Hier die Parteiresultate:\n\n",
            ]);
        }
        foreach($municipality["resultat"]["listen"] as $partyResult) {
            echo("Updating " . $municipality["geoLevelname"] . " " . $partyResult["listeCode"] . PHP_EOL);
            $partyResultModel = PartyResult::where("party_id", "2023_" . $partyResult["listeNummer"])->where("municipality_id", $municipality["geoLevelnummer"])->where("municipal", true)->first();
            $partyResultModel->votes = $partyResult["waehler"];
            $partyResultModel->voteShare = $partyResult["waehlerProzent"];
            $partyResultModel->voteShare_change = $partyResult["gewinnWaehlerProzent"];
            $partyResultModel->save();
            foreach ($scheduledMessages as $scheduledMessage) {
                $scheduledMessage->content .= <<<EOD
                <b>{$partyResultModel->party->name}:</b>
                ------
                <em>Stimmen:</em> {$partyResultModel->votes}
                <em>Stimmenanteil:</em> {$partyResultModel->voteShare}%
                <em>Stimmenanteil Veränderung:</em> {$partyResultModel->voteShare_change}%


                EOD;
            }
        }
        foreach ($scheduledMessages as $scheduledMessage) {
            $scheduledMessage->save();
        }
    }

    public function handle_kr_party_constituency($constituency)
    {
        if (CountedMunicipality::where("constituency_id", $constituency["wahlkreisNummer"])->where("type", "constituencyParty")->first() == null) {
            CountedMunicipality::create([
                "constituency_id" => $constituency["wahlkreisNummer"],
                "type" => "constituencyParty",
            ]);
        } else {
            return;
        }
        $message_identifier = "constituency_{$constituency["wahlkreisNummer"]}_parties";
        $chats_interested = PartyResult::where("constituency_id", $constituency["wahlkreisNummer"])->where("municipal", false)->where("party_id", "LIKE", "2023_%")->first()->chats_interested;
        $scheduledMessages = [];
        foreach($chats_interested as $chat_interested) {
            $scheduledMessages[] = ScheduledMessage::firstOrNew([
                "tele_chat_id" => $chat_interested,
                "message_identifier" => $message_identifier,
                "content" => "Der Wahlkreis {$constituency["wahlkreisBezeichnung"]} hat seine Ergebnisse veröffentlicht. Hier die Parteiresultate:\n\n",
            ]);
        }
        foreach($constituency["resultat"]["listen"] as $partyResult) {
            echo("Updating " . $constituency["wahlkreisBezeichnung"] . " " . $partyResult["listeCode"] . PHP_EOL);
            $partyResultModel = PartyResult::where("party_id", "2023_" . $partyResult["listeNummer"])->where("constituency_id", $constituency["wahlkreisNummer"])->where("municipal", false)->first();
            $partyResultModel->votes = $partyResult["waehler"];
            $partyResultModel->voteShare = $partyResult["waehlerProzent"];
            $partyResultModel->voteShare_change = $partyResult["gewinnWaehlerProzent"];
            $partyResultModel->save();
            foreach ($scheduledMessages as $scheduledMessage) {
                $scheduledMessage->content .= <<<EOD
                <b>{$partyResultModel->party->name}:</b>
                ------
                <em>Stimmen:</em> {$partyResultModel->votes}
                <em>Stimmenanteil:</em> {$partyResultModel->voteShare}%
                <em>Stimmenanteil Veränderung:</em> {$partyResultModel->voteShare_change}%


                EOD;
            }
        }
        foreach ($scheduledMessages as $scheduledMessage) {
            $scheduledMessage->save();
        }
    }

    public function handle_kr_politicians_municipality($municipality)
    {
        if (CountedMunicipality::where("municipality_id", $municipality["geoLevelnummer"])->where("type", "municipalityPolitician")->first() == null) {
            CountedMunicipality::create([
                "municipality_id" => $municipality["geoLevelnummer"],
                "type" => "municipalityPolitician",
            ]);
        } else {
            return;
        }
        foreach($municipality["resultat"]["kandidaten"] as $politician) {
            $message_identifier = "municipality_politician_{$municipality["geoLevelnummer"]}_{$politician["kandidatNummer"]}";
            $politicianResultModel = PoliticianResult::where("politician_id", "{$municipality["geoLevelnummer"]}_{$politician["kandidatNummer"]}")->where("municipality_id", $municipality["geoLevelnummer"])->where("municipal", true)->first();
            $chats_interested = $politicianResultModel->chats_interested ?? [];
            $politicianResultModel->votes = $politician["stimmen"];
            $politicianResultModel->finalPosition = $politician["rangInListeInWahlkreis"];
            $politicianResultModel->save();
            $scheduledMessages = [];
            foreach($chats_interested as $chat_interested) {
                $scheduledMessages[] = ScheduledMessage::firstOrNew([
                    "tele_chat_id" => $chat_interested,
                    "message_identifier" => $message_identifier,
                    "content" => <<<EOD
                    Die Gemeinde {$municipality["geoLevelname"]} hat ihre Ergebnisse veröffentlicht. Hier das Ergenis von {$politicianResultModel->name}:
                        <b>Stimmen:</b> {$politicianResultModel->votes}
                        <b>Listenplatz:</b> {$politicianResultModel->initialPosition}
                        <b>Rang:</b> {$politicianResultModel->finalPosition}
                    EOD,
                ]);
            }
            foreach ($scheduledMessages as $scheduledMessage) {
                $scheduledMessage->save();
            }
        }
    }

    public function handle_kr_politicians_constituency($constituency)
    {
        if (CountedMunicipality::where("constituency_id", $constituency["wahlkreisNummer"])->where("type", "constituencyPolitician")->first() == null) {
            CountedMunicipality::create([
                "constituency_id" => $constituency["wahlkreisNummer"],
                "type" => "constituencyPolitician",
            ]);
        } else {
            return;
        }
        foreach($constituency["resultat"]["kandidaten"] as $politician) {
            $message_identifier = "constituency_politician_{$politician["kandidatNummer"]}";
            $politicianResultModel = PoliticianResult::where("politician_id", "{$politician["kandidatNummer"]}")->where("constituency_id", $constituency["wahlkreisNummer"])->where("municipal", false)->first();
            $chats_interested = $politicianResultModel->chats_interested ?? [];
            $politicianResultModel->votes = $politician["stimmen"];
            $politicianResultModel->finalPosition = $politician["rangInListeInWahlkreis"];
            $politicianResultModel->save();
            $scheduledMessages = [];
            foreach($chats_interested as $chat_interested) {
                $scheduledMessages[] = ScheduledMessage::firstOrNew([
                    "tele_chat_id" => $chat_interested,
                    "message_identifier" => $message_identifier,
                    "content" => <<<EOD
                    Der Wahlkreis {$constituency["wahlkreisNummer"]} hat seine Ergebnisse veröffentlicht. Hier das Ergenis von {$politicianResultModel->name}:
                        <b>Stimmen:</b> {$politicianResultModel->votes}
                        <b>Listenplatz:</b> {$politicianResultModel->initialPosition}
                        <b>Rang:</b> {$politicianResultModel->finalPosition}
                    EOD,
                ]);
            }
            foreach ($scheduledMessages as $scheduledMessage) {
                $scheduledMessage->save();
            }
        }
    }
}
