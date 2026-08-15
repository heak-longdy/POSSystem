<!-- SIDEBAR -->
<div class="SDHeader" x-data="XSDHeader">
    <a href="#" class="brand" @click="infoProfile()">
        {{-- <i class='bx bxl-squarespace icon'></i> --}}
        {{-- <i class='bx bxl-product-hunt icon bx-tada'></i> --}}
        <i class='bx bxl-stripe icon iconLogo bx-tada-hover bx-tada'></i>
        {{-- <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M13.479 9.883c-1.626-.604-2.512-1.067-2.512-1.803 0-.622.511-.977 1.423-.977 1.667 0 3.379.642 4.558 1.22l.666-4.111c-.935-.446-2.847-1.177-5.49-1.177-1.87 0-3.425.489-4.536 1.401-1.155.954-1.757 2.334-1.757 4 0 3.023 1.847 4.312 4.847 5.403 1.936.688 2.579 1.178 2.579 1.934 0 .732-.629 1.155-1.762 1.155-1.403 0-3.716-.689-5.231-1.578l-.674 4.157c1.304.732 3.705 1.488 6.197 1.488 1.976 0 3.624-.467 4.735-1.356 1.245-.977 1.89-2.422 1.89-4.289 0-3.091-1.889-4.38-4.935-5.468h.002z"></path></svg> --}}
        <span id="clear-storage-button">POS System</span>
        {{-- <nav><i class='bx bx-menu toggle-sidebar'></i></nav> --}}
    </a>
    @include('admin::components.confirm-dialog-info-profile')
</div>
<ul class="side-menu">
    <li class="li">
        <a href="{{ route('admin-dashboard') }}" class="overView {{ routeActive('admin/dashboard') ? 'active' : '' }}">
            <div class="div">
                {{-- <i class='bx bxl-stack-overflow icon'></i> --}}
                {{-- <i class='bx bxs-dashboard icon'></i> --}}
                <i class='bx bx-tachometer icon'></i>
                {!! \App\Support\Language::translatedValue(['en' => 'Dashboard', 'km' => 'ផ្ទាំងគ្រប់គ្រង']) !!}
            </div>
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
                            <a href="{!! isset($itemListMenu['path']) ? url($itemListMenu['path']) : '#' !!}"
                                class="{{ routeActive($itemListMenu['active']) ? 'active' : '' }}">
                                {{-- <i class='bx bxs-inbox '></i> --}}
                                {{-- <i class='bx bx-menu-alt-right icon'></i> --}}
                                {{-- <i class='bx bx-menu icon'></i> --}}
                                {{-- <i class='bx bx-category-alt icon'></i> --}}
                                {{-- <i class='bx bx-list-ol icon'></i> --}}
                                {{-- <i class='bx bx-cog icon'></i> --}}
                                <i
                                    class='bx {{ isset($itemListMenu['icon']) && $itemListMenu['icon'] ? $itemListMenu['icon'] : 'bxs-wrench' }} icon'></i>
                                {{-- <i class='bx bx-detail icon'></i> --}}
                                {{-- <i class='bx bx-align-right icon'></i> --}}
                                {{-- <i class='bx bx-right-indent icon'></i> --}}
                                {!! Str::limit(\App\Support\Language::translatedValue($itemListMenu['name']), 25, ' ...') !!}
                                @if (!isset($itemListMenu['dropDown']) || $itemListMenu['dropDown'] != 'disable')
                                    <i class='bx bx-chevron-right icon-right'></i>
                                @endif

                            </a>
                            @if (isset($itemListMenu['children']) && $itemListMenu['children'])
                                <ul class="side-dropdown {{ routeActive($itemListMenu['active']) ? 'show' : '' }}">
                                    @foreach ($itemListMenu['children'] as $child)
                                        <li>
                                            <a href="{!! url($child['path']) !!}"
                                                class="{{ routeActive($child['active']) ? 'active' : '' }}">
                                                <i
                                                    class='bx {{ isset($child['icon']) && $child['icon'] ? $child['icon'] : 'bxs-wrench' }} icon'></i>
                                                {!! Str::limit(\App\Support\Language::translatedValue($child['name']), 25, ' ...') !!}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </div>
            </div>
        @elseif(isset($item['type']) && $item['type'] == 'dropdown-single')
            <div class="navItemSiderbarGroup li">
                <li class="divider" data-text="{{ \App\Support\Language::translatedValue($item['label']) }}"></li>
                <div class="navSidber">
                    <li class="">
                        <a href="#" class="{{ routeActive($item['active']) ? 'active' : '' }}">
                            {{-- <i class='bx bxs-inbox '></i> --}}
                            {{-- <i class='bx bx-menu-alt-right icon'></i> --}}
                            {{-- <i class='bx bx-menu icon'></i> --}}
                            <i
                                class='bx {{ isset($item['icon']) && $item['icon'] ? $item['icon'] : 'bx-wrench' }} icon'></i>
                            {{-- <i class='bx bx-list-ol icon'></i> --}}
                            {{-- <i class='bx bx-detail icon'></i> --}}
                            {{-- <i class='bx bx-align-right icon'></i> --}}
                            {{-- <i class='bx bx-right-indent icon'></i> --}}
                            {!! \App\Support\Language::translatedValue($item['name']) !!}
                            <i class='bx bx-chevron-right icon-right'></i>
                        </a>
                        <ul class="side-dropdown {{ routeActive($item['active']) ? 'show' : '' }}">
                            @foreach ($item['children'] as $child)
                                <li>
                                    <a href="{!! url($child['path']) !!}"
                                        class="{{ routeActive($child['active']) ? 'active' : '' }}">
                                        <i
                                            class='bx {{ isset($child['icon']) && $child['icon'] ? $child['icon'] : 'bxs-wrench' }} icon'></i>
                                        {!! Str::limit(\App\Support\Language::translatedValue($child['name']), 25, ' ...') !!}
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
                    {!! Str::limit(\App\Support\Language::translatedValue($item['name']), 25, ' ...') !!}
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
    <i class='bx bx-chevron-right'></i>
</button>

<!-- SIDEBAR -->


<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('XSDHeader', () => ({
            open: false,

            infoProfile() {
                console.log('object');
                this.$store.confirmDialogInfoProfile.open({
                    data: {
                        message: `Are you sure want to ?`,
                        btnClose: `{{ __('action_button.cancel') }}`,
                        btnSave: "Save",
                        item: {},
                        urlName: ``,
                        typeAction: "",
                        digPosition: "center",
                        class: "infoProfileDialog",
                        width: "25rem"
                    },
                    afterClosed: (result) => {
                        if (result) {
                            let Url = `{{ url()->full() }}`;
                            reloadData(Url)
                        }
                    }
                });
            },
        }))
    })
</script>
