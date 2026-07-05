<div class="form-row">
    <label>{{ $label ?? 'Input Label' }} @if ($required ?? false)<span>*</span>@endif </label>
    <textarea @isset($rows) rows="{{ $rows }}" @endisset name="{{ $name ?? '' }}" @isset($cols)
    cols="{{ $cols }}" @endisset name="{{ $name ?? '' }}" placeholder="{{ $placeholder ?? '' }}"
    @foreach ($attr ?? [] as $attribute => $key)
        {{ $attribute }}="{{ $key }}"
        @endforeach>{!! $value ?? '' !!}</textarea>
</div>
