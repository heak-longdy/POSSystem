@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-'.$routeName.'-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ $id ? __('product.form.title.update') : __('product.form.title.create') }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('product.form.category.label') }}<span>*</span></label>
                        <div class="select2Group">
                            <select name="category_id" class="SelectCategory" id="category_id" x-init="fetchSelectCategory()">
                                <option value=""> {{ __('product.form.category.placeholder') }}</option>
                            </select>
                            <div class="select2Reset" x-show="category?.id" @click="$select2Data('#category_id')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path
                                        d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" x-model="category.text" name="category_text">
                        @error('category_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>{{ __('product.form.uom.label') }}<span>*</span></label>
                        <div class="select2Group">
                            <select name="uom_id" class="SelectUom" id="uom_id" x-init="fetchSelectUOM()">
                                <option value=""> {{ __('product.form.uom.placeholder') }}</option>
                            </select>
                            <div class="select2Reset" x-show="uom?.id" @click="$select2Data('#uom_id')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path
                                        d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" x-model="uom.text" name="uom_text">
                        @error('uom_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="form-row iconInput">
                        <label>{{ __('product.form.name.label') }} <span>*</span> </label>
                        <input type="text" name="name" value="{!! request('id') ? $data?->name : old('name') !!}" placeholder="{{ __('product.form.name.placeholder') }}">
                        <i class='bx bx-font-family'></i>
                        @error('name')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('product.form.cost.label') }}</label>
                        <input type="number" step="0.01" name="cost" value="{!! request('id') ? $data?->cost : old('cost') !!}" placeholder="{{ __('product.form.cost.placeholder') }}">
                        <i class='bx bx-dollar'></i>
                        @error('cost')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('product.form.price.label') }}</label>
                        <input type="number" step="0.01" name="price" value="{!! request('id') ? $data?->price : old('price') !!}" placeholder="{{ __('product.form.price.placeholder') }}">
                        <i class='bx bx-dollar'></i>
                        @error('price')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('product.form.status.label') }}<span>*</span></label>
                        <select name="status">
                            <option value="1" {!! (request('id') && $data->status == 1) || old('status') == 1 ? 'selected' : '' !!}>{{ __('product.form.status.active') }}</option>
                            <option value="2" {!! (request('id') && $data->status == 2) || old('status') == 2 ? 'selected' : '' !!}>{{ __('product.form.status.disable') }}</option>
                        </select>
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('product.form.photo.label') }}</label>
                        <div class="form-select-photo image" @click="selectImage(event)">
                            <div class="select-photo" :class='{ active: image }'>
                                <div class="icon">
                                    <i class='bx bx-cloud-upload'></i>
                                </div>
                                <div class="title">
                                    <p>{{ __('product.form.photo.placeholder') }}</p>
                                </div>
                            </div>
                            <template x-if="image">
                                <div class="image-view active">
                                    <img x-bind:src="baseImageUrl + image" alt="">
                                </div>
                            </template>
                            <input type="hidden" x-model="image" name="image" autocomplete="off" role="presentation">
                        </div>
                        @error('image')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>{{ __('global.button.submit') }}</span>
                    </button>
                    <button type="submit" name="save_opt" value="save_new" color="success">
                        <i data-feather="save"></i>
                        <span>{{ __('global.button.save_new') }}</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>{{ __('global.button.cancel') }}</span>
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
                category: {
                    "id": "",
                    "text": ""
                },
                uom: {
                    "id": "",
                    "text": ""
                },
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
                    console.log(data, 'data');
                    this.image = data?.image ?? `{{ old('image') }}`;
                    this.position.id = data?.position_id ?? `{{ old('position_id') }}`;
                    this.position.text = data?.pos_title ?? `{{ old('position_text') }}`;

                    this.sector.id = data?.sector_id ?? `{{ old('sector_id') }}`;
                    this.sector.text = data?.sector_title ?? `{{ old('sector_text') }}`;
                    this.category.id = data?.category_id ?? `{{ old('category_id') }}`;
                    
                    this.category.text = data?.category_title ?? `{{ old('category_text') }}`;
                    this.uom.id = data?.uom_id ?? `{{ old('uom_id') }}`;
                    this.uom.text = data?.uom_title ?? `{{ old('uom_text') }}`;

                    this.placement_type.id = data?.placement_type_id ??
                        `{{ old('placement_type_id') }}`;

                    this.start_month = data?.start_month ?? `{{ old('start_month') }}`;
                    this.to_month = data?.to_month ?? `{{ old('to_month') }}`;

                    $select2Data('#position_id', this.position.id, this.position.text);
                    $select2Data('#sector_id', this.sector.id, this.sector.text);

                    $select2Data('#category_id', this.category.id, this.category.text);
                    $select2Data('#uom_id', this.uom.id, this.uom.text);
                    
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
                fetchSelectUOM() {
                    $(`#uom_id`).select2({
                        placeholder: `{{ __('product.form.uom.placeholder') }}`,
                        ajax: {
                            url: '{{ route('admin-select-uom') }}',
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
                                            text: item?.name || '',
                                            id: item.id
                                        }
                                    })
                                };
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
                        this.uom = Obj
                    }).on('select2:open', (e) => {
                        $select2FocusInputSearch();
                    });
                },
                fetchSelectCategory() {
                    $(`#category_id`).select2({
                        placeholder: `{{ __('product.form.category.placeholder') }}`,
                        ajax: {
                            url: '{{ route('admin-select-category') }}',
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
                                            text: item?.name || '',
                                            id: item.id
                                        }
                                    })
                                };
                            },
                            error: (xhr, status, error) => {
                                console.error('Error fetching categories:', error);
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
                        this.category = Obj
                    }).on('select2:open', (e) => {
                        $select2FocusInputSearch();
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
