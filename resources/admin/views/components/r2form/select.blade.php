<div class="form-row">
    <label>{{ $label ?? 'Select Label' }} @if ($required ?? false)<span>*</span>@endif </label>
    <select name="{{ $name ?? nul }}" @foreach ($attr ?? [] as $attribute => $key)
        {{ $attribute }}="{{ $key }}"
        @endforeach>
        {{ $slot }}
        @foreach (json_decode($options ?? [], true) as $option)
            <option value="{{ $option[$valueField] }}" @if ($option[$valueField] == $selected) selected @endif">
                {{ $option[$nameField] ?? '' }}</option>
        @endforeach
    </select>
</div>
