@include('admin::components.listHeaderComponent', [
    'routeName' => $routeName,
    'createName' => $createName,
    'filterStatus' => $filterStatus,
    'exportUrl' => $exportUrl ?? '',
])
<div class="content-body">
    <div class="table">
        @if ($data->count() > 0)
            <div class="table-wrapper">
                <div class="paginationLayout2">
                    @include('admin::components.pagination', ['paginate' => $data])
                </div>
                <div class="table-header">
                    @foreach ($tbHeader as $item)
                        <div class="row table-row-{{ $item['colVal'] }} {{ $item['class'] }}">
                            @if(isset($item['title']) && $item['title'])
                                <span>{{ $item['title'] }}</span>
                            @else
                                <span></span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="table-body">
                    @foreach ($data as $index => $item)
                        <div class="column">
                            @foreach ($tbHeader as $header)
                                <div class="row table-row-{{ $header['colVal'] }} {{ $header['class'] }}">
                                    @if ($header['field'] === 'index')
                                        <span>{!! $data->currentPage() * $data->perPage() - $data->perPage() + ($index + 1) !!}</span>
                                    @elseif($header['field'] === 'image_url')
                                        <div class="thumbnail {!! isset($header['rowClass']) ? $header['rowClass'] : '' !!}" data-fancybox data-src="{!! isset($item->{$header['field']}) ? $item->{$header['field']} : '' !!}">
                                            <img src="{!! isset($item->{$header['field']}) ? $item->{$header['field']} : '' !!}"
                                                onerror="(this).src='{{ asset('images/logo/default.png') }}'"
                                                alt="">
                                        </div>
                                    @elseif($header['field'] != 'action')
                                        <span>{!! isset($item->{$header['field']}) ? $item->{$header['field']} : '---' !!}</span>
                                    @elseif($header['field'] === 'action')
                                        <div class="dropdown">
                                            <i data-feather="more-vertical" class="action-btn" id="dropdownMenuButton"
                                                data-mdb-toggle="dropdown" aria-expanded="false">
                                            </i>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                @foreach ($header['actions'] as $acIn => $itHd)
                                                    @if ($status != 'trash')
                                                        @if ($itHd['key'] == 'active')
                                                            @foreach ($itHd['action'] as $acItem)
                                                                <li>
                                                                    @if (isset($acItem['type']) && $acItem['type'] == 'link')
                                                                        <a class="dropdown-item"
                                                                            href="{!! route('admin-' . $routeName.'-'.$acItem['url'], $item->id) !!}">
                                                                            <i
                                                                                class="material-symbols-outlined">{{ $acItem['icon'] }}</i>
                                                                            <span>{{ $acItem['title'] }}</span>
                                                                        </a>
                                                                    @else
                                                                        <a class="dropdown-item {!! isset($acItem['class']) ? $acItem['class'] : '' !!}"
                                                                            @click="verifyDialog({{ $item }},'{!! $acItem['url'] !!}','{{ $acItem['title'] }}')">
                                                                            <i
                                                                                class="material-symbols-outlined">{{ $acItem['icon'] }}</i>
                                                                            <span>{{ $acItem['title'] }}</span>
                                                                        </a>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                        @if ($item->status == 2)
                                                            @if ($itHd['key'] == 'enable')
                                                                @foreach ($itHd['action'] as $acItem)
                                                                    <li>
                                                                        <a class="dropdown-item {!! isset($acItem['class']) ? $acItem['class'] : '' !!}"
                                                                            @click="verifyDialog({{ $item }},'{!! $acItem['url'] !!}','{{ $acItem['title'] }}')">
                                                                            <i
                                                                                class="material-symbols-outlined">{{ $acItem['icon'] }}</i>
                                                                            <span>{{ $acItem['title'] }}</span>
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            @endif
                                                        @else
                                                            @if ($itHd['key'] == 'disable')
                                                                @foreach ($itHd['action'] as $acItem)
                                                                    <li>
                                                                        <a class="dropdown-item {!! isset($acItem['class']) ? $acItem['class'] : '' !!}"
                                                                            @click="verifyDialog({{ $item }},'{!! $acItem['url'] !!}','{{ $acItem['title'] }}')">
                                                                            <i
                                                                                class="material-symbols-outlined">{{ $acItem['icon'] }}</i>
                                                                            <span>{{ $acItem['title'] }}</span>
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            @endif
                                                        @endif
                                                    @else
                                                        @if ($itHd['key'] == 'trash')
                                                            @foreach ($itHd['action'] as $acItem)
                                                                <li>
                                                                    <a class="dropdown-item {!! isset($acItem['class']) ? $acItem['class'] : '' !!}"
                                                                        @click="verifyDialog({{ $item }},'{!! $acItem['url'] !!}','{{ $acItem['title'] }}')">
                                                                        <i
                                                                            class="material-symbols-outlined">{{ $acItem['icon'] }}</i>
                                                                        <span>{{ $acItem['title'] }}</span>
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                                {{-- <div class="dropdown">
                                        <i data-feather="more-vertical" class="action-btn" id="dropdownMenuButton"
                                            data-mdb-toggle="dropdown" aria-expanded="false">
                                        </i>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            @if ($status != 'trash')
                                                <li>
                                                    <a class="dropdown-item" href="{!! route('admin-' . $routeName . '-edit', $item->id) !!}">
                                                        <i class="material-symbols-outlined">edit</i>
                                                        <span>Edit</span>
                                                    </a>
                                                </li>
                                                @if ($item->status == 2)
                                                    <li>
                                                        <a class="dropdown-item enable-btn"
                                                            @click="verifyDialog({{ $item }},'status','@lang('action_button.enable')')">
                                                            <i data-feather="rotate-ccw"></i>
                                                            <span>@lang('action_button.enable')</span>
                                                        </a>
                                                    </li>
                                                @else
                                                    <li>
                                                        <a class="dropdown-item disable-btn"
                                                            @click="verifyDialog({{ $item }},'status','@lang('action_button.disable')')">
                                                            <i class="material-symbols-outlined">hide_source</i>
                                                            <span>@lang('action_button.disable')</span>
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <a class="dropdown-item text-danger trash-btn"
                                                        data-url="{!! route('admin-' . $routeName . '-edit', $item->id) !!}"
                                                        data-name="{!! isset($item->name) ? $item->name : $item->name !!}">
                                                        <i data-feather="trash-2"></i>
                                                        <span>@lang('table.option.delete')</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger"
                                                        @click="verifyDialog({{ $item }},'delete','@lang('action_button.delete')')">
                                                        <i data-feather="trash-2"></i>
                                                        <span>@lang('table.option.deleted')</span>
                                                    </a>
                                                </li>
                                            @else
                                                <li>
                                                    <a class="dropdown-item disable-btn"
                                                        @click="verifyDialog({{ $item }},'restore','@lang('action_button.restore')')">
                                                        <i data-feather="rotate-ccw"></i>
                                                        <span>@lang('table.option.restore')</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger"
                                                        @click="verifyDialog({{ $item }},'destroy','@lang('action_button.destroy')')">
                                                        <i data-feather="trash"></i>
                                                        <span>@lang('action_button.destroy')</span>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div> --}}
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <div class="paginationLayout">
                    @include('admin::components.paginationNumber', ['paginate' => $data])
                </div>
            </div>
        @else
            @component('admin::components.empty', [
                'name' => __('No data'),
                'msg' => 'You can create a new ' . $routeName . ' by clicking the button below',
                'permission' => 'Position-create',
                'url' => route('admin-' . $routeName . '-create'),
                'button' => $createName,
            ])
            @endcomponent
        @endif

    </div>
</div>
@include('admin::components.verify')
