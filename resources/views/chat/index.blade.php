
<x-app-layout>
<livewire:chat-form chat_id="{{ $chat->id }}"/>
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
</x-app-layout>
