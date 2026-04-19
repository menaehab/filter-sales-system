@props([
    'title' => 'Print',
    'orientation' => 'portrait',
])

<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            @page {
                size: A4 {{ $orientation === 'landscape' ? 'landscape' : 'portrait' }};
                margin: 0.5cm;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Arial', sans-serif;
            background: white;
            color: black;
        }
    </style>
</head>

<body class="bg-white text-black">
    {{ $slot }}

    <script>
        // Auto print on load (optional)
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto') === '1') {
                setTimeout(() => window.print(), 500);
            }
        });
    </script>
</body>

</html>
