<div class="form-row">
    <label>{{ $label ?? 'Photo Label' }} @if ($required ?? false)<span>*</span>@endif </label>
    <div class="form-select-photo">
        <div class="select-photo {!! $value ? 'active' : '' !!}">
            <div class="icon">
                <i data-feather="image"></i>
            </div>
            <div class="title">
                <span>{!! $placeholder ?? 'Click to choose a <b>Photo</b>.' !!}</span>
            </div>
        </div>
        <div class="image-view {!! $value ? 'active' : '' !!}">
            <img src="{!! $value ? $path : null !!}" alt="">
        </div>
        <input type="file" name="image" accept="image/*" id="selectThumb">
        <input type="hidden" name="tmp_file" value="{!! $value ?? '' !!}">
    </div>
</div>
