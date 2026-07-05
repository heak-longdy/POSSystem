@extends('website::shared.layout')
@section('layout')
    <div class="wb-home-layout">
        @include('website::components.banner', [
            'header_name' => '',
            'imgUrl' => '../../website/img/searchJob.jpg',
            'text1' => 'Seach or view the latest internships below',
            'text2' => 'Need more info?',
            'btnText' => "Let's talk!",
            'btnRoute' => 'web-contact',
        ])
        <div class="jobLayout">
            <div class="jobListing">
                <h3 class="jobTitle">Internships</h3>
                <div class="jobContainer">
                    @foreach ($data as $index => $item)
                        @include('website::components.jobItem', [
                            'item' => $item,
                            'urlDetail' => url('/internship/detail/' . $item?->id),
                        ])
                    @endforeach
                </div>
                <div class="paginationLayout">
                    @include('website::components.paginationNumber', ['paginate' => $data])
                </div>
            </div>
        </div>
        {{-- Contact Us --}}
        @include('website::components.contact', [
            'header_name' => '',
            'imgUrl' => '../../website/img/contactus01.webp',
            'text1' => 'Book A Free Consultation With Our UK Team',
            'btnText' => 'click here',
        ])
    </div>
@stop
@section('script')
    <script></script>
@stop
