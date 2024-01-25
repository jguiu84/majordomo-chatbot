
<x-app-layout>
<livewire:chat-form chat_id="{{ $chat->id }}"/>
     <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <script>
           /* window.addEventListener('load',  () =>{

                console.log('loaded');
                Echo.private('chat.{{ $chat->id }}')
                    .subscribed(function(){
                        console.log('subscribed To Channel')
                    })
                    .listenToAll(function(){
                        console.log('listening to channel')
                    })
                    .listen('MessageSent', (data) => {
                        console.log(data);
                        
                    });

             });*/
            </script>

            
        </div>
    </div>
</x-app-layout>
