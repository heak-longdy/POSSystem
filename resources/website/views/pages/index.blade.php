@extends('website::shared.layout')
@section('layout')
    <div class="wb-home-layout">
        @include('website::components.banner', [
            'header_name' => '',
            'imgUrl' => '../../website/img/W1-01.jpg',
            'textBold' => 'Internships in Cambodia',
            'text1' => 'Unlock new career paths with industry leaders in the Kingdom of Wonder',
            'text1Class' => 'fontSize',
            'text2' => 'View all internships: Here',
            'btnText' => 'click here',
            'btnRoute' => 'web-job',
        ])

        {{-- WHO WE ARE --}}
        <div class="webContentListLayout paddingTop_Bot60">
            <div class="webContentList">
                <div class="hLeft" data-aos="zoom-in" data-aos-delay="200">
                    <h3 class="colorYellow margin_bot20">Who We Are</h3>
                    <div>
                        <p>We specialize in high-ROI placements for UK interns in Cambodia, offering unmatched personal and
                            professional growth opportunities in a rapidly developing economy. Our internships are with
                            British international businesses or industry leaders within the country, supported by our expert
                            team to ensure a seamless and transformative experience. Invest in your future today.</p>
                        <a href="{{ route('web-about') }}" class="colorYellow">Learn why we do it here</a>
                    </div>
                </div>
                <div class="hRight text_align_right" data-aos="zoom-in" data-aos-delay="200">
                    <img src="{{ asset('website/img/ourService01.webp') }}" alt="" class="border-radius wwaImg"
                        onerror="(this).src='{{ asset('website/img/emptyImage.webp') }}'">
                </div>
            </div>
        </div>

        {{-- Why us? --}}
        <div class="webContentListLayout paddingTop_Bot60 bgYellow" style="color: #fff;padding: 35px 0;">
            <div class="webContentList">
                <div class="hLeft" data-aos="zoom-in" data-aos-delay="200">
                    <h3 class="margin_bot20">Why ISEA?</h3>
                    <div>
                        <p style="display: flex;margin-bottom: 7px;">
                            <i class='bx bx-check-circle'></i>&nbsp;
                            <span style="margin-top: -2px;"><span class="fontWeight">British Expertise:</span> Founded by
                                British expats in Cambodia, we combine UK standards with local insights to ensure cultural
                                immersion and career growth.</span>
                        </p>
                        <p style="display: flex;margin-bottom: 7px;">
                            <i class='bx bx-check-circle'></i>&nbsp;
                            <span style="margin-top: -2px;"><span class="fontWeight">Exclusive Networks:</span> Strong ties
                                with British businesses and local industry leaders provide access to high-quality placements
                                for maximum professional impact.</span>
                        </p>
                        <p style="display: flex;margin-bottom: 7px;">
                            <i class='bx bx-check-circle'></i>&nbsp;
                            <span style="margin-top: -2px;"><span class="fontWeight">Comprehensive Support:</span> Full pre-
                                and post-landing services, in-country orientation, and ongoing support ensure safety,
                                credibility, and a seamless experience.</span>
                        </p>
                    </div>
                </div>
                <div class="colLine" style="background: #fff;"></div>
                <div class="hRight" data-aos="zoom-in" data-aos-delay="200">
                    <h3 class="margin_bot20">Why Cambodia?</h3>
                    <div>
                        <p style="display: flex;margin-bottom: 7px;">
                            <i class='bx bx-check-circle'></i>&nbsp;
                            <span style="margin-top: -2px;"><span class="fontWeight">Thriving Economy:</span> Gain hands-on
                                experience in Cambodia’s rapidly growing Southeast Asian market.</span>
                        </p>
                        <p style="display: flex;margin-bottom: 7px;"><i class='bx bx-check-circle'></i>&nbsp;
                            <span style="margin-top: -2px;"><span class="fontWeight">Global Collaboration:</span> Work with
                                international teams, gain diverse perspectives, and expand your professional network.</span>
                        </p>
                        <p style="display: flex;margin-bottom: 7px;"><i class='bx bx-check-circle'></i>&nbsp;
                            <span style="margin-top: -2px;"><span class="fontWeight">Cultural Immersion:</span> Enjoy
                                Cambodia’s rich history, vibrant culture, and warm, welcoming environment.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Our Partners --}}
        @if (count($partners) > 0)
            <div class="webContentListLayout" style="padding: 60px 0;">
                <div class="webContentList" style="align-items: center;flex-direction: column;">
                    <h3 class="colorYellow" style="padding-bottom: 20px;">Our Partners</h3>
                    <div class="partnerLayout owl-carousel owl-carousel-partner">
                        @foreach ($partners as $item)
                            <div class="partnerGp">
                                <div class="partnerItem">
                                    <div class="partnerImg">
                                        <img src="{{ $item->image_url }}" alt="Partners"
                                            onerror="(this).src='{{ asset('website/img/empty.webp') }}'">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- listJob --}}
        <div class="jobLayout" style="background: url('../../website/img/homeProgram.webp');background-size: cover;">
            <div class="jobListing">
                <h3 class="jobTitle">Internships</h3>
                <div class="jobContainer">
                    @foreach ($jobs as $index => $item)
                        @include('website::components.jobItem', [
                            'item' => $item,
                            'urlDetail' => url('/internship/detail/' . $item?->id),
                        ])
                    @endforeach
                </div>
            </div>
            <div class="viewMore">
                <a href="{{ route('web-job') }}">
                    <div>See More<i class='bx bx-chevron-right'></i></div>
                </a>
            </div>
        </div>

        {{-- Contact Us --}}
        @include('website::components.contact', [
            'header_name' => '',
            'imgUrl' => '../../website/img/contact2.webp',
            'text1' => 'Book a free chat with one of our UK team',
            'text2' => 'Are you looking for interns?',
            'btnText' => 'click here',
        ])

        {{-- View our testimonials --}}
        @if (count($testimonials) > 0)
            <div class="webContentListLayout" style="padding: 35px 0;">
                <div class="webContentList testimonialsList" style="align-items: center;flex-direction: column;">
                    <h3 class="colorYellow">View our testimonials</h3>
                    <section>
                        <div class="blog">
                            <div class="container">
                                <div class="owl-carousel owl-theme blog-post owl-carousel-testimonial">
                                    @foreach ($testimonials as $item)
                                        <div class="blog-content" data-aos="fade-right" data-aos-delay="200">
                                            <img src="{{ $item->image_url }}" alt="testimonials"
                                                onerror="(this).src='{{ asset('website/img/empty.webp') }}'">
                                            <div class="blog-title">
                                                <p>{{ $item?->name ?? '' }}</p>
                                                <span>{{ $item?->description ?? '' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="owl-navigation">
                                    <span class="owl-nav-prev"><i class='bx bx-chevron-left'></i></span>
                                    <span class="owl-nav-next"><i class='bx bx-chevron-right'></i></span>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        @endif
    </div>
@stop
@section('script')
    <script>
        // owl-crousel for blog
        const responsive = {
            0: {
                items: 1
            },
            320: {
                items: 1
            },
            560: {
                items: 1
            },
            960: {
                items: 2
            }
        }
        $('.owl-carousel-testimonial').owlCarousel({
            loop: true,
            autoplay: false,
            autoplayTimeout: 3000,
            dots: false,
            nav: true,
            navText: [$('.owl-navigation .owl-nav-prev'), $('.owl-navigation .owl-nav-next')],
            responsive: responsive
        });
    </script>

    <script>
        // owl-crousel for blog
        const responsivePartner = {
            0: {
                items: 2
            },
            320: {
                items: 3
            },
            560: {
                items: 4
            },
            960: {
                items: 4
            }
        }
        $('.owl-carousel-partner').owlCarousel({
            loop: true,
            autoplay: true,
            autoplayTimeout: 3000,
            dots: true,
            responsive: responsivePartner
        });
    </script>
@stop
