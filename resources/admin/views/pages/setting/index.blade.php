@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="#" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    Top Up Rate
                </h3>
            </div>
            @csrf
            <div class="form-body">
                <div class="row">
                    <div class="form-row">
                        <label>Exchange Rate <span>*</span></label>
                        <input type="number" name="rate" value=""
                            placeholder="">
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
