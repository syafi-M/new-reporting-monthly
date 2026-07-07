<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SILAB - Login') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="/js/jquery3.7.1-min.js"></script>
        <script src="/js/Notify.js"></script>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
            
            body {
                font-family: 'Inter', sans-serif;
            }
            
            .illustration {
                background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%);
            }

            .field-required-marker {
                margin-left: 0.25rem;
                color: #dc2626;
                font-weight: 700;
            }

            .field-optional-marker {
                margin-left: 0.35rem;
                color: #0284c7;
                font-style: italic;
                font-weight: 500;
                font-size: 0.85em;
            }

            [x-cloak] {
                display: none !important;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div id="notifications"></div>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-white">

            <div class="w-screen bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        @include('components.session-toast')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const labels = document.querySelectorAll('label');

                labels.forEach(function(label) {
                    if (label.querySelector('.field-required-marker, .field-optional-marker')) {
                        return;
                    }

                    let field = null;
                    const fieldId = label.getAttribute('for');

                    if (fieldId) {
                        field = document.getElementById(fieldId);
                    }

                    if (!field) {
                        field = label.closest('.form-control')?.querySelector('input, select, textarea')
                            || label.parentElement?.querySelector('input, select, textarea');
                    }

                    if (!field) {
                        return;
                    }

                    const marker = document.createElement('span');

                    if (field.required) {
                        marker.className = 'field-required-marker';
                        marker.textContent = '*';
                    } else {
                        marker.className = 'field-optional-marker';
                        marker.textContent = '(opsional)';
                    }

                    label.appendChild(marker);
                });
            });
        </script>
    </body>
</html>
