@unless (request()->routeIs('ajuda.index'))
    <a
        href="{{ route('ajuda.index') }}"
        class="btn-ajuda-flutuante"
        title="Ajuda e perguntas frequentes"
        aria-label="Ajuda e perguntas frequentes"
    >
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true">
            <path
                d="M9.1 8.5a3.15 3.15 0 0 1 6.05 1.15c0 1.95-2.95 2.75-3.15 4.45"
                stroke="currentColor"
                stroke-width="2.75"
                stroke-linecap="round"
            />
            <circle cx="12" cy="18.2" r="1.55" fill="currentColor" />
        </svg>
    </a>
@endunless
