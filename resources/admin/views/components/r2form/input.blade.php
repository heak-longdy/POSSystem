<div class="form-row">
    <label>{{ $label ?? 'Input Label' }} @if ($required ?? false)<span>*</span>@endif </label>
    <input type="{{ $type ?? 'text' }}" name="{{ $name ?? '' }}" value="{{ $value ?? '' }}"
        placeholder="{{ $placeholder ?? '' }}" @foreach ($attr ?? [] as $attribute => $key)
    {{ $attribute }}="{{ $key }}"
    @endforeach
    >
</div>
