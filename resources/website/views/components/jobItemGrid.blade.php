<!-- Job Card Grid 1 -->
<div class="job-card-grid {{ isset($classJobCus) ? $classJobCus : '' }}"">
    <div class="bgJob"></div>
    <div class="jobImg">
        <img src="{{ $item->image_url }}" alt="Job Image" onerror="(this).src='{{ asset('website/img/empty.webp') }}'">
    </div>
    <div class="job-card-grid-content">
        <h3>{{ $item?->title }}</h3>
        <p>{!! strip_tags($item->job_des) !!}</p>
        <div class="job-card-grid-footer">
            <a href="{{ route('web-apply-form',$item->id) }}">
                <button type="button" class="btnJob apply"><i
                        class='bx bx-right-top-arrow-circle'></i><span>Apply
                        Now</span></button>
            </a>
            <a href="{{ url($url) }}">
                <button type="button" class="btnView btnJob apply"><i class='bx bx-show'></i><span>View Internship</span></button>
            </a>
        </div>
    </div>
</div>
