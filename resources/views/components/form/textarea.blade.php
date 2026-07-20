@props([
    'label',
    'name',
    'value' => null,
    'required' => false,
    'rows' => 4,
])

<label class="block">
    <span class="mb-2 block text-sm font-semibold text-slate-700">
        {{ $label }}

        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </span>

    <textarea
        name="{{ $name }}"
        rows="{{ $rows }}"
        @required($required)
        {{ $attributes->merge([
            'class' =>
                'w-full resize-y rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100'
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <span class="mt-1.5 block text-xs font-medium text-red-600">
            {{ $message }}
        </span>
    @enderror
</label>