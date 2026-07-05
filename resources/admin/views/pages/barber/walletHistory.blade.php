@extends('admin::shared.layout')
@section('layout')
    <style>
        .status {
            font-weight: 600;
            font-weight: unset;
            -webkit-text-stroke: .3px;
            border-radius: 20px;
            color: white;
            padding: 5px 0;
            margin: 0 10px;
        }

        .status.red {
            background: red;
        }

        .status.approved {
            background: green;
        }

        .cusHeader {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .cusHeader svg{
            margin-right: 10px;
        }
    </style>
    <div class="content-wrapper">
        <div class="header">
            <div class="cusHeader">
                <i data-feather="arrow-left" s-click-link="{!! route('admin-barber-list', 1) !!}"></i>
                @include('admin::shared.header', ['header_name' => 'Wallet History '.'Barber: '.$name->name . ' ' .$name->phone])
            </div>
            <div class="header-tab">
                <div class="header-tab-wrapper">
                    <div class="menu-row">
                        <div class="menu-item active" s-click-link="{!! route('admin-barber-wallet-history', $name->id) !!}">
                            All</div>
                    </div>
                </div>
                <div class="header-action-button">
                    <button s-click-link="{!! url()->current() !!}">
                        <i data-feather="refresh-ccw"></i>
                        <span>@lang('user.button.reload')</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="table">
                @if ($data->count() > 0)
                    <div class="table-wrapper">
                        <div class="table-header">
                            <div class="row table-row-5">
                    			<span>Nº</span>
                        	</div>
                          <div class="row table-row-10">
                              <span>Barber</span>
                          </div>
                          <div class="row table-row-15">
                              <span>Phone</span>
                          </div>
                          <div class="row table-row-15">
                              <span>Top Up</span>
                          </div>
                          <div class="row table-row-15">
                              <span>Amount</span>
                          </div>
                          <div class="row table-row-15">
                              <span>Top Up Date</span>
                          </div>
                          <div class="row table-row-15">
                              <span>Transection ID</span>
                          </div>
                          <div class="row table-row-10">
                              <span>Status</span>
                          </div>
                        <!--  <div class="row table-row-20">
                              <span>Remark</span>
                          </div> -->
                        </div>
                        <div class="table-body">
                            @foreach ($data as $index => $item)
                                <div class="column">
                                    <div class="row table-row-5">
                                        <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                                    </div>
                                    <div class="row table-row-10">
                                        <span>{!! isset($item->barber->name) ? $item->barber->name : '--' !!}</span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span>{!! isset($item->barber->phone) ? $item->barber->phone : '--' !!}</span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span>{!! isset($item->amount_dollar) ? number_format($item->amount_dollar, 2) : 0 !!}&nbsp;$</span>
                                    </div>
									<div class="row table-row-15">
                                        <span>{!! isset($item->amount) ? number_format($item->amount, 2) : 0 !!}&nbsp; KHR</span>
                                    </div>
                                    <div class="row table-row-15">
                                        <span>{!! isset($item->status_date) ? $item->status_date : '--' !!}</span>
                                    </div>
                                  	<div class="row table-row-15">
                                        <span>{!! isset($item->tran_id) ? $item->tran_id : '--' !!}</span>
                                    </div>
                                    <div class="row table-row-10">
                                        <span
                                            class="status {{ $item->status == 2 ? 'approved' : 'red' }}">{!! isset($item->status) && $item->status == 2 ? 'Approved' : 'Unsuccess' !!}</span>
                                    </div>
                                   
                                </div>
                            @endforeach

                        </div>
                        <div class="table-footer">
                            @include('admin::components.pagination', ['paginate' => $data])
                        </div>
                    </div>
                @else
                    @component('admin::components.empty', [
                        'name' => __('Wallet empty'),
                    ])
                    @endcomponent
                @endif
            </div>
        </div>
    @stop
    @section('script')
        <script lang="ts">

            $(document).ready(function() {
                $("#from_date,#to_date").datepicker({
                    changeYear: true,
                    gotoCurrent: true,
                    yearRange: "-1:+1",
                    dateFormat: "yy-mm-dd",
                });
                $("#from_date").change(function() {
                    let str = $(this).val();
                    $("#to_date").datepicker("option", "minDate", new Date(str));
                });
                const date = $('#from_date').val();
                if (date) {
                    $("#to_date").datepicker("option", "minDate", new Date(date));
                }
            });
        </script>
    @stop
