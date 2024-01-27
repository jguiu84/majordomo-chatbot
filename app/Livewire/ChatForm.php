<?php

namespace App\Livewire;

use Livewire\Component;
use App\Events\MessageSent;
use Auth;
use App\Models\ChatMessages;
use App\Models\Chats;
use App\Models\Bots;
use App\Models\OpenaiAssitants;
use OpenAI\Laravel\Facades\OpenAI;

class ChatForm extends Component
{
    public $chat_id;

    public $messages = [];
    public $newMessage;
    public $botTyping = false;

    public $chat = null;
    public $bot = null;
    public $assistant = null;

    public $openai_thread_id = null;

    protected $rules = [
        'newMessage' => 'required'
        //'date_start' => 'required_unless:repeat,0',
        //'date_end' => 'required_unless:repeat,0'
    ];

    protected $listeners = [
        //'bank-account-added' => '$refresh',
        'send-to-bot' => 'sendToBot'
        //'expense-updated' => '$refresh',
        //'togglePayment'
    ];

    //solo se ejecuta al inciar la conversacion
    public function mount() { 
        $this->chat = Chats::where("id", $this->chat_id)->first();
        $this->bot = Bots::where("id", $this->chat->bot_id)->first();
        $this->assistant = OpenaiAssitants::where("bot_id", $this->bot->id)->first();
        //TODO:aqui comprobamos de que tipo es y lo inicializamos
        
        //Buscamos los mensajes anteriores

        $old_messages = ChatMessages::where('chat_id', $this->chat_id)
                            ->orderBy("created_at")
                            ->get();

        foreach($old_messages as $old_message){
            array_push($this->messages, $old_message);
        }

        //buscamos el thread de OPENAI

        if($this->chat->meta && $this->chat->meta["openai_thread_id"]){

        } else {
            $response = OpenAI::threads()->create([]);

            $this->chat->meta = [
                "openai_thread_id" => $response->id
            ];
            $this->chat->update();
        } 

        $this->openai_thread_id = $this->chat->meta["openai_thread_id"];

    }


    /*public function getListeners()
    {
        return [
            // Public Channel
            //"echo:chat.{$this->chat_id},MessageSent" => 'notifyNewOrder',
            // Private Channel
            "echo-private:chat.{$this->chat_id},MessageSent" => 'notifyShipped',

            // Presence Channel
            //"echo-presence:chat.{$this->chat_id},MessageSent" => 'notifyNewOrder',
            //"echo-presence:chat.{$this->chat_id},here" => 'notifyNewOrder',
            //"echo-presence:chat.{$this->chat_id},joining" => 'notifyNewOrder',
            //"echo-presence:chat.{$this->chat_id},leaving" => 'notifyNewOrder',
        ];
    }*/
 

    public function notifyShipped($event){ 
        //array_push($this->messages, $this->newMessage . json_encode($event["message"]));
        array_push($this->messages, (object)$event["message"]);
        
    }

    public function sendMessage()
    {
        header('X-Accel-Buffering: no');
        $this->validate();

        $dbMessage = ChatMessages::create([
            'chat_id' => $this->chat_id,
            'message' => $this->newMessage,
            'is_bot_answer' => false
        ]);
        

        //array_push($this->messages, $this->newMessage);
        $this->newMessage=null;

        array_push($this->messages, $dbMessage);

        $this->botTyping = true;
        $this->dispatch("scroll-bottom");
        $this->dispatch("send-to-bot", message: $dbMessage->message)->self();//->to(BankAccountsComponent::class);
        //$this->sendToBot($dbMessage->message);
        //broadcast(new MessageSent($dbMessage));
    }

    function sendToBot($message){
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

        //LOAD ANSWER

        while(in_array($threadRun->status, ['queued', 'in_progress'])) {
            $threadRun = OpenAI::threads()->runs()->retrieve(
                threadId: $threadRun->threadId,
                runId: $threadRun->id,
            );
        }

        if ($threadRun->status !== 'completed') {
            $this->error = 'Request failed, please try again';
        }

        $messageList = OpenAI::threads()->messages()->list(
            threadId: $threadRun->threadId,
        );

        $answer = $messageList->data[0]->content[0]->text->value;

        $this->botAnswer($answer);
    }

    public function botAnswer($message){

        
        $this->botTyping = false;

        $dbMessage = ChatMessages::create([
            'chat_id' => $this->chat_id,
            'message' => $message,
            'is_bot_answer' => true
        ]);
        array_push($this->messages, $dbMessage);
        $this->dispatch("scroll-bottom");
    }


    public function render()
    {
        return view('livewire.chat-form');
    }
}
