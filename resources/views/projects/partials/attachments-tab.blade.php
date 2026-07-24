<div x-show="activeTab === 'attachments'" x-cloak>
    <div class="space-y-6">
        @can('attachments.upload')
            <section class="rounded-3xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                <form
                    method="POST"
                    action="{{ route(
                        'projects.attachments.store',
                        $project
                    ) }}"
                    enctype="multipart/form-data"
                    class="flex flex-col gap-4 lg:flex-row lg:items-end"
                >
                    @csrf

                    <label class="block flex-1">
                        <span class="mb-2 block text-sm font-bold text-indigo-950">
                            Upload Project Attachments
                        </span>

                        <input
                            type="file"
                            name="attachments[]"
                            multiple
                            required
                            class="block w-full rounded-2xl border border-dashed border-indigo-300 bg-white p-4 text-sm"
                        >
                    </label>

                    <button class="min-h-12 rounded-2xl bg-indigo-600 px-6 text-sm font-bold text-white">
                        Upload Files
                    </button>
                </form>
            </section>
        @endcan

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($attachments as $file)
                @continue(
                    !$file->isAccessibleBy(
                        auth()->user()
                    )
                )

                <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-xl">
                            📎
                        </div>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                            {{ str($file->category)
                                ->replace('_', ' ')
                                ->title() }}
                        </span>
                    </div>

                    <h3 class="mt-4 break-words font-black text-slate-950">
                        {{ $file->original_name }}
                    </h3>

                    <p class="mt-2 text-xs text-slate-500">
                        {{ $file->mime_type ?: 'Unknown file type' }}
                        ·
                        {{ $file->formatted_size }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Uploaded by
                        {{ $file->uploadedBy?->name ?? 'Unknown' }}
                        on
                        {{ $file->created_at->format('d M Y') }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Downloads:
                        {{ $file->download_count }}
                    </p>

                    <a
                        href="{{ $file->secure_download_url }}"
                        class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-2xl bg-slate-950 px-4 text-sm font-bold text-white"
                    >
                        Download Attachment
                    </a>

                    @if (
                        in_array(
                            $file->category,
                            ['general', 'note', 'work_log'],
                            true
                        ) &&
                        (
                            auth()->user()->can(
                                'attachments.delete'
                            ) ||
                            $file->uploaded_by ===
                                auth()->id()
                        )
                    )
                        <form
                            method="POST"
                            action="{{ route(
                                'projects.attachments.destroy',
                                [$project, $file]
                            ) }}"
                            class="mt-3"
                            onsubmit="return confirm('Delete this attachment?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button class="w-full text-sm font-bold text-red-600">
                                Delete Attachment
                            </button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-3 rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <p class="font-bold text-slate-900">
                        No project attachments available
                    </p>
                </div>
            @endforelse
        </section>

        {{ $attachments->appends([
            'tab' => 'attachments'
        ])->links() }}
    </div>
</div>