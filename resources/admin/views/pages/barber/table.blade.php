<div class="table">
    @if ($data->count() > 0)
        <div class="table-wrapper">
            <div class="table-header">
                {{-- <div class="row table-row-5">
                    <span>Nº</span>
                </div> --}}
                <div class="row table-row-5">
                    <span>BarberID</span>
                </div>
                <div class="row table-row-10">
                    <span>Profile</span>
                </div>
                <div class="row table-row-10">
                    <span>Name</span>
                </div>
                <div class="row table-row-10">
                    <span>Phone</span>
                </div>
                <div class="row table-row-10">
                    <span>Gender</span>
                </div>
                <div class="row table-row-10">
                    <span>Shop</span>

                </div>
                <div class="row table-row-20">
                    <span>Address</span>
                </div>
                <div class="row table-row-10">
                    <span>Commission %</span>
                </div>
                <div class="row table-row-10">
                    <span>Wallet KHR</span>
                </div>
                <div class="row table-row-5">
                    <span></span>
                </div>
            </div>
            <div class="table-body">
                @foreach ($data as $index => $item)
                    <div class="column">
                        {{-- <div class="row table-row-5">
                            <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                        </div> --}}
                        <div class="row table-row-5">
                            <span>{!! $item?->number_id !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <div class="thumbnail" data-fancybox data-src="{{ asset('file_manager' . $item->image) }}">
                                <img src="{!! $item->image != null ? asset('file_manager' . $item->image) : asset('images/logo/default.png') !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->name) ? $item->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->phone) ? $item->phone : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->gender) ? $item->gender : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->shop) ? $item->shop->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-20">
                            <span>{!! isset($item->address) ? $item->address : '--' !!}</span>
                        </div>

                        <div class="row table-row-10">
                            <span>{!! isset($item->commission) ? $item->commission : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->wallet) ? $item->wallet : '--' !!}</span>
                        </div>
                        <div class="row table-row-5">
                            @canany(['barber-update', 'barber-delete'])
                                <div class="dropdown">
                                    <i data-feather="more-vertical" class="action-btn" id="dropdownMenuButton"
                                        data-mdb-toggle="dropdown" aria-expanded="false">
                                    </i>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        @if ($status != 'trash')
                                            @can('barber-update')
                                                <li>
                                                    <a class="dropdown-item" s-click-link="{!! route('admin-barber-create', $item->id) !!}">
                                                        <i data-feather="edit"></i>
                                                        <span>Edit</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" s-click-link="{!! route('admin-barber-change-password', $item->id) !!}">
                                                        <i data-feather="key"></i>
                                                        <span>Change Password</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" s-click-link="{!! route('admin-barber-commission-history', $item->id) !!}">
                                                        <i data-feather="bookmark" class="text-warning"></i>
                                                        <span>Commission History</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" s-click-link="{!! route('admin-barber-wallet-history', $item->id) !!}">
                                                        <i data-feather="alert-circle" class="text-warning"></i>
                                                        <span>Wallet History</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" s-click-link="{!! route('admin-barber-top-up', $item->id) !!}">
                                                        <i data-feather="dollar-sign" class="text-success"></i>
                                                        <span>Top Up</span>
                                                    </a>
                                                </li>
                                                @if ($item->id != Auth::user()->id)
                                                    @if ($item->status == 2)
                                                        <li>
                                                            <a class="dropdown-item enable-btn"
                                                                onclick="$onConfirmMessage(
                                                                    '{!! route('admin-barber-status', ['id' => $item->id, 'status' => 1]) !!}',
                                                                    '@lang('dialog.msg.enable', ['name' => $item->name])',
                                                                    {
                                                                        confirm: '@lang('dialog.button.enable')',
                                                                        cancel: '@lang('dialog.button.cancel')'
                                                                    },
                                                                );">
                                                                <i data-feather="rotate-ccw"></i>
                                                                <span>Enable</span>
                                                            </a>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <a class="dropdown-item disable-btn"
                                                                onclick="$onConfirmMessage(
                                                                    '{!! route('admin-barber-status', ['id' => $item->id, 'status' => 2]) !!}',
                                                                    '@lang('dialog.msg.disable', ['name' => $item->name])',
                                                                    {
                                                                        confirm: '@lang('dialog.button.disable')',
                                                                        cancel: '@lang('dialog.button.cancel')'
                                                                    }
                                                                );">
                                                                <i data-feather="x-circle"></i>
                                                                <span>Disable</span>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endif
                                            @endcan
                                            {{-- @can('barber-delete')
                                                <li>
                                                    <a class="dropdown-item text-danger trash-btn"
                                                        data-url="{!! route('admin-barber-delete', $item->id) !!}" data-name="Barber">
                                                        <i data-feather="trash-2"></i>
                                                        <span>@lang('table.option.delete')</span>
                                                    </a>
                                                </li>
                                            @endcan --}}
                                        @else
                                            <li>
                                                <a class="dropdown-item disable-btn"
                                                    onclick="$onConfirmMessage(
                                                                            '{!! route('admin-barber-restore', ['id' => $item->id, 'status' => 'restore']) !!}',
                                                                            '@lang('dialog.msg.restore', ['name' => 'Barber'])',
                                                                            {
                                                                                confirm: '@lang('dialog.button.restore')',
                                                                                cancel: '@lang('dialog.button.cancel')'
                                                                            }
                                                                        );">
                                                    <i data-feather="rotate-ccw"></i>
                                                    <span>@lang('table.option.restore')</span>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @endcan
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
            'name' => __('Barber is empty'),
            'msg' => __('You can create a new Barber by clicking the button below.'),
            'permission' => 'barber-create',
            'url' => route('admin-barber-create'),
            'button' => __('Create New Barber'),
        ])
        @endcomponent
    @endif
</div>
