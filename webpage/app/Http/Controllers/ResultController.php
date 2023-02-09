<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\PoliticianResult;
use App\Models\PartyResult;
use App\Models\Announcement;
use App\Models\Constituency;

class ResultController extends Controller
{

    public static function handleKrMunicipalities($municipalities) {
        
    }

    public static function handleKrParties($parties) {

    }

    public static function handleKrPoliticians($politicians) {

    }

    public function handleResultChangeKR($changedConstituencies, $constituencies) {

        $changedConstituencies = array_keys($changedConstituencies);
        $selectedConstituencies = [];
        foreach ($changedConstituencies as $changedConstituency) {
            $selectedConstituencies[] = $constituencies[$changedConstituency];
        }
        foreach ($selectedConstituencies as $constituency) {
            if (!isset($constituency["resultat"]["gebietAusgezaehlt"]) || $constituency["resultat"]["gebietAusgezaehlt"] == false) {
                echo("Constituency " . $constituency["wahlkreisNummer"] . " not counted yet" . PHP_EOL);
                continue;
            }
            foreach($constituency["resultat"]["listen"] as $partyResult) {
                $party = PartyResult::where("partyId", "2023_" . $partyResult["listeNummer"])->where("constituencyId", $constituency["wahlkreisNummer"])->first();
                if ($party == null) {
                    $party = new PartyResult();
                    $party->partyId = "2023_" . $partyResult["listeNummer"];
                    $party->constituencyId = $constituency["wahlkreisNummer"];
                }
                $party->votes = $partyResult["stimmen"] ?? 0;
                $party->voteShare = $partyResult["waehlerProzent"] ?? 0;
                $party->seats = $partyResult["sitze"] ?? 0;
                $party->seatsChange = $partyResult["sitzeVeraenderung"] ?? 0;
                $party->year = 2023;
                $party->save();
            }

            foreach($constituency["resultat"]["kandidaten"] as $politicianResult) {
                $politician = PoliticianResult::where("politicianId", $politicianResult["kandidatNummer"])->first();
                if ($politician == null) {
                    $politician = new PoliticianResult();
                    $politician->politicianId = $politicianResult["kandidatNummer"];
                }
                $politician->votes = $politicianResult["stimmen"] ?? 0;
                $politician->finalPosition = $politicianResult["rangInListeInWahlkreis"] ?? 0;
                $updatedType = "finalPosition";
                if ($politicianResult["gewaehlt"] != null) {
                    $politician->elected = $politicianResult["gewaehlt"];
                    $updatedType = "elected";
                }
                $politician->change_type = $updatedType;
                $politician->save();
            }

            $this->createAnnouncementKR($constituency);
        }
    }

    public function createAnnouncementKR($constituency) {
        $announcement = new Announcement();
        $constituencyName = Constituency::where("id", $constituency["wahlkreisNummer"])->first()->name;
        if (Announcement::where("title", "Neues Ergebnis für den Wahlkreis {$constituencyName}")->first() != null) {
            return;
        }
        $data = [
            "title" => "Neues Ergebnis für den Wahlkreis {$constituencyName}",
            "subtitle" => "Kantonsrat | Neues Ergebnis",
            "content" => [
                "time" => Carbon::now()->timestamp,
                "blocks" => [
                    [
                        "id"=> Str::random(10),
                        "type"=> "paragraph",
                        "data"=> [
                            "text"=> "Der Wahlkreis {$constituencyName} wurde soeben ausgezählt! Hier die Ergebnisse der Listen:"
                        ]
                    ],
                    [
                        "id"=> Str::random(10),
                        "type"=> "list",
                        "data"=> [
                            "style"=> "unordered",
                            "items"=> []
                        ]
                    ]
                ],
                "version" => "2.26.5"
            ]
        ];
        foreach($constituency["resultat"]["listen"] as $partyResult) {
            $data["content"]["blocks"][1]["data"]["items"][] = "{$partyResult["listeCode"]}: {$partyResult["waehlerProzent"]}% ({$partyResult["gewinnWaehlerProzent"]}%)";
        }
        $data["content"] = json_encode($data["content"], JSON_UNESCAPED_UNICODE);
        $announcement->createWithMessage($data);
        return true;
    }
}
