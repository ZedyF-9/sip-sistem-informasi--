@props(['name' => 'document'])

<svg {{ $attributes->class(['dashboard-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('school')
            <path d="m3 9 9-5 9 5-9 5-9-5Z"/><path d="M6 11v6c4 3 8 3 12 0v-6M21 9v7"/>
            @break
        @case('grid')
            <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
            @break
        @case('send')
            <path d="m21 3-7 18-4-7-7-4 18-7ZM10 14 21 3"/>
            @break
        @case('inbox')
            <path d="M4 9 6 4h12l2 5v11H4V9Z"/><path d="M4 13h5l1 3h4l1-3h5M8 8h8"/>
            @break
        @case('chart')
            <path d="M4 3v18h17M8 16v-5m5 5V7m5 9V4"/>
            @break
        @case('user')
            <circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
            @break
        @case('check')
            <path d="m5 12 4 4L19 6"/>
            @break
        @case('arrow')
            <path d="M4 12h16m-6-6 6 6-6 6"/>
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16"/>
            @break
        @case('close')
            <path d="m6 6 12 12M6 18 18 6"/>
            @break
        @case('logout')
            <path d="M9 4H4v16h5m6-12 4 4-4 4m-6-4h10"/>
            @break
        @case('globe')
            <circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/>
            @break
        @default
            <path d="M14 3H5v18h14V8l-5-5Z"/><path d="M14 3v5h5M9 12h6m-6 4h6"/>
    @endswitch
</svg>
