<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Module Dashboard
            </h2>
            <button class="bg-blue-500 text-white px-4 py-2 text-sm sm:text-base rounded" id="increaseFontBtn">
                Tekstgrootte Vergroten
            </button>
        </div>
    </x-slot>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 mx-4 sm:mx-8">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div
            class="mx-4 sm:mx-8 mb-4 flex items-start justify-between rounded-lg border border-green-300 bg-green-100 px-6 py-4 text-green-800 shadow-md">
            <div class="flex items-center gap-2">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()"
                class="text-green-600 hover:text-green-800 text-xl font-bold leading-none">
                &times;
            </button>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center mx-4 sm:mx-8 mt-6">
        <button onclick="window.location.href='{{ route('simulatiedashboard') }}';"
            class="bg-green-700 hover:bg-green-900 text-white px-4 py-2 rounded w-full sm:w-auto">
            Simulatie Dashboard
        </button>
        <button id="openModuleForm" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded w-full sm:w-auto">
            Module Toevoegen
        </button>
    </div>

    <div class="pb-12 pt-6 px-4 sm:px-6 lg:px-8">
        <div class="overflow-x-auto">
            <table class="min-w-[700px] w-full text-sm text-left border border-gray-300 rounded-lg shadow-sm">
                <thead class="bg-gray-100">
                <!-- Eerste rij met checkbox + knop, de knop spreidt zich uit over de overige 5 kolommen -->
                <tr>
                    <th class="px-4 py-3">
                        <input type="checkbox" id="selectAll" />
                    </th>
                    <th colspan="5" class="px-4 py-3 text-gray-800">
                        <div class="my-2 max-w-md">
                            <button id="deleteSelectedBtn"
                                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-md shadow-md transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                Verwijder Geselecteerde Modules
                            </button>
                        </div>
                    </th>
                </tr>
                <!-- Tweede rij met kolomkoppen -->
                <tr>
                    <th class="px-4 py-3 font-medium text-gray-700 min-w-[120px]">Naam</th>
                    <th class="px-4 py-3 font-medium text-gray-700 min-w-[200px]">Beschrijving</th>
                    <th class="px-4 py-3 font-medium text-gray-700 min-w-[120px]">Categorie</th>
                    <th class="px-4 py-3 font-medium text-gray-700 min-w-[120px]">Afbeelding</th>
                    <th class="px-4 py-3 font-medium text-gray-700 min-w-[160px]">Acties</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($modules as $module)
                    <tr>
                        <td class="px-4 py-3">
                            <input type="checkbox" class="module-checkbox" value="{{ $module->id }}" />
                        </td>
                        <td class="px-4 py-3 text-gray-800 break-words">{{ $module->name }}</td>
                        <td class="px-4 py-3 text-gray-600 break-words">{{ $module->description }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $module->category }}</td>
                        <td class="px-4 py-3">
                            <img src="{{ asset('storage/' . $module->image_path) }}" alt="{{ $module->name }}"
                                 class="w-16 h-16 object-contain rounded-md shadow mx-auto">
                        </td>
                        <td class="px-4 py-3 text-gray-600 space-y-2">
                            <button
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow transition duration-200"
                                onclick="openEditModal({{ $module->id }})">
                                Module Wijzigen
                            </button>
                            <form action="{{ route('modules.destroy', $module->id) }}" method="POST"
                                  onsubmit="return confirm('Weet je zeker dat je deze module wilt verwijderen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md text-sm">
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

    <!-- Modal voor toevoegen -->
    <div id="moduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg w-full max-w-md mx-4 relative">
            <button id="closeModuleForm" class="absolute top-2 right-2 text-xl font-bold">&times;</button>
            <h2 class="text-xl font-semibold mb-4">Nieuwe Module Toevoegen</h2>
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
                <input type="file" name="image" id="image" class="w-full mb-1 p-2 border rounded"
                    accept=".jpeg,.jpg,.png" required>
                <small class="text-gray-500 block mb-3">Toegestane bestandstypes: .jpg, .jpeg, .png – Max. 2MB</small>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded w-full">Opslaan</button>
            </form>
        </div>
    </div>

    <!-- Modal voor bewerken -->
    <div id="editModuleModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg w-full max-w-md mx-4 relative">
            <button id="closeEditModuleModal" class="absolute top-2 right-2 text-xl font-bold">&times;</button>
            <h2 class="text-xl font-semibold mb-4">Module Wijzigen</h2>
            <form method="POST" id="editModuleForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="text" name="name" id="editName" placeholder="Naam"
                    class="w-full mb-3 p-2 border rounded" required>
                <textarea name="description" id="editDescription" placeholder="Beschrijving" class="w-full mb-3 p-2 border rounded"
                    required></textarea>
                <select name="category" id="editCategory" class="w-full mb-3 p-2 border rounded" required>
                    <option value="" disabled>Kies een categorie</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
                <input type="file" name="image" id="image" class="w-full mb-1 p-2 border rounded"
                    accept=".jpeg,.jpg,.png">
                <small class="text-gray-500 block mb-3">Toegestane bestandstypes: .jpg, .jpeg, .png – Max. 2MB</small>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded w-full">Opslaan</button>
            </form>
        </div>
    </div>

    {{-- Check if there are any modules before setting up JavaScript variables --}}
    @if ($modules->count())
        <script>
            // Make the list of modules available globally in JavaScript (used in other scripts)
            window.modules = @json($modules);
            // Set the URL for performing a bulk delete action (used in fetch requests)
            window.bulkDeleteUrl = "{{ route('modules.bulkDestroy') }}";
            // Set the CSRF token as a global variable, required for secure fetch() POST requests in Laravel
            window.csrfToken = "{{ csrf_token() }}";
        </script>
    @endif

    {{-- Load the corresponding JavaScript file via Vite --}}
    @vite(['resources/js/openModule.js'])
</x-app-layout>
