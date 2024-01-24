<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bots') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Bot Information') }}
                            </h2>

                        </header>

                        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                            @csrf
                        </form>

                        <form method="post" action="{{ route('backend.bots.create') }}" class="mt-6 space-y-6">
                            @csrf

                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus autocomplete="name" />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Description')" />
                                <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description')" required autofocus autocomplete="description" />
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
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
