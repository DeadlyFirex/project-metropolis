<x-app-layout>
    <x-slot name="header">
        header
        </h2>
    </x-slot>
    <button class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md shadow transition duration-200">
        Module Toevoegen
    </button>
    <div class="py-12 px-4 sm:px-6 lg:px-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-300 rounded-lg shadow-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Naam</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Beschrijving</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Categorie</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Afbeelding</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Acties</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($modules as $module)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-800">{{ $module->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $module->description }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $module->category }}</td>
                            <td class="px-4 py-3">
                                <img src="{{ asset('storage/' . $module->image_path) }}" alt="{{ $module->name }}" class="w-16 h-16 object-cover rounded-md shadow">
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow transition duration-200">
                                    Module Wijzigen
                                </button>
                                <form action="{{ route('modules.destroy', $module->id) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je deze module wilt verwijderen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-sm">
                                        Verwijderen
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
