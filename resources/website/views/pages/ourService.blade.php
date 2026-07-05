@extends('website::shared.layout')
@section('layout')
    <style>
        .ourServiceContainer {
            width: 100%;
            overflow: hidden;
        }

        .ourServiceContainer>h2 {
            text-align: center;
            font-size: 25px;
            color: #ff9900;
            margin: 60px 0 70px 0;
        }

        .ourSerItemLeft {
            display: flex;
            justify-content: flex-start;
            margin-top: -70px;
        }

        .ourSerItemLeft>.ItemLeft {
            /* width: 540px; */
            /* width: 100%; */
            display: flex;
            /* align-items: flex-start; */
            align-items: center;
            width: 50%;
            position: relative;
        }

        .ourSerItemLeft>.ItemLeft>.lineLeft {
            right: -50px;
            left: unset;
            bottom: 0;
        }

        .ItemLeft>.ItemLeftText {
            flex: 1;
            padding-right: 50px;

        }

        .h3 {
            font-size: 18px;
            margin-bottom: 5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: initial;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }

        .ourSerItemLayout {
            width: 100%;
            margin: 140px 0 70px 0;
        }

        .ourSerItemRight {
            display: flex;
            justify-content: flex-end;
            margin-top: -90px;
        }

        .ourSerItemRight>.ourSerItem {
            /* width: 540px; */
            width: 50%;
            display: flex;
            flex-direction: row-reverse;
            /* align-items: flex-start; */
            align-items: center;
            grid-gap: 35px;
            position: relative;
        }

        .ourSerItemLeft>.ItemLeft>.lineLeft {
            right: -50px;
            left: unset;
            bottom: 0;
        }

        .ourSerText {
            flex: 1;
            /* width: calc(60% - 250px);
                                                                    padding-left: 50px; */
        }

        .ItemLeftText>.ItemText>div>span,
        .ItemLeftText>div>.fontWeight,
        .ItemLeftText>.ItemText>div>.fontWeight,
        .ItemLeftText>div>span,
        .ourSerText>div>.fontWeight,
        .ourSerText>div>span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: initial;
            display: -webkit-box;
            -webkit-line-clamp: 5;
            -webkit-box-orient: vertical;
        }

        .ourSerImage {
            width: 110px;
            height: 110px;
            min-width: 110px;
            min-height: 110px;
            position: relative;
            background: #ff9900;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            font-size: 55px;
            z-index: 2;
        }

        .ourSerImage>img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .lineLeft {
            position: absolute;
            bottom: -45px;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: flex-start;
            left: 61px;
            z-index: 1;


        }

        .chiLineTop {
            height: 1px;
            border-left: 0.125rem dashed hsla(223deg, 10%, 50%, 0.4);
            display: flex;
            justify-content: center;
            width: 1px;
            height: 100px;

        }

        .chiLineRight {
            height: 1px;
            width: 110px;
            border-top: 0.125rem dashed hsla(223deg, 10%, 50%, 0.4);

        }

        /* Our Version */
        .OurVision {
            display: flex;
            flex-wrap: wrap;
            grid-gap: 30px;
            margin-bottom: 70px;
        }

        .OurVisionItem {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: flex-start;
            width: calc(100% / 3 - 30px);
        }

        .OurVisionText>h2 {
            font-size: 16px;
            color: #ff9900;
            margin-top: 0;
            margin-bottom: 10px;
            text-align: left;
        }

        .OurVisionText>p {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .OurVisionText>p>strong {
            display: flex;
            align-items: center;
            padding-left: 18px;
            position: relative;
        }

        .OurVisionText>p>strong>i {
            font-size: 18px;
            position: absolute;
            left: 0;
            top: 0;
        }

        .ourSerItemRight>.ourSerItem>.ourSerImage>.lineLeft {
            flex-direction: column;
            align-items: flex-end;
            right: 60px;
            bottom: -45px;
        }

        .ItemText {}

        .ourSerItemRight>.ourSerItem>.lineLeft {
            flex-direction: column;
            align-items: flex-end !important;
            right: unset;
            left: -45px;
            top: 50%;
        }


        @media screen and (max-width: 1650px) {
            .ItemLeft>.ItemLeftText {
                width: calc(55% - 200px);
                padding-right: 15px;
            }

            .ourSerItem>.ourSerText {
                grid-gap: 20px;
                width: calc(65% - 250px);
            }
        }

        @media screen and (max-width: 1315px) {
            .OurVisionItem {
                width: calc(100% / 2 - 30px);
            }
        }

        @media screen and (max-width: 1210px) {
            .ourSerItem>.ourSerText {
                grid-gap: 20px;
                width: calc(67% - 250px);
            }
        }

        @media screen and (max-width: 800px) {
            .ourSerItemLayout {
                margin: 40px 0 70px 0;
            }

            .ourServiceContainer>h2 {
                margin: 60px 0 40px 0;
            }

            .ourSerItemLeft {
                display: flex;
                justify-content: flex-start;
                margin-top: 0;
            }

            .ourSerItemRight {
                display: flex;
                justify-content: flex-end;
                margin-top: 0;
            }

            .ourSerItemLeft>.ItemLeft {
                grid-gap: 35px;
                flex-direction: row-reverse;
                width: 100%;

            }


            .ourSerItemRight>.ourSerItem {
                width: 100%;
            }



            .ItemLeft>.ItemLeftText {
                width: 100%;
                padding-right: 0;
            }

            .ourSerItem>.ourSerText {
                width: 100%;
            }

            .lineLeft {
                display: none;
            }

            .ourSerItemLeft,
            .ourSerItemRight {
                margin-bottom: 25px;
            }

            .ItemLeftText>.ItemText>div>span,
            .ItemLeftText>div>.fontWeight,
            .ItemLeftText>.ItemText>div>.fontWeight,
            .ItemLeftText>div>span,
            .ourSerText>div>.fontWeight,
            .ourSerText>div>span {
                overflow: unset;
                display: block;
                -webkit-line-clamp: unset;
                -webkit-box-orient: vertical;
            }
        }

        @media screen and (max-width: 650px) {
            .OurVisionItem {
                width: 100%;
            }
        }

        @media screen and (max-width: 400px) {

            .ourSerItemLeft>.ItemLeft,
            .ourSerItemRight>.ourSerItem {
                flex-direction: column-reverse;
            }
        }
    </style>
    <div class="wb-home-layout">

        @include('website::components.banner', [
            'header_name' => '',
            'imgUrl' => '../../website/img/OurService.webp',
            'text1' => 'Our Services',
            'text2' => 'End-to-End Solutions for Win-Win Internship Experiences',
            'search' => 'disable',
            'clickhere' => 'disable',
            'clickhere' => 'disable',
        ])
        {{-- our service --}}
        <div class="webContentListLayout">
            <div class="webContentList">
                <div class="ourServiceContainer">
                    <h2>The Process for Interns</h2>
                    <div class="ourSerItemLayout">
                        @foreach ($data['Interns'] as $key => $item)
                            <div class="{{ $item->position == 'left' ? 'ourSerItemLeft' : 'ourSerItemRight' }}">
                                <div class="{{ $item->position == 'left' ? 'ItemLeft' : 'ourSerItem' }}">
                                    <div class="ItemLeftText" data-aos="fade-left" data-aos-delay="100">
                                        <h3 class="h3">{{ $key + 1 }}. {{ $item?->title }}</h3>
                                        @foreach ($item->ModelSettingDe as $chIndex => $chItem)
                                            <div>
                                                <span
                                                    class="fontWeight">{{ $chItem?->title ? $chItem?->title . ' :' : '' }}</span>
                                                <span class="spanText">{{ $chItem->description }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="ourSerImage">
                                        <img src="{{ $item?->image_url }}"
                                            onerror="this.onerror=null;this.src='{{ asset('website/img/empty.webp') }}';" />
                                    </div>
                                    @if (count($data['Interns']) != $key + 1)
                                        <div class="lineLeft">
                                            <div class="chiLineTop"></div>
                                            <div class="chiLineRight"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="underLine"></div>
                    <h2 class="">
                        Our vision goes beyond simply placing interns.<br />
                        We want to create win-win development</h2>
                    <div class="OurVision">
                        @foreach ($data['Vision'] as $key => $item)
                            <div class="OurVisionItem" data-aos="zoom-in" data-aos-delay="200">
                                <div class="OurVisionText">
                                    <h2 class="fontWeight">{{ $item?->title }}</h2>
                                    @foreach ($item->ModelSettingDe as $chIndex => $chItem)
                                        @if ($chItem?->title)
                                            <p><strong class="fontWeight"><i class='bx bx-check-circle'></i>&nbsp;{{ $chItem?->title }}:</strong>{{ $chItem?->description }}</p>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- <div class="underLine"></div>
                    <h2>The Process for Providers </h2>
                    <div class="ourSerItemLayout">
                        @foreach ($data['Providers'] as $key => $item)
                            <div class="{{ $item->position == 'left' ? 'ourSerItemLeft' : 'ourSerItemRight' }}">
                                <div class="{{ $item->position == 'left' ? 'ItemLeft' : 'ourSerItem' }}">
                                    <div class="ItemLeftText" data-aos="fade-left" data-aos-delay="100">
                                        <h3 class="h3">{{ $key + 1 }}. {{ $item?->title }}</h3>
                                        @foreach ($item->ModelSettingDe as $chIndex => $chItem)
                                            <div>
                                                <span
                                                    class="fontWeight">{{ $chItem?->title ? $chItem?->title . ' :' : '' }}</span>
                                                <span class="spanText">{{ $chItem->description }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="ourSerImage">
                                        <img src="{{ $item?->image_url }}"
                                            onerror="this.onerror=null;this.src='{{ asset('website/img/empty.webp') }}';" />
                                    </div>
                                    @if (count($data['Providers']) != $key + 1)
                                        <div class="lineLeft">
                                            <div class="chiLineTop"></div>
                                            <div class="chiLineRight"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="underLine"></div> --}}
                </div>
            </div>
        </div>
        <div class="webContentListLayout" style="background: url('../../website/img/homeProgram.webp');background-size: cover;">
            <div class="webContentList">
                <div class="ourServiceContainer">
                    <h2>The Process for Providers </h2>
                    <div class="ourSerItemLayout">
                        @foreach ($data['Providers'] as $key => $item)
                            <div class="{{ $item->position == 'left' ? 'ourSerItemLeft' : 'ourSerItemRight' }}">
                                <div class="{{ $item->position == 'left' ? 'ItemLeft' : 'ourSerItem' }}">
                                    <div class="ItemLeftText" data-aos="fade-left" data-aos-delay="100">
                                        <h3 class="h3">{{ $key + 1 }}. {{ $item?->title }}</h3>
                                        @foreach ($item->ModelSettingDe as $chIndex => $chItem)
                                            <div>
                                                <span
                                                    class="fontWeight">{{ $chItem?->title ? $chItem?->title . ' :' : '' }}</span>
                                                <span class="spanText">{{ $chItem->description }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="ourSerImage">
                                        <img src="{{ $item?->image_url }}"
                                            onerror="this.onerror=null;this.src='{{ asset('website/img/empty.webp') }}';" />
                                    </div>
                                    @if (count($data['Providers']) != $key + 1)
                                        <div class="lineLeft">
                                            <div class="chiLineTop"></div>
                                            <div class="chiLineRight"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- <div class="underLine"></div> --}}
                </div>
            </div>
        </div>
        {{-- WHO WE ARE --}}
        <div class="webContentListLayout paddingTop_Bot60">
            <div class="webContentList">
                <div class="hLeft" data-aos="zoom-in" data-aos-delay="200">
                    <h3 class="colorYellow margin_bot20">Why ISEA?</h3>
                    <div>
                        <p>We are committed to providing a holistic and enriching internship experience that benefits all
                            stakeholders.</p>
                        <p>Our comprehensive services ensure that interns are well-prepared, supported, and equipped to make
                            the most of their time in Cambodia, while companies receive dedicated, skilled interns who add
                            value to their operations.</p>
                        <p>Together, we create impactful opportunities that drive growth and development for individuals,
                            organizations, and the nation as a whole.</p>
                    </div>
                </div>
                <div class="hRight text_align_right" data-aos="zoom-in" data-aos-delay="200">
                    <img src="{{ asset('website/img/ourService01.webp') }}" alt="" class="border-radius wwaImg"
                        onerror="this.onerror=null;this.src='{{ asset('website/img/empty.webp') }}';">
                </div>
            </div>
        </div>


        {{-- Contact Us --}}
        @include('website::components.contact', [
            'header_name' => '',
            'imgUrl' => '../../website/img/contactus01.webp',
            'text1' => 'Book a free chat with one of our UK team',
            'text2' => 'Are you looking for interns?',
            'btnText' => 'click here',
        ])


    </div>
@stop
@section('script')
    <script></script>
@stop
