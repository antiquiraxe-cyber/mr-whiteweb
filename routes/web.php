<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'home'])->name('home');
Route::post('/pass', [GameController::class, 'createPass'])->name('rooms.pass');
Route::post('/multiplayer', [GameController::class, 'createMultiplayer'])->name('rooms.multiplayer');
Route::post('/join', [GameController::class, 'join'])->middleware('throttle:20,1')->name('rooms.join');
Route::get('/rooms/{room}', [GameController::class, 'show'])->name('rooms.show');
Route::post('/rooms/{room}/settings', [GameController::class, 'settings'])->name('rooms.settings');
Route::post('/rooms/{room}/start', [GameController::class, 'start'])->name('rooms.start');
Route::post('/rooms/{room}/reveal', [GameController::class, 'nextReveal'])->name('rooms.reveal');
Route::post('/rooms/{room}/players/{player}/eliminate', [GameController::class, 'eliminate'])->name('players.eliminate');
Route::post('/rooms/{room}/guess', [GameController::class, 'guess'])->name('rooms.guess');
