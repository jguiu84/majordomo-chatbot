<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('whatsapp/webhooks')->group(function () {
    Route::post('/inbound',  [WhatsAppController::class,'inbound'])->name('whatsapp.inbound');
    Route::post('/status',  [WhatsAppController::class,'status'])->name('whatsapp.status');
});


Route::post('telegram/webhooks/{token}',  [TelegramController::class,'webhook'])->name('telegram.webhook');
Route::get('telegram/createwebhook',  [TelegramController::class,'createWebhook'])->name('telegram.createwebhook');
