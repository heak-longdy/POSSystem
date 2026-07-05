<div class="dialog" x-data="confirmDeleteDialog">
    <div class="dialog-container">
        <form class="dialog-form" id="dialog-form" style="width: 300px" @submit.prevent>
            <div class="dialog-form-header">
                <h3>@lang('file-manager.delete-file.title')</h3>
            </div>
            <div class="dialog-form-body">
                <div class="form-row">
                    {{-- <template x-if="deleteFiles?.length > 1">
                        <p>@lang('file-manager.delete-file.confirm')?</p>
                        <template x-for="(item,index) in deleteFiles">
                            <p><span x-text="index+1"></span>.<b x-text="item?.name"></b></p>
                        </template>
                    </template> --}}
                    <template x-if="!data?.delete_all">
                        <p>@lang('file-manager.delete-file.confirm') <b
                                x-text="deleteFiles?.length+' files and folders'"></b>?</p>
                    </template>
                    <template x-if="data?.delete_all">
                        <p>@lang('file-manager.delete-file.confirm') <b>All files and folders</b>?</p>
                    </template>
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
                this.data = this.dialog.data['confirmDeleteDialog'];
                this.deleteFiles = this.data.contentMenu?.show ? [this.data.contentMenu?.data] : this.data
                    .selected_files;
                Alpine.store('animate').enter(this.$root.children[0], () => {
                    this.closeContextMenu();
                });
            },
            onClose(data = null) {
                Alpine.store('animate').leave(this.$root.children[0], () => {
                    this.dialog.close('confirmDeleteDialog', data);
                });
            },
            onSave() {
                this.form.loading = true;
                this.form.disabled = true;
                Axios.delete(`{{ route('admin-file-manager-delete-all') }}`, {
                    data: {
                        all: this.data.delete_all || '',
                        data: this.deleteFiles,
                    }
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
