<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeleBot;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::match(['get', 'post'], '/', function () {
    $telebot = new TeleBot();
    if (!isset(request()->all()['message']))  {
        dd(array(
            'request' => request()->all(),
            'response' => "No message",
            "code" => 200
        ));
    }
    $telebot->webhook(request());
});


Route::get("chiara", function () {
    $file = Http::withOptions([
        'verify' => false,
    ])->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ])->get(env("FILE_URL"))->body();
    $json = json_decode($file, true);

    $chiaraK11 = $json["kantone"][1]["vorlagen"][0]["zaehlkreise"][14]["resultat"]["kandidaten"][15];
    $chiaraK12 = $json["kantone"][1]["vorlagen"][0]["zaehlkreise"][15]["resultat"]["kandidaten"][15];
    $chiaraWK = $json["kantone"][1]["vorlagen"][0]["wahlkreise"][5]["resultat"]["kandidaten"][15];

    echo("K11: " . $chiaraK11["stimmen"] . " (Rang: " . $chiaraK11["rangInListeInWahlkreis"] . "%) <br>");
    echo("K12: " . $chiaraK12["stimmen"] . " (Rang: " . $chiaraK12["rangInListeInWahlkreis"] . "%) <br> \n");
    echo("WK: " . $chiaraWK["stimmen"] . " (Rang: " . $chiaraWK["rangInListeInWahlkreis"] . "%) \n");
    return;
});
