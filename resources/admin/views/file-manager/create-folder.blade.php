<div class="dialog" x-data="createFolderDialog">
    <div class="dialog-container">
        <form class="dialog-form" id="dialog-form" style="width: 300px" @submit.prevent>
            <div class="dialog-form-header">
                <h3>@lang('file-manager.create-folder.title')</h3>
            </div>
            <div class="dialog-form-body">
                <div class="form-row no-label">
                    <input x-model="form.value.name" type="text" name="folder_name"
                        placeholder="@lang('file-manager.create-folder.form.folder_name.placeholder')"
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
                    @click="onClose()">@lang('file-manager.create-folder.button.cancel')</button>
                <button type="button" @click="onSave()" x-bind:disabled="form.disabled && form.loading">
                    @lang('file-manager.create-folder.button.create')
                    <div class="loader" style="display: none" x-show="form.disabled && form.loading"></div>
                </button>
            </div>
        </form>
    </div>
    <script>
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
                Alpine.store('animate').enter(this.$root.children[0]);
                this.data = this.dialog.data['createFolderDialog']?.dataFolders;
            },
            onClose(data = null) {
                Alpine.store('animate').leave(this.$root.children[0], () => {
                    this.dialog.close('createFolderDialog', data);
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
                        Axios.post(`{{ route('admin-file-manager-create-folder') }}`, {
                            ...this.form.value,
                            parent_id: this.data[this.data.length - 1]?.id ?? null,
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
