@component('admin::components.dialog', ['dialog' => 'confirmDialog'])
    <div x-data="confirmDialog" class="dialog-form" style="width: 300px">
        <div class="dialog-form-header">
            <h3 x-text="data?.title"></h3>
        </div>
        <div class="dialog-form-body">
            <div class="form-row">
                <p x-html="data?.message"></p>
            </div>
        </div>
        <div class="dialog-form-footer">
            <button type="button" class="close" @click="$store.confirmDialog.close(false)" x-text="data?.btnClose || 'Close'"></button>
            <button type="button" @click="onConfirm" x-bind:disabled="disabled || loading">
                <span x-text="data?.btnSave || 'Save'"></span>
                <div class="loader" style="display: none" x-show="loading"></div>
            </button>
        </div>
    </div>
    <script>
        Alpine.data("confirmDialog", () => ({
            data: null,
            disabled: false,
            loading: false,
            init() {
                this.data = this.$store.confirmDialog.data;
                feather.replace();
            },
            onConfirm() {
                this.disabled = true;
                this.$store.confirmDialog.close(true);
            }
        }))
    </script>
@endcomponent
