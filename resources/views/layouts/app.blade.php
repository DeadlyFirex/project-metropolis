<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <script src="/recources/js/font-size-control.js"></script> --}}
    <script>
        function applyFontScale(multiplier) {
            const elements = document.querySelectorAll('body *');

            elements.forEach((el) => {
                if (el.children.length === 0 && el.textContent.trim() !== '') {
                    let originalSize = el.getAttribute('data-original-font-size');

                    if (!originalSize) {
                        const computedSize = window.getComputedStyle(el).fontSize;
                        el.setAttribute('data-original-font-size', computedSize);
                        originalSize = computedSize;
                    }

                    const baseSize = parseFloat(originalSize);
                    const newSize = baseSize * multiplier;

                    el.style.fontSize = newSize + 'px';
                }
            });

            // ⬇️ Nieuw: zet attribuut op body
            document.body.setAttribute('data-font-scale', multiplier);
            localStorage.setItem('fontScale', multiplier);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const button = document.getElementById('increaseFontBtn');
            if (!button) return;

            const normalScale = 1;
            const increasedScale = 1.7;
            let currentScale = parseFloat(localStorage.getItem('fontScale')) || normalScale;

            // Apply scale to elements using stored or default multiplier
            applyFontScale(currentScale);

            // Update button label
            button.textContent = currentScale > normalScale ? 'Tekstgrootte Verkleinen' : 'Tekstgrootte Vergroten';

            button.addEventListener('click', () => {
                currentScale = currentScale === normalScale ? increasedScale : normalScale;
                button.textContent = currentScale === normalScale ? 'Tekstgrootte Vergroten' :
                    'Tekstgrootte Verkleinen';

                applyFontScale(currentScale);
            });
        });
    </script>

</head>

<body class="font-sans antialiased overflow-x-hidden">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>
