<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('throttle:30,1')->group(function () {
    //Route::get('/ip', [\App\Http\Controllers\APIController::class, 'getIPData'])->name('ip.get');
    // Va a ver cuantos mensajes tiene ese email que le paso
    Route::get('/inbox', [\App\Http\Controllers\APIController::class, 'inbox'])->name('inbox.get');
    Route::post('/inbox/create', [\App\Http\Controllers\APIController::class, 'createEmail'])->name('createEmail');
    // delete temp email
    Route::post('/inbox/delete/email={email}', [\App\Http\Controllers\APIController::class, 'delete'])->name('delete');
    // delete message
    Route::post('/inbox/delete/message={message_id}', [\App\Http\Controllers\APIController::class, 'deleteMessage'])->name('deleteMessage');
});
