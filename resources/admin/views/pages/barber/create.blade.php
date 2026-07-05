@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin" x-data="xData">
        {{-- <div class="form-bg"></div> --}}
        {{-- <form id="form" class="form-wrapper" action="{!! route('admin-barber-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-barber-list', 1) !!}"></i>
                    {!! request('id') ? 'Update Barber' : 'Create Barber' !!}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>Barber ID<span>*</span> </label>
                        <input type="text" name="number_id" value="{!! isset($number_id) && $number_id && request('id') ? $data->number_id : $number_id !!}" placeholder="Barber ID">
                        @error('number_id')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>Shop <span>*</span></label>
                        <select name="shop_id">
                            @foreach ($shops as $value)
                                <option value="{!! $value->id !!}" {!! (request('id') && $data->shop_id == $value->id) || old('shop_id') == $value->id ? 'selected' : '' !!}>{!! $value->name !!}
                                </option>
                            @endforeach
                        </select>
                        @error('shop_id')
                            <span class="error">@lang('message.' . $message)</span>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>Name<span>*</span> </label>
                        <input type="text" name="name" value="{!! request('id') ? $data->name : old('name') !!}" placeholder="Name">
                    </div>
                    <div class="form-row">
                        <label>Gender <span>*</span></label>
                        <select name="gender">
                            <option value="F" {!! (request('id') && $data->gender == 'F') || old('gender') == 'F' ? 'selected' : '' !!}>F</option>
                            <option value="M" {!! (request('id') && $data->gender == 'M') || old('gender') == 'M' ? 'selected' : '' !!}>M</option>
                        </select>
                        @error('gender')
                            <span class="error">@lang('message.' . $message)</span>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>Phone<span>*</span> </label>
                        <input type="text" name="phone" value="{!! request('id') ? $data->phone : old('phone') !!}" placeholder="Phone">
                    </div>
                    <div class="form-row">
                        <label>DOB</label>
                        <input type="text" name="dob" value="{!! request('id') ? $data->dob : old('dob') !!}" placeholder="DOB"
                            id="dob" autocomplete="off">
                        @error('dob')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row">
                        <label>Commission %</label>
                        <input type="number" name="commission" value="{!! request('id') ? $data->commission : old('commission') !!}" placeholder="Commission">
                        @error('commission')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    @if (!request('id'))
                        <div class="form-row">
                            <label>Wallet $</label>
                            <input type="number" name="wallet" value="{!! request('id') ? $data->wallet : old('wallet') !!}" placeholder="Wallet">
                            @error('wallet')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>Address</label>
                        <textarea placeholder="Address" name="address" row="3">{!! request('id') ? $data->address : old('address') !!}</textarea>
                        @error('address')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                @if (!request('id'))
                    <div class="row-2">
                        <div class="form-row">
                            <label>Password<span>*</span> </label>
                            <input type="password" name="password" placeholder="Password" autocomplete="new-password">
                        </div>
                        <div class="form-row">
                            <label>Confirm Password<span>*</span> </label>
                            <input type="password" name="confirm_password" placeholder="Confirm Password">
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="form-row">
                        <label>Profile</label>
                        <div class="form-select-photo image">
                            <div class="select-photo {!! request('id') && isset($data) && $data->image != null ? 'active' : '' !!}">
                                <div class="icon">
                                    <i data-feather="image"></i>
                                </div>
                                <div class="title">
                                    <span>Profile</span>
                                </div>
                            </div>
                            <div class="image-view {!! request('id') && isset($data) && $data->image != null ? 'active' : '' !!}">
                                <img src="{!! request('id') && isset($data) && $data->image != null ? asset('file_manager' . $data->image) : null !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                            <input type="text" name="image" s-click-fn="selectImage(event)" autocomplete="off"
                                role="presentation">
                            <input type="hidden" name="tmp_file" value="{!! request('id') && isset($data) && $data->image != null ? $data->image : '' !!}">
                        </div>
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>@lang('user.form.button.submit')</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-barber-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>@lang('user.form.button.cancel')</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form> --}}
        {{-- <div class="head-title">
            <div class="left">
                <h1>Create Shop</h1>
                <ul class="breadcrumb">
                    <li>
                        <a href="#">Listing</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="#">Create</a>
                    </li>
                </ul>
            </div>
        </div> --}}
        <div class="formLayout">
            <div class="formHeader">
                <a href="#">
                    <i class='bx bx-arrow-back'></i>
                </a>
                <span>Create Shop</span>
            </div>
            <div class="formControl">
                <div class="formLeft form">
                    <div class="itemFormGroup row2">
                        <div class="formItem">
                            <label class="valid">Name</label>
                            <input type="text" placeholder="Enter name ..." />
                            {{-- <span>សូមបញ្ចូលរថយន្ដម៉ាក</span> --}}
                        </div>
                        <div class="formItem">
                            <label>Name</label>
                            <input type="text" placeholder="Enter name ..." />
                            {{-- <span>សូមបញ្ចូលរថយន្ដម៉ាក</span> --}}
                        </div>
                    </div>
                    <div class="itemFormGroup row2">
                        <div class="formItem">
                            <label>Name</label>
                            <select id="js-choice" class="js-choice" multiple>
                                <option value="">This is a placeholder</option>
                                <option value="1">This is a placeholder1</option>
                                <option value="2" selected>This is a placeholder2</option>
                            </select>
                        </div>
                    </div>
                    <div class="itemFormGroup row2">
                        <div class="formItem">
                            <label class="valid">Name</label>
                            <input type="text" placeholder="Enter name ..." />
                            <span>សូមបញ្ចូលរថយន្ដម៉ាក</span>
                        </div>
                        <div class="formItem">
                            <label>Name</label>
                            <input type="text" placeholder="Enter name ..." />
                            <span>សូមបញ្ចូលរថយន្ដម៉ាក</span>
                        </div>
                    </div>
                    <div class="itemFormGroup row2">
                        <div class="formItem">
                            <label class="valid">Name</label>
                            <input type="text" placeholder="Enter name ..." />
                            <span>សូមបញ្ចូលរថយន្ដម៉ាក</span>
                        </div>
                        <div class="formItem">
                            <label>Name</label>
                            <input type="text" placeholder="Enter name ..." />
                            <span>សូមបញ្ចូលរថយន្ដម៉ាក</span>
                        </div>
                    </div>
                    <div class="itemFormGroup row2">
                        <div class="formItem">
                            <label class="valid">Name</label>
                            <div class="form-select-photo image"
                                @click="formData?.disable == false ? selectImage(event):''">
                                <div class="select-photo" :class='{ active: formData?.image }'>
                                    <div class="icon">
                                        <i class='bx bx-image-alt'></i>
                                    </div>
                                    <div class="title">
                                        <p>Choose upload</p>
                                    </div>
                                </div>
                                <template x-if="formData?.image">
                                    <div class="image-view active">
                                        <img x-bind:src="baseImageUrl + formData?.image" alt="">
                                    </div>
                                </template>
                                <input type="hidden" x-model="formData.image" autocomplete="off" role="presentation"
                                    :disabled="formData?.disable">
                            </div>
                            <span>សូមបញ្ចូលរថយន្ដម៉ាក</span>
                        </div>
                        <div class="formItem">
                            <label>Name</label>
                            <input type="text" placeholder="Enter name ..." />
                            <span>សូមបញ្ចូលរថយន្ដម៉ាក</span>
                        </div>
                    </div>

                    {{-- <div class="formBlock">
                        <div class="label">Shop information</div>
                        <span class="span">:</span>
                        <div class="formBlockControl">
                            <div class="formItem">
                                <div class="form-select-photo image"
                                    @click="formData?.disable == false ? selectImage(event):''">
                                    <div class="select-photo" :class='{ active: formData?.image }'>
                                        <div class="icon">
                                            <i data-feather="image"></i>
                                        </div>
                                        <div class="title">
                                            <span>Choose upload</span>
                                        </div>
                                    </div>
                                    <template x-if="formData?.image">
                                        <div class="image-view active">
                                            <img x-bind:src="baseImageUrl + formData?.image" alt="">
                                        </div>
                                    </template>
                                    <input type="hidden" x-model="formData.image" autocomplete="off"
                                        role="presentation" :disabled="formData?.disable">
                                </div>
                            </div>
                            <div class="formItem">
                                <label>Name</label>
                                <input type="text" name="name" />
                                <span class="error">Error name</span>
                            </div>
                            <div class="formItem">
                                <label>Name</label>
                                <input type="text" name="name" />
                                <span class="error">Error name</span>
                            </div>
                        </div>
                    </div>
                    <div class="formBlock">
                        <div class="label">Shop information</div>
                        <span class="span">:</span>
                        <div class="formBlockControl">
                            <div class="formItem">
                                <label>Name</label>
                                <input type="text" name="name" />
                                <span class="error">Error name</span>
                            </div>
                            <div class="formItem">
                                <label>Name</label>
                                <input type="text" name="name" />
                                <span class="error">Error name</span>
                            </div>
                            <div class="formItem">
                                <label>Name</label>
                                <input type="text" name="name" />
                                <span class="error">Error name</span>
                            </div>
                        </div>
                    </div>
                    <div class="formBlock">
                        <div class="label">Shop information</div>
                        <span class="span">:</span>
                        <div class="formBlockControl">
                            <div class="formItem">
                                <label>Name</label>
                                <input type="text" name="name" />
                                <span class="error">Error name</span>
                            </div>
                            <div class="formItem">
                                <label>Name</label>
                                <input type="text" name="name" />
                                <span class="error">Error name</span>
                            </div>
                            <div class="formItem">
                                <label>Name</label>
                                <input type="text" name="name" />
                                <span class="error">Error name</span>
                            </div>
                        </div>
                    </div> --}}
                </div>
                {{-- <div class="formRight form"></div> --}}
            </div>
            <div class="formFooter">
                <div class="btnActionForm">
                    <button class="submit"><i class='bx bx-save'></i>Submit</button>
                    <a href="#">
                        <button class="cancel"><i class='bx bx-x'></i>Cancel</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @include('admin::file-manager.popup')
