<div class="dialog" x-data="confirmDialog">
    <div class="dialog-container">
        <form class="dialog-form" id="dialog-form" style="width: 300px" @submit.prevent>
            <div class="dialog-form-header">
                <h3>@lang('file-manager.delete-file.title')</h3>
            </div>
            <div class="dialog-form-body">
                <div class="form-row">
                    <template x-for="(item, index) in deleteFiles">
                        <p>@lang('file-manager.delete-file.confirm')<b x-text="item?.data?.name"></b>?</p>
                    </template>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault"
                        x-model="form.value.to_trash" />
                    <label class="form-check-label"
                        for="flexSwitchCheckDefault">@lang('file-manager.delete-file.form.to_trash.label')</label>
                </div>
            </div>
            <div class="dialog-form-footer">
                <button type="button" class="close"
                    @click="onClose()">@lang('file-manager.delete-file.button.cancel')</button>
                <button type="button" @click="onSave()"
                    x-bind:disabled="form.disabled && form.loading">@lang('file-manager.delete-file.button.delete')
                    <div class="loader" style="display: none" x-show="form.disabled && form.loading"></div>
                </button>
            </div>
        </form>
    </div>
    <script>
        Alpine.data('confirmDialog', () => ({
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
                this.data = this.dialog.data['confirmDialog'];
                this.deleteFiles.push(this.data.contentMenu);
                Alpine.store('animate').enter(this.$root.children[0], () => {
                    this.closeContextMenu();
                });
            },
            onClose(data = null) {
                Alpine.store('animate').leave(this.$root.children[0], () => {
                    this.dialog.close('confirmDialog', data);
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
                            file_id: file.data.id
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
                            folder_id: file.data.id
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
    </script>
</div>
