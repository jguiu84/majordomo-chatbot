<?php

namespace App\Services\OpenAIService;

use App\Contracts\Services\OpenAIService\OpenAIServiceInterface;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Threads\Messages\ThreadMessageListResponse;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;

class OpenAIService implements OpenAIServiceInterface
{
    public function createThread(): string
    {
        $thread = OpenAI::threads()->create([]);
        return $thread->id;
    }

    public function generateAssistantsResponseOnThread($threadId, $assistantId, $requestMessage) : string {

        $this->checkPendingMessagesFromThread($threadId);

        [$message, $threadRun] = $this->submitMessage($threadId, $assistantId, $requestMessage);

        $threadRun = $this->waitOnRun($threadRun);

        $response = $this->processThreadRunResponse($threadRun, $message);

        return $response;
    }

    private function submitMessage($threadId, $assistantId, $requestMessage): array
    {
        //SEND MESSAGE TO THREAD
        $message = OpenAI::threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $requestMessage,
        ]);

        //RUN THREAD
        $threadRun = OpenAI::threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => $assistantId,
            ],
        );

        return[
            $message,
            $threadRun
        ];
    }

    private function waitOnRun($threadRun): ThreadRunResponse
    {

        while(in_array($threadRun->status, ['queued', 'in_progress'])) {

            $threadRun = OpenAI::threads()->runs()->retrieve(
                threadId: $threadRun->threadId,
                runId: $threadRun->id,
            );

            sleep(1);
        }

        return $threadRun;

    }

    private function processThreadRunResponse($threadRun, $message) : string {
        \Log::info("ProcessThreadRunResponse:". $threadRun->id ." - ". $threadRun->status);

        // check if any assistant functions should be processed
        $threadRun = $this->processRunFunctions($threadRun);

        \Log::debug($threadRun->id ." - ". $threadRun->status);
        if($threadRun->status == 'completed') {
            $messages = $this->getMessages($threadRun->threadId, 'asc', $message->id);
            $messagesData = $messages->data;

            if(!empty($messagesData)) {
                $messagesCount = count($messagesData);
                $assistantResponseMessage = '';

                // check if assistant sent more than 1 message
                if($messagesCount > 1) {
                    foreach ($messagesData as $message) {
                        // concatenate multiple messages
                        $assistantResponseMessage .= $message->content[0]->text->value . "\n\n";
                    }

                    // remove the last new line
                    $assistantResponseMessage = rtrim($assistantResponseMessage);
                }
                else {
                    // take the first and only message
                    $assistantResponseMessage = $messagesData[0]->content[0]->text->value;
                }

                // delete files source tags
                $assistantResponseMessage = preg_replace("/【.*?】/", "", $assistantResponseMessage);

                return $assistantResponseMessage;
            }
            else {
                \Log::error('Something went wrong; assistant didn\'t respond');
            }
        }
        else{
            \Log::error('Something went wrong; assistant run wasn\'t completed successfully');
        }
        return 'Request failed, please try again';
    }

    private function getMessages($threadId, $order = 'asc', $messageId = null): ThreadMessageListResponse
    {
        $params = [
            'order' => $order,
            'limit' => 10
        ];

        if($messageId) {
            $params['after'] = $messageId;
        }

        return OpenAI::threads()->messages()->list($threadId, $params);
    }

    private function processRunFunctions($threadRun) : ThreadRunResponse {

        if ($threadRun->status == 'requires_action' && $threadRun->requiredAction->type == 'submit_tool_outputs')
        {
            // Extract tool calls, multiple calls possible
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

            $threadRun = OpenAI::threads()->runs()->submitToolOutputs(
                threadId: $threadRun->threadId,
                runId: $threadRun->id,
                parameters: [
                    'tool_outputs' => $toolOutputs
                ]
            );

            $threadRun = $this->waitOnRun($threadRun);
        }

        return $threadRun;

    }

    private function checkPendingMessagesFromThread($threadId): void
    {

        //Check Pending Messages from thread
        $response = OpenAI::threads()->runs()->list(
            threadId: $threadId,
            parameters: [
                'limit' => 10,
            ],
        );
        foreach ($response->data as $threadRun) {
            Log::debug($threadRun->id ." - ". $threadRun->status);

            if( in_array($threadRun->status, ['queued', 'in_progress']) ) {
                $this->waitOnRun($threadRun);
            } /*else if ($threadRun->status == 'completed') {
                    $this->getThreadResponse($threadRun);
                }*/

            /* $response = OpenAI::threads()->runs()->cancel(
                 threadId: $this->openai_thread_id,
                 runId: $result->id,
             );*/

        }
    }


}
