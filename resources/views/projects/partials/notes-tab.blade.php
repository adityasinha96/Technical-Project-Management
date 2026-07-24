<div x-show="activeTab === 'notes'" x-cloak>
    <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
        <section class="space-y-4">
            @forelse ($notes as $note)
                <article class="rounded-3xl border {{ $note->is_pinned ? 'border-amber-300 bg-amber-50/30' : 'border-slate-200 bg-white' }} p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap gap-2">
                                @if ($note->is_pinned)
                                    <span class="rounded-full bg-amber-500 px-3 py-1 text-xs font-bold text-white">
                                        📌 Pinned
                                    </span>
                                @endif

                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $note->note_type->badgeClasses() }}">
                                    {{ $note->note_type->label() }}
                                </span>

                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $note->visibility->badgeClasses() }}">
                                    {{ $note->visibility->label() }}
                                </span>
                            </div>

                            @if ($note->title)
                                <h2 class="mt-4 text-lg font-black text-slate-950">
                                    {{ $note->title }}
                                </h2>
                            @endif

                            <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">
                                {{ $note->content }}
                            </p>

                            @if ($note->fileLinks->isNotEmpty())
                                <div class="mt-5 flex flex-wrap gap-2">
                                    @foreach ($note->fileLinks as $link)
                                        @if ($link->file)
                                            <a
                                                href="{{ $link->file->secure_download_url }}"
                                                class="inline-flex items-center rounded-xl bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700"
                                            >
                                                📎 {{ $link->file->original_name }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <p class="mt-5 text-xs text-slate-500">
                                Added by
                                <strong>
                                    {{ $note->createdBy?->name ?? 'Unknown' }}
                                </strong>
                                on
                                {{ $note->created_at->format('d M Y, h:i A') }}

                                @if ($note->updated_at->gt($note->created_at))
                                    · Updated
                                    {{ $note->updated_at->diffForHumans() }}
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @can('notes.pin')
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'projects.notes.pin',
                                        [$project, $note]
                                    ) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <button class="rounded-xl border border-amber-200 bg-white px-3 py-2 text-xs font-bold text-amber-700">
                                        {{ $note->is_pinned
                                            ? 'Unpin'
                                            : 'Pin' }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    @if ($note->canBeManagedBy(auth()->user()))
                        <details class="mt-5 border-t border-slate-100 pt-5">
                            <summary class="cursor-pointer text-sm font-bold text-indigo-600">
                                Edit or Delete Note
                            </summary>

                            <form
                                method="POST"
                                action="{{ route(
                                    'projects.notes.update',
                                    [$project, $note]
                                ) }}"
                                enctype="multipart/form-data"
                                class="mt-5 space-y-4 rounded-2xl bg-slate-50 p-4"
                            >
                                @csrf
                                @method('PUT')

                                <x-form.input
                                    label="Title"
                                    name="title"
                                    :value="$note->title"
                                />

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <x-form.select
                                        label="Note Type"
                                        name="note_type"
                                        required
                                    >
                                        @foreach ($noteTypes as $type)
                                            <option
                                                value="{{ $type->value }}"
                                                @selected(
                                                    $note->note_type === $type
                                                )
                                            >
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </x-form.select>

                                    <x-form.select
                                        label="Visibility"
                                        name="visibility"
                                        required
                                    >
                                        @foreach ($noteVisibilities as $visibility)
                                            <option
                                                value="{{ $visibility->value }}"
                                                @selected(
                                                    $note->visibility ===
                                                    $visibility
                                                )
                                            >
                                                {{ $visibility->label() }}
                                            </option>
                                        @endforeach
                                    </x-form.select>
                                </div>

                                <x-form.textarea
                                    label="Note"
                                    name="content"
                                    :value="$note->content"
                                    rows="6"
                                    required
                                />

                                <input
                                    type="file"
                                    name="attachments[]"
                                    multiple
                                    class="block w-full rounded-2xl border border-dashed border-slate-300 bg-white p-4 text-sm"
                                >

                                <button class="min-h-11 rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white">
                                    Save Changes
                                </button>
                            </form>

                            @can('notes.delete')
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'projects.notes.destroy',
                                        [$project, $note]
                                    ) }}"
                                    class="mt-3"
                                    onsubmit="return confirm('Delete this project note?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button class="text-sm font-bold text-red-600">
                                        Delete Note
                                    </button>
                                </form>
                            @endcan
                        </details>
                    @endif
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <p class="font-bold text-slate-900">
                        No project notes available
                    </p>
                </div>
            @endforelse

            {{ $notes->appends([
                'tab' => 'notes'
            ])->links() }}
        </section>

        @can('notes.create')
            <aside class="h-fit rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-slate-950">
                    Add Project Note
                </h2>

                <form
                    method="POST"
                    action="{{ route(
                        'projects.notes.store',
                        $project
                    ) }}"
                    enctype="multipart/form-data"
                    class="mt-5 space-y-4"
                >
                    @csrf

                    <x-form.input
                        label="Note Title"
                        name="title"
                    />

                    <x-form.select
                        label="Note Type"
                        name="note_type"
                        required
                    >
                        @foreach ($noteTypes as $type)
                            <option value="{{ $type->value }}">
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-form.select
                        label="Visibility"
                        name="visibility"
                        required
                    >
                        @foreach ($noteVisibilities as $visibility)
                            @continue(
                                $visibility ===
                                    \App\Enums\ProjectNoteVisibility::Management &&
                                !auth()->user()->can(
                                    'notes.view-sensitive'
                                )
                            )

                            <option value="{{ $visibility->value }}">
                                {{ $visibility->label() }}
                            </option>
                        @endforeach
                    </x-form.select>

                    <x-form.textarea
                        label="Note Content"
                        name="content"
                        rows="7"
                        required
                    />

                    @can('notes.pin')
                        <label class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                            <input
                                type="checkbox"
                                name="is_pinned"
                                value="1"
                                class="h-5 w-5 rounded border-amber-300 text-amber-600"
                            >

                            <span class="text-sm font-bold text-amber-900">
                                Pin this information
                            </span>
                        </label>
                    @endcan

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-slate-700">
                            Attachments
                        </span>

                        <input
                            type="file"
                            name="attachments[]"
                            multiple
                            class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm"
                        >
                    </label>

                    <button class="min-h-12 w-full rounded-2xl bg-slate-950 px-5 text-sm font-bold text-white">
                        Save Note
                    </button>
                </form>
            </aside>
        @endcan
    </div>
</div>