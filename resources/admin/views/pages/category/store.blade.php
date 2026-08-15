@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-'.$routeName.'-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ $id ? 'Update Category' : 'Create Category' }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Name <span>*</span> </label>
                        <input type="text" name="name" value="{!! request('id') ? $data?->name : old('name') !!}" placeholder="Enter name ...">
                        <i class='bx bx-font-family'></i>
                        @error('name')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>@lang('adminGlobal.form.status.label')<span>*</span></label>
                        <select name="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (request('id') && $data->status == $key) || old('status') == $key ? 'selected' : '' !!}>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>Submit</span>
                    </button>
                    <button type="submit" name="save_opt" value="save_new" color="success">
                        <i data-feather="save"></i>
                        <span>Save & New</span>
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
