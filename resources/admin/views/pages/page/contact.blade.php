@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', [
        'header_name' => '',
        'customClass' => 'headerInForm',
    ])
    {{-- <div class="content-wrapper form-admin" id="app" x-data="xIndex">
        <div class="ourServiceLayout">
            <div class="form-admin">
                <div class="form-bg"></div>
                <form id="form" class="form-wrapper" action="{!! route('admin-contact-save', ['id' => $data?->id, 'type' => request('type')]) !!}" method="POST"
                    enctype="multipart/form-data">
                    <div class="form-header">
                        <h3>
                            Contact Us
                        </h3>
                    </div>
                    @csrf
                    <div class="form-body">
                        <div class="row-2">
                            <div class="form-row">
                                <label>Phone <span>*</span></label>
                                <input type="number" name="phone" value="{!! $data->phone !!}" placeholder="">
                            </div>
                            <div class="form-row">
                                <label>Email <span>*</span></label>
                                <input type="text" name="email" value="{!! $data->email !!}" placeholder="">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-row">
                                <label>Address</label>
                                <textarea placeholder="" name="address" row="3">{!! $data->address !!}</textarea>
                                @error('address')
                                    <label class="error">{{ $message }}</label>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-row">
                                <label>Status<span>*</span></label>
                                <select name="status">
                                    <option value="1" {!! (isset($data->status) && $data->status == 1) || old('status') == 1 ? 'selected' : '' !!}> Active</option>
                                    <option value="2" {!! (isset($data->status) && $data->status == 2) || old('status') == 2 ? 'selected' : '' !!}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="row-1">
                            <div class="form-row">
                                <label>Image</label>
                                <div class="form-select-photo image">
                                    <div class="select-photo {!! isset($data) && $data->image != null ? 'active' : '' !!}">
                                        <div class="icon">
                                            <i data-feather="image"></i>
                                        </div>
                                        <div class="title">
                                            <span>Image</span>
                                        </div>
                                    </div>
                                    <div class="image-view {!! isset($data) && $data->image != null ? 'active' : '' !!}">
                                        <img src="{!! isset($data->image) && $data->image != null ? $data->image_url : null !!}"
                                            onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                                    </div>
                                    <input type="text" name="image" s-click-fn="selectImage(event)" autocomplete="off"
                                        role="presentation" value="{{ isset($data->image) ? $data->image : null }}">
                                    <input type="hidden" name="tmp_file"
                                        value="{{ isset($data->image) && $data->image != null ? $data->image : null }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-button">
                            @can('contact-update')
                                <button type="submit" color="primary">
                                    <i data-feather="save"></i>
                                    <span> Save</span>
                                </button>
                            @endcan
                        </div>
                    </div>
                    <div class="form-footer"></div>
                </form>
            </div>
        </div>
    </div> --}}
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-contact-save', ['id' => $data?->id]) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    Contact
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Email (1)</label>
                        <input type="text" name="email_1" value="{!! $data?->id ? $data?->email_1 : old('email_1') !!}" placeholder="Enter email_1 ...">
                        <i class='bx bx-mail-send'></i>
                        @error('email_1')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Email (2)</label>
                        <input type="text" name="email_2" value="{!! $data?->id ? $data?->email_2 : old('email_2') !!}" placeholder="Enter email_2 ...">
                        <i class='bx bx-mail-send'></i>
                        @error('email_2')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Email (3)</label>
                        <input type="text" name="email_3" value="{!! $data?->id ? $data?->email_3 : old('email_3') !!}" placeholder="Enter email_3 ...">
                        <i class='bx bx-mail-send'></i>
                        @error('email_3')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Email (4)</label>
                        <input type="text" name="email_4" value="{!! $data?->id ? $data?->email_4 : old('email_4') !!}" placeholder="Enter email_4 ...">
                        <i class='bx bx-mail-send'></i>
                        @error('email_4')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Office Hour Kh</label>
                        <input type="text" name="office_hour_kh" value="{!! $data?->id ? $data?->office_hour_kh : old('office_hour_kh') !!}" placeholder="Enter office_hour_kh ...">
                        <i class='bx bx-text'></i>
                        @error('office_hour_kh')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Office Hour UK</label>
                        <input type="text" name="office_hour_uk" value="{!! $data?->id ? $data?->office_hour_uk : old('office_hour_uk') !!}" placeholder="Enter office_hour_uk ...">
                        <i class='bx bx-text'></i>
                        @error('office_hour_uk')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Address KH </label>
                        <input type="text" name="address_kh" value="{!! $data?->id ? $data?->address_kh : old('address_kh') !!}" placeholder="Enter address_kh ...">
                        <i class='bx bx-map-alt' ></i>
                        @error('address_kh')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Address UK </label>
                        <input type="text" name="address_uk" value="{!! $data?->id ? $data?->address_uk : old('address_uk') !!}" placeholder="Enter address_uk ...">
                        <i class='bx bx-map-alt' ></i>
                        @error('address_uk')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-3">
                    <div class="form-row iconInput">
                        <label>Phone Number KH</label>
                        <input type="text" name="phone_kh" value="{!! $data?->id ? $data?->phone_kh : old('phone_kh') !!}" placeholder="Enter phone_kh ...">
                        <i class='bx bx-phone' ></i>
                        @error('phone_kh')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Facebook </label>
                        <input type="text" name="facebook" value="{!! $data?->id ? $data?->facebook : old('facebook') !!}" placeholder="Enter facebook ...">
                        <i class='bx bxl-facebook' ></i>
                        @error('facebook')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>YouTube </label>
                        <input type="text" name="youtube" value="{!! $data?->id ? $data?->youtube : old('youtube') !!}" placeholder="Enter youtube ...">
                        <i class='bx bxl-youtube' ></i>
                        @error('youtube')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Link In </label>
                        <input type="text" name="link_in" value="{!! $data?->id ? $data?->link_in : old('link_in') !!}" placeholder="Enter link_in ...">
                        <i class='bx bxl-linkedin' ></i>
                        @error('link_in')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>Instagram </label>
                        <input type="text" name="instagram" value="{!! $data?->id ? $data?->instagram : old('instagram') !!}" placeholder="Enter instagram ...">
                        <i class='bx bxl-instagram' ></i>
                        @error('instagram')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>Submit</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-partner-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@endsection

@section('script')
    <script lang="ts">
        $(document).ready(function() {
            $validator("#form", {
                address: {
                    required: true,
                },
                phone: {
                    required: true,
                },
                email: {
                    required: true,
                },
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
        const header = {
            headers: {
                "Content-Type": "application/x-www-form-urlencoded;charset=utf-8",
                Accept: "application/json",
            },
            responseType: "json",
        };
        document.addEventListener('alpine:init', () => {
            Alpine.data("xComponent", () => ({}));
        });
    </script>
@endsection
