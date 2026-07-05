@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-user-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-user-list', 1) !!}"></i>
                    {{ request('id') ? 'Update User' : 'Create User' }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-3">
                    <div class="form-row">
                        <label>Name <span>*</span> </label>
                        <input type="text" name="name" value="{!! request('id') ? $data?->name : old('name') !!}" placeholder="Enter name ...">
                        @error('name')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-3">
                    <div class="form-row">
                        <label>Email <span>*</span> </label>
                        <input type="text" name="email" value="{!! request('id') ? $data?->email : old('email') !!}" placeholder="Enter email ...">
                        @error('email')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>@lang('adminGlobal.form.status.label')<span>*</span></label>
                        <select name="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (request('id') && $data->status == $key) || old('status') == $key ? 'selected' : '' !!}>{{ $item }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                @if (!request('id'))
                    <div class="row-3">
                        <div class="form-row">
                            <label>@lang('user.form.password.label')<span>*</span> </label>
                            <input type="password" name="password" placeholder="@lang('user.form.password.placeholder')"
                                autocomplete="new-password">
                            @error('password')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                        <div class="form-row">
                            <label>@lang('user.form.password_confirmation.label')<span>*</span> </label>
                            <input type="password" name="confirm_password" placeholder="@lang('user.form.password_confirmation.placeholder')" autocomplete="new-password">
                            @error('confirm_password')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                    </div>
                @endif
                <div class="row-2">
                    <div class="form-row">
                        <label>Image</label>
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
                        <span>Submit</span>
                    </button>
                    <button type="submit" name="save_opt" value="save_new" color="success">
                        <i data-feather="save"></i>
                        <span>Save & New</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-user-list', 1) !!}">
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
                    this.image = data?.image ?? "";
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
            }));
        });
    </script>
@stop
