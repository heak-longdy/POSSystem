@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', [
        'header_name' => '',
        'customClass' => 'headerInForm',
    ])
    <style>
        .accordionGp {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            margin-bottom: 15px;
            width: 100%;
        }

        .accordionGp>h3 {
            display: flex;
            align-items: center;
            grid-gap: 10px;
            margin: 0 0 10px 0;
            width: 75%;
        }

        .accordionItem {
            border-radius: 10px;
            margin-bottom: 10px;
            width: 75%;
            cursor: pointer;
        }

        .accordion {
            padding: 10px;
            font-size: 16px;
            border: none;
            text-align: left;
            outline: none;
            width: 100%;
            background-color: #f1f1f1;
            transition: background-color 0.3s ease;
            box-shadow: 0 3px 3px 0 rgba(181, 181, 181, 0.13);
            border-radius: 10px;
            display: flex;

        }

        .accordion:last-child {
            box-shadow: none;
        }

        .accordion>span {
            display: flex;
            align-items: center;
            grid-gap: 10px;
            flex: 1;
            padding-left: 5px;
            padding-right: 25px;
        }

        .accordion>span>span {
            cursor: pointer;
        }

        .accordion>span>span:hover {
            color: #3C91E6;
        }

        .accordion>span>i {
            font-size: 17px;
            cursor: pointer;
        }

        .accordion>span>i:hover {
            color: #3C91E6;
        }

        .lineWord {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: initial;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }

        .accordion:hover {
            background-color: #ddd;
        }

        .icon {
            float: right;
            transition: transform 0.3s ease;
            font-size: 27px;
            cursor: pointer;
        }

        .rotate {
            transform: rotate(180deg);
        }

        .panel {
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease-out;
            background-color: white;
            /* border: 1px solid #ccc; */
            padding: 0 10px;
        }

        .panel p {
            padding: 10px 0;
        }
        .ourServiceLayout {
            padding: 30px 200px;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .actOurserFooter {
            margin-top: 10px;
            width: 60%;
        }

        .btnSave {
            height: 45px;
            font-size: 14px;
            border-radius: 10px !important;
            box-sizing: border-box;
            color: #fff;
            background-color: #3C91E6;
        }

        .btnSave>i.bx {
            font-size: 22px;
            margin-right: 5px;
        }

        .form-admin .form-wrapper {
            padding: 0;
        }

        .form-admin .form-wrapper .form-body {
            box-shadow: none;
            padding: 0 0 20px 0px;
        }

        .form-body .row-2 {
            grid-gap: 15px;
        }

        .form-body .row-2 .form-row {
            flex: 1;
        }

        .w110 {
            flex: unset !important;
            width: 110px !important;
        }

        .dialog .dialog-container {
            opacity: 1 !important;
            transform: unset !important;
        }


        .timelineLayout {
            margin: 25px 0;
            transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
        }

        .timeline {
            position: relative;
            padding-left: 4rem;
            margin: 0 0 0 30px;
        }

        .timeline::before {
            content: "";
            width: 2.5px;
            height: 95%;
            position: absolute;
            background-color: #f1f1f1;
            left: 0;
            top: 15px;
        }

        .timeline .timeline-box {
            position: relative;
            margin-bottom: 2.5rem;
        }

        .timeline .timeline-box .icon {
            position: absolute;
            left: -88px;
            top: 4px;
            width: 48px;
            height: 48px;
            background-color: #f1f1f1;
            border-radius: 50%;
            font-size: 3rem;
            text-align: center;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .timeline .timeline-box .icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            border-radius: 50%;
        }

        .timeline .timeline-box .icon i {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .timeline .timeline-box .timeline-body {
            background-color: #f1f1f1;
            border-radius: 6px;
            padding: 20px 18px 15px 15px;
            box-shadow: 1px 3px 9px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.5s ease;
        }

        /* .timeline .timeline-box .timeline-body:hover {
                                                                                                                                                        box-shadow: 1px 3px 20px rgba(0, 0, 0, 0.6);
                                                                                                                                                    } */

        .timeline .timeline-box .timeline-body::before {
            content: "";
            background-color: inherit;
            width: 20px;
            height: 20px;
            display: block;
            position: absolute;
            left: -10px;
            transform: rotate(45deg);
            border-radius: 0 0 0 3px;
        }

        .timeline .timeline-box .timeline-body .timeLineItem {
            margin-bottom: 15px;
        }

        .timeline .timeline-box .timeline-body .header {
            margin-bottom: 7px;
        }

        .timeline .timeline-box .timeline-body .timeLineItem .header .badge {
            background-color: #4f537b;
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 4px;
            font-weight: bold;
        }

        .timeline .timeline-box .timeline-body .time {
            font-weight: 300;
            font-style: italic;
            opacity: 0.4;
            margin-top: 16px;
            font-size: 11px;
            padding: 0;
        }

        .timeline-body>.timeLineItem>h4 {
            background: none;
            padding: 0;
            margin: 0;
        }

        .timeline-body>.timeLineItem>p {
            background: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
            color: #4e4c4cb8 !important;
        }

        #primary .badge,
        #primary .icon {
            /* background-color: #42a5f5; */
            padding: 5px;
        }

        #success .badge,
        #success .icon {
            background-color: #66bb6a;
        }

        #danger .badge,
        #danger .icon {
            background-color: #ec407a;
        }

        #warning .badge,
        #warning .icon {
            background-color: #ffa726;
        }

        #info .badge,
        #info .icon {
            background-color: #29b6f6;
        }

        .timeLineAction {
            margin-top: 20px;
        }

        .timeLineAction>a>button {
            width: 35px;
            margin: 0;
            padding: 0;
            font-size: 20px;
            background: #80808021;
            color: #0000008a !important;
            height: 35px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50% !important;
            max-width: 35px;
            min-width: 35px !important;
        }

        .timeLineAction>a>button:hover {
            background: #42a5f5;
            color: #fff !important;
        }
    </style>
    <div class="content-wrapper form-admin" id="app" x-data="xIndex">

        <div class="ourServiceLayout">
            <div class="accordionGp">
                <h3 class="fontWeight"><i class='bx bxs-wrench'></i>About Us</h3>
                <template x-for="(item,index) in data?.AboutMission">
                    <div class="accordionItem" x-data="{ indexPanel: (item.type + index) }">
                        <div class="accordion" @click="active === indexPanel ? active = null : active = indexPanel">
                            <span><span class="lineWord"><span x-text="index+1"></span>.&nbsp;<span
                                        x-text="item.title"></span></span></span>
                            <i class='bx bx-chevron-down icon' :class="{ 'rotate': active === indexPanel }"></i>
                        </div>
                        <div x-ref="indexPanel"
                            :style="active === indexPanel ? 'max-height: ' + $refs['indexPanel'].scrollHeight + 'px' :
                                'max-height: 0'"
                            class="panel form-wrapper">
                            <div class="timelineLayout">
                                <div class="timeline">
                                    <div class="timeline-box" id="primary">
                                        <div class="icon">
                                            <img x-bind:src="baseImageUrl + item?.image" alt=""
                                                onerror="this.onerror=null;this.src='{{ asset('admin-public/logo/profile.png') }}';">
                                        </div>
                                        <div class="timeline-body">
                                            <template x-for="(valItem,valIndex) in item?.model_setting_de">
                                                <div class="timeLineItem" x-show="valItem?.title || valItem?.description">
                                                    <h4 class="header" x-text="item?.title ?? '---'"></h4>
                                                    <p x-text="valItem?.description ?? ''"></p>
                                                </div>
                                            </template>
                                            <div class="timeLineAction">
                                                <a href="#" @click="dialogStore(item)">
                                                    <button><i class='bx bxs-edit-alt'></i></button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="accordionGp">
                <h3 class="fontWeight"><i class='bx bx-eraser'></i>Our Founders</h3>
                <template x-for="(item,index) in data?.Founders">
                    <div class="accordionItem" x-data="{ indexPanel: (item.type + index) }">
                        <div class="accordion" @click="active === indexPanel ? active = null : active = indexPanel">
                            <span><span class="lineWord"><span x-text="index+1"></span>.&nbsp;<span
                                        x-text="item.title"></span></span></span>
                            <i class='bx bx-chevron-down icon' :class="{ 'rotate': active === indexPanel }"></i>
                        </div>
                        <div x-ref="indexPanel"
                            :style="active === indexPanel ? 'max-height: ' + $refs['indexPanel'].scrollHeight + 'px' :
                                'max-height: 0'"
                            class="panel form-wrapper">
                            <div class="timelineLayout">
                                <div class="timeline">
                                    <div class="timeline-box" id="primary">
                                        <div class="icon">
                                            <img x-bind:src="baseImageUrl + item?.image" alt=""
                                                onerror="this.onerror=null;this.src='{{ asset('admin-public/logo/profile.png') }}';">
                                        </div>
                                        <div class="timeline-body">
                                            <template x-for="(valItem,valIndex) in item?.model_setting_de">
                                                <div class="timeLineItem">
                                                    <h4 class="header" x-text="item?.title+':'"></h4>
                                                    <p x-text="valItem?.description"></p>
                                                </div>
                                            </template>

                                            <div class="timeLineAction">
                                                <a href="#" @click="dialogStore(item)">
                                                    <button><i class='bx bxs-edit-alt'></i></button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="accordionGp">
                <h3 class="fontWeight"><i class='bx bxs-widget'></i>Our Values</h3>
                <template x-for="(item,index) in data?.Values">
                    <div class="accordionItem" x-data="{ indexPanel: (item.type + index) }">
                        <div class="accordion" @click="active === indexPanel ? active = null : active = indexPanel">
                            <span><span class="lineWord"><span x-text="index+1"></span>.&nbsp;<span
                                        x-text="item.title"></span></span></span>
                            <i class='bx bx-chevron-down icon' :class="{ 'rotate': active === indexPanel }"></i>
                        </div>
                        <div x-ref="indexPanel"
                            :style="active === indexPanel ? 'max-height: ' + $refs['indexPanel'].scrollHeight + 'px' :
                                'max-height: 0'"
                            class="panel form-wrapper">
                            <div class="timelineLayout">
                                <div class="timeline">
                                    <div class="timeline-box" id="primary">
                                        <div class="icon">
                                            <img x-bind:src="baseImageUrl + item?.image" alt=""
                                                onerror="this.onerror=null;this.src='{{ asset('admin-public/logo/profile.png') }}';">
                                        </div>
                                        <div class="timeline-body">
                                            <template x-for="(valItem,valIndex) in item?.model_setting_de">
                                                <div class="timeLineItem">
                                                    <h4 class="header" x-text="item?.title+':'"></h4>
                                                    <p x-text="valItem?.description"></p>
                                                </div>
                                            </template>

                                            <div class="timeLineAction">
                                                <a href="#" @click="dialogStore(item)">
                                                    <button><i class='bx bxs-edit-alt'></i></button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    @include('admin::pages.about.store')
