<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit OpenAI Assistant') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Assistant Information') }}
                            </h2>

                        </header>
                        <form method="post" action="{{ route('backend.bots.openai.update', ['botid' => $bot->id]) }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="prompt" :value="__('Prompt')" />
                                <x-textarea-input id="prompt" name="prompt" type="text" class="mt-1 block w-full" rows="8">{{ old('prompt', $assistant->prompt??'') }}</x-textarea-input>
                                <x-input-error class="mt-2" :messages="$errors->get('prompt')" />
                            </div>

                            <div>
                                <x-input-label for="prompt" :value="__('OpenAI Assistant ID')" />
                                <strong class="text-sm">{{ $assistant->openai_assistant_id??'[NONE]' }}</strong>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-backend-layout>
