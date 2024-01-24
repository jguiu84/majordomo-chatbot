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

    public function mount() { }

    public function getListeners()
    {
        return [
            "echo-private:chat.{$this->chat_id},MessageSent" => 'notifyShipped',
        ];
    }
    /*
    public function getListeners()
    {
        return [
            // Public Channel
            "echo:chat-{$this->chat_id},MessageSent" => 'notifyNewOrder',
 
            // Private Channel
            "echo-private:chat-{$this->chat_id},MessageSent" => 'notifyNewOrder',
 
            // Presence Channel
            "echo-presence:chat-{$this->chat_id},MessageSent" => 'notifyNewOrder',
            "echo-presence:chat-{$this->chat_id},here" => 'notifyNewOrder',
            "echo-presence:chat-{$this->chat_id},joining" => 'notifyNewOrder',
            "echo-presence:chat-{$this->chat_id},leaving" => 'notifyNewOrder',
        ];
    }
    */

    public function notifyShipped($event){ 
        //array_push($this->messages, $this->newMessage . json_encode($event["message"]));
        array_push($this->messages, $event["message"]["message"]);
        
    }

    public function sendMessage()
    {

        $dbMessage = ChatMessages::create([
            'chat_id' => $this->chat_id,
            'message' => $this->newMessage,
            'is_bot_answer' => false
        ]);
        

        //array_push($this->messages, $this->newMessage);
        $this->newMessage = '';

        broadcast(new MessageSent($dbMessage));
    }


    public function render()
    {
        return view('livewire.chat-form');
    }
}
