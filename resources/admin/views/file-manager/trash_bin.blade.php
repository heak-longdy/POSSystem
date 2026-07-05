<div class="file-manager-wrapper" x-data="trashBin" :class="{'full-page':$store.page.full_page}">
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
            <span @click="firstPage()">
                @lang('file-manager.breadcrumb.trash_bin')
            </span>
            <template x-for="(folder,index) in dataFolders">
                <span @click="onBreadcrumbClick(folder,index)">
                    <i data-feather="chevron-right"></i>
                    <i data-feather="folder" class="folder-icon"></i>
                    <span x-text="folder?.name"></span>
                </span>
            </template>
        </div>
        <div class="file-side">
            @include('admin::file-manager.menu')
            <div class="file-list">
                <div class="action">
                    <div class="form-row">
                        <input type="text" placeholder="@lang('file-manager.input.search')"
                            x-on:input="onSearch($event)">
                        <i data-feather="search"></i>
                    </div>
                    <button @click="reloadPage()" class="reload">
                        <i data-feather="refresh-cw"></i>
                        <span>@lang('file-manager.button.reload')</span>
                    </button>
                    <div class="gap"></div>
                    <button @click="restoreAll" x-bind:disabled="folders.length == 0 && files.length == 0">
                        <i data-feather="rotate-ccw"></i>
                        <span>@lang('file-manager.button.restore_all')</span>
                    </button>
                    <button @click="deleteAll" class="danger"
                        x-bind:disabled="folders.length ==0 &&  files.length == 0">
                        <i data-feather="trash-2"></i>
                        <span>@lang('file-manager.button.delete_all')</span>
                    </button>
                </div>
                <div class="file-list-wrapper" x-on:scroll="onScroll($event)">
                    <template x-if="!loading && folders.length > 0">
                        <div class="folder-row">
                            <template x-for="folder in folders">
                                <div class="folder-item" x-bind:class="isSelected(folder,'selected')"
                                    x-on:contextmenu="onRightClick($event,folder,'folder')"
                                    x-on:mousemove="showTooltip($event,folder)" x-on:mouseleave="hideTooltip()"
                                    @click="onSelect(folder)">
                                    <div class="img">
                                        <img x-bind:src="`{{ asset('images/logo/folder-empty.png') }}`" alt="">
                                    </div>
                                    <div class="name">
                                        <span x-text="folder.name"></span>
                                    </div>
                                    <template x-if="!isSelected(folder)">
                                        <div class="select-folder-icon">
                                            <div></div>
                                        </div>
                                    </template>
                                    <template x-if="isSelected(folder)">
                                        <div class="selected-folder-icon">
                                            <div x-text="selectedIndex(folder)"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!loading && files.length > 0">
                        <div class="file-row">
                            <template x-for="(file,index) in files">
                                <div class="file-item" x-bind:class="isSelected(file,'selected')"
                                    x-on:contextmenu="onRightClick($event,file,'file')"
                                    x-on:mousemove="showTooltip($event,file)" x-on:mouseleave="hideTooltip()"
                                    x-on:dblclick="viewImage(file)" @click="onSelect(file)">
                                    <div class="img">
                                        <img x-bind:src="base_path+file?.path"
                                            onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                                    </div>
                                    <div class="name">
                                        <span x-text="file?.name"></span>
                                    </div>
                                    <template x-if="!isSelected(file)">
                                        <div class="select-file-icon">
                                            <div></div>
                                        </div>
                                    </template>
                                    <template x-if="isSelected(file)">
                                        <div class="selected-file-icon">
                                            <div x-text="selectedIndex(file)"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!loading && files.length == 0 && folders.length == 0">
                        @component('admin::components.empty', ['name' => 'File not found', 'image' =>
                            asset('images/logo/em.svg'), 'style' => 'padding: 50px 0;'])
                        @endcomponent
                    </template>
                    <template x-if="loading || fileLoading">
                        <div class="skeleton-row">
                            <template x-for="skeleton in skeletons">
                                <div class="skeleton-item">
                                    <div class="img skeleton"></div>
                                    <div class="name skeleton"></div>
                                    <div class="name skeleton"></div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    <div class="file-manager-footer">
        <template x-if="selected_files.length > 0">
            <div class="file-manager-button">
                <button @click="onUnselectFiles">
                    <i data-feather="x"></i>
                    <span>@lang('file-manager.button.unselect_files')</span>
                </button>
                <button class="success" @click="restoreBySelected">
                    <i data-feather="rotate-ccw"></i>
                    <span>
                        @lang('file-manager.button.restore')
                        <span x-text="`(${selected_files.length})`"></span>
                    </span>
                </button>
                <button class="danger" @click="deleteBySelected">
                    <i data-feather="trash-2"></i>
                    <span>
                        @lang('file-manager.button.delete')
                        <span x-text="`(${selected_files.length})`"></span>
                    </span>
                </button>
            </div>
        </template>
    </div>
    <template x-if="dialog.component.confirmDeleteDialog">
        @include('admin::file-manager.confirm-delete-dialog')
    </template>
    <template x-if="dialog.component.confirmRestoreDialog">
        @include('admin::file-manager.confirm-restore-dialog')
    </template>
    <template x-if="contentMenu.show">
        <div class="dialog">
            <div class="context-menu" :style="`top:${contentMenu.y}px;left:${contentMenu.x}px`">
                <template x-if="contentMenu?.type == 'file'">
                    <div class="context-menu-item" @click="viewImage(contentMenu?.data)" @click.away="closeContextMenu">
                        <span>@lang('file-manager.button.open')</span>
                        <i data-feather="arrow-right"></i>
                    </div>
                </template>
                <div class="context-menu-item" @click="dialog.open('confirmRestoreDialog')"
                    @click.away="closeContextMenu">
                    <span>@lang('file-manager.button.restore')</span>
                    <i data-feather="rotate-ccw"></i>
                </div>
                <div class="context-menu-item danger" @click="dialog.open('confirmDeleteDialog')"
                    @click.away="closeContextMenu">
                    <span>@lang('file-manager.button.delete')</span>
                    <i data-feather="trash-2"></i>
                </div>
            </div>
        </div>
    </template>
    <template x-if="tooltip.show && !contentMenu.show">
        <div class="tooltipX" :style="`top:${tooltip.y}px;left:${tooltip.x}px`">
            <p>@lang('file-manager.tooltip.name') : <span x-text="tooltip.data?.name"></span></p>
            <p style="display: none" x-show="typeof tooltip.data?.folder_id != 'undefined'">
                @lang('file-manager.tooltip.size') : <span
                    x-text="new Intl.NumberFormat().format(tooltip.data?.size)+' bytes'"></span></p>
            <p style="display: none" x-show="tooltip.data?.is_image == 1">@lang('file-manager.tooltip.dimensions') :
                <span x-text="tooltip.data?.width+' x '+tooltip.data?.height"></span>
            </p>
            <p style="display: none" x-show="typeof tooltip.data?.folder_id != 'undefined'">
                @lang('file-manager.tooltip.type') : <span x-text="tooltip.data?.extension?.toUpperCase()"></span></p>
            <p>@lang('file-manager.tooltip.deleted_at') : <span
                    x-text="moment(tooltip.data?.deleted_at).calendar()"></span></p>
        </div>
    </template>
    <script>
        Alpine.data('trashBin', () => ({
            files: [],
            folders: [],
            filePage: 1,
            base_path: '',
            selected_files: [],
            delete_all: false,
            restore_all: false,
            dataFolders: [],
            loading: false,
            fileLoading: false,
            delayQuery: null,
            delayTooltip: null,
            currentParams: {},
            lastScrollTarget: 0,
            skeletons: new Array(10),
            contentMenu: {
                element: null,
                show: false,
                x: 0,
                y: 0,
                data: null
            },
            delayTooltip: null,
            tooltip: {
                show: false,
                x: 0,
                y: 0,
                data: null
            },
            init() {
                this.reloadIcon();
                this.firstPage();
                this.dialog.initData(this);
            },
            getData(params = {}) {
                this.filePage = 1;
                this.loading = true;
                this.currentParams = params;
                Axios
                    .get(`{{ route('admin-file-manager-first') }}`, {
                        params: {
                            ...params,
                            only_trash: true
                        },
                    })
                    .then((data) => {
                        this.filePage += 1;
                        this.files = data.data.files.data;
                        this.folders = data.data.folders;
                        this.base_path = data.data.base_path;
                    })
                    .then(() => {
                        this.delete_all = false;
                        this.selected_files = [];
                        this.lastScrollTarget = 0;
                        this.loading = false;
                        this.reloadIcon();
                    });
            },
            getFile(params = {}, next) {
                this.fileLoading = true;
                Axios
                    .get(`{{ route('admin-file-manager-files') }}`, {
                        params: {
                            ...params,
                            only_trash: true
                        }
                    })
                    .then((data) => {
                        this.files.push(...data.data.files.data);
                    })
                    .then(() => {
                        next();
                        this.fileLoading = false;
                        this.reloadIcon();
                    });
            },
            viewImage(file) {
                Fancybox.show([{
                    src: this.base_path + file.path,
                    caption: file.name,
                    type: "image",
                }]);
            },
            firstPage() {
                this.resetBreadcrumb();
                this.getData();
            },
            reloadPage() {
                if (this.currentParams.page) delete this.currentParams.page;
                this.getData(this.currentParams);
            },
            onScroll(el, offset_bottom = 0) {
                let target = el.target;
                let scroll = target.scrollTop;
                let scrollTarget = (target.scrollHeight - target.clientHeight) - offset_bottom;
                if (scrollTarget > this.lastScrollTarget && scroll >= scrollTarget) {
                    if (!this.loading && !this.fileLoading) {
                        this.lastScrollTarget = scrollTarget;
                        this.getFile({
                            ...this.currentParams,
                            page: this.filePage,
                        }, () => {
                            this.filePage += 1;
                        });
                    }
                }
            },
            onSearch(e) {
                this.loading = true;
                clearTimeout(this.delayQuery);
                const currentFolder = this.dataFolders[this.dataFolders.length - 1];
                this.delayQuery = setTimeout(this.getData({
                    q: e?.target?.value ?? '',
                    folder_id: currentFolder?.id ?? ''
                }), 500);
            },
            onOpenFolder(folder, option = null) {
                switch (option) {
                    case 'dbclick':
                        break;
                    default:
                        this.loading = true;
                        this.dataFolders.push(folder);
                        this.reloadIcon();
                        this.getData({
                            folder_id: folder.id
                        });
                        break;
                }
            },
            onBreadcrumbClick(folder, index) {
                this.loading = true;
                this.dataFolders.splice(index + 1, 1);
                this.reloadIcon();
                Axios
                    .get(`{{ route('admin-file-manager-first') }}`, {
                        params: {
                            folder_id: folder?.id
                        }
                    })
                    .then((data) => {
                        this.files = data.data.files;
                        this.folders = data.data.folders;
                        this.base_path = data.data.base_path;
                    })
                    .then(() => {
                        this.loading = false;
                        this.reloadIcon();
                    });
            },
            resetBreadcrumb() {
                this.dataFolders = [];
                this.reloadIcon();
            },
            onClose() {
                Alpine.store('animate').leave(".dialog-container", (res) => {
                    Alpine.store('page').active = false;
                    Alpine.store('page').options.afterClose(false);
                    this.selected_files = [];
                    this.dataFolders = [];
                    this.lastScrollTarget = 0;
                });
            },
            restoreBySelected() {
                this.dialog.open('confirmRestoreDialog');
            },
            deleteBySelected() {
                this.dialog.open('confirmDeleteDialog');
            },
            restoreAll() {
                this.restore_all = true;
                this.dialog.open('confirmRestoreDialog');
            },
            deleteAll() {
                this.delete_all = true;
                this.dialog.open('confirmDeleteDialog');
            },
            onSelect(file) {
                if (this.selected_files.includes(file)) {
                    this.selected_files = this.selected_files.filter(item => item !== file);
                } else {
                    this.selected_files.push(file);
                }
                this.reloadIcon();
            },
            onUnselectFiles() {
                this.selected_files = [];
                this.reloadIcon();
            },
            onRightClick(el, file, type) {
                el.preventDefault();
                let item = el.target;
                const currentOffset = item.closest('.file-item') ?? item.closest('.folder-item') ?? el.target;
                currentOffset.style.width = currentOffset.clientWidth + 'px';
                currentOffset.style.height = currentOffset.clientHeight + 'px';
                currentOffset.style.position = 'relative';
                currentOffset.style.zIndex = '9999999';
                currentOffset.style.background = "#ffffff";
                const {
                    top,
                    left
                } = this.offset(currentOffset);
                let positionX = left + currentOffset.clientWidth + 10;
                positionX = positionX + 180 > document.body.clientWidth ? left - 180 - 10 :
                    positionX;
                this.contentMenu = {
                    element: currentOffset,
                    show: true,
                    x: positionX,
                    y: top,
                    data: file,
                    type: type
                };
                this.reloadIcon();
            },
            offset(el) {
                var rect = el.getBoundingClientRect(),
                    scrollLeft = window.pageXOffset || document.documentElement.scrollLeft,
                    scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                return {
                    top: rect.top + scrollTop,
                    left: rect.left + scrollLeft
                }
            },
            closeContextMenu() {
                this.contentMenu.element?.removeAttribute('style');
                this.contentMenu = {
                    element: null,
                    show: false,
                    x: 0,
                    y: 0,
                    data: null
                };
            },
            showTooltip(el, file) {
                this.tooltip.show = false;
                clearTimeout(this.delayTooltip);
                this.delayTooltip = setTimeout(() => {
                    this.tooltip = {
                        show: true,
                        x: el.clientX + 10,
                        y: el.clientY + 10,
                        data: file,
                    };
                }, 1000);
            },
            hideTooltip() {
                clearTimeout(this.delayTooltip);
                this.tooltip = {
                    show: false,
                    x: 0,
                    y: 0,
                    data: null,
                };
            },
            isSelected(file, call_back) {
                return this.selected_files.includes(file) ? call_back ?? true : false;
            },
            selectedIndex(file) {
                return this.selected_files.findIndex(item => item.id == file.id) + 1;
            },
            reloadIcon() {
                feather.replace();
                setTimeout(() => {
                    feather.replace();
                }, 10);
            },
            dialog: {
                rootData: null,
                initData(root) {
                    this.rootData = root;
                },
                component: {
                    confirmDeleteDialog: false,
                    confirmRestoreDialog: false,
                },
                data: {
                    confirmDeleteDialog: {},
                    confirmRestoreDialog: {},
                },
                open(dialogRef) {
                    this.component[dialogRef] = true;
                    this.data[dialogRef] = this.rootData;
                },
                close(dialogRef, data) {
                    this.rootData.delete_all = false;
                    this.rootData.restore_all = false;
                    this.component[dialogRef] = false;
                    if (data) {
                        const currentFolder = this.rootData?.dataFolders[this.rootData?.dataFolders.length - 1];
                        this.rootData.getData({
                            folder_id: currentFolder?.id ?? ''
                        });
                    }
                },
            }
        }));
    </script>
</div>
