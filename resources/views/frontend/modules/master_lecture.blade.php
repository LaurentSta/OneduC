<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Lecture SCORM')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Plugin collapse AlpineJS -->
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.13.5/dist/cdn.min.js" defer></script>

    <!-- AlpineJS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js" defer></script>


    <link href="https://vjs.zencdn.net/8.9.0/video-js.css" rel="stylesheet" />
    <script src="https://vjs.zencdn.net/8.9.0/video.min.js"></script>

</head>
<body class="bg-white text-gray-900">

    @if (!isset($hideHeader))
        @include('frontend.modules.body.header')
    @endif

    <div class="flex bg-gray-50 min-h-screen">
    @if(isset($module))
        @include('frontend.modules.body.sidebar')
    @endif

    <div class="flex-1 px-4 lg:px-8 py-8">
        @yield('content')
    </div>
</div>

</body>
</html>
