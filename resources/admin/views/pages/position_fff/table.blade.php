
<div class="table">
    
    @if ($data->count() > 0)
        <div class="table-wrapper">
            
            <div class="table-header">
                <div class="row table-row-5">
                    <span>Nº</span>
                </div>
                <div class="row table-row-10">
                    <span>Image</span>
                </div>
                <div class="row table-row-80 text-left">
                    <span>Name</span>
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
                            <div class="thumbnail" data-fancybox data-src="{{ asset('file_manager' . $item->profile) }}">
                                <img src="{!! $item->image_url !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                        </div>
                        <div class="row table-row-80 text left">
                            <span>{!! isset($item->title) ? $item->title : '---' !!}</span>
                        </div>
                        <div class="row table-row-5">
                            <div class="dropdown">
                                <i data-feather="more-vertical" class="action-btn" id="dropdownMenuButton"
                                    data-mdb-toggle="dropdown" aria-expanded="false">
                                </i>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    @if ($status != 'trash')
                                        @can('customer-update')
                                            <li>
                                                <a class="dropdown-item" s-click-link="{!! route('admin-position-edit', $item->id) !!}">
                                                    <i data-feather="edit"></i>
                                                    <span>Edit</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" s-click-link="#">
                                                    <i data-feather="bookmark" class="text-warning"></i>
                                                    <span>Point History</span>
                                                </a>
                                            </li>
                                            @if ($item->status == 2)
                                                <li>
                                                    <a class="dropdown-item enable-btn">
                                                        <i data-feather="rotate-ccw"></i>
                                                        <span>@lang('table.option.enable')</span>
                                                    </a>
                                                </li>
                                            @else
                                                <li>
                                                    <a class="dropdown-item disable-btn">
                                                        <i data-feather="x-circle"></i>
                                                        <span>@lang('table.option.disable')</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endcan
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="paginationLayout">
                @include('admin::components.pagination', ['paginate' => $data])
            </div>
        </div>
    @else
        @component('admin::components.empty',
            [
                'name' => __('No data'),
                'msg' => "You can create a new position by clicking the button below",
                'permission' => 'Position-create',
                'url' => route('admin-position-create'),
                'button' => __('adminGlobal.button.createPosition'),
            ])
        @endcomponent
    @endif
</div>
