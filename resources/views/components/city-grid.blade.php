 <div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
 
 <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Metropolis Grid</h2>

<table class="table-auto border-collapse border border-gray-300 w-full text-center">
    <tbody>
        @foreach($slots->chunk(3) as $row)
        <tr>
            @foreach($row as $slot)
            <td class="border border-gray-300 p-4 w-[200px] h-[150px] bg-gray-100 align-middle text-center">
                <div
                    class="city-slot flex flex-col items-center justify-center h-full"
                    data-slot-id="{{ $slot->id }}">
                    <div class="text-sm text-gray-500 mb-1">position: {{ $slot->index }}</div>

                    @if($slot->module_id != null && $slot->module && $slot->module->image_path)
                    <div class="relative flex flex-col items-center">
                        <img src="{{ asset('storage/' . $slot->module->image_path) }}"
                            alt="{{ $slot->module->name }}"
                            class="w-[60px] pointer-events-none">

                        <span class="text-xs text-gray-700">{{ $slot->module->name }}</span>

                        <form method="POST" action="{{ route('slots.removeModule', $slot->id) }}" class="absolute top-0 right-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="bg-red-500 text-white rounded-full w-5 h-5 text-xs leading-none">
                                ×
                            </button>
                        </form>
                    </div>
                    @else
                    <span class="text-xs text-gray-400">Empty</span>
                    @endif

                </div>
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

    </div>
</div>