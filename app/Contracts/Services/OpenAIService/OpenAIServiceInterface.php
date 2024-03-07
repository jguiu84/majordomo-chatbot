<?php

namespace App\Contracts\Services\OpenAIService;

interface OpenAIServiceInterface
{
    public function createThread() : string;

}
