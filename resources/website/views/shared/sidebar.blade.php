<!-- SIDEBAR -->
{{-- <section id="sidebar" class="sidebar"> --}}
<a href="#" class="brand">
    {{-- <i class='bx bxl-squarespace icon'></i> --}}
    <i class='bx bxl-product-hunt icon bx-tada'></i>
    <span>System</span>
    {{-- <nav><i class='bx bx-menu toggle-sidebar'></i></nav> --}}
</a>
<ul class="side-menu">
    <li class="li">
        <a href="{{ route('admin-dashboard') }}" class="overView {{ routeActive('admin/dashboard') ? 'active' : '' }}">
            <div class="div"><i class='bx bxl-stack-overflow icon'></i> {!! \App\Support\Language::translatedValue(['en' => 'Dashboard', 'km' => 'ផ្ទាំងគ្រប់គ្រង']) !!}</div>
            <div class="iRight">
                <i class='bx bx-cog bx-tada-hover'></i>
            </div>
        </a>
    </li>
    @foreach (config('menu') as $key => $item)
        {{-- @foreach (Menu::menuList() as $key => $item) --}}
        @if (isset($item['type']) && $item['type'] == 'dropdown-multiple')
            <div class="navItemSiderbarGroup li">
                <li class="divider" data-text="{{ \App\Support\Language::translatedValue($item['label']) }}"></li>
                <div class="navSidber">
                    @foreach (($item['listMenu'] ?? $item['list-menu'] ?? []) as $keyListMenu => $itemListMenu)
                        <li class="">
                            <a href="#" class="{{ routeActive($itemListMenu['active']) ? 'active' : '' }}">
                                {{-- <i class='bx bxs-inbox '></i> --}}
                                {{-- <i class='bx bx-menu-alt-right icon'></i> --}}
                                {{-- <i class='bx bx-menu icon'></i> --}}
                                {{-- <i class='bx bx-category-alt icon'></i> --}}
                                <i class='bx bx-list-ol icon'></i>
                                {{-- <i class='bx bx-detail icon'></i> --}}
                                {{-- <i class='bx bx-align-right icon'></i> --}}
                                {{-- <i class='bx bx-right-indent icon'></i> --}}
                                {!! Str::limit(\App\Support\Language::translatedValue($itemListMenu['name']), 18, ' (...)') !!}
                                <i class='bx bx-chevron-right icon-right'></i>

                            </a>
                            <ul class="side-dropdown {{ routeActive($itemListMenu['active']) ? 'show' : '' }}">
                                @foreach ($itemListMenu['children'] as $child)
                                    <li>
                                        <a href="{!! url($child['path']) !!}"
                                            class="{{ routeActive($child['active']) ? 'active' : '' }}">
                                            <i
                                                class='bx {{ isset($child['icon']) && $child['icon'] ? $child['icon'] : 'bxs-wrench' }} icon'></i>
                                            {!! Str::limit(\App\Support\Language::translatedValue($child['name']), 18, ' (...)') !!}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </div>
            </div>
        @elseif(isset($item['type']) && $item['type'] == 'dropdown-single')
            <div class="navItemSiderbarGroup li">
                <li class="divider" data-text="{{ \App\Support\Language::translatedValue($item['label'] ?? ['en' => 'Main', 'km' => 'មេ']) }}"></li>
                <div class="navSidber">
                    <li class="">
                        <a href="#">
                            {{-- <i class='bx bxs-inbox '></i> --}}
                            {{-- <i class='bx bx-menu-alt-right icon'></i> --}}
                            <i class='bx bx-menu icon'></i>
                            {{-- <i class='bx bx-list-ol icon'></i> --}}
                            {{-- <i class='bx bx-detail icon'></i> --}}
                            {{-- <i class='bx bx-align-right icon'></i> --}}
                            {{-- <i class='bx bx-right-indent icon'></i> --}}
                            {!! \App\Support\Language::translatedValue($item['name']) !!}
                            <i class='bx bx-chevron-right icon-right'></i>
                        </a>
                        <ul class="side-dropdown">
                            @foreach ($item['children'] as $child)
                                <li>
                                    <a href="{!! url($child['path']) !!}"
                                        class="{{ routeActive($child['active']) ? 'active' : '' }}">
                                        <i
                                            class='bx {{ isset($child['icon']) && $child['icon'] ? $child['icon'] : 'bxs-wrench' }} icon'></i>
                                        {!! Str::limit(\App\Support\Language::translatedValue($child['name']), 18, ' (...)') !!}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                </div>
            </div>
        @else
            <li class="li">
                <a href="{!! url($item['path']) !!}" class="{{ routeActive($item['active']) ? 'active' : '' }}">
                    <i class='bx {{ isset($item['icon']) && $item['icon'] ? $item['icon'] : 'bxs-wrench' }} icon'></i>
                    {!! Str::limit(\App\Support\Language::translatedValue($item['name']), 18, ' (...)') !!}
                </a>
            </li>
        @endif
    @endforeach
</ul>
<button aria-label="expand or collapse navigation bar"
    class="toggle-sidebar navbar-toggle nav-section ng-tns-c2859535076-1" aria-expanded="true">
    {{-- <mat-icon _ngcontent-ng-c2859535076="" role="img"
        class="mat-icon notranslate ng-tns-c2859535076-1 material-icons mat-ligature-font mat-icon-no-color"
        aria-hidden="true" data-mat-icon-type="font">chevron_left</mat-icon> --}}
    <i class='bx bx-right-arrow-alt'></i>
</button>
{{-- </section> --}}

<!-- SIDEBAR -->
