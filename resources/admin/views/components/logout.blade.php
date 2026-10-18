@if(!defined('ADMIN_LOGOUT_LOADED'))
@php define('ADMIN_LOGOUT_LOADED', true); @endphp
<template x-data="{}" x-if="$store.logOut.active">
    <div class="dialog confirmDialogLayout" x-data="logoutDialog"
        x-bind:style="{ zIndex: $store.libs.getLastIndex() + 1 }" :class="$store.logOut.active ? 'dialogAnimation':''">
        <div class="dialog-container modal-background hidden">
            <div class="confirmDialog modal" id="select-option" style="padding: 25px 35px;">
                <i class='bx bx-help-circle' style="margin-bottom: 12px;"></i>
                <h3 style="margin-bottom: 10px;" x-text="$store.logOut.options?.title || @json(__('setting.sign_out'))">@lang('setting.sign_out')</h3>
                <div class="textDelete" x-text="$store.logOut.options?.message || $store.logOut.options?.msg || @json(__('dialog.msg.sign_out'))">@lang('dialog.msg.sign_out')</div>
                <div class="btnActionConfirmDialog">
                    <a href="{!! route('admin-sign-out') !!}" style="display: flex;justify-content: center; margin: 0 20px;">
                        <button class="btnConfirm">
                            <span x-text="$store.logOut.options?.btnConfirm || $store.logOut.options?.confirm || @json(__('global.dialog.yes'))">@lang('global.dialog.yes')</span>
                        </button>
                    </a>
                    <button class="btnCancel" @click="close(false)" style="background: none;color: red;" x-text="$store.logOut.options?.btnCancel || $store.logOut.options?.cancel || @json(__('dialog.button.cancel'))">@lang('dialog.button.cancel')</button>
                </div>
            </div>
        </div>
        <script>
            Alpine.data('logoutDialog', () => ({
                close(data = null) {
                    const doClose = () => {
                        if (Alpine.store('animate')) {
                            Alpine.store('animate').leave(this.$root.children[0], () => {
                                this.$store.logOut.active = false;
                                if (typeof this.$store.logOut.options?.afterClose === 'function') {
                                    this.$store.logOut.options.afterClose(data);
                                }
                            });
                        } else {
                            this.$store.logOut.active = false;
                            if (typeof this.$store.logOut.options?.afterClose === 'function') {
                                this.$store.logOut.options.afterClose(data);
                            }
                        }
                    };

                    if (typeof this.$store.logOut.options?.beforeClose === 'function') {
                        this.$store.logOut.options.beforeClose(data, (proceed) => {
                            if (proceed) {
                                doClose();
                            }
                        });
                    } else {
                        doClose();
                    }
                }
            }));
        </script>
    </div>
</template>
<script>
    Alpine.store('logOut', {
        active: false,
        options: {
            title: null,
            message: null,
            btnConfirm: null,
            btnCancel: null,
            allow_close: true,
            afterClose: () => {}
        }
    });
    window.logOut = (options = {}) => {
        Alpine.store('logOut', {
            active: true,
            options: {
                ...Alpine.store('logOut').options,
                ...options
            }
        });
    };
</script>
@endif