@stop

@section('script')
    <script lang="ts">
        document.addEventListener('alpine:init', () => {
            Alpine.data('xIndex', () => ({
                loading: null,
                selected_id: null,
                groupClassList: [],
                professionList: [],
                active: null,
                baseImageUrl: "{{ asset('file_manager') }}",
                image: null,
                data: {},
                dataDialog: {
                    data: {
                        'digPosition': 'top'
                    }
                },
                async init() {
                    this.loading = true;
                    this.data = {!! $data !!};
                    console.log(this.data, 'ffsfsfsf');
                    // Object.keys(this.data).forEach(category => {
                    //     this.data[category].forEach((service, index) => {
                    //         if(service.id == 9){
                    //             console.log(service,'service');
                    //             service.model_setting_de.map(val=>{
                    //                 val.title="fsfsfsfsfsfsf";
                    //             });
                    //         }
                    //         service.position = (index % 2 === 0) ? "left" : "right";
                    //     });
                    // });
                    console.log(this.data);
                    this.loading = false;
                },
                findData(data = [], id = 9) {
                    return data.find((val, index) => val.ser_id == id);
                },
                dialogStore(item) {
                    this.$store.dialogAboutUs.open({
                        data: {
                            'digPosition': 'top',
                            ...item
                        },
                        afterClosed: (result) => {
                            if (result) {

                                const dataRes = result?.data ?? {};
                                // update realtime data List
                                let service = this.data[dataRes?.type]?.find(service =>
                                    service.id === dataRes?.id);
                                if (service) {
                                    service.title = dataRes?.title;
                                    service.image = dataRes?.image;
                                    service.model_setting_de = dataRes?.details ?? [];
                                }
                                iziToast.success({
                                    title: "Done",
                                    message: "save change successfully",
                                    position: 'topCenter',
                                    timeout: 2500,
                                    animateInside: true,
                                    transitionIn: 'fadeIn',
                                    progressBarEasing: 'linear',
                                    pauseOnHover: true
                                });
                            }
                        }
                    });
                }
            }))
        });
    </script>
@stop
