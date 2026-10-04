<script>
    (function() {
        if (window.__FILE_MANAGER_SCRIPTS_LOADED__) return;
        window.__FILE_MANAGER_SCRIPTS_LOADED__ = true;

        if (window.moment) {
            moment.locale('{{ App::currentLocale() }}');
        }

        if (window.Alpine) {
            Alpine.store('page', {
                active: false,
                full_page: false,
                options: {
                    multiple: false,
                    returnUrl: null,
                    afterClose: () => {}
                }
            });

            // Ensure Alpine.store('animate') is always available
            if (!Alpine.store('animate')) {
                Alpine.store('animate', {
                    enter: (target, fn) => {
                        if (!target) return;
                        if (window.anime) {
                            anime({
                                targets: target,
                                scale: [0.9, 1],
                                opacity: [0, 1],
                                direction: 'forwards',
                                easing: 'easeInSine',
                                duration: 200,
                                complete: (res) => { fn ? fn(res) : null; }
                            });
                        } else if (fn) {
                            fn();
                        }
                    },
                    leave: (target, fn) => {
                        if (!target) return;
                        if (window.anime) {
                            anime({
                                targets: target,
                                scale: [1, 0.9],
                                opacity: [1, 0],
                                direction: 'forwards',
                                easing: 'easeOutSine',
                                duration: 200,
                                complete: (res) => { fn ? fn(res) : null; }
                            });
                        } else if (fn) {
                            fn();
                        }
                    }
                });
            }

            // 1. File Manager Main Component
            Alpine.data('fileManager', () => ({
                files: [],
                folders: [],
                filePage: 1,
                base_path: '',
                loading: false,
                fileLoading: false,
                selected_file: [],
                dataFolders: [],
                delayQuery: null,
                currentParams: {},
                lastScrollTarget: 0,
                skeletons: new Array(10),
                contentMenu: {
                    element: null,
                    show: false,
                    x: 0,
                    y: 0,
                    data: null,
                    type: null
                },
                delayTooltip: null,
                tooltip: {
                    show: false,
                    x: 0,
                    y: 0,
                    data: null
                },
                options: {
                    multiple: false,
                },
                init() {
                    const storeOptions = Alpine.store('page')?.options;
                    this.options.multiple = storeOptions ? !!storeOptions.multiple : false;
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
                            params: params
                        })
                        .then((res) => {
                            this.filePage += 1;
                            this.files = res.data?.files?.data || [];
                            this.folders = res.data?.folders || [];
                            this.base_path = res.data?.base_path || '';
                        })
                        .catch((err) => {
                            console.error('File manager load error:', err);
                        })
                        .finally(() => {
                            this.lastScrollTarget = 0;
                            this.loading = false;
                            this.reloadIcon();
                        });
                },
                getFile(params = {}, next) {
                    this.fileLoading = true;
                    Axios
                        .get(`{{ route('admin-file-manager-files') }}`, {
                            params: params
                        })
                        .then((res) => {
                            if (res.data?.files?.data) {
                                this.files.push(...res.data.files.data);
                            }
                        })
                        .catch((err) => {
                            console.error('File manager pagination error:', err);
                        })
                        .finally(() => {
                            if (typeof next === 'function') next();
                            this.fileLoading = false;
                            this.reloadIcon();
                        });
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
                        if (!this.loading && !this.fileLoading && this.files.length > 0) {
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
                    const query = e?.target?.value ?? '';
                    this.delayQuery = setTimeout(() => {
                        this.getData({
                            q: query,
                            folder_id: currentFolder?.id ?? ''
                        });
                    }, 400);
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
                viewImage(file) {
                    if (window.Fancybox) {
                        Fancybox.show([{
                            src: this.base_path + file.path,
                            caption: file.name,
                            type: "image",
                        }]);
                    }
                },
                onBreadcrumbClick(folder, index) {
                    this.loading = true;
                    this.dataFolders.splice(index + 1);
                    this.getData({
                        folder_id: folder?.id
                    });
                    this.reloadIcon();
                },
                resetBreadcrumb() {
                    this.dataFolders = [];
                    this.reloadIcon();
                },
                onClose() {
                    Alpine.store('animate')?.leave(".dialog-container", (res) => {
                        const pageStore = Alpine.store('page');
                        if (pageStore) {
                            pageStore.active = false;
                            if (typeof pageStore.options?.afterClose === 'function') {
                                pageStore.options.afterClose(false);
                            }
                        }
                        this.selected_file = [];
                        this.dataFolders = [];
                        this.lastScrollTarget = 0;
                    });
                },
                onChooseFiles() {
                    Alpine.store('animate')?.leave(".dialog-container", (res) => {
                        const pageStore = Alpine.store('page');
                        if (pageStore) {
                            pageStore.active = false;
                            if (typeof pageStore.options?.afterClose === 'function') {
                                pageStore.options.afterClose(this.selected_file, this.base_path);
                            }
                        }
                        this.selected_file = [];
                        this.dataFolders = [];
                        this.lastScrollTarget = 0;
                    });
                },
                onSelectFiles(file) {
                    if (this.options.multiple) {
                        if (this.isSelected(file)) {
                            this.selected_file = this.selected_file.filter(item => item.id !== file.id);
                        } else {
                            this.selected_file.push(file);
                        }
                    } else {
                        this.selected_file = [file];
                    }
                    this.reloadIcon();
                },
                onUnselectFiles() {
                    this.selected_file = [];
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
                    positionX = positionX + 180 > document.body.clientWidth ? left - 180 - 10 : positionX;
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
                        data: null,
                        type: null
                    };
                },
                copyLink(file) {
                    this.closeContextMenu();
                    let text = this.base_path + file.path;
                    let el = document.createElement('textarea');
                    el.value = text;
                    el.setAttribute('readonly', '');
                    el.style.position = 'absolute';
                    el.style.left = '-9999px';
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
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
                    return this.selected_file.find(item => item.id == file.id) ? (call_back ?? true) : false;
                },
                selectedIndex(file) {
                    return this.selected_file.findIndex(item => item.id == file.id) + 1;
                },
                reloadIcon() {
                    if (window.feather && typeof window.feather.replace === 'function') {
                        feather.replace();
                        setTimeout(() => {
                            if (window.feather) feather.replace();
                        }, 50);
                    }
                },
                dialog: {
                    rootData: null,
                    initData(root) {
                        this.rootData = root;
                    },
                    component: {
                        createFolderDialog: false,
                        uploadFileDialog: false,
                        renameFolderDialog: false,
                        trashConfirmDialog: false
                    },
                    data: {
                        createFolderDialog: {},
                        uploadFileDialog: {},
                        renameFolderDialog: {},
                        trashConfirmDialog: {}
                    },
                    open(dialogRef) {
                        this.component[dialogRef] = true;
                        this.data[dialogRef] = this.rootData;
                    },
                    close(dialogRef, data) {
                        this.component[dialogRef] = false;
                        if (data && this.rootData) {
                            const currentFolder = this.rootData.dataFolders?.[this.rootData.dataFolders.length - 1];
                            this.rootData.getData({
                                folder_id: currentFolder?.id ?? ''
                            });
                        }
                    },
                }
            }));

            // 2. Trash Bin Component
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
                    data: null,
                    type: null
                },
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
                        .then((res) => {
                            this.filePage += 1;
                            this.files = res.data?.files?.data || [];
                            this.folders = res.data?.folders || [];
                            this.base_path = res.data?.base_path || '';
                        })
                        .catch(() => {})
                        .finally(() => {
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
                        .then((res) => {
                            if (res.data?.files?.data) {
                                this.files.push(...res.data.files.data);
                            }
                        })
                        .catch(() => {})
                        .finally(() => {
                            if (typeof next === 'function') next();
                            this.fileLoading = false;
                            this.reloadIcon();
                        });
                },
                viewImage(file) {
                    if (window.Fancybox) {
                        Fancybox.show([{
                            src: this.base_path + file.path,
                            caption: file.name,
                            type: "image",
                        }]);
                    }
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
                    const query = e?.target?.value ?? '';
                    this.delayQuery = setTimeout(() => {
                        this.getData({
                            q: query,
                            folder_id: currentFolder?.id ?? ''
                        });
                    }, 400);
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
                    this.dataFolders.splice(index + 1);
                    this.reloadIcon();
                    Axios
                        .get(`{{ route('admin-file-manager-first') }}`, {
                            params: {
                                folder_id: folder?.id,
                                only_trash: true
                            }
                        })
                        .then((res) => {
                            this.files = res.data?.files || [];
                            this.folders = res.data?.folders || [];
                            this.base_path = res.data?.base_path || '';
                        })
                        .catch(() => {})
                        .finally(() => {
                            this.loading = false;
                            this.reloadIcon();
                        });
                },
                resetBreadcrumb() {
                    this.dataFolders = [];
                    this.reloadIcon();
                },
                onClose() {
                    Alpine.store('animate')?.leave(".dialog-container", (res) => {
                        const pageStore = Alpine.store('page');
                        if (pageStore) {
                            pageStore.active = false;
                            if (typeof pageStore.options?.afterClose === 'function') {
                                pageStore.options.afterClose(false);
                            }
                        }
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
                    positionX = positionX + 180 > document.body.clientWidth ? left - 180 - 10 : positionX;
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
                        data: null,
                        type: null
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
                    return this.selected_files.includes(file) ? (call_back ?? true) : false;
                },
                selectedIndex(file) {
                    return this.selected_files.findIndex(item => item.id == file.id) + 1;
                },
                reloadIcon() {
                    if (window.feather && typeof window.feather.replace === 'function') {
                        feather.replace();
                        setTimeout(() => {
                            if (window.feather) feather.replace();
                        }, 50);
                    }
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
                        if (this.rootData) {
                            this.rootData.delete_all = false;
                            this.rootData.restore_all = false;
                        }
                        this.component[dialogRef] = false;
                        if (data && this.rootData) {
                            const currentFolder = this.rootData.dataFolders?.[this.rootData.dataFolders.length - 1];
                            this.rootData.getData({
                                folder_id: currentFolder?.id ?? ''
                            });
                        }
                    },
                }
            }));

            // 3. Settings Component
            Alpine.data('settingsPage', () => ({
                init() {
                    this.reloadIcon();
                },
                reloadIcon() {
                    if (window.feather && typeof window.feather.replace === 'function') {
                        feather.replace();
                        setTimeout(() => {
                            if (window.feather) feather.replace();
                        }, 50);
                    }
                },
                onClose() {
                    Alpine.store('animate')?.leave(".dialog-container", (res) => {
                        const pageStore = Alpine.store('page');
                        if (pageStore) {
                            pageStore.active = false;
                            if (typeof pageStore.options?.afterClose === 'function') {
                                pageStore.options.afterClose(false);
                            }
                        }
                    });
                },
            }));

            // 4. Create Folder Dialog Component
            Alpine.data('createFolderDialog', () => ({
                data: null,
                form: {
                    value: {
                        name: '',
                    },
                    validate_message: {},
                    loading: false,
                    disabled: false,
                },
                init() {
                    Alpine.store('animate')?.enter(this.$root.children[0]);
                    this.data = this.dialog?.data?.['createFolderDialog']?.dataFolders || [];
                },
                onClose(data = null) {
                    Alpine.store('animate')?.leave(this.$root.children[0], () => {
                        this.dialog?.close('createFolderDialog', data);
                    });
                },
                onSave() {
                    if (typeof $validatorOption === 'function') {
                        $validatorOption('#dialog-form', {
                            folder_name: {
                                required: true,
                            }
                        }, {
                            inputClass: "required",
                        }, (result) => {
                            if (result.every(i => i == false)) {
                                this.form.loading = true;
                                this.form.disabled = true;
                                Axios.post(`{{ route('admin-file-manager-create-folder') }}`, {
                                    ...this.form.value,
                                    parent_id: this.data?.[this.data.length - 1]?.id ?? null,
                                }).then(response => {
                                    this.onClose(response.data);
                                }).catch(error => {
                                    this.form.loading = false;
                                    this.form.disabled = false;
                                    this.form.validate_message = error.response?.data?.message || {};
                                });
                            }
                        });
                    }
                }
            }));

            // 5. Rename Folder Dialog Component
            Alpine.data('renameFolderDialog', () => ({
                data: null,
                folder_id: null,
                form: {
                    value: {
                        name: '',
                    },
                    validate_message: {},
                    loading: false,
                    disabled: false,
                },
                init() {
                    Alpine.store('animate')?.enter(this.$root.children[0]);
                    this.data = this.dialog?.data?.['renameFolderDialog'];
                    this.folder_id = this.data?.contentMenu?.data?.id;
                    this.form.value.name = this.data?.contentMenu?.data?.name || '';
                },
                onClose(data = null) {
                    Alpine.store('animate')?.leave(this.$root.children[0], () => {
                        this.dialog?.close('renameFolderDialog', data);
                    });
                },
                onSave() {
                    if (typeof $validatorOption === 'function') {
                        $validatorOption('#dialog-form', {
                            folder_name: {
                                required: true,
                            }
                        }, {
                            inputClass: "required",
                        }, (result) => {
                            if (result.every(i => i == false)) {
                                this.form.loading = true;
                                this.form.disabled = true;
                                Axios.post(`{{ route('admin-file-manager-rename-folder') }}`, {
                                    ...this.form.value,
                                    parent_id: this.data?.dataFolders?.[this.data.dataFolders.length - 1]?.id ?? null,
                                    folder_id: this.folder_id,
                                }).then(response => {
                                    this.onClose(response.data);
                                }).catch(error => {
                                    this.form.loading = false;
                                    this.form.disabled = false;
                                    this.form.validate_message = error.response?.data?.message || {};
                                });
                            }
                        });
                    }
                }
            }));

            // 6. Upload File Dialog Component
            Alpine.data('uploadFile', () => ({
                data: null,
                form: {
                    value: {
                        files: []
                    },
                    validate_message: {},
                    loading: false,
                    disabled: false,
                },
                init() {
                    this.data = this.dialog?.data?.['uploadFileDialog']?.dataFolders || [];
                    this.reloadIcon();
                    Alpine.store('animate')?.enter(this.$root.children[0]);
                },
                reloadIcon() {
                    if (window.feather && typeof window.feather.replace === 'function') {
                        feather.replace();
                    }
                },
                onClose(data = null) {
                    Alpine.store('animate')?.leave(this.$root.children[0], () => {
                        this.dialog?.close('uploadFileDialog', data);
                    });
                },
                onSelectFile() {
                    const lastFolder = this.data?.[this.data.length - 1];
                    const url = `{{ route('admin-file-manager-upload') }}?folder_id=${lastFolder?.id ?? ''}`;
                    if (typeof $onUploadFile === 'function') {
                        $onUploadFile(url, true, 'image/*', (loading, data, file, percent, key, error) => {
                            if (this.checkExist(key)) {
                                this.form.value.files.forEach(item => {
                                    if (item.key === key) {
                                        if (error) {
                                            item.error = true;
                                        } else {
                                            item.percent = percent;
                                            item.loading = loading;
                                        }
                                    }
                                });
                            } else {
                                if (file) {
                                    this.form.value.files.push({
                                        key,
                                        file,
                                        percent,
                                        loading
                                    });
                                }
                            }
                            setTimeout(() => {
                                if (this.form.value.files.every(item => !item.loading)) {
                                    this.onClose(true);
                                }
                            }, 1000);
                            this.reloadIcon();
                        });
                    }
                },
                cancelUpload(file, index) {},
                checkExist(key) {
                    return this.form.value.files.some(item => item.key === key);
                },
                onSave() {
                    this.onClose(true);
                }
            }));

            // 7. Trash / Single Delete Confirmation Dialog
            Alpine.data('trashConfirmDialog', () => ({
                data: null,
                deleteFiles: [],
                form: {
                    value: {
                        to_trash: true,
                    },
                    validate_message: {},
                    loading: false,
                    disabled: false,
                },
                init() {
                    this.data = this.dialog?.data?.['trashConfirmDialog'];
                    if (this.data?.contentMenu) {
                        this.deleteFiles = [this.data.contentMenu];
                    }
                    Alpine.store('animate')?.enter(this.$root.children[0], () => {
                        this.closeContextMenu();
                    });
                },
                closeContextMenu() {
                    if (typeof this.dialog?.rootData?.closeContextMenu === 'function') {
                        this.dialog.rootData.closeContextMenu();
                    }
                },
                onClose(data = null) {
                    Alpine.store('animate')?.leave(this.$root.children[0], () => {
                        this.dialog?.close('trashConfirmDialog', data);
                    });
                },
                onSave() {
                    this.form.loading = true;
                    this.form.disabled = true;
                    const file = this.deleteFiles[0];
                    if (file?.type == 'file') {
                        Axios.delete(`{{ route('admin-file-manager-delete-file') }}`, {
                            params: {
                                ...this.form.value,
                                file_id: file.data?.id
                            }
                        }).then((response) => {
                            this.onClose(response.data);
                        }).catch(error => {
                            this.form.loading = false;
                            this.form.disabled = false;
                        });
                    } else if (file?.type == 'folder') {
                        Axios.delete(`{{ route('admin-file-manager-delete-folder') }}`, {
                            params: {
                                ...this.form.value,
                                folder_id: file.data?.id
                            }
                        }).then((response) => {
                            this.onClose(response.data);
                        }).catch(error => {
                            this.form.loading = false;
                            this.form.disabled = false;
                        });
                    } else {
                        this.onClose();
                    }
                }
            }));

            // 8. Bulk Confirm Delete Dialog (Trash Bin)
            Alpine.data('confirmDeleteDialog', () => ({
                data: null,
                deleteFiles: [],
                form: {
                    value: {},
                    validate_message: {},
                    loading: false,
                    disabled: false,
                },
                init() {
                    this.data = this.dialog?.data?.['confirmDeleteDialog'];
                    this.deleteFiles = this.data?.contentMenu?.show ? [this.data.contentMenu?.data] : (this.data?.selected_files || []);
                    Alpine.store('animate')?.enter(this.$root.children[0], () => {
                        this.closeContextMenu();
                    });
                },
                closeContextMenu() {
                    if (typeof this.dialog?.rootData?.closeContextMenu === 'function') {
                        this.dialog.rootData.closeContextMenu();
                    }
                },
                onClose(data = null) {
                    Alpine.store('animate')?.leave(this.$root.children[0], () => {
                        this.dialog?.close('confirmDeleteDialog', data);
                    });
                },
                onSave() {
                    this.form.loading = true;
                    this.form.disabled = true;
                    Axios.delete(`{{ route('admin-file-manager-delete-all') }}`, {
                        params: {
                            data: this.deleteFiles,
                            all: this.data?.delete_all || '',
                        }
                    }).then((response) => {
                        this.onClose(response.data);
                    }).catch(error => {
                        this.form.loading = false;
                        this.form.disabled = false;
                    });
                }
            }));

            // 9. Bulk Confirm Restore Dialog (Trash Bin)
            Alpine.data('confirmRestoreDialog', () => ({
                data: null,
                restoreFiles: [],
                form: {
                    value: {},
                    validate_message: {},
                    loading: false,
                    disabled: false,
                },
                init() {
                    this.data = this.dialog?.data?.['confirmRestoreDialog'];
                    this.restoreFiles = this.data?.contentMenu?.show ? [this.data.contentMenu?.data] : (this.data?.selected_files || []);
                    Alpine.store('animate')?.enter(this.$root.children[0], () => {
                        this.closeContextMenu();
                    });
                },
                closeContextMenu() {
                    if (typeof this.dialog?.rootData?.closeContextMenu === 'function') {
                        this.dialog.rootData.closeContextMenu();
                    }
                },
                onClose(data = null) {
                    Alpine.store('animate')?.leave(this.$root.children[0], () => {
                        this.dialog?.close('confirmRestoreDialog', data);
                    });
                },
                onSave() {
                    this.form.loading = true;
                    this.form.disabled = true;
                    Axios.put(`{{ route('admin-file-manager-restore-all') }}`, {
                        data: this.restoreFiles,
                        all: this.data?.restore_all || '',
                    }).then((response) => {
                        this.onClose(response.data);
                    }).catch(error => {
                        this.form.loading = false;
                        this.form.disabled = false;
                    });
                }
            }));
        }

        // Global trigger function for opening File Manager
        window.fileManager = (options) => {
            if (window.Alpine) {
                Alpine.store('page', {
                    active: 'all_files',
                    full_page: false,
                    options: options || {}
                });
                setTimeout(() => {
                    Alpine.store('animate')?.enter(".dialog-container", (res) => {
                        res?.animatables?.[0]?.target?.removeAttribute('style');
                    });
                    if (window.feather && typeof window.feather.replace === 'function') {
                        feather.replace();
                    }
                }, 50);
            }
        };
    })();
</script>
