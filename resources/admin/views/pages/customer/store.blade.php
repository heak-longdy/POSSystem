@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-'.$routeName.'-save', $id ?: (request('id') ?: ($data?->id ?? ''))) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ ($id || request('id') || isset($data)) ? __('customer.form.title.update') : __('customer.form.title.create') }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                {{-- Code & Name --}}
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('customer.form.code.label') }}</label>
                        <input type="text" name="code" value="{{ old('code', $data?->code ?? '') }}" placeholder="{{ __('customer.form.code.placeholder') }}">
                        <i class='bx bx-barcode'></i>
                        @error('code')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('customer.form.name.label') }} <span>*</span> </label>
                        <input type="text" name="name" value="{{ old('name', $data?->name ?? '') }}" placeholder="{{ __('customer.form.name.placeholder') }}">
                        <i class='bx bx-font-family'></i>
                        @error('name')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Phone & Status --}}
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('customer.form.phone.label') }} <span>*</span> </label>
                        <input type="text" name="phone" value="{{ old('phone', $data?->phone ?? '') }}" placeholder="{{ __('customer.form.phone.placeholder') }}">
                        <i class='bx bx-phone'></i>
                        @error('phone')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>@lang('global.form.status.label') <span>*</span></label>
                        <select name="status" id="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (string) old('status', $data?->status ?? 1) === (string) $key ? 'selected' : '' !!}>
                                    {{ $key == 1 ? __('global.form.status.active') : __('global.form.status.disable') }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Payment Restriction --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('customer.form.payment_restriction.label') }} <span>*</span></label>
                        <select name="payment_restriction" id="payment_restriction">
                            <option value="N" {!! old('payment_restriction', $data?->payment_restriction ?? 'N') === 'N' ? 'selected' : '' !!}>
                                {{ __('customer.form.payment_restriction.no') }}
                            </option>
                            <option value="Y" {!! old('payment_restriction', $data?->payment_restriction ?? 'N') === 'Y' ? 'selected' : '' !!}>
                                {{ __('customer.form.payment_restriction.yes') }}
                            </option>
                        </select>
                        @error('payment_restriction')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row"></div>
                </div>

                {{-- Address --}}
                <div class="form-row">
                    <label>{{ __('customer.form.address.label') }}</label>
                    <textarea name="address" rows="3" placeholder="{{ __('customer.form.address.placeholder') }}">{{ old('address', $data?->address ?? '') }}</textarea>
                    @error('address')
                        <label class="error">{{ $message }}</label>
                    @enderror
                </div>

                {{-- Customer Images (Profile, Image 1, Image 2) --}}
                <div class="row-3">
                    {{-- Profile Image --}}
                    <div class="form-row">
                        <label>{{ __('customer.form.profile.label') }}</label>
                        <div class="form-select-photo image" @click="selectImage('profile')">
                            <div class="select-photo" :class='{ active: profile }'>
                                <div class="icon">
                                    <i class='bx bx-cloud-upload'></i>
                                </div>
                                <div class="title">
                                    <p>@lang('global.form.image.placeholder')</p>
                                </div>
                            </div>
                            <template x-if="profile">
                                <div class="image-view active" style="position: relative;">
                                    <img :src="getImageSrc(profile)" alt="">
                                    <button type="button" @click.stop="profile = ''" title="Remove" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: #fff; width: 26px; height: 26px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;min-width:26px;padding:0;">
                                        <i class='bx bx-x' style="font-size: 18px;"></i>
                                    </button>
                                </div>
                            </template>
                            <input type="hidden" x-model="profile" name="profile" autocomplete="off" role="presentation">
                        </div>
                        @error('profile')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    {{-- Image 1 --}}
                    <div class="form-row">
                        <label>{{ __('customer.form.image_1.label') }}</label>
                        <div class="form-select-photo image" @click="selectImage('image_1')">
                            <div class="select-photo" :class='{ active: image1 }'>
                                <div class="icon">
                                    <i class='bx bx-cloud-upload'></i>
                                </div>
                                <div class="title">
                                    <p>@lang('global.form.image.placeholder')</p>
                                </div>
                            </div>
                            <template x-if="image1">
                                <div class="image-view active" style="position: relative;">
                                    <img :src="getImageSrc(image1)" alt="">
                                    <button type="button" @click.stop="image1 = ''" title="Remove" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: #fff; width: 26px; height: 26px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;min-width:26px;padding:0;">
                                        <i class='bx bx-x' style="font-size: 18px;"></i>
                                    </button>
                                </div>
                            </template>
                            <input type="hidden" x-model="image1" name="image_1" autocomplete="off" role="presentation">
                        </div>
                        @error('image_1')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    {{-- Image 2 --}}
                    <div class="form-row">
                        <label>{{ __('customer.form.image_2.label') }}</label>
                        <div class="form-select-photo image" @click="selectImage('image_2')">
                            <div class="select-photo" :class='{ active: image2 }'>
                                <div class="icon">
                                    <i class='bx bx-cloud-upload'></i>
                                </div>
                                <div class="title">
                                    <p>@lang('global.form.image.placeholder')</p>
                                </div>
                            </div>
                            <template x-if="image2">
                                <div class="image-view active" style="position: relative;">
                                    <img :src="getImageSrc(image2)" alt="">
                                    <button type="button" @click.stop="image2 = ''" title="Remove" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: #fff; width: 26px; height: 26px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;min-width:26px;padding:0;">
                                        <i class='bx bx-x' style="font-size: 18px;"></i>
                                    </button>
                                </div>
                            </template>
                            <input type="hidden" x-model="image2" name="image_2" autocomplete="off" role="presentation">
                        </div>
                        @error('image_2')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>{{ ($id || request('id') || isset($data)) ? __('global.button.update') : __('global.button.submit') }}</span>
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
        document.addEventListener('alpine:init', () => {
            Alpine.data("xComponent", () => ({
                baseImageUrl: "{{ asset('file_manager') }}",
                profile: "",
                image1: "",
                image2: "",
                init() {
                    const data = @json($data ?? '');
                    this.profile = data?.profile ?? `{{ old('profile') }}`;
                    this.image1 = data?.image_1 ?? `{{ old('image_1') }}`;
                    this.image2 = data?.image_2 ?? `{{ old('image_2') }}`;
                    if ($(this.$root).find('#status').length) {
                        $(this.$root).find('#status').select2();
                    }
                },
                getImageSrc(path) {
                    if (!path) return '';
                    if (path.startsWith('http://') || path.startsWith('https://')) {
                        return path;
                    }
                    return path.startsWith('/') 
                        ? this.baseImageUrl + path 
                        : this.baseImageUrl + '/' + path;
                },
                profileSrc() {
                    return this.getImageSrc(this.profile);
                },
                selectImage(field = 'profile') {
                    fileManager({
                        multiple: false,
                        afterClose: (data, basePath) => {
                            if (data && data.length > 0) {
                                if (field === 'image_1') {
                                    this.image1 = data[0].path;
                                } else if (field === 'image_2') {
                                    this.image2 = data[0].path;
                                } else {
                                    this.profile = data[0].path;
                                }
                            }
                        }
                    });
                }
            }));
        });
    </script>
@stop
