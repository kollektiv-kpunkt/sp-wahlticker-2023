<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\Announcement;
use App\Http\Controllers\AnnouncementController;
use Illuminate\Support\Facades\Storage;
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

Route::match(['get', 'post'], '/wahlbot', function () {
    $telebot = new TeleBot();
    $telebot->webhook();
})->name('telegraph.webhook');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', function () {
    $announcements = Announcement::orderBy('created_at', 'desc')->paginate(7);
    return view('admin', compact('announcements'));
})->middleware(['auth', 'verified'])->name('admin');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('announcements', AnnouncementController::class);

require __DIR__.'/auth.php';
