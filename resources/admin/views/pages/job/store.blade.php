@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-'.$routeName.'-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ $id ? 'Update Internship' : 'Create Internship' }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Title <span>*</span> </label>
                        <input type="text" name="title" value="{!! request('id') ? $data?->title : old('title') !!}" placeholder="Enter title ...">
                        <i class='bx bx-font-family'></i>
                        @error('title')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    {{-- <div class="form-row">
                        <label>Position</label>
                        <div class="select2Group">
                            <select name="position_id" class="SelectPosition" id="position_id" x-init="fetchSelectPosition()">
                                <option value=""> Select Position</option>
                            </select>
                            <div class="select2Reset" x-show="position?.id" @click="$select2Data('#position_id');position='';sector='';$select2Data('#sector_id')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path
                                        d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" x-model="position.text" name="position_text">
                        @error('position_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div> --}}
                    <div class="form-row">
                        <label>Sector<span>*</span></label>
                        {{-- <select name="sector_id" id="sector_id" x-init="fetchSelectSector()">
                            <option value=""> Select Sector</option>
                        </select> --}}
                        <div class="select2Group">
                            <select name="sector_id" class="SelectPosition" id="sector_id" x-init="fetchSelectSector()">
                                <option value=""> Select Position</option>
                            </select>
                            <div class="select2Reset" x-show="sector?.id" @click="$select2Data('#sector_id')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path
                                        d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" x-model="sector.text" name="sector_text">
                        @error('sector_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                </div>
                <div class="row-4">
                    <div class="form-row">
                        <label>Start Month</label>
                        <select name="start_month" id="start_month">
                            <option value="">Choose item...</option>
                            <template x-for="(item,index) in monthList">
                                <option :value="item.id" x-text="item.id+' - '+item.title"
                                    :selected="item.id == start_month ? true : false"></option>
                            </template>
                        </select>
                        @error('start_month')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>To Month</label>
                        <select name="to_month" id="to_month">
                            <option value="">Choose item...</option>
                            <template x-for="(item,index) in monthList">
                                <option :value="item.id" x-text="item.id+' - '+item.title"
                                    :selected="item.id == to_month ? true : false"></option>
                            </template>
                        </select>
                        @error('to_month')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-4">
                    {{-- <div class="form-row">
                        <label>Number Of Day<span>*</span></label>
                        <input type="number" x-model="number_of_day" name="number_of_day" placeholder="Number Of Day" value="{!! old('number_of_day') !!}"
                            id="number_of_day" autocomplete="off" @input="CalGetCloseDate()">
                        @error('number_of_day')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div> --}}
                    <div class="form-row iconInput">
                        <label>Start Date</label>
                        <input type="text" x-ref="post_date" name="post_date" placeholder="Start Date"
                            value="{!! $data?->post_date ?? old('post_date') !!}" id="post_date" autocomplete="off">
                        <i data-feather="calendar" id="from_date"></i>
                        @error('post_date')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row iconInput">
                        <label>Close Date</label>
                        <input type="text" name="close_date" x-model="close_date" placeholder="Close Date"
                            id="close_date" autocomplete="off">
                        <i data-feather="calendar" id="close_date"></i>
                        @error('close_date')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-4">
                    <div class="form-row iconInput">
                        <label>Amount<span>*</span></label>
                        <input type="number" name="salary_from" placeholder="Enter Amount" class="disableIcon"
                            value="{!! $data->salary_from ?? old('salary_from') !!}" min="0.01" step="0.01" autocomplete="off">
                        <i class='bx bx-dollar-circle'></i>
                        @error('salary_from')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>Placement Type<span>*</span></label>
                        <select name="placement_type_id" id="placement_type_id">
                            <option value="">Choose item...</option>
                            <template x-for="item in placement_typeL_list">
                                <option :value="item.id" x-text="item.title"
                                    :selected="item.id == placement_type.id ? true : false"></option>
                            </template>
                        </select>
                        @error('placement_type_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-4">
                    <div class="form-row">
                        <label>@lang('adminGlobal.form.status.label')<span>*</span></label>
                        <select name="status" id="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (request('id') && $data->status == $key) || old('status') == $key ? 'selected' : '' !!}>{{ $item }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="form-row">
                    <label>Job Descriptions<span>*</span></label>
                    <textarea type="text" rows="8" name="job_des" placeholder="" id="job_des">{!! isset($data) ? $data?->job_des : old('job_des') !!}</textarea>
                    @error('job_des')
                        <label class="error">{{ $message }}</label>
                    @enderror
                </div>
                <div class="form-row">
                    <label>Job Responsibilities<span>*</span></label>
                    <textarea type="text" rows="8" name="job_res" placeholder="" id="job_res">{!! isset($data) ? $data?->job_res : old('job_res') !!}</textarea>
                    @error('job_res')
                        <label class="error">{{ $message }}</label>
                    @enderror
                </div>
                <div class="form-row">
                    <label>Job Requirement<span>*</span></label>
                    <textarea type="text" rows="8" name="job_requirement" placeholder="" id="job_requirement">{!! isset($data) ? $data?->job_requirement : old('job_requirement') !!}</textarea>
                    @error('job_requirement')
                        <label class="error">{{ $message }}</label>
                    @enderror
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>Image<span>*</span></label>
                        <div class="form-select-photo image" @click="selectImage(event)">
                            <div class="select-photo" :class='{ active: image }'>
                                <div class="icon">
                                    <i class='bx bx-cloud-upload'></i>
                                </div>
                                <div class="title">
                                    <p>@lang('adminGlobal.form.image.placeholder')</p>
                                </div>
                            </div>
                            <template x-if="image">
                                <div class="image-view active">
                                    <img x-bind:src="baseImageUrl + image" alt="">
                                </div>
                            </template>
                            <input type="hidden" x-model="image" name="image" autocomplete="off"
                                role="presentation">
                        </div>
                        @error('image')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>Submit</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
    @include('admin::file-manager.popup')
@stop

@section('script')
    <script>
        const header = {
            headers: {
                "Content-Type": "application/x-www-form-urlencoded;charset=utf-8",
                Accept: "application/json",
            },
            responseType: "json",
        };
        document.addEventListener('alpine:init', () => {
            Alpine.data("xComponent", () => ({
                baseImageUrl: "{{ asset('file_manager') }}",
                image: "",
                position_id: "",
                post_date: "",
                close_date: "",
                number_of_day: "",
                start_month: "",
                to_month: "",
                amount: 0,
                position: {
                    "id": "",
                    "text": ""
                },
                sector: {
                    "id": "",
                    "text": ""
                },
                placement_type: {
                    "id": "",
                    "text": ""
                },
                placement_typeL_list: [],
                monthList: [],
                async init() {
                    const data = @json($data ?? '');
                    this.image = data?.image ?? `{{ old('image') }}`;
                    this.position.id = data?.position_id ?? `{{ old('position_id') }}`;
                    this.position.text = data?.pos_title ?? `{{ old('position_text') }}`;

                    this.sector.id = data?.sector_id ?? `{{ old('sector_id') }}`;
                    this.sector.text = data?.sector_title ?? `{{ old('sector_text') }}`;
                    this.placement_type.id = data?.placement_type_id ??
                        `{{ old('placement_type_id') }}`;

                    this.start_month = data?.start_month ?? `{{ old('start_month') }}`;
                    this.to_month = data?.to_month ?? `{{ old('to_month') }}`;

                    $select2Data('#position_id', this.position.id, this.position.text);
                    $select2Data('#sector_id', this.sector.id, this.sector.text);
                    this.number_of_day = data?.number_of_day ?? `{{ old('number_of_day') }}`;
                    this.close_date = data?.close_date ?? `{{ old('close_date') }}`;

                    this.monthList = @json(config('dummy.months'));

                    await $fetchData('/admin/select/placement-type', (res) => {
                        this.placement_typeL_list = res?.data;
                    });

                    $(`#start_month`).select2();
                    $(`#to_month`).select2();
                    $(`#placement_type_id`).select2();
                    $(`#status`).select2();

                    //Date
                    $("#post_date").datepicker({
                        changeYear: true,
                        gotoCurrent: true,
                        yearRange: "-100:+100",
                        dateFormat: "yy-mm-dd",
                        onSelect: (select) => {
                            $('#close_date').datepicker('option', 'minDate', select)
                            this.CalGetCloseDate();
                        }
                    });
                    $("#close_date").datepicker({
                        changeYear: true,
                        gotoCurrent: true,
                        yearRange: "-100:+100",
                        dateFormat: "yy-mm-dd",
                        onSelect: (select) => {
                            $('#post_date').datepicker('option', 'maxDate', select)
                        }
                    });

                    //Editor
                    tinymce.init({
                        relative_urls: false,
                        remove_script_host: false,
                        convert_urls: false,
                        forced_root_block: '', // Disable automatic paragraph creation
                        content_style: "p { margin: 5px 0; }", // Adjust the margin in the editor
                        selector: 'textarea#job_des , textarea#job_requirement, textarea#job_res',
                        plugins: [
                            'advlist', 'autolink', 'lists', 'link', 'image',
                            'charmap', 'preview', 'anchor', 'searchreplace',
                            'visualblocks',
                            'code', 'fullscreen', 'insertdatetime', 'media', 'table',
                            'wordcount'
                        ],
                        toolbar: 'fullscreen  |customEmojis | bold italic underline | addImage media link | numlist bullist | styles | alignleft aligncenter alignright alignjustify | outdent indent',
                        setup: (editor) => {
                            editor.ui.registry.addButton('addImage', {
                                text: 'Image',
                                icon: 'image',
                                onAction: () => {
                                    fileManager({
                                        multiple: true,
                                        afterClose: (result,
                                            baseDes) => {
                                            if (result && result
                                                .length > 0) {
                                                result.map((
                                                    file
                                                    ) => {
                                                    const
                                                        img =
                                                        editor
                                                        .dom
                                                        .createHTML(
                                                            'img', {
                                                                src: baseDes +
                                                                    file
                                                                    .path,
                                                                style: 'width:100%;'
                                                            }
                                                        );
                                                    editor
                                                        .insertContent(
                                                            img
                                                        );
                                                });
                                            }
                                        }
                                    });
                                }
                            });

                            // Custom Emoji Menu Button
                            editor.ui.registry.addMenuButton('customEmojis', {
                                text: '✅',
                                tooltip: 'Insert Emoji',
                                fetch: (callback) => {
                                    var items = [{
                                            type: 'menuitem',
                                            text: '👍',
                                            onAction: () => {
                                                editor
                                                    .insertContent(
                                                        '👍');
                                            }
                                        },
                                        {
                                            type: 'menuitem',
                                            text: '🎉',
                                            onAction: () => {
                                                editor
                                                    .insertContent(
                                                        '🎉');
                                            }
                                        },
                                        {
                                            type: 'menuitem',
                                            text: '✅',
                                            onAction: () => {
                                                editor
                                                    .insertContent(
                                                        '✅');
                                            }
                                        },
                                        {
                                            type: 'menuitem',
                                            text: '✔︎',
                                            onAction: () => {
                                                editor
                                                    .insertContent(
                                                        '✔︎');
                                            }
                                        },
                                    ];
                                    callback(items);
                                }
                            });
                        }
                    });

                },
                fetchSelectPosition() {
                    $(`#position_id`).select2({
                        placeholder: `Select Position`,
                        ajax: {
                            url: '{{ route('admin-select-position') }}',
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
                        this.position = Obj;
                        this.sector = "";
                    }).on('select2:close', async (eventClose) => {
                        const ID = eventClose?.target?.value ?? "";
                        if (this.position.id != ID) {
                            $select2Data('#sector_id')
                        }

                    });
                },
                fetchSelectSector() {
                    $(`#sector_id`).select2({
                        placeholder: `Select Sector`,
                        ajax: {
                            url: '{{ route('admin-select-sector') }}',
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
                    });
                },
                selectImage() {
                    fileManager({
                        multiple: false,
                        afterClose: (data, basePath) => {
                            if (data?.length > 0) {
                                this.image = data[0].path;
                            }
                        }
                    })
                },
                CalGetCloseDate() {
                    this.post_date = this.$refs?.post_date.value;
                    this.close_date = FindEnDate(this.post_date, this.number_of_day);
                }
            }));
        });
    </script>
@stop
