@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => ''])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-user-save-password', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-user-list', 1) !!}"></i>
                    @lang('user.form.title.change_password') ( {!! $data->name !!} )
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>@lang('user.form.new_password.label')<span>*</span> </label>
                        <input type="password" name="password" placeholder="@lang('user.form.new_password.placeholder')">
                        @error('password')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>@lang('user.form.password_confirmation.label')<span>*</span> </label>
                        <input type="password" name="confirm_password" placeholder="@lang('user.form.password_confirmation.label')">
                        @error('confirm_password')
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
