@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '','customClass'=>'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-' . $routeName . '-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ request('id') ? 'Update ' . $routeName : 'Create ' . $routeName }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>Title <span>*</span> </label>
                        <input type="text" name="title" value="{!! request('id') ? $data?->title : old('title') !!}" placeholder="Enter title ...">
                        @error('title')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row-3">
                    <div class="form-row wLen1">
                        <label>@lang('global.form.status.label')<span>*</span></label>
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
                <div class="form-row">
                    <label>Descriptions<span>*</span></label>
                    <textarea type="text" rows="8" name="des" placeholder="" id="des">{!! isset($data) ? $data?->des : old('des') !!}</textarea>
                    @error('des')
                        <label class="error">{{ $message }}</label>
                    @enderror
                </div>
                <div class="row-3">
                    <div class="form-row">
                        <label>Image<span>*</span></label>
                        <div class="form-select-photo image" @click="selectImage(event)">
                            <div class="select-photo" :class='{ active: image }'>
                                <div class="icon">
                                    <i class='bx bx-cloud-upload'></i>
                                </div>
                                <div class="title">
                                    <p>@lang('global.form.image.placeholder')</p>
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
                    <button color="danger" type="button" s-click-link="{!! route('admin-'.$routeName.'-list', 1) !!}">
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
                    console.log(data, "data----");
                    tinymce.init({
                        relative_urls: false,
                        remove_script_host: false,
                        convert_urls: false,
                        selector: 'textarea#des',
                        plugins: [
                            'advlist', 'autolink', 'lists', 'link', 'image',
                            'charmap', 'preview',
                            'anchor', 'searchreplace', 'visualblocks', 'code',
                            'fullscreen',
                            'insertdatetime', 'media', 'table', 'wordcount'
                        ],
                        toolbar: 'fullscreen | bold italic underline | addImage media link | numlist bullist | styles | alignleft aligncenter alignright alignjustify | outdent indent ',
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
                        },
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
            }));
        });
    </script>
@stop
