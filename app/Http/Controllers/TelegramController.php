<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Telegram\Bot\FileUpload\InputFile;


use App\Models\OpenaiAssistants;
use OpenAI\Laravel\Facades\OpenAI;

use App\Models\User;
use App\Models\Bots;
use App\Models\Chats;
use App\Models\ChatMessages;

class TelegramController extends Controller
{
    public $telegram = null;
    public $telegram_chat_id = null;

    public $chat = null;
    public $bot = null;
    public $assistant = null;

    public $openai_thread_id = null;

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

        $message = $update->getMessage();
        if($message !== null ) {
            try{
                $this->telegram = new Api($token);
            } catch(\Exception $e){
                Log::info($e->getMessage());
            }

            $chat = $message->getChat();
            $this->telegram_chat_id = $chat->getId();
    


            $user = User::where("email", "$this->telegram_chat_id@telegram")->first();
            if(!$user){
                $name = $chat->getFirstName();
                $meta = ["from" => $message->getFrom(), "chat" => $chat];
                $user = User::create([
                    "name" => $name,
                    "email" => "$this->telegram_chat_id@telegram",
                    "password" => Hash::make(Str::random(20)),
                    "can_login" => false,
                    "meta" => json_encode($meta)
                ]);
            }

            $this->bot = Bots::where('id', 2)->first();
            
            $this->assistant = OpenaiAssistants::where("bot_id", $this->bot->id)->first();

            $this->chat = Chats::where('user_id', $user->id)->where('bot_id', $this->bot->id)->first();

            Log::info($this->chat);
            if(!$this->chat){
                $response = OpenAI::threads()->create([]);
                $this->chat = Chats::create([
                    'user_id' => $user->id,
                    'bot_id' => $this->bot->id,
                    'name' => $this->bot->name,
                    'meta' => ["openai_thread_id" => $response->id]
                ]);
            }

            if($this->chat->meta && $this->chat->meta["openai_thread_id"]){

            } else {
                $response = OpenAI::threads()->create([]);

                $this->chat->meta = [
                    "openai_thread_id" => $response->id
                ];
                $this->chat->update();
            } 

            $this->openai_thread_id = $this->chat->meta["openai_thread_id"];

            $newMessage = $message->getText();
            
            $dbMessage = ChatMessages::create([
                'chat_id' => $this->chat->id,
                'message' => $newMessage,
                'is_bot_answer' => false
            ]);

            
            $this->sendToBot($newMessage);
        

            /*
                *bold text*
                _italic text_
                [text](URL)
                `inline fixed-width code`
                ```pre-formatted fixed-width code block```
            */
            /*
            if($message->has('photo')){
                $photos = $message->getPhoto();
                //es una foto, leemos la ultima. creemos que van ordenadas por tamaño
                $photo = $photos[count($photos)-1];
                //Log::info($telegram->getFile( ["file_id" => $photo["file_id"]] ));
            }

            //https://api.telegram.org/file/bot6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8/photos/file_1.jpg

            try{
                $response = $this->telegram->sendMessage([
                    'chat_id' => $this->telegram_chat_id,
                    'text' => '*Pong*'
                ]);

            } catch(\Exception $e){
                Log::info($e->getMessage());
                
            }*/
        }
        
        return "ok";
    }
    //https://api.telegram.org/bot6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8/sendMessage?chat_id=1429508817&text=test

    public function createWebhook(){
        ///telegram/webhooks/*'
        $token = "6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8";
        $telegram = new Api($token);

        $response = $telegram->setWebhook(['url' => 'https://mihai.creaclick.net/api/telegram/webhooks/'.$token]);
        dd($response);
        //https://api.telegram.org/bot6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8/setWebhook?url=https://mihai.creaclick.net/api/telegram/webhooks/6634213094:AAH_mgIiIeD3-yPeNKKvjXN_qSzJUPOd8r8
    }

    function sendToBot($message){
        try {
            
            //Check Pending Messages from thread
            $response = OpenAI::threads()->runs()->list(
                threadId: $this->openai_thread_id,
                parameters: [
                    'limit' => 10,
                ],
            );
            foreach ($response->data as $threadRun) {
                Log::info($threadRun->id ." - ". $threadRun->status);

                if( in_array($threadRun->status, ['queued', 'in_progress']) ) {
                    $this->processThreadRun($threadRun);
                } /*else if ($threadRun->status == 'completed') {
                    $this->getThreadResponse($threadRun);
                }*/

               /* $response = OpenAI::threads()->runs()->cancel(
                    threadId: $this->openai_thread_id,
                    runId: $result->id,
                );*/

            }
             
            //SEND MESSAGE TO THREAD
            $message = OpenAI::threads()->messages()->create($this->openai_thread_id, [
                'role' => 'user',
                'content' => $message,
            ]);


            //RUN THREAD
            $threadRun = OpenAI::threads()->runs()->create(
                threadId: $this->openai_thread_id, 
                parameters: [
                    'assistant_id' => $this->assistant->openai_assistant_id,
                ],
            );
            //Log::info($threadRun);

            //LOAD ANSWER
            $this->processThreadRun($threadRun);

            
        } catch(\Exception $e){

            Log::error($e->getMessage());
        }
    }

    function processThreadRun($threadRun) {
        while(in_array($threadRun->status, ['queued', 'in_progress'])) {
            sleep(1);

            $threadRun = OpenAI::threads()->runs()->retrieve(
                threadId: $threadRun->threadId,
                runId: $threadRun->id,
            );
        }

        if ($threadRun->status !== 'completed') {
            $this->botAnswer('Request failed, please try again');
        }

        $this->getThreadResponse($threadRun);
    }

    function getThreadResponse($threadRun){
        $messageList = OpenAI::threads()->messages()->list(
            threadId: $threadRun->threadId,
        );

        $answer = $messageList->data[0]->content[0]->text->value;

        Log::info($answer);

        $this->botAnswer($answer);
    }

    public function botAnswer($message){


        $dbMessage = ChatMessages::create([
            'chat_id' => $this->chat->id,
            'message' => $message,
            'is_bot_answer' => true
        ]);

        preg_match( '/src="([^"]*)"/i', $message, $imagenes ) ;

        $message = strip_tags($message);
        
        $imagen_final = null;
        if(!empty($imagenes)){
            try{
                $imagen_final = new InputFile($imagenes[1]);
            } catch(\Exception $e){
                Log::info($e->getMessage());
                
            }
        }

        if($imagen_final){
            $message = str_replace("**Imagen:**", "", $message);
            try{
                $response = $this->telegram->sendPhoto([
                    'chat_id' => $this->telegram_chat_id,
                    'photo' => $imagen_final,
                    'caption' => $message,
                    'parse_mode' => "Markdown"
                ]);
            } catch(\Exception $e){
                Log::info($e->getMessage());
                
            }
        } else {
            try{
                $response = $this->telegram->sendMessage([
                    'chat_id' => $this->telegram_chat_id,
                    'text' => $message,
                    'parse_mode' => "Markdown"
                ]);
            } catch(\Exception $e){
                Log::info($e->getMessage());
                
            }
        }


        
    }
    
}