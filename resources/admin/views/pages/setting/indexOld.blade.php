@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-setting-save', ['id' => $data?->id, 'type' => request('type')]) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    Rate & Commission
                </h3>
            </div>
            @csrf
            <div class="form-body">
                <div class="row-3">
                    <div class="form-row">
                        <label>Exchange Rate <span>*</span></label>
                        <input type="number" name="rate" value="{!!$data->rate!!}"
                            placeholder="">
                    </div>
                    <div class="form-row">
                        <label>Service Commission % <span>*</span></label>
                        <input type="text" name="service" value="{!!$data->service!!}" placeholder="">
                    </div>
                    <div class="form-row">
                        <label>Product Commission %<span>*</span></label>
                        <input type="text" name="product" value="{!!$data->product!!}" placeholder="">
                    </div>
                </div>
                <div class="form-button">
                    @can('setting-update')
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
    @include('admin::file-manager.popup')
@endsection

@section('script')
    <script lang="ts">
        $(document).ready(function() {
            $validator("#form", {
                rate: {
                    required: true,
                },
                product: {
                    required: true,
                },
                service: {
                    required: true,
                },
            });
        });
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
            Alpine.data("xService", () => ({}));
        });
    </script>
@endsection
