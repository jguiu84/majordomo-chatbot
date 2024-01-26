<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bots;
use App\Models\OpenaiAssitants;
use OpenAI\Laravel\Facades\OpenAI;

class OpenaiBotController extends Controller
{
    function config(Request $request){

        $bot = Bots::where("id", $request->botid)->first();
        $assistant = OpenaiAssitants::where("bot_id", $bot->id)->first();
        return view('backend.bots.openai.config', [
            'bot' => $bot,
            'assistant' => $assistant
        ]);
    }


    function update(Request $request){

        $bot = Bots::where("id", $request->botid)->first();

        $assistant = OpenaiAssitants::where("bot_id", $bot->id)->first();
        if(!$assistant) { 
            $assistant = OpenaiAssitants::create([
                "bot_id" => $bot->id,
                "prompt" => ""
            ]);
        }

        $assistant->prompt = $request->prompt;
        $assistant->update();
    
        if($assistant->openai_assistant_id){
            $this->updateOpenAIAssistant($bot->name, $assistant);
        } else {
            $openai_assistant_id = $this->createOpenAIAssistant($bot->name, $assistant);
            $assistant->openai_assistant_id = $openai_assistant_id;
            $assistant->update();
        }
        return redirect()->route('backend.bots.openai.config',['botid' => $bot->id]);
    }

    function createOpenAIAssistant($name, $assistant){
        $OPENAIassistant = OpenAI::assistants()->create([
            'name' => $name,
            /*'file_ids' => [
                $this->argument('file_id'),
            ],*/
            'tools' => [
                [
                    'type' => 'retrieval',
                ],
            ],
            'instructions' => $assistant->prompt,
            'model' => 'gpt-4-1106-preview',
        ]);

        return $OPENAIassistant->id;
    }

    function updateOpenAIAssistant($name, $assistant){
        $OPENAIassistant = OpenAI::assistants()->modify($assistant->openai_assistant_id, [
            'name' => $name,
            /*'file_ids' => [
                $this->argument('file_id'),
            ],*/
            'tools' => [
                [
                    'type' => 'retrieval',
                ],
            ],
            'instructions' => $assistant->prompt,
            'model' => 'gpt-4-1106-preview',
        ]);
    }
}
