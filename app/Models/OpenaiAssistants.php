<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenaiAssistants extends Model
{
    use HasFactory;

    protected $fillable = [
        'bot_id',
        'prompt',
        'openai_assistant_id'
    ];

}
