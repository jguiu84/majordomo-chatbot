<?php

namespace App\Http\Controllers;

use App\Events\WhatsappProcessMessageEvent;
use App\Jobs\WhatsappProcessMessage;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class WhatsAppController extends Controller
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

    /**
     * inbound
     *
     * @param  mixed $request
     * @return void
     */
    public function inbound(Request $request)
    {

        \Log::info($request->all());

        // WhatsApp sender phone number
        $sender = $request->input('To');
        $from = $request->input('From');
        $body = $request->input('Body');
        $messageStatus = $request->input("MessageStatus");


        $result = "";

        // process the message
        //$message ="Pong";
        // Send whatsapp message result
        //$result =$this->sendWhatsAppMessage($message, $sender);

        // map request to WhatsappProcessMessageEvent & dispatch event
        // dispatched asynchronously
        WhatsappProcessMessageEvent::dispatch($request);


        return response()->json($result,200);
    }

    /**
     * status
     *
     * @param  mixed $request
     * @return void
     */
    public function status(Request $request){
        \Log::Info($request->all());
        return response()->json([],200);
    }

    public function sendWhatsAppMessage(string $message, string $recipient)
    {
        $twilio_whatsapp_number = getenv('TWILIO_WHATSAPP_NUMBER');
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");

        $client = new Client($account_sid, $auth_token);
        return $client->messages->create($recipient, [
                "from" => "whatsapp:$twilio_whatsapp_number",
                "body" => $message
            ]
        );
    }
}
