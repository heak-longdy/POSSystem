@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-barber-save-top-up', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-barber-list', 1) !!}"></i>
                    Top Up {!! $data->name !!}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row">
                    <div class="form-row">
                        <label>Amount<span>*</span> </label>
                        <input type="number" name="amount" value="{!! request('id') && $data->amount ? $data->amount : old('amount') !!}" placeholder="" min="0" step="any">
                        @error('amount')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>Remark<span>*</span></label>
                        <textarea placeholder="Remark" name="remark" row="3">{!! request('id') ? $data->remark : old('remark') !!}</textarea>
                        @error('remark')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="form-row">
                        <label>Receipt<span>*</span></label>
                        <div class="form-select-photo image">
                            <div class="select-photo {!! request('id') && isset($data) && $data->image != null ? 'active' : '' !!}">
                                <div class="icon">
                                    <i data-feather="image"></i>
                                </div>
                                <div class="title">
                                    <span>Receipt</span>
                                </div>
                            </div>
                            <div class="image-view {!! request('id') && isset($data) && $data->image != null ? 'active' : '' !!}">
                                <img src="{{ asset('images/logo/default.png') }}" alt="">
                            </div>
                            <input type="text" name="image" s-click-fn="selectImage(event)" autocomplete="off"
                                role="presentation">
                            <input type="hidden" name="tmp_file" value="{!! request('id') && isset($data) && $data->image != null ? $data->image : '' !!}">
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
                    <button color="danger" type="button" s-click-link="{!! route('admin-barber-list', 1) !!}">
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
    <script lang="ts">
        $(document).ready(function() {
            $validator("#form", {
                amount: {
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
@stop
