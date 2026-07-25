<nav class="flex gap-2 overflow-x-auto rounded-3xl border border-slate-200 bg-white p-2 shadow-sm">
    @foreach ([
        'reports.index' => 'Executive',
        'reports.projects' => 'Projects',
        'reports.team' => 'Team',
        'reports.collections' => 'Collections',
        'reports.profitability' => 'Profitability',
        'reports.ticket-sla' => 'Ticket SLA',
    ] as $routeName => $label)
        @if (
            $routeName === 'reports.team'
            && !auth()->user()->can(
                'reports.view-team'
            )
        )
            @continue
        @endif

        @if (
            in_array(
                $routeName,
                [
                    'reports.collections',
                    'reports.profitability',
                ],
                true
            )
            && !auth()->user()->can(
                'reports.view-financial'
            )
        )
            @continue
        @endif

        @if (
            $routeName ===
                'reports.ticket-sla'
            && !auth()->user()->can(
                'reports.view-ticket-sla'
            )
        )
            @continue
        @endif

        <a
            href="{{ route(
                $routeName,
                $filters->toArray()
            ) }}"
            class="whitespace-nowrap rounded-2xl px-4 py-3 text-sm font-bold transition
                {{
                    request()->routeIs(
                        $routeName
                    )
                        ? 'bg-slate-950 text-white'
                        : 'text-slate-600 hover:bg-slate-100'
                }}"
        >
            {{ $label }}
        </a>
    @endforeach
</nav>