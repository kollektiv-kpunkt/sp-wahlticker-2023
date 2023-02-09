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
    if (!isset($request->all()['message']))  {
        dd(array(
            'request' => $request->all(),
            'response' => "No message",
            "code" => 200
        ));
    }
    $telebot->webhook(request());
});
