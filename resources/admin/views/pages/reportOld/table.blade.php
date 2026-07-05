<div class="table">
    @if ($data->count() > 0)
        <div class="table-wrapper">
            <div class="table-header">
                <div class="row table-row-5">
                    <span>Nº</span>
                </div>
                <div class="row table-row-10">
                    <span>Profile</span>
                </div>
                <div class="row table-row-15">
                    <span>Name</span>
                </div>
                <div class="row table-row-15">
                    <span>Phone</span>
                </div>
                <div class="row table-row-10">
                    <span>Gender</span>
                </div>
                <div class="row table-row-30">
                    <span>Address</span>
                </div>
                <div class="row table-row-10">
                    <span>Commission $</span>
                </div>
                <div class="row table-row-5">
                    <span></span>
                </div>
            </div>
            <div class="table-body">
                @foreach ($data as $index => $item)
                    <div class="column">
                        <div class="row table-row-5">
                            <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <div class="thumbnail" data-fancybox data-src="{{ asset('file_manager' . $item->image)  }}">
                                <img src="{!! $item->image != null ? asset('file_manager' . $item->image) : asset('images/logo/default.png') !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                        </div>
                        <div class="row table-row-15">
                            <span>{!! isset($item->name) ? $item->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-15">
                            <span>{!! isset($item->nick_name) ? $item->nick_name : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->phone) ? $item->phone : '--' !!}</span>
                        </div>
                        <div class="row table-row-30">
                            <span>{!! isset($item->address) ? $item->address : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{!! isset($item->total_wallet) ? $item->total_wallet : '--' !!}</span>
                        </div>
                        <div class="row table-row-5">
                            @canany(['barber-update', 'barber-delete'])
                                <div class="dropdown">
                                    <i data-feather="more-vertical" class="action-btn" id="dropdownMenuButton"
                                        data-mdb-toggle="dropdown" aria-expanded="false">
                                    </i>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
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
                                            @if ($item->id != Auth::user()->id)
                                                @if ($item->status == 2)
                                                    <li>
                                                        <a class="dropdown-item enable-btn" onclick="$onConfirmMessage(
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
                                                        <a class="dropdown-item disable-btn" onclick="$onConfirmMessage(
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
