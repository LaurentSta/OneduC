<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <g stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
        <path stroke-dasharray="10" stroke-dashoffset="10" d="M4 6h6">
            <animate fill="freeze" attributeName="stroke-dashoffset" dur="0.25s" to="0" />
        </path>
        <path stroke-dasharray="6" stroke-dashoffset="6" d="M18 6h2">
            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.1s" dur="0.2s" to="0" />
        </path>
        <circle cx="14" cy="6" r="2" stroke-dasharray="13" stroke-dashoffset="13">
            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.2s" dur="0.2s" to="0" />
        </circle>

        <path stroke-dasharray="6" stroke-dashoffset="6" d="M4 12h2">
            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.25s" dur="0.2s" to="0" />
        </path>
        <path stroke-dasharray="10" stroke-dashoffset="10" d="M12 12h8">
            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.35s" dur="0.25s" to="0" />
        </path>
        <circle cx="8" cy="12" r="2" stroke-dasharray="13" stroke-dashoffset="13">
            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.45s" dur="0.2s" to="0" />
        </circle>

        <path stroke-dasharray="12" stroke-dashoffset="12" d="M4 18h8">
            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.5s" dur="0.25s" to="0" />
        </path>
        <path stroke-dasharray="4" stroke-dashoffset="4" d="M18 18h2">
            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.6s" dur="0.15s" to="0" />
        </path>
        <circle cx="16" cy="18" r="2" stroke-dasharray="13" stroke-dashoffset="13">
            <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.7s" dur="0.2s" to="0" />
        </circle>
    </g>
</svg>
