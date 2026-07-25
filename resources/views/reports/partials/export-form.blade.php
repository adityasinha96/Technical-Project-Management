@can('reports.export')
    <form
        method="POST"
        action="{{ route('reports.export') }}"
    >
        @csrf

        <input
            type="hidden"
            name="report_type"
            value="{{ $reportType->value }}"
        >

        @foreach (
            $filters->toArray()
            as $key => $value
        )
            @if ($value !== null)
                <input
                    type="hidden"
                    name="{{ $key }}"
                    value="{{ $value }}"
                >
            @endif
        @endforeach

        <button class="inline-flex min-h-11 items-center rounded-2xl bg-emerald-600 px-5 text-sm font-bold text-white">
            Export CSV
        </button>
    </form>
@endcan