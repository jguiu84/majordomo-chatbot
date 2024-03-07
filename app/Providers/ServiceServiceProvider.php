<?php

namespace App\Providers;

use App\Contracts\Services\OpenAIService\OpenAIServiceInterface;
use App\Services\OpenAIService\OpenAIService;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(
            OpenAIServiceInterface::class,
            OpenAIService::class
        );
    }
}
