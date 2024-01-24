<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes();
        //Broadcast::routes(['middleware' => ['auth:sanctum']]); //https://github.com/pusher/pusher-js/issues/536
        require base_path('routes/channels.php');
    }
}
