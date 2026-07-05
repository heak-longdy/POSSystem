@extends('website::shared.layout')
@section('layout')
    <div class="wb-home-layout">
        @include('website::components.banner', [
            'header_name' => '',
            'imgUrl' => '../../website/img/aboutBg.webp',
            'text1' => 'Our Blogs',
            'text2' => 'Our blog is your go-to resource for everything related to internships in Cambodia.',
            'search' => 'disable',
            'clickhere' => 'disable',
        ])
        <style>
            .itemDetailBody img {
                width: 100% !important;
            }

            .wp-block-image img {
                width: 100% !important;
                object-fit: contain !important;
                height: fit-content !important;
                border-radius: 15px !important;
            }
        </style>
        <div class="webContentListLayout marginTop_Bot30">
            <div class="webContentList">
                <div class="itemDetailLayout">
                    <div class="itemDetailGp">
                        <div class="itemDetailLeft">
                            <div class="itemDetailTitle">
                                <h3>{{ $data?->title }}</h3>
                            </div>
                            <div class="itemDetailBody">
                                <div style="white-space: pre-line;">
                                    {!! $data?->des !!}
                                </div>
                            </div>
                        </div>
                        <div class="itemDetailRight sticky">
                            <h3>Related Articles</h3>
                            @foreach ($blogRelates as $index => $item)
                                <!-- Blog Card 1 -->
                                <div class="blog-card wFullBlogItem">
                                    <div class="bgBlog"></div>
                                    <div class="blogImg">
                                        <img src="{{ $item->image_url }}"
                                            onerror="this.onerror=null;this.src='{{ asset('website/img/empty.webp') }}';"
                                            alt="Blog Image">
                                    </div>
                                    <div class="blog-content">
                                        <h3>{{ $item?->title }}</h3>
                                        <p>{!! strip_tags($item->des) !!}</p>
                                        <div class="blog-footer">
                                            <span>{!! date('d F, Y', strtotime($item->created_at)) !!}</span>
                                            <a href="{{ url('/blog/detail/' . $item->id) }}">Read More</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="RelateItemLayout">
                        <h3>Related Jobs</h3>
                        <div class="RelateItemGp">
                            @foreach ($jobRelates as $index => $item)
                                @include('website::components.jobItemGrid', [
                                    'item' => $item,
                                    'url' => '/internship/detail/' . $item->id,
                                    'classJobCus' => 'relateItem',
                                ])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
