@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-'.$routeName.'-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ $id ? 'Update Pay Note' : 'Create Pay Note' }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <!-- <div class="row-2">
                    <div class="form-row">
                        <label>Customer</label>
                        <div class="select2Group">
                            <select name="customer_id" class="SelectPosition" id="customer_id" x-init="fetchSelectCustomer()">
                                <option value=""> Select Customer</option>
                            </select>
                            <div class="select2Reset" x-show="customer?.id" @click="$select2Data('#customer_id')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path
                                        d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" x-model="customer.text" name="customer_name">
                        @error('customer_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div> -->
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Customer </label>
                        <input type="text" name="des" value="{!! request('id') && $data?->des ? $data?->des : old('des') !!}" placeholder="Enter name ...">
                        <i class='bx bx-font-family'></i>
                        @error('des')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Amount (KHR) </label>
                        <input type="text" name="amount_kh" value="{!! request('id') && $data?->amount_kh ? $data?->amount_kh : old('amount_kh') !!}" placeholder="Enter amount ..." oninput="formatCurrency(this)">
                        <i class='bx bx-registered'></i> 
                        @error('amount_kh')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Amount (USD) </label>
                        <input type="text" name="amount_usd" value="{!! request('id') && $data?->amount_usd ? $data?->amount_usd : old('amount_usd') !!}" placeholder="Enter amount ..." oninput="formatCurrency(this)">
                        <i class='bx bx-dollar'></i> 
                        @error('amount_usd')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <label>Descriptions</label>
                    <textarea type="text" rows="8" name="des_01" placeholder="" id="des">{!! isset($data) ? $data?->des_01 : old('des_01') !!}</textarea>
                    @error('des_01')
                        <label class="error">{{ $message }}</label>
                    @enderror
                </div>
                <div class="form-button">
                    <button type="submit" color="primary" id="btn-submit">
                        <i data-feather="save"></i>
                        <span>Submit</span>
                    </button>
                    <button type="submit" name="save_opt" value="save_new" color="success" id="btn-save-new">
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

        function formatCurrency(input) {
            // Remove non-numeric characters except decimals
            let value = input.value.replace(/[^0-9.]/g, '');

            // Check for multiple decimals
            if ((value.match(/\./g) || []).length > 1) {
                value = value.replace(/\.+$/, "");
            }

            // Split into integer and decimal parts
            let parts = value.split('.');
            let integerPart = parts[0];
            let decimalPart = parts.length > 1 ? '.' + parts[1] : '';

            // Add commas to the integer part
            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            // Combine and set the value
            input.value = integerPart + decimalPart;
        }

        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 's') {
                e.preventDefault();
                if (e.shiftKey) {
                    // Save & New (Cmd+Shift+S / Ctrl+Shift+S)
                    document.getElementById('btn-save-new').click();
                } else {
                    // Save (Cmd+S / Ctrl+S)
                    document.getElementById('btn-submit').click();
                }
            }
        });

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
                customer: {
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
                    this.customer.id = data?.customer_id ?? `{{ old('customer_id') }}`;
                    this.customer.text = data?.customer_title ?? `{{ old('customer_name') }}`;
                    $select2Data('#customer_id', this.customer.id, this.customer.text);

                    //Editor
                    tinymce.init({
                        relative_urls: false,
                        remove_script_host: false,
                        convert_urls: false,
                        forced_root_block: '', // Disable automatic paragraph creation
                        content_style: "p { margin: 5px 0; }", // Adjust the margin in the editor
                        selector: 'textarea#des',
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
                fetchSelectCustomer(){
                    $(`#customer_id`).select2({
                        placeholder: `Select Customer`,
                        ajax: {
                            url: '{{ route('admin-select-customer') }}',
                            dataType: 'json',
                            type: "GET",
                            quietMillis: 50,
                            data: (param) => {
                                return {
                                    search: param.term,
                                    customer_id: this.customer.id
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
                                console.error('Error fetching customer:', error);
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
                        this.customer = Obj;
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
