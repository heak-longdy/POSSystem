<div class="dialog" x-data="confirmRestoreDialog">
    <div class="dialog-container">
        <form class="dialog-form" id="dialog-form" style="width: 300px" @submit.prevent>
            <div class="dialog-form-header">
                <h3>@lang('file-manager.restore-file.title')</h3>
            </div>
            <div class="dialog-form-body">
                <div class="form-row">
                    <template x-if="!data?.restore_all">
                        <p>@lang('file-manager.restore-file.confirm') <b
                                x-text="restoreFiles?.length+' files and folders'"></b>?</p>
                    </template>
                    <template x-if="data?.restore_all">
                        <p>@lang('file-manager.restore-file.confirm') <b>All files and folders</b>?</p>
                    </template>
                </div>
            </div>
            <div class="dialog-form-footer">
                <button type="button" class="close"
                    @click="onClose()">@lang('file-manager.restore-file.button.cancel')</button>
                <button type="button" @click="onSave()"
                    x-bind:disabled="form.disabled && form.loading">@lang('file-manager.restore-file.button.restore')
                    <div class="loader" style="display: none" x-show="form.disabled && form.loading"></div>
                </button>
            </div>
        </form>
    </div>
    <script>
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
                this.data = this.dialog.data['confirmRestoreDialog'];
                this.restoreFiles = this.data.contentMenu?.show ? [this.data.contentMenu?.data] : this.data
                    .selected_files;
                Alpine.store('animate').enter(this.$root.children[0], () => {
                    this.closeContextMenu();
                });
            },
            onClose(data = null) {
                Alpine.store('animate').leave(this.$root.children[0], () => {
                    this.dialog.close('confirmRestoreDialog', data);
                });
            },
            onSave() {
                this.form.loading = true;
                this.form.disabled = true;
                Axios.put(`{{ route('admin-file-manager-restore-all') }}`, {
                    data: this.restoreFiles,
                    all: this.data.restore_all || '',
                }).then((response) => {
                    this.onClose(response.data);
                }).catch(error => {
                    this.form.loading = false;
                    this.form.disabled = false;
                });
            }
        }));
    </script>
</div>
