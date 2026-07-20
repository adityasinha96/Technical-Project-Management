@props([
    'label',
    'name',
    'required' => false,
])

<label class="block">
    <span class="mb-2 block text-sm font-semibold text-slate-700">
        {{ $label }}

        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </span>

    <select
        name="{{ $name }}"
        @required($required)
        {{ $attributes->merge([
            'class' =>
                'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100'
        ]) }}
    >
        {{ $slot }}
    </select>

    @error($name)
        <span class="mt-1.5 block text-xs font-medium text-red-600">
            {{ $message }}
        </span>
    @enderror
</label>