<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bots;
use App\Models\OpenaiAssistants;
use App\Models\OpenaiAssistantsFiles;
use OpenAI\Laravel\Facades\OpenAI;

class OpenaiBotController extends Controller
{
    function config(Request $request){

        $bot = Bots::where("id", $request->botid)->first();
        $assistant = OpenaiAssistants::where("bot_id", $bot->id)->first();
        if(!$assistant){
           $assistant = OpenaiAssistants::create([
            "bot_id" => $bot->id,
            "prompt" => ""
           ]);
        }
        $files = OpenaiAssistantsFiles::where("bot_openai_assistant_id", $assistant->id)->get();

        return view('backend.bots.openai.config', [
            'bot' => $bot,
            'assistant' => $assistant,
            'files' => $files, 
        ]);
    }


    function update(Request $request){

        $bot = Bots::where("id", $request->botid)->first();

        $assistant = OpenaiAssistants::where("bot_id", $bot->id)->first();
        if(!$assistant) { 
            $assistant = OpenaiAssistants::create([
                "bot_id" => $bot->id,
                "prompt" => ""
            ]);
        }

        $assistant->prompt = $request->prompt;
        $assistant->update();
    
        if($assistant->openai_assistant_id){
            $files = OpenaiAssistantsFiles::where("bot_openai_assistant_id", $assistant->id)->get();

            $this->updateOpenAIAssistant($bot->name, $assistant, $files);
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

    function updateOpenAIAssistant($name, $assistant, $files){
        $fileids = [];

        foreach($files as $file){
            $fileids[] = $file->openai_file_id;
        }

        $OPENAIassistant = OpenAI::assistants()->modify($assistant->openai_assistant_id, [
            'name' => $name,
            'file_ids' => $fileids,
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
