<template x-data="{}" x-if="$store.logOut.active">
    <div class="dialog confirmDialogLayout" x-data="selectOption"
        x-bind:style="{ zIndex: $store.libs.getLastIndex() + 1 }" :class="$store.logOut.active ? 'dialogAnimation':''">
        <div class="dialog-container modal-background hidden">
            <div class="confirmDialog modal" id="select-option">
                <i class='bx bx-help-circle'></i>
                <h3>Sing Out</h3>
                <div class="textDelete">Are you want to Sing Out ?</div>
                <div class="btnActionConfirmDialog">
                <a href="{!! route('admin-sign-out') !!}"><button class="btnConfirm" s-click-link="">Yes</button></a>
                <button class="btnCancel" @click="close(false)">Cancel</button>
                </div>
            </div>
        </div>
        <script>
            Alpine.data('selectOption', () => ({
                data: null,
                loading: true,
                options: null,
                selected: [],
                init() {
                    // this.options = Alpine.store('logOut').options;
                    // this.data = this.options.data;
                    // this.selected = this.options.selected;
                    // Alpine.store('animate').enter(this.$root.children[0], () => {
                    //     this.onReady();
                    // });
                    console.log(this.$store.logOut.active,'$store.logOut.active');
                },
                onReady() {
                    this.$store.logOut.options.onReady((data) => {
                        if (!data) return;
                        this.loading = false;
                        this.data = data;
                    });
                },
                onInput(e) {
                    this.data = [];
                    this.loading = true;
                    this.$store.logOut.options.onSearch(e.target.value, (data) => {
                        if (!data) return;
                        this.loading = false;
                        this.data = data;
                    });
                },
                onSelect(data) {
                    if (this.options.multiple) {
                        if (this.isSelected(data)) {
                            this.selected = this.selected.filter(item => item._id !== data._id);
                        } else {
                            this.selected.push(data);
                        }
                    } else {
                        this.onClose(data);
                    }
                },
                isSelected(data, call_back) {
                    return this.selected?.find(item => item._id == data._id) ? call_back ?? true : false;
                },
                selectedIndex(data) {
                    return this.selected.findIndex(item => item._id == data._id) + 1;
                },
                onClose(data = null) {
                    if (typeof this.$store.logOut.options.beforeClose === 'undefined') {
                        this.close(data);
                        return;
                    }
                    this.$store.logOut.options.beforeClose(data, (close) => {
                        if (close) {
                            this.close(data);
                        }
                    });
                },
                close(data = null) {
                    Alpine
                        .store('animate')
                        .leave(this.$root.children[0], () => {
                            this.$store.logOut.active = false;
                            this.$store.logOut.options.afterClose(data);
                        });
                }
            }));
        </script>
    </div>
</template>
<script>
    Alpine.store('logOut', {
        active: false,
        options: {
            data: null,
            selected: null,
            multiple: false,
            title: 'Choose an option',
            placeholder: 'Type to search...',
            allow_close: true,
            onReady: () => {},
            onSearch: () => {},
            // beforeClose: () => {},
            afterClose: () => {}
        }
    });
    window.logOut = (options) => {
        Alpine.store('logOut', {
            active: true,
            options: {
                ...Alpine.store('logOut').options,
                ...options
            }
        });
    };
</script>
