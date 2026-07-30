<div
    x-show="activeTab === 'client-portal'"
    x-cloak
    class="space-y-6"
>
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-black text-slate-950">
            Client Portal Settings
        </h2>

        <form
            method="POST"
            action="{{ route(
                'projects.client-portal.update',
                $project
            ) }}"
            class="mt-6 space-y-5"
        >
            @csrf
            @method('PUT')

            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4">
                <input
                    type="checkbox"
                    name="client_portal_enabled"
                    value="1"
                    @checked(
                        $project->client_portal_enabled
                    )
                >

                <span class="font-bold text-slate-800">
                    Enable client portal for this project
                </span>
            </label>

            <label class="block">
                <span class="mb-2 block text-sm font-bold text-slate-700">
                    Client-Facing Project Summary
                </span>

                <textarea
                    name="client_portal_summary"
                    rows="5"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3"
                >{{ $project->client_portal_summary }}</textarea>
            </label>

            <button class="min-h-11 rounded-2xl bg-slate-950 px-6 text-sm font-bold text-white">
                Save Portal Settings
            </button>
        </form>
    </section>

    @if ($project->client_portal_enabled)
        <section class="rounded-3xl border border-indigo-200 bg-indigo-50 p-6">
            <h2 class="font-black text-indigo-950">
                Client Portal Users
            </h2>

            <p class="mt-2 text-sm text-indigo-700">
                Invite authorised client contacts and manage project-specific access.
            </p>

            <a
                href="{{ route(
                    'projects.client-portal.users',
                    $project
                ) }}"
                class="mt-5 inline-flex min-h-11 items-center rounded-2xl bg-indigo-600 px-5 text-sm font-bold text-white"
            >
                Manage Client Users
            </a>
        </section>
    @endif
</div>