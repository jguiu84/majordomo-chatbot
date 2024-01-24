<div>
    <ul>
        @foreach ($messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>

    <input type="text" wire:model="newMessage" wire:keydown.enter="sendMessage">
</div>
