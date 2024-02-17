<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bots;
use App\Models\OpenaiAssistants;
use App\Models\OpenaiAssistantsFiles;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Storage;


class OpenaiBotFilesController extends Controller
{
    function index(Request $request){
        $bot = Bots::where("id", $request->botid)->first();
        $assistant = OpenaiAssistants::where("bot_id", $bot->id)->first();
        $files = OpenaiAssistantsFiles::where("bot_openai_assistant_id", $assistant->id)->get();

        return view('backend.bots.openai.files', [
            'bot' => $bot,
            'assistant' => $assistant,
            'files' => $files, 
        ]);

    }


    function update(Request $request){

        $bot = Bots::where("id", $request->botid)->first();
        $assistant = OpenaiAssistants::where("bot_id", $bot->id)->first();
        $files = OpenaiAssistantsFiles::where("bot_openai_assistant_id", $assistant->id)->get();

        $maxfiles = 20 - count($files);

        $messages = [
            "attachments.max" => "file can't be more than $maxfiles."
        ];


        $this->validate($request, [
                /*'files.*' => 'mimes:c,cpp,css,csv,docx,gif,html,java,jpeg,jpg,js,json,md,pdf,text/php,png,pptx,py,rb,tar,tex,ts,txt,xlsx,xml,zip',*/
                'files' => 'max:'.$maxfiles,
        ],$messages);

        if($request->hasFile("files")){
            foreach($request->file("files") as $tmpfile){
                
                
                $path = $tmpfile->storeAs('openai_assistants/files', $tmpfile->hashName());
                try{
                    $uploadedOpenaiFile = OpenAI::files()->upload([
                        'file' => Storage::disk('local')->readStream($path),
                        'purpose' => 'assistants',
                    ]);
                } catch(\Exception $e){
                    Storage::disk('local')->delete($path);
                    continue;
                }

                $assistanfile = OpenaiAssistantsFiles::create([
                    "bot_openai_assistant_id" => $assistant->id,
                    "description" => $tmpfile->getClientOriginalName(),
                    "localpath" => $path,
                    "openai_file_id" => $uploadedOpenaiFile->id
                ]); 

            }

            //retrieve all files again
            $files = OpenaiAssistantsFiles::where("bot_openai_assistant_id", $assistant->id)->get();
        }

        $this->updateOpenAIAssistant($bot->name, $assistant, $files);
        return redirect()->route('backend.bots.openai.files', ['botid' => $bot->id]);

    }

    function delete(Request $request){
        $bot = Bots::where("id", $request->botid)->first();
        $assistant = OpenaiAssistants::where("bot_id", $bot->id)->first();
        $file = OpenaiAssistantsFiles::where("bot_openai_assistant_id", $assistant->id)->where("id", $request->id)->first();
        $deleteOpenAi = null;
        if(!$file){
            return "File not found";
        }
        if($file->openai_file_id){
            //delete from openai
            $deleteOpenAi = OpenAI::files()->delete($file->openai_file_id);
        }

        $local = Storage::disk('local')->delete($file->localpath);

        $file->delete();

        $files = OpenaiAssistantsFiles::where("bot_openai_assistant_id", $assistant->id)->get();

        $this->updateOpenAIAssistant($bot->name, $assistant, $files);
        return redirect()->route('backend.bots.openai.files', ['botid' => $bot->id]);
    }


    function updateOpenAIAssistant($name, $assistant, $files){
        $fileids = [];

        foreach($files as $file){
            $fileids[] = $file->openai_file_id;
        }

        $OPENAIassistant = OpenAI::assistants()->modify($assistant->openai_assistant_id, [
            'name' => $name,
            'file_ids' => 
                $fileids
            ,
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
