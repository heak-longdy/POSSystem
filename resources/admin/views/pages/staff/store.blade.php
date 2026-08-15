@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-' . $routeName . '-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ request('id') ? 'Update Staff' : 'Create Staff' }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                {{-- Profile Image --}}
                <div class="row-1">
                    <div class="form-row">
                        <label>Image</label>
                        <div class="image-upload-wrapper" @click="selectImage()" style="cursor: pointer; display: inline-block;">
                            <template x-if="image">
                                <div style="position: relative; width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;">
                                    <img :src="baseImageUrl + image" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            </template>
                            <template x-if="!image">
                                <div style="width: 120px; height: 120px; border-radius: 8px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; background: #f9f9f9; color: #888;">
                                    <span style="font-size: 13px; text-align: center;">Click to select image</span>
                                </div>
                            </template>
                        </div>
                        <input type="hidden" name="image" x-model="image">
                    </div>
                </div>

                {{-- Name & Position --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>Name <span>*</span></label>
                        <input type="text" name="name" value="{!! request('id') ? $data?->name : old('name') !!}" placeholder="Enter staff name ...">
                        @error('name')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label>Position</label>
                        <select name="position_id" id="position_id">
                            <option value="">-- Select Position --</option>
                            @foreach ($positions as $pos)
                                <option value="{{ $pos->id }}" {!! (request('id') && $data?->position_id == $pos->id) || old('position_id') == $pos->id ? 'selected' : '' !!}>
                                    {{ $pos->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('position_id')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Phone Number & Email --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" value="{!! request('id') ? $data?->phone_number : old('phone_number') !!}" placeholder="Enter phone number ...">
                        @error('phone_number')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label>Email</label>
                        <input type="email" name="email" value="{!! request('id') ? $data?->email : old('email') !!}" placeholder="Enter email address ...">
                        @error('email')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Address --}}
                <div class="row-1">
                    <div class="form-row">
                        <label>Address</label>
                        <textarea name="address" rows="3" placeholder="Enter staff address ...">{!! request('id') ? $data?->address : old('address') !!}</textarea>
                        @error('address')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Status --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>@lang('adminGlobal.form.status.label')<span>*</span></label>
                        <select name="status" id="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (request('id') && $data?->status == $key) || old('status') == $key ? 'selected' : '' !!}>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Submit Buttons --}}
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
        document.addEventListener('alpine:init', () => {
            Alpine.data("xComponent", () => ({
                baseImageUrl: "{{ asset('file_manager') }}",
                image: "",
                init() {
                    const data = @json($data ?? '');
                    this.image = data?.image ?? `{{ old('image') }}`;
                    if ($('#position_id').length) {
                        $('#position_id').select2();
                    }
                    if ($('#status').length) {
                        $('#status').select2();
                    }
                },
                selectImage() {
                    fileManager({
                        multiple: false,
                        afterClose: (data, basePath) => {
                            if (data && data.length > 0) {
                                this.image = data[0].path;
                            }
                        }
                    });
                }
            }));
        });
    </script>
@stop
