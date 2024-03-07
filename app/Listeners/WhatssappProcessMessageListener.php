<?php

namespace App\Listeners;

use App\Events\WhatsappProcessMessageEvent;
use App\Jobs\WhatsappProcessMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WhatssappProcessMessageListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WhatsappProcessMessageEvent $event): void
    {
        \Log::info("WhatssappListener: Processing event...");
        WhatsappProcessMessage::dispatch($event);
    }
}
