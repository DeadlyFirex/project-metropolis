<table class="table-auto border-collapse border border-gray-300 w-full text-center">
    <tbody>
        @foreach($slots->chunk(3) as $row)
            <tr>
                @foreach($row as $slot)
                    <td id="slot-position-{{ $slot->index }}" class="border border-gray-300 p-4 w-[200px]">
                            position: {{ $slot->index }} <br>
                            @if( $slot->module_id  != null)
                            <img src="{{ asset($slot->module->image_path) }}" alt="{{ $slot->module->name }}">
                            @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
