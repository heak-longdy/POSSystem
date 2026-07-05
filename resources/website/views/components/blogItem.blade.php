
<!-- Blog Card 1 -->
<div class="blog-card {{ isset($classJobCus) ? $classJobCus : '' }}" data-aos="flip-up" data-aos-delay="400">
    <div class="bgBlog"></div>
    <div class="blogImg">
        <img src="{{$item->image_url}}"  onerror="this.onerror=null;this.src='{{ asset('website/img/empty.webp') }}';" alt="Blog Image">
    </div>
    <div class="blog-content">
        <h3>{{$item?->title}}</h3>
        {{-- <p>Discover the unique advantages of choosing Cambodia
            for your internship, from professional growth
            opportunities to cultural immersion experiences.</p> --}}
        <p>{!! strip_tags($item->des) !!}</p>
        <div class="blog-footer">
            {{-- <span>February 3, 2023</span> --}}
            <span>{!! date('d F, Y', strtotime($item->created_at)) !!}</span>
            <a href="{{url($url)}}">Read More</a>
        </div>
    </div>
</div>
