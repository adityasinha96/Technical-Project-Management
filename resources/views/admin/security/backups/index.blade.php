@extends('layouts.admin')

@section('title', 'Backup Management')
@section('page-title', 'Backup Management')

@section('content')
    <div class="space-y-6">
        <section class="rounded-3xl bg-slate-950 p-7 text-white">
            <h1 class="text-3xl font-black">
                System Backup Management
            </h1>

            <p class="mt-2 text-sm text-slate-300">
                Create, verify, download and review secure system backups.
            </p>
        </section>

        @can('backups.run')
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-950">
                    Create Manual Backup
                </h2>

                <form
                    method="POST"
                    action="{{ route(
                        'security.backups.store'
                    ) }}"
                    class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end"
                >
                    @csrf

                    <label class="block flex-1">
                        <span class="mb-2 block text-sm font-bold text-slate-700">
                            Backup Type
                        </span>

                        <select
                            name="backup_type"
                            required
                            class="min-h-12 w-full rounded-2xl border border-slate-200 px-4"
                        >
                            @foreach ($backupTypes as $type)
                                <option value="{{ $type->value }}">
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <button class="min-h-12 rounded-2xl bg-indigo-600 px-6 text-sm font-bold text-white">
                        Create Backup
                    </button>
                </form>
            </section>
        @endcan

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left">
                                Backup
                            </th>

                            <th class="px-5 py-4 text-left">
                                Type
                            </th>

                            <th class="px-5 py-4 text-left">
                                Status
                            </th>

                            <th class="px-5 py-4 text-left">
                                Verification
                            </th>

                            <th class="px-5 py-4 text-right">
                                Size
                            </th>

                            <th class="px-5 py-4 text-left">
                                Completed
                            </th>

                            <th class="px-5 py-4 text-right">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($backups as $backup)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-black text-slate-950">
                                        {{ $backup->filename
                                            ?? $backup->backup_uuid }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $backup->trigger->label() }}
                                        ·
                                        {{ $backup->requestedBy?->name
                                            ?? 'System' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    {{ $backup->backup_type->label() }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $backup->status->badgeClasses() }}">
                                        {{ $backup->status->label() }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    {{ $backup->verification_status->label() }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    @if ($backup->size_bytes)
                                        {{ number_format(
                                            $backup->size_bytes
                                            / 1024
                                            / 1024,
                                            2
                                        ) }}
                                        MB
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    {{ $backup->completed_at?->format(
                                        'd M Y, h:i A'
                                    ) ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        @if (
                                            $backup->status ===
                                                \App\Enums\BackupStatus::Completed
                                        )
                                            @can('backups.download')
                                                <a
                                                    href="{{ route(
                                                        'security.backups.download',
                                                        $backup
                                                    ) }}"
                                                    class="rounded-xl bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700"
                                                >
                                                    Download
                                                </a>
                                            @endcan
                                        @endif

                                        @can('backups.delete')
                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'security.backups.destroy',
                                                    $backup
                                                ) }}"
                                                onsubmit="return confirm(
                                                    'Delete this backup file permanently?'
                                                )"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button class="rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-700">
                                                    Delete
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="px-5 py-14 text-center text-slate-500"
                                >
                                    No system backups have been created.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $backups->links() }}
            </div>
        </section>
    </div>
@endsection