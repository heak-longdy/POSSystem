<template x-data="{}" x-if="$store?.changePassword?.active">
    <div class="formDialogLayout" x-data="xChangePassword">
        <div class="formDialog" style="width: 30%;">
            <div class="formDialogHeader">
                <div class="hTitle">Change Password (User)</div>
                <button @click="closeUp()"><i class='bx bx-x'></i></button>
            </div>
            <div class="formDialogBody form-admin">
                <form id="form" class="form-wrapper">
                    <div class="form-body">
                        <div class="row">
                            <div class="form-row">
                                <label>@lang('user.form.password.label')<span>*</span> </label>
                                <input type="password" x-model="formData.password" :disable="formData.disable"
                                    name="password" placeholder="@lang('user.form.password.placeholder')" autocomplete="new-password">
                                <template x-for="item in dataError?.password">
                                    <div class="errorCenter">
                                        <span class="error" x-text="item">Error</span>
                                    </div>
                                </template>
                            </div>
                            <div class="form-row">
                                <label>@lang('user.form.password_confirmation.label')<span>*</span> </label>
                                <input type="password" x-model="formData.confirm_password" :disable="formData.disable"
                                    name="confirm_password" placeholder="@lang('user.form.password_confirmation.placeholder')">
                                <template x-for="item in dataError?.confirm_password">
                                    <div class="errorCenter">
                                        <span class="error" x-text="item">Error</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="formDialogFooter">
                <button class="button" @click="Submit()"><i class='bx bxs-save'></i>Submit</button>
                <button class="close" @click="closeUp()"><i class='bx bx-x'></i>Close</button>
            </div>
            <template x-if="loadingSubmit">
                @include('admin::components.spinner')
            </template>
        </div>

    </div>
</template>
<script>
    Alpine.data('xChangePassword', () => ({
        loading: false,
        loadingSubmit: false,
        dataError: null,
        dataClosePrint: false,
        total: 0,
        serviceTotal: 0,
        packageType: '',
        formData: {
            id:null,
            password: null,
            confirm_password: null
        },
        baseImageUrl: "{{ asset('file_manager') }}",
        paymentPopup: false,
        data: [],
        dataCheckAcc: [],
        async init() {
            const data = this.$store.changePassword?.data;
            console.log(data.id,'datatat');
            if (this.$store.changePassword.data) {
                this.formData.id = this.$store.changePassword.data.id;
            }
            console.log(this.$store.changePassword.data.id,'this.$store.changePassword.data.id');
            console.log(this.formData,'formDataformDataformData');
        },
        closeUp() {
            this.dataError = null;
            Alpine.store('changePassword').active = false;
        },
        async fetchData(url, callback) {
            await fetch(url, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    }
                })
                .then(async (res) => {
                    let data = await res.json();
                    if (data) {
                        callback(data);
                    }
                })
                .catch(() => {})
                .finally(() => {});
        },
        paymentPackage(type) {
            this.packageType = type;
            this.formData.payment_type = type;
            this.dataError.payment_type = [];
        },
        Submit() {
            this.btnSubmit = 'Submit';
            var status = 'change password';
            Swal.fire({
                customClass: "confirm-message",
                icon: "warning",
                html: `Are you sure to ${status} user ?`,
                confirmButtonText: `${this.btnSubmit}`,
                cancelButtonText: "Cancel",
                focusConfirm: false,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    if (result.value == 1) {
                        this.loadingSubmit = true;
                        this.dataError = null;
                        this.formData.disable = true;
                        let url = `{!! route('admin-user-save-password') !!}`;
                        let dataForm = this.formData;
                        setTimeout(() => {
                            Axios({
                                    method: 'post',
                                    url: url,
                                    data: dataForm
                                })
                                .then((response) => {
                                    this.loadingSubmit = false;
                                    this.formData.disable = false;
                                    this.$store.create.active = false;

                                    reloadData('{!! url()->current() !!}');
                                })
                                .catch((error) => {
                                    this.loadingSubmit = false;
                                    this.dataError = error?.response?.data
                                        ?.errors;
                                    this.formData.disable = false;
                                });
                        }, 1000);
                    }
                }
            });
        },
        selectImage() {
            fileManager({
                multiple: false,
                afterClose: (data, basePath) => {
                    if (data?.length > 0) {
                        this.formData.image = data[0].path;
                    }
                }
            })
        },
        clickSuccess() {
            this.paymentPopup = false;
            this.closeUp();
            window.location.href = '#';
        },
    }));
</script>
<script>
    Alpine.store('changePassword', {
        active: false,
        data: null
    });
    window.changePassword = (result) => {
        Alpine.store('changePassword', {
            active: true,
            data: result.data,
        });
    };
</script>
