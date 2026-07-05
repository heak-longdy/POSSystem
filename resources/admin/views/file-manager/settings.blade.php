<div class="file-manager-wrapper" x-data="settingsPage" :class="{'full-page':$store.page.full_page}">
    <div class="file-manager-header">
        <div class="label">
            <span>@lang('file-manager.title')</span>
        </div>
        <div class="gap"></div>
        <button @click="onClose()">
            <i data-feather="x"></i>
            <span>@lang('file-manager.button.close')</span>
        </button>
    </div>
    <div class="file-manager-body">
        <div class="breadcrumb">
            <span>
                @lang('file-manager.breadcrumb.setting')
            </span>
        </div>
        <div class="file-side">
            @include('admin::file-manager.menu')
            <div class="file-list">
                @component('admin::components.empty', [
                    'name' => 'Coming Soon...',
                    'image' => asset('images/logo/em.svg'),
                    'msg' => 'This feature is coming soon.',
                    'style' => 'padding: 50px 0;',
                    ])
                @endcomponent
            </div>
        </div>
    </div>
    <div class="file-manager-footer">
        <div class="file-manager-button">
            {{-- <button class="save" @click="onSave">
                <i data-feather="check"></i>
                <span>
                    @lang('file-manager.button.save')
                </span>
            </button> --}}
        </div>
    </div>
    <script>
        Alpine.data('settingsPage', () => ({
            init() {
                this.reloadIcon();
            },
            reloadIcon() {
                feather.replace();
                setTimeout(() => {
                    feather.replace();
                }, 10);
            },
            onClose() {
                Alpine.store('animate').leave(".dialog-container", (res) => {
                    Alpine.store('page').active = false;
                    Alpine.store('page').options.afterClose(false);
                });
            },
        }));
    </script>
</div>
