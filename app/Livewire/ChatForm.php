<?php

namespace App\Livewire;

use Livewire\Component;
use App\Events\MessageSent;
use Auth;
use App\Models\ChatMessages;

class ChatForm extends Component
{
    public $chat_id;

    public $messages = [];
    public $newMessage;
    public $botTyping = false;


    protected $rules = [
        'newMessage' => 'required'
        //'date_start' => 'required_unless:repeat,0',
        //'date_end' => 'required_unless:repeat,0'
    ];

    public function mount() { }

    public function getListeners()
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
    }
 

    public function notifyShipped($event){ 
        //array_push($this->messages, $this->newMessage . json_encode($event["message"]));
        array_push($this->messages, (object)$event["message"]);
        
    }

    public function sendMessage()
    {
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

        $this->botAnswer($dbMessage->message);
        //broadcast(new MessageSent($dbMessage));
    }

    public function botAnswer($message){
        $this->botTyping = false;
        $message = strrev($message);
        $dbMessage = ChatMessages::create([
            'chat_id' => $this->chat_id,
            'message' => $message,
            'is_bot_answer' => true
        ]);
        array_push($this->messages, $dbMessage);
    }


    public function render()
    {
        return view('livewire.chat-form');
    }
}
