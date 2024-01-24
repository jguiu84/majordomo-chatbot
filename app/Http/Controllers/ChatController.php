<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bots;
use App\Models\Chats;
use App\Models\ChatMessages;

class ChatController extends Controller
{
    public function index(Request $request) {

        $user = $request->user();

        $bot = Bots::where('id', $request->botid)->first();
        

        $chat = Chats::where('user_id', $user->id)->where('bot_id', $bot->id)->first();
        if(!$chat){
            $chat = Chats::create([
                'user_id' => $user->id,
                'bot_id' => $bot->id,
                'name' => $bot->name
            ]);
        }
        return view('chat.index', ['chat' => $chat]);
    }
}
