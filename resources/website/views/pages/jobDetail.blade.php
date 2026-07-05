@extends('website::shared.layout')
@section('layout')
    <div class="wb-home-layout">
        @include('website::components.banner', [
            'header_name' => '',
            'imgUrl' => '../../website/img/searchJob.jpg',
            'clickhere' => 'disable',
        ])

        <style>
            /* .wb-header {
                    color: #545454 !important;
                }

                .wb-header .header .h-left .logo .first {
                    display: none !important;
                }

                .wb-header .header .h-left .logo .last {
                    display: block !important;
                } */

            .itemDetailBody {
                margin: 25px 0;
            }

            .itemDetailBody img {
                width: 100% !important;
            }

            .wp-block-image img {
                width: 100% !important;
                object-fit: contain !important;
                height: fit-content !important;
                border-radius: 15px !important;
            }

            .margin25 {
                margin: 25px 0;
            }

            .itemDetailLeft>a,
            .itemDetailTitle>a {
                text-decoration: unset;
            }

            .jobAmount {
                margin: 10px 0;
                font-size: 20px;
            }

            /* .marginTop_Bot30{
                    margin: 170px 0 0 0;
                } */
        </style>

        {{-- WHO WE ARE --}}
        {{-- <div class="homeLayout paddingTop_Bot60">
            <div class="homeList">
                <div class="hLeft">
                    <h3 class="colorYellow margin_bot20">WHO WE ARE</h3>
                    <div>
                        <p>Founded by UK and Cambodian nationals, we specialise in premium, tailored
                            internships for UK citizens in South East Asia. We are passionate about
                            quality internships in SEA, and understand the needs of our interns and
                            internship providers.</p>
                        <a href="#" class="colorYellow">Learn why we do it here</a>
                    </div>
                </div>
                <div class="hRight text_align_right">
                    <img src="{{ asset('website/img/ourService01.png') }}" alt="" class="border-radius wwaImg">
                </div>
            </div>
        </div> --}}
        <div class="webContentListLayout marginTop_Bot30">
            <div class="webContentList">
                <div class="itemDetailLayout">
                    <div class="itemDetailGp">
                        <div class="itemDetailLeft">
                            <div class="itemDetailTitle">
                                <h3 class="fontWeight">{{ $data?->title }}</h3>
                                {{-- <a href="{{ route('web-apply-form') }}">
                                    <button type="button" class="btnJob apply"><i
                                            class='bx bx-right-top-arrow-circle bx-fade-right-hover'></i><span>Apply
                                            Now</span></button>
                                </a> --}}
                            </div>
                            {{-- <a href="{{ route('web-apply-form') }}">
                                <button type="button" class="btnJob apply"><i
                                        class='bx bx-right-top-arrow-circle bx-fade-right-hover'></i><span>Apply
                                        Now</span></button>
                            </a> --}}

                            <div class="itemDetailBody">

                                <div class="fontWeight">Placement Name:&nbsp;{{ $data?->Sector?->title }}</div>
                                <div class="jobAmount fontWeight colorYellow">
                                    {{ '$' . number_format($data?->salary_from, 2) }}
                                </div>
                                {{-- <div><span class="fontWeight">Role:</span>&nbsp;{{ $data?->Sector?->title }}</div> --}}

                                @if ($data->month)
                                    <div><span class="fontWeight">Type of
                                            Placement:</span>&nbsp;{{ $data?->PlacementType?->title }}</div>
                                    <div><span class="fontWeight">Duration:</span>&nbsp;{{ $data?->month ?? '' }}
                                        Months</div>
                                @endif
                                @if ($data->post_date || $data->close_date)
                                    <div>
                                        <p class="fontWeight">Dates Available:</p>
                                        <div class="paddingItemDetail">
                                            @if ($data->post_date)
                                                <p>Start Date: {!! date('d F, Y', strtotime($data->post_date)) !!}</p>
                                            @endif
                                            @if ($data->close_date)
                                                <p>End Date: {!! date('d F, Y', strtotime($data->close_date)) !!}</p>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                <div>
                                    <p class="fontWeight">Dates Available:</p>
                                    <div class="paddingItemDetail">
                                        Flexible
                                    </div>
                                </div>
                                @endif
                                <div>
                                    <p class="fontWeight">Job Description:</p>
                                    <div class="paddingItemDetail">
                                        {!! $data?->job_des !!}
                                    </div>
                                </div>
                                <div>
                                    <p class="fontWeight">Job Requirement:</p>
                                    <div class="paddingItemDetail">
                                        {!! $data?->job_requirement !!}
                                    </div>
                                </div>
                                <div>
                                    <p class="fontWeight">Job Responsibilities:</p>
                                    <div class="paddingItemDetail">
                                        {!! $data?->job_res !!}
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="underLine"></div> --}}
                            <a href="{{ route('web-apply-form', $data->id) }}">
                                <button type="button" class="btnJob apply margin25"><i
                                        class='bx bx-right-top-arrow-circle bx-fade-right-hover'></i><span>Apply
                                        Now</span></button>
                            </a>
                        </div>
                        <div class="itemDetailRight sticky">
                            <h3 class="fontWeight">Similar Posts</h3>
                            @foreach ($blogRelates as $index => $item)
                                @include('website::components.jobItemGrid', [
                                    'item' => $item,
                                    'url' => '/internship/detail/' . $item->id,
                                    'classJobCus' => 'wFullJobItem',
                                ])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
@section('script')
    <script></script>
@stop
