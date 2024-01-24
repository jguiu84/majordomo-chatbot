
<x-app-layout>

    index.blade.php
    <script type="module">

        Echo.private('chat.{$chatId}')
        .listen('MessageSent', (e) => {
            console.log(e.message);
        });

    </script>
</x-app-layout>
