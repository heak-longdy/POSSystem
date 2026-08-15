@component('admin::components.dialog', ['dialog' => 'confirmDialog'])
    <div x-data="confirmDialog" class="dialog-form" x-bind:style="{ width: data?.width }"
        style="text-align: center;align-items: center;">
        <div class="dialog-form-header" style="justify-content: center;">
            <i class='bx bx-question-mark'
                style="font-size: 55px;margin-bottom: 15px; border: 1px solid rgba(255, 0, 0, 0.8); color:rgb(255 0 0 / 61%); border-radius: 50%;"></i>
        </div>
        <div class="dialog-form-body" style="padding: 15px 30px;">
            <div class="form-row" style="margin-bottom: 10px;">
                <p x-html="data?.message" style="text-align: left;font-weight: 600;"></p>
            </div>
            <template x-if="typeAction =='delete'">
                <div style="display: flex; align-items: center;grid-gap: 10px;margin: 0 5px; font-size: 14px;">
                    <label class="containerInputCheckBox">
                        <input :checked="statusTrash" type="checkbox" x-model="statusTrash">
                        <div class="checkmark"></div>
                    </label>
                    <label>Move to trash.</label>
                </div>
            </template>
        </div>
        <div class="dialog-form-footer" style="padding: 0px 30px 15px;width: 100%;">
            <button type="button" class="close" @click="$store.confirmDialog.close(false)"
                x-text="data?.btnClose || 'Close'" x-bind:disabled="disabled || loading"
                style="margin-right: 10px;background: none !important;"></button>
            <button type="button" @click="onConfirm" x-bind:disabled="disabled || loading" style="border-radius: 25px;">
                <span class='bx bx-loader-alt spinLoading bx-spin' x-show="loading"
                    style="margin-right: 10px;display: none;"></span>
                <span x-text="data?.btnSave || 'Save'"></span>
            </button>
        </div>
    </div>
    <script>
        Alpine.data("confirmDialog", () => ({
            data: null,
            disabled: false,
            loading: false,
            statusTrash: true,
            urlRute: "#",
            typeAction: "",
            init() {
                this.data = this.$store.confirmDialog.data;
                console.log(this.data,'data----');
                this.typeAction = this.data?.typeAction;
                console.log(this.typeAction,' this.typeAction');
                this.funStatusTrash(url=>{ this.urlRute = url });
                console.log( this.urlRute,' this.urlRute');
            },
            funStatusTrash(cb) {
                let Status = this.data?.item?.status == 1 ? 2 : 1;
                const urlDelete = `/admin/${this.data.urlName}/delete/${this.data?.item?.id}`;
                const urlDestory = `/admin/${this.data.urlName}/destroy/${this.data?.item?.id}`;
                const urlRestore = `/admin/${this.data.urlName}/restore/${this.data?.item?.id}`;
                const urlStatus = `/admin/${this.data.urlName}/status/${this.data?.item?.id}/${Status}`;
                let urlRute = "";
                if (this.typeAction == 'restore') {
                    urlRute = urlRestore;
                } else if (this.typeAction == 'delete') {
                    urlRute = this.statusTrash ? urlDelete : urlDestory;
                } else if (this.typeAction == 'destroy') {
                    urlRute = urlDestory;
                } else if (this.typeAction == 'status') {
                    urlRute = urlStatus;
                }
                cb(urlRute)
            },
            onConfirm() {
                if (this.typeAction == 'manual') {
                    this.$store.confirmDialog.close(true);
                    return;
                }

                this.funStatusTrash(url => {
                    this.disabled = true;
                    this.loading = true;

                    setTimeout(async () => {
                        Axios({
                            url: url,
                            method: 'POST',
                            data: {
                                ...this.data?.item
                            }
                        }).then((res) => {
                            if (res.data.message == "success") {
                                this.$store.confirmDialog.close(true);
                            }
                        }).catch((e) => {
                            this.validate = e.response.data.errors;
                            this.disabled = false;
                            this.loading = false;
                        }).finally(() => {
                            this.disabled = false;
                            this.loading = false;
                        });
                    }, 500);
                });
            }
        }))
    </script>
@endcomponent
