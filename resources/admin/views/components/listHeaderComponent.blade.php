@php
    $showTabs = $showTabs ?? true;
    $showCreate = $showCreate ?? true;
    $showSearch = $showSearch ?? true;
    $canCreate = !isset($createPermission) || auth()->user()->can($createPermission);
    $tabs = $tabs ?? null;
@endphp
<div class="header box-shadow-bottom">
    <div class="header-tab">
        @if ($showTabs)
            <div class="header-tab-wrapper">
                <div class="menu-row">
                    <div class="tabs">
                        @if ($tabs)
                            @foreach ($tabs as $tab)
                                <a href="{!! $tab['url'] !!}" class="{!! !empty($tab['active']) ? 'tabActive' : '' !!}">
                                    <i class='{{ $tab['icon'] ?? 'bx bx-data' }}'></i>
                                    {{ $tab['label'] }}
                                </a>
                            @endforeach
                        @else
                            <a href="{!! route('admin-' . $routeName . '-list', 1) !!}" class="{!! Request::is('admin/' . $routeName . '/list/1') ? 'tabActive' : '' !!}">
                                <i class='bx bx-data'></i>
                                Active
                            </a>
                            <a href="{!! route('admin-' . $routeName . '-list', 2) !!}" class="{!! Request::is('admin/' . $routeName . '/list/2') ? 'tabActive' : '' !!}">
                                <i class='bx bx-navigation'></i>
                                Disable
                            </a>
                            <a href="{!! route('admin-' . $routeName . '-list', 'trash') !!}" class="{!! Request::is('admin/' . $routeName . '/list/trash') ? 'tabActive' : '' !!}">
                                <i class='bx bx-trash-alt'></i>
                                Trash
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        <div class="header-action-button">
            <form class="filter" action="{!! url()->current() !!}" method="GET">
                @if (isset($filterView) && $filterView)
                    @include($filterView, $filterData ?? [])
                @else
                    @if ($filterStatus)
                        <div class="form-row w80 custom-select">
                            <select name="payment_status">
                                <option value="">All Status</option>
                                <option value="Pending" {!! request('payment_status') == 'Pending' ? 'selected' : '' !!}> Pending</option>
                                <option value="Paid" {!! request('payment_status') == 'Paid' ? 'selected' : '' !!}> Paid</option>
                            </select>
                        </div>
                    @endif
                    @if ($showSearch)
                        <div class="form-row w200">
                            <input type="text" name="search" placeholder="Search..." value="{!! request('search') !!}">
                        </div>
                    @endif
                @endif
                @if ($showSearch || (isset($filterView) && $filterView))
                    <button mat-flat-button type="submit" class="bg-success btnSearch">
                        {{-- <i class='bx bx-search'></i> --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                          </svg>

                    </button>
                @endif
            </form>
            @if(isset($exportAction) && $exportAction)
                <button type="button" @click="{!! $exportAction !!}" class="{{ $exportClass ?? 'btnExcel' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><path d="M252.31-180Q222-180 201-201q-21-21-21-51.31v-78.46q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v78.46q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85h455.38q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46v-78.46q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v78.46Q780-222 759-201q-21 21-51.31 21H252.31ZM450-664.46l-76.92 76.92q-8.93 8.92-21.19 8.81-12.27-.12-21.58-9.43-8.69-9.3-9-21.07-.31-11.77 9-21.08l124.38-124.38q5.62-5.62 11.85-7.92 6.23-2.31 13.46-2.31t13.46 2.31q6.23 2.3 11.85 7.92l124.38 124.38q8.92 8.92 8.81 20.89-.12 11.96-8.81 21.26-9.31 9.31-21.38 9.62-12.08.31-21.39-9L510-664.46v306q0 12.77-8.62 21.38-8.61 8.62-21.38 8.62t-21.38-8.62q-8.62-8.61-8.62-21.38v-306Z"/></svg>
                    <span>{{ $exportLabel ?? 'Export' }}</span>
                </button>
            @elseif(isset($exportUrl) && $exportUrl)
                <a href="{!! $exportUrl !!}" target="_blank">
                    <button type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"><path d="M252.31-180Q222-180 201-201q-21-21-21-51.31v-78.46q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v78.46q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85h455.38q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46v-78.46q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v78.46Q780-222 759-201q-21 21-51.31 21H252.31ZM450-664.46l-76.92 76.92q-8.93 8.92-21.19 8.81-12.27-.12-21.58-9.43-8.69-9.3-9-21.07-.31-11.77 9-21.08l124.38-124.38q5.62-5.62 11.85-7.92 6.23-2.31 13.46-2.31t13.46 2.31q6.23 2.3 11.85 7.92l124.38 124.38q8.92 8.92 8.81 20.89-.12 11.96-8.81 21.26-9.31 9.31-21.38 9.62-12.08.31-21.39-9L510-664.46v306q0 12.77-8.62 21.38-8.61 8.62-21.38 8.62t-21.38-8.62q-8.62-8.61-8.62-21.38v-306Z"/></svg>
                        <span>Export</span>
                    </button>
                </a>
            @endif
            <button s-click-link="{!! url()->current() !!}">
                {{-- <i class='bx bx-revision'></i> --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                  </svg>
                  
                <span>Reload</span>
            </button>
            @if ($showCreate && $canCreate)
                <button class="btn btn-create" s-click-link="{!! route('admin-' . $routeName . '-create') !!}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                      </svg>

                    <span>{{ $createName }}</span>
                </button>
            @endif
        </div>
    </div>
</div>
