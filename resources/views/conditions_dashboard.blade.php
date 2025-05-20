<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            Conditions&nbsp;Dashboard
        </h1>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if(session('status'))
                    <p class="mb-4 text-green-600">{{ session('status') }}</p>
                @endif


                {{-- ───── 1. MAX PER CATEGORIE ───── --}}
                <h2 class="font-semibold text-lg mb-3">
                    Maximaal aantal modules per categorie
                </h2>

                <table class="w-full mb-10 border">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 text-left">Categorie</th>
                        <th class="p-2 text-left">Max&nbsp;(– = ∞)</th>
                        <th class="p-2"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($conditions as $cond)
                        <tr class="border-t">
                            <td class="p-2">{{ $cond->category }}</td>
                            <td class="p-2">
                                <form method="POST"
                                      action="{{ route('conditions.update', $cond) }}"
                                      class="flex gap-2">
                                    @csrf @method('PUT')
                                    <input type="text"
                                           name="max"
                                           value="{{ $cond->max === null ? '-' : $cond->max }}"
                                           pattern="(-|\d+)"
                                           title="Voer een getal of '-' in"
                                           class="w-24 border rounded px-2 py-1 text-center"
                                           required>
                                    <button class="px-3 py-1 bg-blue-600 text-white rounded">
                                        Opslaan&nbsp;max
                                    </button>
                                </form>
                            </td>
                            <td class="p-2">
                                <form method="POST"
                                      action="{{ route('conditions.destroy', $cond) }}">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1 bg-red-600 text-white rounded"
                                            onclick="return confirm('Weet je het zeker?')">
                                        Verwijder
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-4 text-center text-gray-500">Nog geen regels.</td></tr>
                    @endforelse
                    </tbody>
                </table>



                {{-- ───── 2. ONVERENIGBARE BUREN ───── --}}
                <h2 class="font-semibold text-lg mb-3">
                    Onverenigbare buren per categorie
                </h2>

                <table class="w-full mb-10 border">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 text-left">Categorie</th>
                        <th class="p-2 text-left">Mag&nbsp;niet&nbsp;naast</th>
                        <th class="p-2"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($conditions as $cond)
                        <tr class="border-t align-top">
                            <td class="p-2">{{ $cond->category }}</td>
                            <td class="p-2">
                                <form method="POST"
                                      action="{{ route('conditions.update', $cond) }}"
                                      class="space-y-2">
                                    @csrf @method('PUT')
                                    <div class="flex flex-wrap gap-4">
                                        @foreach(['Veiligheid','Recreatie','Milieukwaliteit','Voorzieningen','Mobiliteit'] as $cat)
                                            @continue($cat === $cond->category)
                                            <label class="inline-flex items-center gap-1">
                                                <input type="checkbox"
                                                       name="incompatible[]"
                                                       value="{{ $cat }}"
                                                       class="rounded border-gray-300"
                                                    @checked(in_array($cat, $cond->incompatible ?? []))>
                                                <span>{{ $cat }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <button class="px-3 py-1 bg-blue-600 text-white rounded">
                                        Opslaan&nbsp;buren
                                    </button>
                                </form>
                            </td>
                            <td></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-4 text-center text-gray-500">Nog geen regels.</td></tr>
                    @endforelse
                    </tbody>
                </table>



                {{-- ───── 3. NIEUWE CATEGORIE ───── --}}
                <h2 class="font-semibold text-lg mb-2">
                    Nieuwe categorie toevoegen
                </h2>

                <form method="POST"
                      action="{{ route('conditions.store') }}"
                      class="space-y-4">
                    @csrf
                    <div class="flex gap-4 flex-wrap">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm text-gray-700 mb-1">Categorie</label>
                            <select name="category"
                                    class="w-full border rounded px-3 py-2"
                                    required>
                                @foreach(['Veiligheid','Recreatie','Milieukwaliteit','Voorzieningen','Mobiliteit'] as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Max&nbsp;(– = ∞)</label>
                            <input type="text"
                                   name="max"
                                   value="-"
                                   pattern="(-|\d+)"
                                   title="Voer een getal of '-' in"
                                   class="w-24 border rounded px-3 py-2 text-center"
                                   required>
                        </div>

                        <div class="flex-1 min-w-[250px]">
                            <label class="block text-sm text-gray-700 mb-1">
                                Mag&nbsp;niet&nbsp;naast <span class="text-xs italic">(meerdere)</span>
                            </label>
                            <div class="flex flex-wrap gap-4">
                                @foreach(['Veiligheid','Recreatie','Milieukwaliteit','Voorzieningen','Mobiliteit'] as $cat)
                                    <label class="inline-flex items-center gap-1">
                                        <input type="checkbox"
                                               name="incompatible[]"
                                               value="{{ $cat }}"
                                               class="rounded border-gray-300">
                                        <span>{{ $cat }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <button class="px-4 py-2 bg-green-600 text-white rounded">
                        Opslaan
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
