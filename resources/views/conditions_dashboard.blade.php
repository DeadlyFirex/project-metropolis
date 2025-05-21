<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">Conditions&nbsp;Dashboard</h1>
    </x-slot>

    <style>[x-cloak]{display:none!important;}</style>

    @php
        $catIds  = $conditions->pluck('id', 'category');
        $allCats = ['Veiligheid','Recreatie','Milieukwaliteit','Voorzieningen','Mobiliteit'];
        $errorMax = $errors->has('max');
        $errorInc = !$errorMax && $errors->any();
        $oldCat   = old('category', $allCats[0]);
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if(session('status'))
                    <p class="mb-4 text-green-600">{{ session('status') }}</p>
                @endif

                @if($errors->any())
                    <div class="mb-4 text-red-600">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ───── 1. Overzicht ───── --}}
                <h2 class="font-semibold text-lg mb-3">Regels per categorie</h2>

                <table class="w-full mb-10 border">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 text-left">Categorie</th>
                        <th class="p-2 text-left">Max&nbsp;(1‒12)</th>
                        <th class="p-2 text-left">Mag&nbsp;niet&nbsp;naast</th>
                        <th class="p-2 text-left">Acties</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($conditions as $cond)
                        @php
                            $inc = is_string($cond->incompatible)
                                ? (json_decode($cond->incompatible, true) ?: [])
                                : ($cond->incompatible ?? []);
                        @endphp
                        <tr class="border-t align-top">
                            <td class="p-2">{{ $cond->category }}</td>
                            <td class="p-2">{{ $cond->max }}</td>
                            <td class="p-2">{{ $inc ? implode(', ', $inc) : '—' }}</td>
                            <td class="p-2">
                                <form method="POST" action="{{ route('conditions.destroy', $cond) }}" class="space-y-1">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Weet je het zeker?')" class="px-3 py-1 bg-red-600 text-white rounded w-full text-sm">Verwijder</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-4 text-center text-gray-500">Nog geen regels.</td></tr>
                    @endforelse
                    </tbody>
                </table>

                {{-- ───── 2. Pop-ups ───── --}}
                <h2 class="font-semibold text-lg mb-2">Regel toevoegen of bijwerken</h2>

                <div x-data="{
                        selected: @js($oldCat),
                        openMax: @json($errorMax),
                        openInc: @json($errorInc),
                        cats: @js($allCats),
                        ids:  @js($catIds),
                    }" class="space-y-4">

                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Categorie</label>
                            <select x-model="selected" class="border rounded px-3 py-2">
                                <template x-for="cat in cats" :key="cat">
                                    <option :value="cat" x-text="cat"></option>
                                </template>
                            </select>
                        </div>
                        <button type="button" @click="openMax=true" class="px-4 py-2 bg-blue-600 text-white rounded h-10">Wijzig&nbsp;max</button>
                        <button type="button" @click="openInc=true" class="px-4 py-2 bg-blue-600 text-white rounded h-10">Wijzig&nbsp;mag&nbsp;niet&nbsp;naast</button>
                    </div>

                    {{-- Modal Max --}}
                    <div x-show="openMax" x-cloak class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-4">
                        <div @click.away="openMax=false" class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6 space-y-4">
                            <h3 class="text-lg font-semibold">Max voor <span x-text="selected"></span></h3>
                            <form method="POST" :action="ids[selected] ? `/conditions/${ids[selected]}` : '/conditions'" class="space-y-4">
                                @csrf
                                <template x-if="ids[selected]">
                                    <input type="hidden" name="_method" value="PUT">
                                </template>
                                <input type="hidden" name="category" :value="selected">
                                <input type="number" name="max" min="1" max="12" placeholder="1-12" class="w-full border rounded px-3 py-2 text-center" required value="{{ old('max') }}">
                                @error('max')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="openMax=false" class="px-4 py-2 bg-gray-200 rounded">Annuleren</button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Opslaan</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Modal Incompatible --}}
                    <div x-show="openInc" x-cloak class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-4 overflow-y-auto">
                        <div @click.away="openInc=false" class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 space-y-4">
                            <h3 class="text-lg font-semibold">Mag niet naast – <span x-text="selected"></span></h3>
                            <form method="POST" :action="ids[selected] ? `/conditions/${ids[selected]}` : '/conditions'" class="space-y-4">
                                @csrf
                                <template x-if="ids[selected]">
                                    <input type="hidden" name="_method" value="PUT">
                                </template>
                                <template x-if="!ids[selected]">
                                    <!-- nieuwe categorie zonder max-veld: standaard op 12 -->
                                    <input type="hidden" name="max" value="12">
                                </template>
                                <input type="hidden" name="category" :value="selected">
                                <div class="flex flex-wrap gap-4">
                                    <template x-for="cat in cats" :key="cat">
                                        <label class="inline-flex items-center gap-1" x-show="cat !== selected">
                                            <input type="checkbox" name="incompatible[]" :value="cat" class="rounded border-gray-300" :checked="@json(old('incompatible', [])) .includes(cat)">
                                            <span x-text="cat"></span>
                                        </label>
                                    </template>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="openInc=false" class="px-4 py-2 bg-gray-200 rounded">Annuleren</button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Opslaan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