@stop

@section('script')
    <script lang="ts">
        $(document).ready(function() {
            $validator("#form", {
                number_id: {
                    required: true,
                },
                name: {
                    required: true,
                },
                gender: {
                    required: true,
                    nick_name: true,
                },
                phone: {
                    required: true,
                    phone: true,
                },
                @if (!request('id'))
                    password: {
                        required: true,
                    },
                    confirm_password: {
                        required: true,
                        match: "password"
                    },
                @endif
                status: {
                    required: true,
                },
            });

            $("#dob").datepicker({
                changeYear: true,
                // gotoCurrent: true,
                // yearRange: "-1:+1",
                dateFormat: "yy-mm-dd",
            });
        });

        function selectImage(e) {
            fileManager({
                multiple: false,
                afterClose: (data, basePath) => {
                    if (data?.length > 0) {
                        const parent = e.target.closest('.form-select-photo');
                        e.target.value = data[0].path;
                        parent
                            .querySelector('.select-photo')
                            .classList.add('active');
                        parent
                            .querySelector('.image-view')
                            .classList
                            .add('active');
                        parent
                            .querySelector('.image-view')
                            .childNodes[0]
                            .nextElementSibling
                            .setAttribute('src', basePath + data[0].path);
                    }
                }
            })
        }
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('xData', () => ({
                loading: false,
                dataError: null,
                garage: Object(),
                formData: {
                    name: null,
                    garage_id: null,
                    brand: null,
                    model: null,
                    year: null,
                    id_plate: null,
                    number_input_counter: null,
                    number_out_counter: null,
                    image: null,
                    amount: null,
                    member_id: null,
                    status: 1,
                    user_id: ``,
                    disable: false
                },
                btnSubmit: 'Save',
                baseImageUrl: "{{ asset('file_manager') }}",
                init() {
                    console.log('shopCreate');
                    // Pass single element
                    let element = document.querySelector('.js-choice');

                    var choices = new Choices(element, {
                        silent: false,
                        items: [],
                        choices: [{
                                value: 'One',
                                label: 'Label One',
                                selected: true
                            },
                            {
                                value: 'Two',
                                label: 'Label Two',
                                selected: true
                            },
                            {
                                value: 'Four',
                                label: 'Label Four',
                                selected: true
                            },
                            {
                                value: 'Five',
                                label: 'Label Five',
                                selected: true
                            },
                            {
                                value: 'Three',
                                label: 'Label Three',
                                selected: true
                            },
                            {
                                value: 'Three',
                                label: 'Label Three',
                            },
                        ],
                        renderChoiceLimit: -1,
                        maxItemCount: -1,
                        addItems: true,
                        addItemFilter: null,
                        removeItems: true,
                        removeItemButton: true,
                        editItems: false,
                        allowHTML: true,
                        duplicateItemsAllowed: true,
                        delimiter: ',',
                        paste: true,
                        searchEnabled: true,
                        searchChoices: true,
                        searchFloor: 1,
                        searchResultLimit: 4,
                        searchFields: ['label', 'value'],
                        position: 'auto',
                        resetScrollPosition: true,
                        shouldSort: true,
                        shouldSortItems: false,
                        sorter: () => {},
                        placeholder: true,
                        placeholderValue: null,
                        searchPlaceholderValue: null,
                        prependValue: null,
                        appendValue: null,
                        renderSelectedChoices: 'auto',
                        loadingText: 'Loading...',
                        noResultsText: 'No results found',
                        noChoicesText: 'No choices to choose from',
                        itemSelectText: 'Press to select',
                        uniqueItemText: 'Only unique values can be added',
                        customAddItemText: 'Only values matching specific conditions can be added',
                        addItemText: (value) => {
                            return `Press Enter to add <b>"${value}"</b>`;
                        },
                        maxItemText: (maxItemCount) => {
                            return `Only ${maxItemCount} values can be added`;
                        },
                        valueComparer: (value1, value2) => {
                            return value1 === value2;
                        },
                        classNames: {
                            containerOuter: 'choices',
                            containerInner: 'choices__inner',
                            input: 'choices__input',
                            inputCloned: 'choices__input--cloned',
                            list: 'choices__list',
                            listItems: 'choices__list--multiple',
                            listSingle: 'choices__list--single',
                            listDropdown: 'choices__list--dropdown',
                            item: 'choices__item',
                            itemSelectable: 'choices__item--selectable',
                            itemDisabled: 'choices__item--disabled',
                            itemChoice: 'choices__item--choice',
                            placeholder: 'choices__placeholder',
                            group: 'choices__group',
                            groupHeading: 'choices__heading',
                            button: 'choices__button',
                            activeState: 'is-active',
                            focusState: 'is-focused',
                            openState: 'is-open',
                            disabledState: 'is-disabled',
                            highlightedState: 'is-highlighted',
                            selectedState: 'is-selected',
                            flippedState: 'is-flipped',
                            loadingState: 'is-loading',
                            noResults: 'has-no-results',
                            noChoices: 'has-no-choices'
                        },
                        fuseOptions: {
                            includeScore: true
                        },
                        labelId: '',
                        callbackOnInit: (e) => {
                            console.log(e, 'calbackiifsuu2u2u4u24');
                        },
                        callbackOnCreateTemplates: null
                    });
                    // setChoices((callback) => {
                    //     console.log(callback, 'dadada');
                    //     // event.target.parentNode.querySelector('input').focus();
                    //     return fetch(
                    //             'https://api.discogs.com/artists/83080/releases?token=QBRmstCkwXEvCjTclCpumbtNwvVkEzGAdELXyRyW'
                    //         )
                    //         .then((res) => {
                    //             // console.log(res.json(), 'resrwrwrw');
                    //             return res.json();
                    //         })
                    //         .then((data) => {
                    //             console.log(data, 'fsysyyssy');
                    //             return data.releases.map((release) => {
                    //                 return {
                    //                     label: release.title,
                    //                     value: release.title
                    //                 };
                    //             });
                    //         });
                    // });
                    let myTimeOut = null;
                    element.addEventListener('search', async (event) => {
                        event.target.parentNode.querySelector('input').focus();
                        clearTimeout(myTimeOut);
                        myTimeOut = setTimeout(() => {
                            if (event.detail.value) {
                                choices.setChoices((callback) => {
                                    return fetch(
                                            'https://randomuser.me/api/?results=20&q=' +
                                            event.detail.value
                                        )
                                        .then((response) => {

                                            return response.json();
                                        })
                                        .then((data) => {
                                            console.log(data,
                                                'datadata');
                                            return data.results.map((
                                                user) => {
                                                return {
                                                    value: user
                                                        .login
                                                        .uuid,
                                                    label: user
                                                        .name
                                                        .first
                                                };
                                            });
                                        });
                                });
                            }
                            // choices.setChoices(async () => {
                            //         return this.doSearch(event.detail
                            //         .value);
                            //     })
                            //     .then(() => {
                            //         event.target.parentNode.querySelector('input').focus();
                            //     });
                        }, 600);
                        console.log(event.detail.value, '33333');
                        //let data = await doSearch(e.detail.value);
                        // .then(res => {
                        //     return res.json();
                        // });

                        //choice.setChoices(data, 'value', 'label', true);
                    });

                    // select.addEventListener('search', (event) => {
                    //     console.log(event.detail.valuem,
                    //         'event.detail.valueevent.detail.valueevent.detail.value');
                    //     if (event.detail.value) {
                    //         choices.setChoices((callback) => {
                    //             return fetch(
                    //                     // query doesnt work because the external api doesnt support it.. now its random..
                    //                     'https://randomuser.me/api/?results=20&q=' +
                    //                     event.detail.value
                    //                 )
                    //                 .then((response) => {
                    //                     return response.json();
                    //                 })
                    //                 .then((data) => {
                    //                     return data.results.map((user) => {
                    //                         return {
                    //                             value: user.login.uuid,
                    //                             label: user.name.first
                    //                         };
                    //                     });
                    //                 });
                    //         })
                    //     }
                    // });

                    // choices.passedElement.element.addEventListener(
                    //     'search',
                    //     _.debounce(async e => {
                    //         console.log(e, '353535');
                    //         const data = await jsonFetch(
                    //             `${this.dataset.search}?q=${encodeURIComponent(e.detail.value)}`
                    //         )
                    //         // this.choices.setChoices(data, this.dataset.value || 'value',
                    //         //     this.dataset.label || 'label', true)
                    //     }, 400)
                    // );
                    // choices.Ajax(function(callback) {
                    //     fetch(url)
                    //         .then(function(response) {
                    //             response.json().then(function(data) {
                    //                 callback(data, 'value', 'label');
                    //             });
                    //         })
                    //         .catch(function(error) {
                    //             console.log(error);
                    //         });
                    // });
                    // choices.setChoices(
                    //     [{
                    //             value: 'One',
                    //             label: 'Label One',
                    //         },
                    //         {
                    //             value: 'Two',
                    //             label: 'Label Two',
                    //             selected: true
                    //         },
                    //         {
                    //             value: 'Four',
                    //             label: 'Label Four'
                    //         },
                    //         {
                    //             value: 'Five',
                    //             label: 'Label Five',
                    //             selected: true
                    //         },
                    //         {
                    //             value: 'Three',
                    //             label: 'Label Three'
                    //         },
                    //     ],
                    //     'value',
                    //     'label',
                    //     false,
                    // );
                },
                closeUp() {
                    this.dataError = null;
                    Alpine.store('carMember').active = false;
                },
                selectImage() {
                    fileManager({
                        multiple: false,
                        afterClose: (data, basePath) => {
                            if (data?.length > 0) {
                                this.formData.image = data[0].path;
                            }
                        }
                    })
                }
            }));
        });
    </script>
@stop
