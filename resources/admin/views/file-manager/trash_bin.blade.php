<div class="file-manager-wrapper" x-data="trashBin" :class="{'full-page':fileManagerState.full_page}">
    <div class="file-manager-header" x-show="!fileManagerState.full_page">
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
                                        <img x-bind:src="base_path+file?.path" loading="lazy" decoding="async"
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
</div>
