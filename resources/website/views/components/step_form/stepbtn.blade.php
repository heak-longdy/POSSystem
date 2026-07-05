@props(['step'=>1, 'icon'=>'circle'])
<div class="step-button" :class="{active:show >= {{$step}} }" @click="changeStep({{$step}})">
    <div class="icon" x-show="show > {{ $step }}">
        <i data-feather="check"></i>
    </div>
    <div class="icon" x-show="show <= {{ $step }}">
        <i data-feather="{{ $icon }}"></i>
    </div>
    <span>{{ $slot }}</span>
</div>