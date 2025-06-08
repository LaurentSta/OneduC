@php
    $activeRoute = request()->route()->getName();
@endphp

<div class=" p-4 text-sm space-y-2">
    <h2 class="text-md font-semibold text-[#004461] mb-2">Informations légales</h2>
    <ul class="space-y-2">
        <li>
            <a href="{{ route('mentions-legales') }}"
               class="{{ $activeRoute == 'mentions-legales' ? 'text-orangeone font-bold' : 'text-gray-700 hover:text-orangeone' }}">
               🏛 Mentions légales
            </a>
        </li>
        <li>
            <a href="{{ route('conditions-utilisation') }}"
               class="{{ $activeRoute == 'conditions-utilisation' ? 'text-orangeone font-bold' : 'text-gray-700 hover:text-orangeone' }}">
               📜 Conditions d’utilisation
            </a>
        </li>
        <li>
            <a href="{{ route('confidentialite') }}"
               class="{{ $activeRoute == 'confidentialite' ? 'text-orangeone font-bold' : 'text-gray-700 hover:text-orangeone' }}">
               🔐 Confidentialité
            </a>
        </li>
        <li>
            <a href="{{ route('cookies') }}"
               class="{{ $activeRoute == 'cookies' ? 'text-orangeone font-bold' : 'text-gray-700 hover:text-orangeone' }}">
               🍪 Cookies
            </a>
        </li>
    </ul>
</div>
