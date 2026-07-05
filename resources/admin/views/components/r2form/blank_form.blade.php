@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <span s-click-link="">
                        <i data-feather="arrow-left"></i>
                    </span>
                    Title
                </h3>
            </div>
            @csrf
            <div class="form-body">
                {{-- Code Here --}}
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>Save</span>
                    </button>
                    <button color="danger" type="button" s-click-link="">
                        <i data-feather="x"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
    @include('admin::file-manager.popup')
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $validator("#form", {
                //Code Here
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
@endsection
