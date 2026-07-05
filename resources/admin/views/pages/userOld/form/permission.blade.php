<template x-data="{}" x-if="$store?.userPermission?.active">
    <div class="formDialogLayout" x-data="XUserPermission">
        <div class="formDialog" style="width: 30%;">
            <div class="formDialogHeader">
                <div class="hTitle">Set Permission To</span>&nbsp;:&nbsp;<span
                        x-text="$store?.userPermission?.data?.full_name"></span></div>
                <button @click="closeUp()"><i class='bx bx-x'></i></button>
            </div>
            <div class="formDialogBody form-admin" x-show="!loading">
                <form id="form" class="form-wrapper">
                    <div class="form-body">
                        <template x-for="item in dataError?.permission">
                            <div class="errorCenter permissionError">
                                <span class="error" x-text="item">Error</span>
                            </div>
                        </template>
                        <div class="row">
                            <div class="permissionLayoutGp">
                                <div class="headerListPermission">
                                    {{-- <label class="titlePer">Permission Listing<span></span></label> --}}
                                    <label for="chk-permissionSelectAll" class="permissionListCheckall">
                                        <span>Select All</span>
                                        <input type="checkbox" id="chk-permissionSelectAll"
                                            class="chk-permissionSelectAll" />
                                    </label>
                                </div>
                                <template x-for="(modulParent,index) in ModulPermission">
                                    <div class="permissionControl">
                                        <label class="parentLabel" x-text="modulParent?.name"></label>
                                        <div class="permissionLayout">
                                            <div class="permissionItem showMenu">
                                                <div class="permissionHeader arrowPermission">
                                                    <i data-feather="chevron-down"></i>
                                                    {{-- <div class="textItem" x-text="modulParent?.name">
                                                    </div> --}}
                                                    <div class="textItem" x-text="'All Option'">
                                                    </div>
                                                    <label class="form-check-label"
                                                        :for="'chk-permission-group-'.modulParent?.id">
                                                        <div class="inputItem">
                                                            <input type="checkbox"
                                                                :id="'chk-permission-group-' + modulParent?.id"
                                                                :data-permission-id="modulParent?.id"
                                                                class="role_permission permissionAllitem chk-permission-group"
                                                                :class="'chk-permission-group-' + modulParent?.id"
                                                                :checked="modulParent?.check" />
                                                        </div>
                                                    </label>
                                                </div>
                                                <div class="permissionListItemGpCh">
                                                    <template x-for="(action,key) in modulParent?.permission">
                                                        <label class="permissionItemCh"
                                                            :for="'permission' + action?.name">
                                                            <i data-feather="disc"></i>
                                                            <div class="textItem" x-text="action?.display_name"></div>
                                                            <div class="inputItem">
                                                                <input type="checkbox" :value="action?.name"
                                                                    class="permissionAllitem"
                                                                    :class="'permission-item-' + modulParent?.id"
                                                                    :id="'permission' + action?.name"
                                                                    :data-permission-id="modulParent?.id"
                                                                    name="permission" :checked="action?.check" />
                                                            </div>
                                                        </label>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="formDialogFooter" x-show="!loading">
                <button class="button" @click="SubmitData()"><i class='bx bxs-save'></i>Submit</button>
                <button class="close" @click="closeUp()"><i class='bx bx-x'></i>Close</button>
            </div>
            <template x-if="loading">
                @include('admin::components.spinner')
            </template>
        </div>
    </div>
</template>
<script>
    Alpine.data('XUserPermission', () => ({
        loading: false,
        loadingSubmit: false,
        dataError: null,
        dataClosePrint: false,
        total: 0,
        serviceTotal: 0,
        packageType: '',
        ModulPermission: [],
        form: {
            id: null,
            permission: [],
        },
        baseImageUrl: "{{ asset('file_manager') }}",
        paymentPopup: false,
        data: [],
        dataCheckAcc: [],
        async init() {
            const {
                id
            } = Alpine.store('userPermission').data;
            this.form.id = id;
            this.loading = true;
            setTimeout(async () => {
                await this.fetchDataSeleted(`/admin/user/permission?id=${id}`, (res) => {
                    this.ModulPermission = res.ModulePermission;
                    this.loading = false;
                });
                var data = [];
                feather.replace();
                let arrow = document.querySelectorAll('.arrowPermission');
                for (var i = 0; i < arrow.length; i++) {
                    arrow[i].addEventListener('click', (e) => {
                        let arrowParent = e.target.parentElement.parentElement;
                        arrowParent.classList.toggle('showMenu');
                    });
                }
                $('.chk-permission-group').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                });
                $('.chk-permissionSelectAll').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                });

                //checkAll
                $('.chk-permissionSelectAll').on('ifChecked', function() {
                    $('.permissionAllitem').each(function() {
                        $(this).iCheck('check');
                    });
                });

                $('.chk-permissionSelectAll').on('ifUnchecked', function() {
                    $('.permissionAllitem').each(function() {
                        $(this).iCheck('uncheck');
                    });
                });
                //endCheckAll

                //checkByGroup
                $('.chk-permission-group').on('ifChecked', function() {
                    $('.permission-item-' + $(this).attr('data-permission-id'))
                        .each(function() {
                            $(this).iCheck('check');
                        });
                });

                $('.chk-permission-group').on('ifUnchecked', function() {
                    $('.permission-item-' + $(this).attr('data-permission-id'))
                        .each(function() {
                            $(this).iCheck('uncheck');
                        });
                });
                //endCheckBYGroup
            }, 500);
        },
        async fetchDataSeleted(url, callback) {
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
        closeUp() {
            this.dataError = null;
            Alpine.store('userPermission').active = false;
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
        async SubmitData() {
            var status = 'set permission';
            this.$store.confirmDialog.open({
                data: {
                    title: "@lang('dialog.title')",
                    message: `Are you sure to ${status} user ?`,
                    btnClose: "@lang('dialog.button.close')",
                    btnSave: "Submit",
                },
                afterClosed: async (result) => {
                    if (result) {
                        this.dataError = [];
                        this.loading = true;
                        var permission = [];
                        const {
                            id
                        } = Alpine.store('userPermission').data;
                        await $.each($("input[name='permission']:checked"), function() {
                            permission.push($(this).val());
                        });
                        Axios({
                            url: `{{ route('admin-user-save-permission') }}`,
                            method: 'POST',
                            data: {
                                id: id,
                                permission: permission
                            }
                        }).then((res) => {
                            if (res.data.error == false) {
                                this.$store.userPermission.active = false;
                                reloadData('{!! url()->current() !!}');
                            }
                        }).catch((e) => {
                            this.dataError = e.response.data.errors;
                        }).finally(() => {
                            this.loading = false;
                        });
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
    Alpine.store('userPermission', {
        active: false,
        data: null
    });
    window.userPermission = (result) => {
        Alpine.store('userPermission', {
            active: true,
            data: result.data,
        });
    };
</script>
