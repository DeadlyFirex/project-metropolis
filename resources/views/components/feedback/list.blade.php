<section class="bg-white dark:bg-gray-900 px-6 py-6 rounded-2xl shadow w-full max-w-3xl mx-auto mt-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Feedbackoverzicht</h3>

    @foreach($feedback as $item)
        <div class="mb-4 p-4 border rounded dark:border-gray-700 dark:text-white">
            @if(request('edit') == $item->id)
                <form method="POST" action="{{ route('feedback.update', $item) }}">
                    @csrf
                    @method('PATCH')

                    <textarea name="content" required rows="4"
                              class="w-full border rounded p-3 text-sm dark:bg-gray-700 dark:text-white">{{ old('content', $item->content) }}</textarea>

                    <div class="mt-2 flex justify-between items-center">
                        <a href="{{ route('feedback.index') }}" class="text-sm text-gray-500 hover:underline">Annuleren</a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm">
                            Opslaan
                        </button>
                    </div>
                </form>
            @else
                <p class="mb-2">{{ $item->content }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Geplaatst op {{ $item->created_at->format('d-m-Y H:i') }}
                </p>

                <form action="{{ route('feedback.destroy', $item) }}" method="POST" class="mt-2 flex gap-4 items-center">
                    @csrf
                    @method('DELETE')

                    <a href="{{ route('feedback.index', ['edit' => $item->id]) }}"
                       class="text-blue-600 text-sm hover:underline">Bewerken</a>

                    <button type="submit" class="text-red-600 text-sm hover:underline">Verwijderen</button>
                </form>
            @endif
        </div>
    @endforeach
</section>
