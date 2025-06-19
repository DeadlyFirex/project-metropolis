<section class="max-w-3xl mx-auto mt-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Feedbackoverzicht</h3>

    @forelse($feedback as $item)
        <div class="border rounded p-4 mb-4 dark:border-gray-700 dark:text-white" data-id="{{ $item->id }}">
            <!-- Show view-mode by default -->
            <div class="view-mode">
                <p class="mb-2">{{ $item->content }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Geplaatst op {{ $item->created_at->format('d-m-Y H:i') }}
                    @if ($item->updated_at && $item->updated_at->gt($item->created_at))
                        <br><span class="text-[11px] text-gray-400 italic">Laatst bewerkt op {{ $item->updated_at->format('d-m-Y H:i') }}</span>
                    @endif
                </p>

                <form action="{{ route('feedback.destroy', $item) }}"
                      method="POST"
                      class="feedback-delete-form mt-2 flex gap-4 items-center"
                      data-id="{{ $item->id }}">
                    @csrf
                    @method('DELETE')

                    <button type="button"
                            data-edit-id="{{ $item->id }}"
                            class="text-blue-600 text-sm hover:underline edit-btn">
                        Bewerken
                    </button>

                    <button type="button" class="text-red-600 text-sm hover:underline delete-btn">Verwijderen</button>
                </form>
            </div>

            <!-- Hidden edit form -->
            <form method="POST"
                  action="{{ route('feedback.update', $item) }}"
                  class="feedback-edit-form hidden"
                  data-id="{{ $item->id }}">
                @csrf
                @method('PATCH')

                <textarea name="content" required rows="3"
                          class="w-full border rounded p-3 text-sm dark:bg-gray-700 dark:text-white">{{ old('content', $item->content) }}</textarea>

                <div class="mt-2 flex justify-between">
                    <button type="button" class="text-sm text-gray-500 hover:underline cancel-edit-btn">Annuleren</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm">
                        Opslaan
                    </button>
                </div>
            </form>
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400 italic">
            Er is nog geen feedback toegevoegd.
        </p>
    @endforelse
</section>
