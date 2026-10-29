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

                {{-- Profile Image --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('customer.form.profile.label') }}</label>
                        <div class="form-select-photo image" @click="selectImage(event)">
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
                                    <img :src="profileSrc()" alt="">
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
                init() {
                    const data = @json($data ?? '');
                    this.profile = data?.profile ?? `{{ old('profile') }}`;
                    if ($(this.$root).find('#status').length) {
                        $(this.$root).find('#status').select2();
                    }
                },
                profileSrc() {
                    if (!this.profile) return '';
                    if (this.profile.startsWith('http://') || this.profile.startsWith('https://')) {
                        return this.profile;
                    }
                    return this.profile.startsWith('/') 
                        ? this.baseImageUrl + this.profile 
                        : this.baseImageUrl + '/' + this.profile;
                },
                selectImage() {
                    fileManager({
                        multiple: false,
                        afterClose: (data, basePath) => {
                            if (data && data.length > 0) {
                                this.profile = data[0].path;
                            }
                        }
                    });
                }
            }));
        });
    </script>
@stop
