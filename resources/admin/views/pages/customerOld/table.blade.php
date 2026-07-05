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
                <div class="row table-row-10">
                    <span>Name</span>
                </div>
                <div class="row table-row-15">
                    <span>Phone Number</span>
                </div>
                <div class="row table-row-20">
                    <span>Date of Birth</span>
                </div>
                <div class="row table-row-15">
                    <span>Identity</span>
                </div>
                <div class="row table-row-15">
                    <span>Identify Expired Date</span>
                </div>
                <div class="row table-row-5">
                    <span>Status</span>
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
                            <div class="thumbnail" data-fancybox data-src="{{ asset('file_manager' . $item->image) }}">
                                <img src="{!! $item->image_url !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                        </div>
                        <div class="row table-row-10 text bold">
                            <span>{!! isset($item->name) ? $item->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-15 text">
                            <span>{!! isset($item->phone) ? $item->phone : '--' !!}</span>
                        </div>
                        <div class="row table-row-20 text">
                            <span>{{ Carbon::parse($item->dob)->format('d-M-Y' ) }}</span>
                        </div>
                        <div class="row table-row-15 text">
                            <span>{!! isset($item->identity) ? $item->identity : '--' !!}</span>
                        </div>
                        <div class="row table-row-15 text">
                            <span>{{ Carbon::parse($item->identity_expired_date)->format('d-M-Y' ) }}</span>
                        </div>
                        <div class="row table-row-5 text">
                             <span>
                                @if($item->status == 1)
                                    <span class="badge rounded-pill text-bg-primary">Active</span> 
                                @else
                                    <span color="danger" class="badge rounded-pill" style="color: rgb(255, 73, 73)">Blocked</span>
                                @endif

                            </span> 
                        </div>
                        <div class="row table-row-5">
                            @canany(['user-update', 'user-change-password'])
                            <div class="dropdown">
                                <i data-feather="more-vertical" class="action-btn" id="dropdownMenuButton"
                                    data-mdb-toggle="dropdown" aria-expanded="false">
                                </i>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    @if ($status != 'trash')
                                        @can('user-update')
                                            <li>
                                                <a class="dropdown-item" s-click-link="{!! route('admin-customer-create', $item->id) !!}">
                                                    <i data-feather="edit"></i>
                                                    <span>@lang('table.option.edit')</span>
                                                </a>
                                            </li>
                                            @if ($item->status == 2)
                                                <li>
                                                    <a class="dropdown-item enable-btn"
                                                        onclick="$onConfirmMessage(
                                                            '{!! route('admin-customer-status', ['id' => $item->id, 'status' => 1]) !!}',
                                                            '@lang('dialog.msg.enable', ['name' => isset($item->name) ? $item->name : $item->name])',
                                                            {
                                                                confirm: '@lang('dialog.button.enable')',
                                                                cancel: '@lang('dialog.button.cancel')'
                                                            },
                                                        );">
                                                        <i data-feather="rotate-ccw"></i>
                                                        <span>@lang('table.option.enable')</span>
                                                    </a>
                                                </li>
                                            @else
                                                <li>
                                                    <a class="dropdown-item disable-btn"
                                                        onclick="$onConfirmMessage(
                                                            '{!! route('admin-customer-status', ['id' => $item->id, 'status' => 2]) !!}',
                                                            '@lang('dialog.msg.disable', ['name' => isset($item->name) ? $item->name : $item->name])',
                                                            {
                                                                confirm: '@lang('dialog.button.disable')',
                                                                cancel: '@lang('dialog.button.cancel')'
                                                            }
                                                        );">
                                                        <i data-feather="x-circle"></i>
                                                        <span>@lang('table.option.disable')</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endcan
                                        @can('user-change-password')
                                            <li>
                                                <a class="dropdown-item" s-click-link="{!! route('admin-customer-change-password', $item->id) !!}">
                                                    <i data-feather="key"></i>
                                                    <span>@lang('table.option.change_password')</span>
                                                </a>
                                            </li>
                                        @endcan
                                        @can('user-delete')
                                            <li>
                                                <a class="dropdown-item text-danger trash-btn"
                                                    data-url="{!! route('admin-customer-destroy', $item->id) !!}" data-name="{!! isset($item->name) ? $item->name : $item->name !!}">
                                                    <i data-feather="trash-2"></i>
                                                    <span>@lang('table.option.delete')</span>
                                                </a>
                                            </li>
                                        @endcan
                                    @else
                                        @can('user-delete')
                                            <li>
                                                <a class="dropdown-item disable-btn"
                                                    onclick="$onConfirmMessage(
                                                        '{!! route('admin-customer-restore', ['id' => $item->id, 'status' => 'restore']) !!}',
                                                        '@lang('dialog.msg.restore', ['name' => $item->name ? $item->name : $item->name])',
                                                        {
                                                            confirm: '@lang('dialog.button.restore')',
                                                            cancel: '@lang('dialog.button.cancel')'
                                                        }
                                                    );">
                                                    <i data-feather="rotate-ccw"></i>
                                                    <span>@lang('table.option.restore')</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger delete-btn"
                                                    onclick="$onConfirmMessage(
                                                        '{!! route('admin-customer-destroy', $item->id) !!}' ,
                                                        '@lang('dialog.msg.delete', ['name' => $item->name ? $item->name : $item->name])',
                                                        {
                                                            confirm: '@lang('dialog.button.delete')',
                                                            cancel: '@lang('dialog.button.cancel')'
                                                        }
                                                    );">
                                                    <i data-feather="trash"></i>
                                                    <span>@lang('table.option.delete')</span>
                                                </a>
                                            </li>
                                        @endcan
                                    @endif
                                </ul>
                            </div>
                        @endcan
                            {{-- @canany(['user-view-detail'])
                                <div class="dropdown">
                                    <i data-feather="more-vertical" class="action-btn" id="dropdownMenuButton"
                                       data-mdb-toggle="dropdown" aria-expanded="false">
                                    </i>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        @can('user-view-detail')
                                         <li>
                                           <a class="dropdown-item text-success" s-click-link="{!! route('admin-customer-income', $item->id) !!}">
                                             <i data-feather="file-text"></i>
                                             <span>View Detail</span>
                                           </a>
                                        </li>                               
                                        @endcan
                                    </ul>
                                </div>
                            @endcan --}}
                        </div>
                    </div>
                @endforeach

            </div>
            <div class="paginationLayout">
                @include('admin::components.pagination', ['paginate' => $data])
            </div>
        </div>

    <style>
        #ball_active{
            content:'';
            display:inline-block;
            width: 1em;
            height: 1em;
            background-color: rgba(90, 189, 90, 1);
            border-radius: 50%;
        }
    </style>
    @else
        @component('admin::components.empty', [
            'name' => __('customer.empty.title'),
            // 'msg' => __('customer.empty.description'),
            ])
        @endcomponent
    @endif
</div>