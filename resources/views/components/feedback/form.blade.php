<section class="bg-white dark:bg-gray-900 px-6 py-6 rounded-2xl shadow w-full max-w-3xl mx-auto">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Laat feedback achter</h3>

    <form method="POST" action="{{ route('feedback.store') }}">
        @csrf
        <textarea name="content" required class="w-full border rounded p-3 dark:bg-gray-700 dark:text-white"
                  rows="4" placeholder="Wat wil je delen?"></textarea>
        <button type="submit" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Verstuur
        </button>
    </form>
</section>
