<style>
    .bgBannerCov {
        height: 100%;
        position: absolute;
        top: 0;
        width: 100%;
        z-index: 1;
        background: black;
        opacity: 0.4;
    }

    .bannerTextGp>h3 {
        line-height: 1.5;
        font-size: 20px;
        white-space: pre-line !important;
        text-align: left;
    }

    .bannerTextGp>.h3 {
        line-height: 1;
        font-size: 30px;
    }

    .bannerTextGp>a {
        width: fit-content;
        height: 40px;
        padding: 0 15px;
        display: flex;
        align-items: center;
        color: #545454;
        text-decoration: none;
        border-radius: 0.75rem;
        grid-gap: 5px;
        background: #fff;
    }


    .bannerTextGp>a>i {
        font-size: 25px;
    }
    .fontSize{
        font-size: 20px !important;
    }
</style>
<div class="bannerLayout" x-data="xBanner" style="{{ isset($height) ? 'height:' . $height : '' }}">
    <div class="bgBannerCov"></div>
    <div class="bannerGp" style="background: url({{ isset($imgUrl) ? $imgUrl : '' }});background-size: cover;">
        @if (!isset($disableText) || $disableText == '')
            <div class="bannerList" data-aos="fade-up" data-aos-delay="100">
                <div class="bannerTextGp" style="flex-direction: column;flex-direction: column;align-items: flex-start;grid-gap: 10px;">
                    @if (isset($textBold))
                        <h3 class="h3">{{ $textBold }}</h3>
                    @endif
                    <h3 class="fontWeight h3 {{isset($text1Class)?$text1Class:''}}">{{ isset($text1) ? $text1 : '' }}</h3>
                </div>
                @if (!isset($search) || $search == '')
                    <div class="bannerSearchGp">
                        {{-- <div class="search">
                    <input type="text" class="search__input" placeholder="Type your text">
                    <button class="search__button">
                        <svg class="search__icon" aria-hidden="true" viewBox="0 0 24 24">
                            <g>
                                <path
                                    d="M21.53 20.47l-3.66-3.66C19.195 15.24 20 13.214 20 11c0-4.97-4.03-9-9-9s-9 4.03-9 9 4.03 9 9 9c2.215 0 4.24-.804 5.808-2.13l3.66 3.66c.147.146.34.22.53.22s.385-.073.53-.22c.295-.293.295-.767.002-1.06zM3.5 11c0-4.135 3.365-7.5 7.5-7.5s7.5 3.365 7.5 7.5-3.365 7.5-7.5 7.5-7.5-3.365-7.5-7.5z">
                                </path>
                            </g>
                        </svg>
                    </button>
                </div> --}}

                        <div class="search">
                            {{-- <select name="position_id" id="position_id" x-init="fetchSelectPosition()">
                                <option value=""> Select Position</option>
                            </select> --}}
                            <select name="sector_id" id="sector_id" x-init="fetchSelectSector()">
                                <option value=""> Select Sector</option>
                            </select>
                            {{-- <select name="job_id" id="job_id" x-init="fetchSelectJob()">
                                <option value=""> Select Job</option>
                            </select> --}}
                            <template x-if="position.id || sector.id || job.id">
                                <a>
                                    <button class="search__button" style="color: #ec1111ab !important;background:#fff !important;"
                                        @click="resetSeach()">
                                        <i class='bx bx-x' style="font-size: 25px;"></i>
                                        <span>Reset</span>
                                    </button>
                                </a>
                            </template>
                            <a>
                                <button class="search__button" @click="SearchJob()">
                                    <svg class="search__icon" aria-hidden="true" viewBox="0 0 24 24">
                                        <g>
                                            <path
                                                d="M21.53 20.47l-3.66-3.66C19.195 15.24 20 13.214 20 11c0-4.97-4.03-9-9-9s-9 4.03-9 9 4.03 9 9 9c2.215 0 4.24-.804 5.808-2.13l3.66 3.66c.147.146.34.22.53.22s.385-.073.53-.22c.295-.293.295-.767.002-1.06zM3.5 11c0-4.135 3.365-7.5 7.5-7.5s7.5 3.365 7.5 7.5-3.365 7.5-7.5 7.5-7.5-3.365-7.5-7.5z">
                                            </path>
                                        </g>
                                    </svg>
                                    <span>Search</span>
                                </button>
                            </a>
                        </div>
                    </div>
                @endif
                <div class="bannerTextGp">
                    <h3 class="fontWeightUnset">{{ isset($text2) ? $text2 : '' }}</h3>
                    @if (!isset($clickhere) || $clickhere == '')
                        <a href="{{ isset($btnRoute) ? route($btnRoute) : '' }}"><i
                                class='bx bxs-hand-right'></i><span>{{ isset($btnText) ? $btnText : '' }}</span></a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data("xBanner", () => ({
            baseImageUrl: "{{ asset('file_manager') }}",
            image: "",
            position: {
                "id": "",
                "text": ""
            },
            sector: {
                "id": "",
                "text": ""
            },
            job: {
                "id": "",
                "text": ""
            },
            init() {
                const data = @json($data ?? '');
                const position = @json($position ?? '');
                const sector = @json($sector ?? '');
                const job = @json($job ?? '');
                this.image = data?.image ?? "";

                this.position.id = position?.id ?? `{{ old('position_id') }}`;
                this.position.text = position?.title ?? `{{ old('position_text') }}`;
                this.sector.id = sector?.id ?? `{{ old('sector_id') }}`;
                this.sector.text = sector?.title ?? `{{ old('sector_text') }}`;
                this.job.id = job?.id ?? `{{ old('job_id') }}`;
                this.job.text = job?.title ?? `{{ old('job_text') }}`;

                $select2Data('#position_id', this.position.id, this.position.text);
                $select2Data('#sector_id', this.sector.id, this.sector.text);
                $select2Data('#job_id', this.job.id, this.job.text);

            },
            fetchSelectPosition() {
                $(`#position_id`).select2({
                    placeholder: `Select Position`,
                    ajax: {
                        url: '{{ route('web-select-position') }}',
                        dataType: 'json',
                        type: "GET",
                        quietMillis: 50,
                        data: (param) => {
                            return {
                                search: param.term
                            };
                        },
                        processResults: (data) => {
                            return {
                                results: $.map(data.data, (item) => {
                                    return {
                                        text: item?.title || '',
                                        id: item.id
                                    }
                                })
                            };
                        }
                    }
                }).on('select2:open', (e) => {
                    $select2FocusInputSearch();
                }).on('select2:select', (event) => {
                    // Capture the ID and text when an option is selected
                    const selectedId = event.params.data.id;
                    const selectedText = event.params.data.text;
                    const Obj = {
                        "id": selectedId,
                        "text": selectedText
                    };
                    this.position = Obj
                }).on('select2:close', async (eventClose) => {
                    const ID = eventClose?.target?.value ?? "";
                    if (this.position.id != ID) {
                        $select2Data('#sector_id')
                        $select2Data('#job_id')
                    }

                });
            },
            fetchSelectSector() {
                $(`#sector_id`).select2({
                    placeholder: `Select Sector`,
                    ajax: {
                        url: '{{ route('web-select-sector') }}',
                        dataType: 'json',
                        type: "GET",
                        quietMillis: 50,
                        data: (param) => {
                            return {
                                search: param.term,
                                position_id: this.position.id
                            };
                        },
                        processResults: (data) => {
                            return {
                                results: $.map(data.data, (item) => {
                                    return {
                                        text: item?.title || '',
                                        id: item.id
                                    }
                                })
                            };
                        },
                        error: (xhr, status, error) => {
                            console.error('Error fetching positions:', error);
                        }
                    }
                }).on('select2:select', (event) => {
                    // Capture the ID and text when an option is selected
                    const selectedId = event.params.data.id;
                    const selectedText = event.params.data.text;
                    const Obj = {
                        "id": selectedId,
                        "text": selectedText
                    };
                    this.sector = Obj
                }).on('select2:open', (e) => {
                    $select2FocusInputSearch();
                }).on('select2:close', async (eventClose) => {
                    const ID = eventClose?.target?.value ?? "";
                    if (this.sector.id != ID) {
                        $select2Data('#job_id')
                    }
                });;
            },
            fetchSelectJob() {
                $(`#job_id`).select2({
                    placeholder: `Select Job`,
                    ajax: {
                        url: '{{ route('web-select-job') }}',
                        dataType: 'json',
                        type: "GET",
                        quietMillis: 50,
                        data: (param) => {
                            return {
                                search: param.term,
                                position_id: this.position.id,
                                sector_id: this.sector.id
                            };
                        },
                        processResults: (data) => {
                            return {
                                results: $.map(data.data, (item) => {
                                    return {
                                        text: item?.title || '',
                                        id: item.id
                                    }
                                })
                            };
                        },
                        error: (xhr, status, error) => {
                            console.error('Error fetching positions:', error);
                        }
                    }
                }).on('select2:select', (event) => {
                    // Capture the ID and text when an option is selected
                    const selectedId = event.params.data.id;
                    const selectedText = event.params.data.text;
                    const Obj = {
                        "id": selectedId,
                        "text": selectedText
                    };
                    this.job = Obj
                }).on('select2:open', (e) => {
                    $select2FocusInputSearch();
                });
            },
            resetSeach() {
                const item = {
                    "id": "",
                    "text": ""
                };
                this.position = {
                    ...item
                };
                this.sector = {
                    ...item
                };
                this.job = {
                    ...item
                };
                $select2Data('#position_id');
                $select2Data('#sector_id');
                $select2Data('#job_id');
            },
            SearchJob() {
                let url =
                    `/internship?position_id=${this.position.id}&sector_id=${this.sector.id}&job_id=${this.job.id}`;
                reloadData(url);
            }
        }));
    });
</script>
