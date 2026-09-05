@component('admin::components.dialog', ['dialog' => 'dialogOurService'])
    <div x-data="dialogOurService" class="dialog-form" style="width: 50rem">
        <style>
            .tabBlockGp {
                margin: 0 0 25px 0;
                border-bottom: 1.5px solid #8080805c;
            }

            .tabBlockGp>.tabBlock {
                width: fit-content;
                border-radius: 8px 8px 0 0;
                display: flex;
                align-items: center;
                grid-gap: 10px;
            }
        </style>
        <div class="dialog-form-header">
            {{-- <h3 x-text="data?.title"></h3> --}}
            <div class="form-admin">
                <form id="form" class="form-wrapper">
                    <div class="form-body" style="display: flex;grid-gap: 30px;">
                        <div
                            style="flex: 1;background: #f6f6f6;padding: 20px 20px 0 20px;border-radius: 10px;height: fit-content;">
                            <div class="row-2">
                                <div class="form-row iconInput">
                                    <label>Title <span>*</span> </label>
                                    <input type="text" x-model="title" name="title" placeholder="Enter title ..." :disabled="disabled">
                                    <i class='bx bx-font-family'></i>
                                    <label class="error" x-text="validateForm?.title"></label>
                                </div>
                            </div>
                            <div class="row-2">
                                <div class="form-row iconInput">
                                    <label>Status <span>*</span> </label>
                                    <input type="text" x-model="status" name="status" placeholder="Enter status ..." :disabled="disabled">
                                    <i class='bx bx-font-family'></i>
                                    <label class="error" x-text="validateForm?.status"></label>
                                </div>
                            </div>
                            <div class="row-2">
                                <div class="form-row">
                                    <label>Image</label>
                                    <div class="form-select-photo image" @click="selectImage(event)" :class="disabled?'disabled':''">
                                        <div class="select-photo" :class='{ active: image }'>
                                            <div class="icon">
                                                <i class='bx bx-cloud-upload'></i>
                                            </div>
                                            <div class="title">
                                                <p>@lang('global.form.image.placeholder')</p>
                                            </div>
                                        </div>
                                        <template x-if="image">
                                            <div class="image-view active">
                                                <img x-bind:src="baseImageUrl + image" alt="">
                                            </div>
                                        </template>
                                        <input type="hidden" x-model="image" name="image" autocomplete="off"
                                            role="presentation">
                                    </div>
                                    <label class="error" x-text="validateForm?.image"></label>
                                </div>
                            </div>
                        </div>
                        {{-- <div style="background: #dbdddfe6;width: 1px;margin-top: 7px;"></div> --}}
                        <div style="flex:1.3;">
                            <div class="tabBlockGp">
                                <div class="tabBlock">
                                    <i class='bx bx-sort-down'></i><span>Details</span>
                                </div>
                            </div>
                            <div class="row-2">
                                <div class="form-row iconInput">
                                    <label>Title (1)<span>*</span> </label>
                                    <input type="text" x-model="dataForm.ser_title1" name="ser_title1"
                                        placeholder="Enter title ..." :disabled="disabled">
                                    <i class='bx bx-font-family'></i>
                                    <label class="error" x-text="validateForm?.ser_title1"></label>
                                </div>
                            </div>
                            <div class="form-row">
                                <label>Descriptions (1)<span>*</span></label>
                                <textarea type="text" rows="3" x-model="dataForm.ser_des1" name="ser_des1" placeholder="" :disabled="disabled"></textarea>
                                <label class="error" x-text="validateForm?.ser_des1"></label>
                            </div>
                            <div class="row-2">
                                <div class="form-row iconInput">
                                    <label>Title (Optional 2)</label>
                                    <input type="text" x-model="dataForm.ser_title2" name="ser_title2"
                                        placeholder="Enter title ..." :disabled="disabled">
                                    <i class='bx bx-font-family'></i>
                                    <label class="error" x-text="validateForm?.ser_title2"></label>
                                </div>
                            </div>
                            <div class="form-row">
                                <label>Descriptions (Optional 2)</label>
                                <textarea type="text" rows="3" x-model="dataForm.ser_des2" name="ser_des2" placeholder="" id="ser_des2" :disabled="disabled"></textarea>
                                <label class="error" x-text="validateForm?.ser_des2"></label>
                            </div>
                            <div class="row-2">
                                <div class="form-row iconInput">
                                    <label>Title (Optional 3)</label>
                                    <input type="text" x-model="dataForm.ser_title3" name="ser_title3"
                                        placeholder="Enter title ..." :disabled="disabled">
                                    <i class='bx bx-font-family'></i>
                                    <label class="error" x-text="validateForm?.ser_title3"></label>
                                </div>
                            </div>
                            <div class="form-row">
                                <label>Descriptions (Optional 3)</label>
                                <textarea type="text" rows="3" x-model="dataForm.ser_des3" name="ser_des3" placeholder="" id="ser_des3" :disabled="disabled"></textarea>
                                <label class="error" x-text="validateForm?.ser_des3"></label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="dialog-form-footer">
            <button type="button" class="close" @click="$store.dialogOurService.close(false)"
                x-text="data?.btnClose || 'Close'" x-bind:disabled="disabled || loading"></button>
            <button type="button" @click="submitForm" x-bind:disabled="disabled || loading">
                <i class='bx bx-save' x-show="!loading"></i>
                <div class="loader" x-show="loading" style="margin-right: 5px;margin-left: 0;display: none"></div>
                <span x-text="data?.btnSave || 'Save Change'"></span>
            </button>
        </div>
    </div>
    @include('admin::file-manager.popup')
    <script>
        Alpine.data("dialogOurService", () => ({
            data: null,
            disabled: false,
            loading: false,
            image: null,
            baseImageUrl: "{{ asset('file_manager') }}",
            title: "",
            status:"1",
            id: "",
            validateForm: {},
            dataForm: {
                ser_id1: "",
                ser_title1: "",
                ser_des1: "",
                ser_id2: "",
                ser_title2: "",
                ser_des2: "",
                ser_id3: "",
                ser_title3: "",
                ser_des3: ""
            },
            init() {
                this.data = this.$store.dialogOurService?.data;
                if (this.data) {
                    this.title = this.data?.title;
                    this.status = this.data?.status;
                    this.image = this.data?.image;
                    this.id = this.data?.id ?? "";
                }
                if (this.data?.model_setting_de?.length > 0) {
                    this.data?.model_setting_de.forEach((val, index) => {
                        this.dataForm[`ser_id${index+1}`] = val?.id ?? "";
                        this.dataForm[`ser_title${index+1}`] = val?.title ?? "";
                        this.dataForm[`ser_des${index+1}`] = val?.description ?? "";
                    });
                }
                // Transform the data
                // const transformedData = this.convertToArrayBySerId(this.dataForm);

                // // Output the transformed data
                // console.log(transformedData);
                // console.log(this.dataForm);
            },
            onConfirm() {
                this.disabled = true;
                this.$store.dialogOurService.close(true);
            },
            selectImage() {
                fileManager({
                    multiple: false,
                    afterClose: (data, basePath) => {
                        if (data?.length > 0) {
                            this.image = data[0].path;
                        }
                    }
                })
            },
            convertToArrayBySerId(data) {
                const result = [];
                const keysCount = Object.keys(data).length / 3; // Assuming each service has 3 keys

                for (let i = 1; i <= keysCount; i++) {
                    const idKey = `ser_id${i}`;
                    const titleKey = `ser_title${i}`;
                    const desKey = `ser_des${i}`;

                    // Check if the current idKey exists in the original data
                    if (data[idKey] !== undefined) {
                        result.push({
                            ser_id: data[idKey],
                            ser_title: data[titleKey],
                            ser_des: data[desKey],
                        });
                    }
                }

                return result;
            },
            submitForm() {
                let delayQuery = null;
                this.loading = true;
                this.disabled = true;
                clearTimeout(this.delayQuery);
                delayQuery = setTimeout(() => {
                    let formData = {
                        'image': this.image,
                        'title': this.title,
                        'status': this.status,
                        'id': this.id,
                        ...this.dataForm,
                        'details': this.convertToArrayBySerId(this.dataForm ?? [])
                    };
                    Axios({
                            method: 'post',
                            url: '{{ route('admin-our-service-store') }}',
                            data: formData
                        })
                        .then((response) => {
                            this.$store.dialogOurService.close(response);
                            this.loading = false;
                            this.disabled = false;
                        })
                        .catch((error) => {
                            console.log(error,"err");
                            this.validateForm = error?.response?.data?.errors;
                            this.loading = false;
                            this.disabled = false;
                            console.log(this.validateForm, 'dataError');
                        });
                }, 500);

            },
        }))
    </script>
@endcomponent
