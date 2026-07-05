<div class="dialog" x-data="renameFolderDialog">
    <div class="dialog-container">
        <form class="dialog-form" id="dialog-form" style="width: 300px" @submit.prevent>
            <div class="dialog-form-header">
                <h3>@lang('file-manager.rename-folder.title')</h3>
            </div>
            <div class="dialog-form-body">
                <div class="form-row no-label">
                    <input x-model="form.value.name" type="text" name="folder_name"
                        placeholder="@lang('file-manager.rename-folder.form.folder_name.placeholder')"
                        x-bind:disabled="form.disabled">
                    <template x-if="!form.loading">
                        <template x-for="message in form.validate_message?.name">
                            <span class="error" x-text="message"></span>
                        </template>
                    </template>
                </div>
            </div>
            <div class="dialog-form-footer">
                <button type="button" class="close"
                    @click="onClose()">@lang('file-manager.rename-folder.button.cancel')</button>
                <button type="button" @click="onSave()"
                    x-bind:disabled="form.disabled && form.loading">@lang('file-manager.rename-folder.button.save')
                    <div class="loader" style="display: none" x-show="form.disabled && form.loading"></div>
                </button>
            </div>
        </form>
    </div>
    <script>
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
                Alpine.store('animate').enter(this.$root.children[0]);
                this.data = this.dialog.data['renameFolderDialog'];
                this.folder_id = this.data.contentMenu.data?.id;
                this.form.value.name = this.data.contentMenu.data?.name;
            },
            onClose(data = null) {
                Alpine.store('animate').leave(this.$root.children[0], () => {
                    this.dialog.close('renameFolderDialog', data);
                });
            },
            onSave() {
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
                            parent_id: this.data?.dataFolders[this.data?.dataFolders?.length -
                                    1]
                                ?.id ?? null,
                            folder_id: this.folder_id,
                        }).then(response => {
                            this.onClose(response.data);
                        }).catch(error => {
                            this.form.loading = false;
                            this.form.disabled = false;
                            this.form.validate_message = error.response.data?.message;
                        });
                    }
                });
            }
        }));
    </script>
</div>
