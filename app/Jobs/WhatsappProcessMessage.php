<?php

namespace App\Jobs;

use App\Contracts\Services\OpenAIService\OpenAIServiceInterface;
use App\Events\WhatsappProcessMessageEvent;
use App\Models\Bots;
use App\Models\ChatMessages;
use App\Models\Chats;
use App\Models\OpenaiAssistants;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use Telegram\Bot\FileUpload\InputFile;
use Twilio\Rest\Client;

class WhatsappProcessMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected OpenAiServiceInterface $openAIService;

    /**
     * Create a new job instance.
     */
    public function __construct(
    )
    {

    }

    /**
     * Execute the job.
     */
    public function handle(WhatsappProcessMessageEvent $whatsappEvent): void
    {
        \Log::info("WhatssappJob: Processing job...");

        /** @var OpenAIServiceInterface $openAIService */
        $this->openAIService = App::make(OpenAIServiceInterface::class);

        // 1 - Retrieve the message from Twilio
        $sender = $whatsappEvent->sender;
        $inputMessage = $whatsappEvent->message;

        // 2 - Check User, create User if not exists
        $userEmail = "$sender@whatsapp";
        $user = User::where("email", $userEmail)->first();
        if(!$user){
            $name = $whatsappEvent->profileName;
            $meta = [
                "channel" => "whatsapp",
                "from" => $sender,
                "chat" => $whatsappEvent
            ];
            $user = User::create([
                "name" => $name,
                "email" => $userEmail,
                "password" => Hash::make(Str::random(20)),
                "can_login" => false,
                "meta" => json_encode($meta)
            ]);
        }

        // 2 - Invoke the model

        $bot = Bots::where('id', 2)->first();
        $assistant = OpenaiAssistants::where("bot_id", $bot->id)->first();
        $chat = Chats::where('user_id', $user->id)->where('bot_id', $bot->id)->first();
        Log::info($chat);
        if(!$chat){
            //$response = OpenAI::threads()->create([]);
            $threadId = $this->openAIService->createThread();
            $chat = Chats::create([
                'user_id' => $user->id,
                'bot_id' => $bot->id,
                'name' => $bot->name,
                'meta' => ["openai_thread_id" => $threadId]
            ]);
        }

        $openai_thread_id = $chat->meta["openai_thread_id"];

        ChatMessages::create([
            'chat_id' => $chat->id,
            'message' => $inputMessage,
            'is_bot_answer' => false
        ]);

        $messageResponse = $this->sendToBot($openai_thread_id,$assistant,$inputMessage);

        ChatMessages::create([
            'chat_id' => $chat->id,
            'message' => $messageResponse,
            'is_bot_answer' => true
        ]);

        preg_match( '/src="([^"]*)"/i', $messageResponse, $imagenes ) ;
        $messageResponse = strip_tags($messageResponse);
        $imagen_final = null;
        if(!empty($imagenes)){
            try{
                $imagen_final = new InputFile($imagenes[1]);
            } catch(\Exception $e){
                Log::info("InputFile: " . $e->getMessage());

            }
        }

        // 3 - Send the answer trough Twilio
        if($imagen_final){
            $messageResponse = str_replace("**Imagen:**", "", $messageResponse);
            $this->sendWhatsAppImage($imagen_final, $sender);

        } else {
            $this->sendWhatsAppMessage($messageResponse, $sender);
        }

    }

    public function sendWhatsAppMessage(string $message, string $recipient)
    {
        $twilio_whatsapp_number = getenv('TWILIO_WHATSAPP_NUMBER');
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");

        $client = new Client($account_sid, $auth_token);
        return $client->messages->create($recipient, [
                "from" => "whatsapp:$twilio_whatsapp_number",
                "body" => $message
            ]
        );
    }

    public function sendWhatsAppImage(InputFile $image, string $recipient): void
    {
        Log::error("Implement method!!");
    }

    function sendToBot($openai_thread_id,$assistant,$message): string
    {
        $response = $this->openAIService->generateAssistantsResponseOnThread(
            $openai_thread_id, $assistant->openai_assistant_id, $message);

        return $response;

        //Check Pending Messages from thread
        /*$response = OpenAI::threads()->runs()->list(
            threadId: $openai_thread_id,
            parameters: [
                'limit' => 10,
            ],
        );
        foreach ($response->data as $threadRun) {
            Log::info($threadRun->id ." - ". $threadRun->status);

            if( in_array($threadRun->status, ['queued', 'in_progress']) ) {
                $this->processThreadRun($threadRun);
            }

        }


        //SEND MESSAGE TO THREAD
        OpenAI::threads()->messages()->create($openai_thread_id, [
            'role' => 'user',
            'content' => $message,
        ]);

        //RUN THREAD
        $threadRun = OpenAI::threads()->runs()->create(
            threadId: $openai_thread_id,
            parameters: [
                'assistant_id' => $assistant->openai_assistant_id,
            ],
        );
        //Log::info($threadRun);

        //LOAD ANSWER
        return $this->processThreadRun($threadRun);*/
    }

    function processThreadRun($threadRun): string
    {
        while(in_array($threadRun->status, ['queued', 'in_progress'])) {
            sleep(1);

            $threadRun = OpenAI::threads()->runs()->retrieve(
                threadId: $threadRun->threadId,
                runId: $threadRun->id,
            );
        }

        if ($threadRun->status == 'requires_action') {
            // message needs to call a function
            $toolCalls = $threadRun->requiredAction->submitToolOutputs->toolCalls;
            $toolOutputs = [];

            foreach ($toolCalls as $toolCall) {
                $name = $toolCall->function->name;
                $arguments = json_decode($toolCall->function->arguments);

                // TODO: Call the function

                $toolOutputs[] = [
                    'tool_call_id' => $toolCall->id,
                    'output' => '90075995'
                ];
            }

            OpenAI::threads()->runs()->submitToolOutputs(
                threadId: $threadRun->threadId,
                runId: $threadRun->id,
                parameters: [
                    'tool_outputs' => $toolOutputs
                ]
            );

        }
        else if ($threadRun->status !== 'completed') {
            return 'Request failed, please try again';
        }

        return $this->getThreadResponse($threadRun);
    }

    function getThreadResponse($threadRun): string
    {
        $messageList = OpenAI::threads()->messages()->list(
            threadId: $threadRun->threadId,
        );

        $answer = $messageList->data[0]->content[0]->text->value;

        Log::info($answer);

        return $answer;
    }


}
