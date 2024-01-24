
<x-app-layout>

    index.blade.php

    <script>
    window.addEventListener('load',  () =>{

        console.log('loaded');
        Echo.private('chat.{{ $chat->id }}')
            .subscribed(function(){
                console.log('subscribed To Channel')
            })
            /*.listenToAll(function(){
                console.log('listening to channel')
            })*/
            .listen('MessageSent', (data) => {
                console.log(data);
                
            });

     });
    </script>

    <livewire:chat-form chat_id="{{ $chat->id }}"/>

</x-app-layout>
