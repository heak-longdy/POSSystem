@extends('admin::shared.layout')
@section('layout')
    <style>
        #ex1-content {
            border: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
            margin-bottom: 0 !important;
        }
    </style>
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-page-save', ['id' => $data?->id, 'type' => request('type')]) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3 style="text-transform: capitalize;">
                    About Us
                </h3>
            </div>
            @csrf
            <div class="form-body" x-data="{ tabSta: 'km' }">
                <div class="tab-content" id="ex1-content">
                    <div class="tab-pane fade show active" id="ex1-pills-1" role="tabpanel" aria-labelledby="ex1-tab-1">
                        <div class="row">
                            <div class="form-row">
                                <label>Title</label>
                                <input type="text" name="title" value="{!!$data->title!!}"
                                    placeholder="">
                                @error('title')
                                    <label class="error">{{ $message }}</label>
                                @enderror
                            </div>
                            <div class="form-row">
                                <label>Descriptions<span>*</span></label>
                                <textarea type="text" rows="8" name="content" placeholder="" id="content_en"
                                    style="height: 300px;">{!!$data->content!!}</textarea>
                                @error('description')
                                    <label class="error">{{ $message }}</label>
                                @enderror
                            </div>
                        </div>
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
                </div>
                <div class="form-button">
                    @canany(['about-update', 'contact-view', 'term-condition-member-view','term-condition-garage-view'])
                        <button type="submit" color="primary">
                            <i data-feather="save"></i>
                            <span>Save</span>
                        </button>
                    @endcan
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
    @include('admin::file-manager.popup')
@endsection

@section('script')
    <script lang="ts">
        $(document).ready(function() {
            $validator("#form", {
                title: {
                    required: true,
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            tinymce.init({
                relative_urls: false,
                remove_script_host: false,
                convert_urls: false,
                selector: 'textarea#content_en , textarea#content_km , textarea#content_zh',
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'wordcount'
                ],
                toolbar: 'fullscreen | bold italic underline | addImage media link | numlist bullist | styles | alignleft aligncenter alignright alignjustify | outdent indent ',
                setup: function(editor) {
                    editor.ui.registry.addButton('addImage', {
                        text: 'Image',
                        icon: 'image',
                        onAction: () => {
                            fileManager({
                                multiple: true,
                                afterClose: (result, baseDes) => {
                                    if (result && result.length > 0) {
                                        result.map((file) => {
                                            const img = editor.dom
                                                .createHTML(
                                                    'img', {
                                                        src: baseDes +
                                                            file
                                                            .path,
                                                        style: 'width:100%;'
                                                    });
                                            editor.insertContent(img);
                                        });
                                    }
                                }
                            });
                        }
                    });

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
@endsection
