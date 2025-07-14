@props([
    'title' => '',
    'value' => '',
    'color' => 'orangeone',
])

<div class="bg-white border-l-8 border-{{ $color }} rounded-xl shadow px-6 py-4">
    <p class="text-sm text-gray-500 font-varela mb-1">{{ $title }}</p>
    <p class="text-2xl font-bold text-{{ $color }} font-raleway">{{ $value }}</p>
</div>

