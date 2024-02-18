<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

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

    public function webhook($token, Request $request){

        $update = Telegram::getWebhookUpdates();
        Log::info($update);

        $chat_id = $update->getMessage()->getChat()->getId();
        
        try{
            $telegram = new Api($token);
        } catch(\Exception $e){
            Log::info($e->getMessage());
        }
        /*
        try{
            $response = Telegram::bot('mih.ai')->getMe();
            Log::info($response);
        } catch(\Exception $e){
            Log::info($e->getMessage());
            
        }*/


        
        /*try{
            $response = $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Miisco'
            ]);
            Log::info($response);
        } catch(\Exception $e){
            Log::info($e->getMessage());
            
        }
        */

        try{
            $response = $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Pong'
            ]);
        } catch(\Exception $e){
            Log::info($e->getMessage());
            
        }
    return "ok";
    }
    //https://api.telegram.org/bot6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8/sendMessage?chat_id=1429508817&text=test

    public function createWebhook(){
        ///telegram/webhooks/*'
        $token = "6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8";
        $response = Telegram::setWebhook(['url' => 'https://mihai.creaclick.net/api/telegram/webhooks/'.$token]);
        dd($response );
        //https://api.telegram.org/bot6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8/setWebhook?url=https://mihai.creaclick.net/api/telegram/webhooks/6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8
    }
}