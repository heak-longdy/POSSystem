@extends('website::shared.layout')
@section('layout')
    <style>
        .h3Title {
            text-align: center;
            margin: 60px 0;
            font-size: 25px;
            color: #ff9900;
        }

        .aboutContainer {
            width: fit-content;
            margin: 60px 0 0px 0;
            display: flex;
            grid-gap: 60px;
        }

        .aboutContainer>h2 {
            text-align: center;
            font-size: 25px;
            color: #ff9900;

        }


        .aboutItem {
            display: flex;
            grid-gap: 60px;
            align-items: flex-start;
            margin-bottom: 60px;
            width: calc(100% / 2);

        }

        /* .aboutItem:last-child {
                                                margin-bottom: 0;
                                            } */

        .aboutItem>img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
        }

        .aboutItem>.aboutText {
            flex: 1;
        }

        .aboutItem>.aboutText>h3 {
            font-size: 25px;
            color: #ff9900;
            margin-bottom: 15px;
        }

        /* OurFunders */
        .OurFunders {
            display: flex;
            flex-wrap: wrap;
            grid-gap: 50px;
            margin-bottom: 60px;
        }

        .OurFunderIitem {
            text-align: center;
            width: calc(100% / 2 - 50px);
        }

        .OurFunderIitem>img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ff9900;
            padding: 5px;
        }

        .OurFunderIitem>h3 {
            margin: 10px 0;
            font-size: 20px;
        }

        .OurFunderIitem>p {
            text-align: left;
        }

        /* OurValues */
        .OurValues {
            display: flex;
            grid-gap: 50px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 100px;
        }

        .OurValueIitem {
            text-align: center;
            width: calc(100% / 3 - 50px);
        }

        .OurValueHeader {
            display: flex;
            align-items: center;
            grid-gap: 20px;
            margin-bottom: 20px;
        }

        .OurValueHeader>h3 {
            font-size: 20px;
        }

        .OurValueHeader>img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            /* border: 2px solid #ff9900;
                                padding: 5px; */
        }

        .OurValueIitem>p {
            text-align: left;
        }

        @media screen and (max-width: 1200px) {
            .aboutContainer {
                margin: 60px 0;
            }
        }

        @media screen and (max-width: 700px) {
            .OurValueIitem {
                text-align: center;
                width: calc(100% / 2 - 50px);
            }
        }

        @media screen and (max-width: 550px) {

            .OurFunderIitem,
            .OurValueIitem {
                width: calc(100% / 1);
            }
        }
    </style>
    <div class="wb-home-layout">
        @include('website::components.banner', [
            'header_name' => '',
            'imgUrl' => '../../website/img/aboutUs.webp',
            'text1' => 'About Us',
            'text2' => 'Building Careers While Driving Positive Impact in Cambodia',
            'search' => 'disable',
            'clickhere' => 'disable',
            'clickhere' => 'disable',
        ])
        <div class="webContentListLayout">
            <div class="webContentList" style="flex-direction: column;">
                {{-- about introduction --}}
                <div class="aboutContainer">
                    @foreach ($data['AboutMission'] as $key => $item)
                        <div class="aboutItem" data-aos="zoom-in" data-aos-delay="200">
                            <img src="{{ $item->image_url }}"
                                onerror="this.onerror=null;this.src='{{ asset('website/img/empty.webp') }}';" alt=""
                                class="border-radius wwaImg">
                            <div class="aboutText">
                                <h3>{{ $item?->title }}</h3>
                                @foreach ($item->ModelSettingDe as $chIndex => $chItem)
                                    <p>{{ $chItem?->description }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="underLine"></div>
                {{-- Our Founders --}}
                <h3 class="h3Title">Our Founders</h3>
                <div class="OurFunders">
                    @foreach ($data['Founders'] as $key => $item)
                        <div class="OurFunderIitem" data-aos="zoom-in" data-aos-delay="200">
                            <img src="{{ $item->image_url }}"
                                onerror="this.onerror=null;this.src='{{ asset('website/img/empty.webp') }}';" alt=""
                                class="border-radius">
                            <h3 class="fontWeight">{{ $item?->title }}</h3>
                            @foreach ($item->ModelSettingDe as $chIndex => $chItem)
                                <p>{{ $chItem?->description }}</p>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div class="underLine"></div>
                {{-- Our Values --}}
                <h3 class="h3Title">Our Values</h3>
                <div class="OurValues">
                    @foreach ($data['Values'] as $key => $item)
                        <div class="OurValueIitem" data-aos="zoom-in" data-aos-delay="200">
                            <div class="OurValueHeader">
                                <img src="{{ $item->image_url }}"
                                    onerror="this.onerror=null;this.src='{{ asset('website/img/empty.webp') }}';"
                                    alt="" class="border-radius">
                                <h3 class="fontWeight">{{ $item?->title }}</h3>
                            </div>
                            @foreach ($item->ModelSettingDe as $chIndex => $chItem)
                                <p>{{ $chItem?->description }}</p>
                            @endforeach
                        </div>
                    @endforeach
                </div>
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


    </div>
@stop
@section('script')
    <script></script>
@stop
