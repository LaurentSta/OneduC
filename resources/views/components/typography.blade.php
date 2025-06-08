@props([
    'variant' => 'paragraph', // titre, sous-titre, paragraph
    'size' => null,
])

@php
$classes = match($variant) {
    'titre' => 'font-raleway text-titre text-bleuone leading-tight mb-4',
    'sous-titre' => 'font-varela text-sous-titre text-orangeone leading-snug mb-3',
    'paragraph' => 'font-lisible text-lg text-gray-800 leading-loose mb-6',
    default => 'text-base',
};

if ($size) {
    $classes .= " text-[$size]";
}
@endphp

<p {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</p>
