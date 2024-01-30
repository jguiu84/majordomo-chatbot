<x-web-layout>
<div class="w-full h-screen flex">
    <div class="max-w-lg mx-auto p-4 md:p-2 place-self-center">
        <div class="text-2xl text-center">mih.AI</div>
        <div class="bg-white max-w-lg mx-auto p-8 md:p-12  rounded-lg shadow-2xl place-self-center">
        @foreach($bots as $bot)
        <div class="mt-2 text-center"><a href="{{ route('chat', [ 'botid' => $bot->id ]) }}">Chat with {{ $bot->name }}</a></div>
        @endforeach
        </div>
    </div>
</div>
</x-web-layout>
