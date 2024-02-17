<div
x-data="{height:0, conversationElement:document.getElementById('conversation')}"
x-init="
    height=conversationElement.scrollHeight;
    $nextTick(() => conversationElement.scrollTop=height)
"
@scroll-bottom.window="$nextTick(() => conversationElement.scrollTop=height)"
>
    <div class="flex flex-col items-center justify-center w-screen min-h-screen bg-slate-100 text-gray-800 p-10">
    
    <!-- Component Start -->
    <div class="flex flex-col flex-grow w-full max-w-xl bg-white shadow-xl rounded-lg overflow-hidden">
        <div id="conversation" class="flex flex-col flex-grow h-1 p-4 overflow-auto">
            @foreach ($messages as $message)
                @if($message->is_bot_answer)
                <div class="flex w-full mt-2 space-x-3 max-w-xs ml-auto justify-end" wire:key="message-{{ $message->id }}">
                    <div>
                        <div class="bg-blue-600 text-white p-3 rounded-l-lg rounded-br-lg">
                            <p class="text-sm">{!! $message->message !!}</p>
                        </div>
                        {{-- <span class="text-xs text-gray-500 leading-none">2 min ago</span> --}}
                    </div>
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-slate-300"></div>
                </div>
                @else
                <div class="flex w-full mt-2 space-x-3 max-w-xs"  wire:key="message-{{ $message->id }}">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-slate-300"></div>
                    <div>
                        <div class="bg-slate-300 p-3 rounded-r-lg rounded-bl-lg">
                            <p class="text-sm">{!! $message->message !!}</p>
                        </div>
                        {{-- <span class="text-xs text-gray-500 leading-none">2 min ago</span> --}}
                    </div>
                </div>
                @endif
            @endforeach
            @if($botTyping)
            <div class="flex w-full mt-2 space-x-3 max-w-xs ml-auto justify-end" >
                <div>
                    <div class="bg-blue-600 text-white p-3 rounded-l-lg rounded-br-lg">
                        <div class='flex-row flex space-x-2 justify-center items-center dark:invert'>
                            <div class='h-2 w-2 bg-white rounded-full animate-bounce [animation-delay:-0.3s]'></div>
                            <div class='h-2 w-2 bg-white rounded-full animate-bounce [animation-delay:-0.15s]'></div>
                            <div class='h-2 w-2 bg-white rounded-full animate-bounce'></div>
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-slate-300"></div>
            </div>
            @endif
        </div>
        
        <div class="bg-slate-300 p-4">
            <form wire:submit.prevent="sendMessage">
                <input class="flex items-center h-10 w-full rounded px-3 text-sm" type="text" placeholder="Type your message…" wire:model="newMessage">
            </form>
        </div>
    </div>
    <!-- Component End  -->
    </div>
</div>
