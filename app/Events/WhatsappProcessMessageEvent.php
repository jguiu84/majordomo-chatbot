<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class WhatsappProcessMessageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $messageId;
    public string $sender;
    public string $to;
    public string $message;
    public string $profileName;
    //public string $messageStatus;


    /**
     * Create a new event instance.
     */
    public function __construct(Request $request)
    {
        $this->messageId = $request->input('MessageSid');
        $this->sender = $request->input('From');
        $this->to = $request->input('To');
        $this->message = $request->input('Body');
        $this->profileName = $request->input('ProfileName');
        //$this->messageStatus = $request->input('MessageStatus');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
