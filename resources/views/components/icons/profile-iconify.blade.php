<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <g stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
        <circle cx="12" cy="8" r="3" stroke-dasharray="19" stroke-dashoffset="19">
            <animate fill="freeze" attributeName="stroke-dashoffset" dur="0.3s" to="0" />
        </circle>
        <path stroke-dasharray="22" stroke-dashoffset="22" d="M5 19c0-3 3-5 7-5s7 2 7 5">
            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.3s" dur="0.35s" to="0" />
        </path>
    </g>
</svg>
