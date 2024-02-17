<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenaiAssistantsFiles extends Model
{
    use HasFactory;

    protected $fillable = [
        'bot_openai_assistant_id',
        'description',
        'openai_file_id',
        'localpath',
    ];
}
