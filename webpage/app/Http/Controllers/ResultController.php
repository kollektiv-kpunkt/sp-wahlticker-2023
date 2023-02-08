<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PoliticianResult;
use App\Models\PartyResult;

class ResultController extends Controller
{

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
        }
    }
}
