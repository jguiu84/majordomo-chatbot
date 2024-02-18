<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }
    //https://api.telegram.org/bot6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8/getWebhookInfo

    public function webhook(){
        $response = Telegram::getMe();
        Log::info($response);
        $updates = Telegram::getWebhookUpdates();
        Log::info($updates);
        return "ok";
    }

    public function createWebhook(){
        ///telegram/webhooks/*'
        $token = "6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8";
        $response = Telegram::setWebhook(['url' => 'https://mihai.creaclick.net/api/telegram/webhooks/'.$token]);
    }
}