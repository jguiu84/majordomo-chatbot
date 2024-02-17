<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit OpenAI Assistant Files') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __(':name Files', ['name' => $bot->name]) }}
                            </h2>

                        </header>
                        <form method="post" enctype="multipart/form-data" action="{{ route('backend.bots.openai.files.update', ['botid' => $bot->id]) }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label :value="__('Files')" />
                                <small class="text-gray-400">{{ 20 - count($files) }} spots available for upload</small>
                                @foreach($files as $file)
                                <div class="my-2"><strong class="text-sm">{{ $file->description }} [{{ $file->openai_file_id??'PENDING' }}]</strong>

                                <x-primary-button-link href="{{ route('backend.bots.openai.files.delete', ['botid' => $bot->id, 'id' => $file->id]) }}">{{ __('Delete') }}</x-primary-button-link>
                                </div>
                                @endforeach

                            </div>

                            <div>
                                <x-input-label for="files" :value="__('Upload Files')" />
                                <div class="bg-slate-100 text-xs text-stone-500">Allowed files: "c", "cpp", "css", "csv", "docx", "gif", "html", "java", "jpeg", "jpg", "js", "json", "md", "pdf", "php", "png", "pptx", "py", "rb", "tar", "tex", "ts", "txt", "xlsx", "xml", "zip"
                                </div>
                                <input class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100" id="file_input" type="file" name="files[]" multiple>
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
<script type="text/javascript">
    
$(function(){
    $("input[type='submit']").click(function(){
        var $fileUpload = $("input[type='file']");
        var $maxnum = {{ 20 - count($files) }};
        if (parseInt($fileUpload.get(0).files.length)>$maxnum){
         alert("You can only upload a maximum of "+$maxnum+" files");
        }
    });    
});​


</script>
</x-backend-layout>
