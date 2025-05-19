<x-app-layout>
    {{-- Koptekst --}}
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            Conditions&nbsp;Dashboard
        </h1>
    </x-slot>

    {{-- Pagina-inhoud --}}
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-600">
                    Beheer hier het <strong>maximale aantal modules per categorie</strong>
                    en de <strong>incompatibele categorie-combinaties</strong>
                    voor buur-slots.
                </p>

                {{-- TODO: voeg hier formulieren of een Livewire/Vue-component toe --}}
            </div>
        </div>
    </div>
</x-app-layout>
