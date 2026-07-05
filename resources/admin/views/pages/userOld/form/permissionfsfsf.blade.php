<template x-data="{}" x-if="$store?.userPermission?.active">
    <div class="modalCus dialogAlertCus" role="dialog" tabindex="-1" x-data="XUserPermission">
        <div class="model-innermodalCus" style="padding:35px;max-width: 800px;">
            <button aria-label="Close" @click="closeUp()" class="buttonClose">✖</button>
            <div class="bodyAlert" style="width: 600px;align-items:unset;text-align:unset;">
                <div class="row">
                    <div class="permissionLayoutGp">
                        <h3><span>Set Permission To</span>&nbsp;:&nbsp;<span
                                x-text="$store?.userPermission?.data?.full_name"></span></h3>
                        <div class="headerListPermission">
                            <label class="titlePer">Permission Listing<span></span></label>
                            <label for="chk-permissionSelectAll" class="permissionListCheckall">
                                <span>Select All</span>
                                <input type="checkbox" id="chk-permissionSelectAll" class="chk-permissionSelectAll" />
                            </label>
                        </div>
                        <template x-for="(modulParent,index) in ModulPermission">
                            <div class="permissionControl">
                                <label class="parentLabel" x-text="modulParent?.name"></label>
                                <div class="permissionLayout">
                                    <div class="permissionItem">
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
                                                <label class="permissionItemCh" :for="'permission' + action?.name">
                                                    <i data-feather="disc"></i>
                                                    <div class="textItem" x-text="action?.display_name"></div>
                                                    <div class="inputItem">
                                                        <input type="checkbox" :value="action?.name"
                                                            class="permissionAllitem"
                                                            :class="'permission-item-' + modulParent?.id"
                                                            :id="'permission' + action?.name"
                                                            :data-permission-id="modulParent?.id" name="permission"
                                                            :checked="action?.check" />
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </template>
                    </div>
                    <div class="form-button btnPermissionActinGp">
                        <button type="button" color="primary" @click="SubmitData()" :disabled="loading">
                            <i data-feather="save"></i>
                            <span>Submit</span>
                            <div class="loaderCus" style="display: none" x-show="loading"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    Alpine.data('XUserPermission', () => ({
        loading: false,
        dataError: null,
        ModulPermission: [],
        form: {
            permission: []
        },
        async init() {
            const {
                id
            } = Alpine.store('userPermission').data;
            await this.fetchDataSeleted(`/admin/user/permission?id=${id}`, (res) => {
                return this.ModulPermission = res.ModulePermission;
            });
            console.log('this.ModulPermissionthis.ModulPermission', this.ModulPermission);
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
                $('.permission-item-' + $(this).attr('data-permission-id')).each(function() {
                    $(this).iCheck('check');
                });
            });

            $('.chk-permission-group').on('ifUnchecked', function() {
                $('.permission-item-' + $(this).attr('data-permission-id')).each(function() {
                    $(this).iCheck('uncheck');
                });
            });
            //endCheckBYGroup
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
        print() {
            $("#BodyInvoice").print();
        },
        async SubmitData() {
            this.$store.confirmDialog.open({
                data: {
                    title: "@lang('dialog.title')",
                    message: "@lang('dialog.msg.save')",
                    btnClose: "@lang('dialog.button.close')",
                    btnSave: "@lang('dialog.button.save')",
                },
                afterClosed: async (result) => {
                    if (result) {
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
                                Toast({
                                    message: res.data.message,
                                    status: res.data.status,
                                    size: 'small',
                                });
                            }
                        }).catch((e) => {
                            this.validate = e.response.data.errors;
                        }).finally(() => {
                            this.loading = false;
                        });
                    }
                }
            });
        },
        formatPhoneNumber(entry) {
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
