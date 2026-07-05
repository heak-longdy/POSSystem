@extends('admin::shared.layout')
@section('layout')
    <div class="form-admin">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-customer-save-password', request('id')) !!}" method="POST">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-customer-list', 1) !!}"></i>
                    Change Password ( {!! $data->name !!} )
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-2">
                    <div class="form-row">
                        <label>Password<span>*</span> </label>
                        <input type="password" name="password" placeholder=""
                            minlength="8">
                    </div>
                    <div class="form-row">
                        <label>Confirm Password<span>*</span> </label>
                        <input type="password" name="confirm_password"
                            placeholder="" minlength="8">
                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>Update</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-customer-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop

@section('script')
    <script>
        $(document).ready(function() {
            $validator("#form", {
                password: {
                    required: true,
                },
                confirm_password: {
                    required: true,
                    match: "password"
                }
            });
        });
    </script>
@stop