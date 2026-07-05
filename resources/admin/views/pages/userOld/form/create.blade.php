<template x-data="{}" x-if="$store?.create?.active">
    <div class="formDialogLayout" x-data="xCreate">
        <div class="formDialog" style="width: 30%;">
            <div class="formDialogHeader">
                <div class="hTitle"><span x-text="formTitle"></span> User</div>
                <button @click="$store.create.active=false"><i class='bx bx-x'></i></button>
            </div>
            <div class="formDialogBody form-admin">
                <form id="form" class="form-wrapper" action="{!! route('admin-user-save', request('id')) !!}" method="POST"
                    enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="form-body">
                        <div class="row">
                            <div class="form-row">
                                <label>@lang('user.form.name.label')<span>*</span> </label>
                                <input type="text" x-model="formData.name" :disable="formData.disable" name="name"
                                    value="{!! old('username') !!}" placeholder="@lang('user.form.name.placeholder')">
                                <template x-for="item in dataError?.name">
                                    <div class="errorCenter">
                                        <span class="error" x-text="item">Error</span>
                                    </div>
                                </template>
                            </div>
                            <div class="form-row">
                                <label>@lang('user.form.phone.label')</label>
                                <input name="phone" x-model="formData.phone" :disable="formData.disable"
                                    placeholder="@lang('user.form.phone.placeholder')" type="text" value="{!! old('phone') !!}">
                                <template x-for="item in dataError?.phone">
                                    <div class="errorCenter">
                                        <span class="error" x-text="item">Error</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-row">
                                <label>@lang('user.form.email.label')<span>*</span> </label>
                                <input type="text" x-model="formData.email" :disable="formData.disable"
                                    name="email" value="{!! old('email') !!}" data-old="{!! old('email') !!}"
                                    placeholder="@lang('user.form.email.placeholder')" autocomplete="off">
                                <template x-for="item in dataError?.email">
                                    <div class="errorCenter">
                                        <span class="error" x-text="item">Error</span>
                                    </div>
                                </template>
                            </div>

                        </div>
                        <div class="row-2">
                            <div class="form-row">
                                <label>@lang('user.form.status.label')<span>*</span></label>
                                <select name="status" x-model="formData.status" :disable="formData.disable">
                                    <option value="1">@lang('user.form.status.active')</option>
                                    <option value="2">@lang('user.form.status.disable')</option>
                                </select>
                                <template x-for="item in dataError?.status">
                                    <div class="errorCenter">
                                        <span class="error" x-text="item">Error</span>
                                    </div>
                                </template>
                            </div>
                            <div class="form-row">
                                <label>Identity<span>*</span> </label>
                                <input name="identity" x-model="formData.identity" :disable="formData.disable"
                                    placeholder="Enter identity..." type="text" value="{!! old('identity') !!}">
                                <template x-for="item in dataError?.identity">
                                    <div class="errorCenter">
                                        <span class="error" x-text="item">Error</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <template x-if="!formData?.id">
                            <div class="row-2">
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
                                    <input type="password" x-model="formData.confirm_password"
                                        :disable="formData.disable" name="confirm_password"
                                        placeholder="@lang('user.form.password_confirmation.placeholder')">
                                    <template x-for="item in dataError?.confirm_password">
                                        <div class="errorCenter">
                                            <span class="error" x-text="item">Error</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <div class="row">
                            <div class="formItem form-row">
                                <label>@lang('user.form.profile.label')</label>
                                <div class="form-select-photo image"
                                    @click="formData?.disable == false ? selectImage(event):''">
                                    <div class="select-photo" :class='{ active: formData?.image }'>
                                        <div class="icon">
                                            <i class='bx bx-image-alt'></i>
                                        </div>
                                        <div class="title">
                                            <p>Choose upload</p>
                                        </div>
                                    </div>
                                    <template x-if="formData?.image">
                                        <div class="image-view active">
                                            <img x-bind:src="baseImageUrl + formData?.image" alt="">
                                        </div>
                                    </template>
                                    <input type="hidden" x-model="formData.image" autocomplete="off"
                                        role="presentation" :disabled="formData?.disable">
                                </div>
                                <template x-for="item in dataError?.image">
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
                <button class="close" @click="$store.create.active=false"><i class='bx bx-x'></i>Close</button>
            </div>
            <template x-if="loadingSubmit">
                @include('admin::components.spinner')
            </template>
        </div>

    </div>
</template>
<script>
    Alpine.data('xCreate', () => ({
        loading: false,
        loadingSubmit: false,
        dataError: null,
        dataClosePrint: false,
        total: 0,
        serviceTotal: 0,
        packageType: '',
        formData: {
            phone: null,
            name: null,
            email: null,
            image: null,
            disable: false,
            status: 1,
            role: "admin",
            password: null,
            confirm_password: null
        },
        baseImageUrl: "{{ asset('file_manager') }}",
        paymentPopup: false,
        data: [],
        dataCheckAcc: [],
        formTitle: "Create",
        async init() {
            if (this.$store.create.data) {
                this.formTitle = "Update";
                this.formData = this.$store.create.data;
            }
        },
        closeUp() {
            this.dataError = null;
            Alpine.store('create').active = false;
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
            const id = this.formData?.id;
            this.btnSubmit = 'Submit';
            var status = id ? 'edit' : 'create';
            this.$store.confirmDialog.open({
                data: {
                    title: "@lang('dialog.title')",
                    message: `Are you sure to ${status} user ?`,
                    btnClose: "@lang('dialog.button.close')",
                    btnSave: "Submit",
                },
                afterClosed: async (result) => {
                    if (result) {
                        this.loadingSubmit = true;
                        this.dataError = null;
                        this.formData.disable = true;
                        let url = `{!! route('admin-user-save') !!}`;
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
        formatPhoneNumber(entry) {
            if (!entry) {
                return 90;
            }
            const match = entry
                .replace(/\D+/g, '').replace(/^1/, '')
                .match(/([^\d]*\d[^\d]*){1,10}$/)[0]
            const part1 = match.length > 2 ? `(${match.substring(0,3)})` : match
            const part2 = match.length > 3 ? ` ${match.substring(3, 6)}` : ''
            const part3 = match.length > 6 ? `-${match.substring(6, 10)}` : ''
            return `${part1}${part2}${part3}`;
        },
    }));
</script>
<script>
    Alpine.store('create', {
        active: false,
        data: null
    });
    window.create = (result) => {
        Alpine.store('create', {
            active: true,
            data: result.data,
        });
    };
</script>
