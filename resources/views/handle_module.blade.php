<x-app-layout>
    <x-slot name="header">
        header
        </h2>
    </x-slot>
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <button id="openModuleForm" class="bg-blue-500 text-white px-4 py-2 rounded">Module Toevoegen</button>

    <!-- Modal -->
    <div id="moduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg w-full max-w-md relative">
            <button id="closeModuleForm" class="absolute top-2 right-2 text-xl">&times;</button>
            <h2 class="text-xl font-semibold mb-4">Nieuwe Module Toevoegen</h2>
            <!-- Formulier voor module toevoegen -->
            <form method="POST" action="{{ route('modules.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="text" name="name" placeholder="Naam" class="w-full mb-3 p-2 border rounded" required>
                <textarea name="description" placeholder="Beschrijving" class="w-full mb-3 p-2 border rounded" required></textarea>
                <select name="category" class="w-full mb-3 p-2 border rounded" required>
                    <option value="" disabled selected>Kies een categorie</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
                <input type="file" name="image" class="w-full mb-3 p-2 border rounded" required>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Opslaan</button>
            </form>
        </div>
    </div>

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
                                <img src="{{ asset('storage/' . $module->image_path) }}" alt="{{ $module->name }}"
                                    class="w-16 h-16 object-cover rounded-md shadow">
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <button
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow transition duration-200">
                                    Module Wijzigen
                                </button>
                                <form action="{{ route('modules.destroy', $module->id) }}" method="POST"
                                    onsubmit="return confirm('Weet je zeker dat je deze module wilt verwijderen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-sm">
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
    @vite('resources/js/openModule.js')
</x-app-layout>
