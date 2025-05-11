<x-app-layout>
    <x-slot name="header">
        header
        </h2>
    </x-slot>

    <div class="py-12">
        @foreach ($modules as $module)
            {{$module}}
        @endforeach

    </div>
</x-app-layout>
