<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-950">
                Client Information
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Basic client and company details.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <x-form.input
                label="Contact Person"
                name="name"
                :value="$client->name"
                required
            />

            <x-form.input
                label="Company Name"
                name="company_name"
                :value="$client->company_name"
            />

            <x-form.input
                label="Email Address"
                name="email"
                type="email"
                :value="$client->email"
            />

            <x-form.input
                label="Phone Number"
                name="phone"
                :value="$client->phone"
                placeholder="+91 98765 43210"
            />

            <x-form.input
                label="WhatsApp Number"
                name="whatsapp"
                :value="$client->whatsapp"
                placeholder="+91 98765 43210"
            />

            <x-form.input
                label="GST Number"
                name="gst_number"
                :value="$client->gst_number"
            />

            <x-form.select
                label="Client Type"
                name="client_type"
                required
            >
                @foreach ([
                    'individual' => 'Individual',
                    'business' => 'Business',
                    'organisation' => 'Organisation / NGO',
                    'agency' => 'Agency',
                    'other' => 'Other',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(
                            old(
                                'client_type',
                                $client->client_type ?: 'business'
                            ) === $value
                        )
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </x-form.select>

            <x-form.select
                label="Client Status"
                name="status"
                required
            >
                @foreach ($statuses as $status)
                    <option
                        value="{{ $status->value }}"
                        @selected(
                            old(
                                'status',
                                $client->status?->value
                                    ?? \App\Enums\ClientStatus::Active->value
                            ) === $status->value
                        )
                    >
                        {{ $status->label() }}
                    </option>
                @endforeach
            </x-form.select>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-950">
                Address
            </h2>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <div class="md:col-span-3">
                <x-form.textarea
                    label="Full Address"
                    name="address"
                    :value="$client->address"
                    rows="3"
                />
            </div>

            <x-form.input
                label="City"
                name="city"
                :value="$client->city"
            />

            <x-form.input
                label="State"
                name="state"
                :value="$client->state"
            />

            <x-form.input
                label="PIN Code"
                name="pincode"
                :value="$client->pincode"
            />
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <x-form.textarea
            label="Internal Client Notes"
            name="notes"
            :value="$client->notes"
            rows="5"
            placeholder="Add important internal information about this client."
        />
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('clients.index') }}"
            class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
        >
            Cancel
        </a>

        <button
            type="submit"
            class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-bold text-white shadow-lg shadow-slate-300 transition hover:bg-indigo-600"
        >
            {{ $submitLabel }}
        </button>
    </div>
</div>