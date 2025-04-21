<table class="table-auto border-collapse border border-gray-300 w-full text-center">
    <tbody>
        @foreach($slots->chunk(3) as $row)
            <tr>
                @foreach($row as $slot)
                    <td class="border border-gray-300 p-4 w-[200px] h-[150px] bg-gray-100 align-middle text-center">
                        <div
                            class="city-slot flex flex-col items-center justify-center h-full"
                            data-slot-id="{{ $slot->id }}"
                        >
                            <div class="text-sm text-gray-500 mb-1">position: {{ $slot->index }}</div>

                            @if($slot->module_id != null && $slot->module && $slot->module->image_path)
                                <img src="{{ asset($slot->module->image_path) }}" alt="{{ $slot->module->name }}" class="w-[60px] pointer-events-none">
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
