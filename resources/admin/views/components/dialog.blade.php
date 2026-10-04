<template x-if="$store?.{{ $dialog }}?.show">
    <div x-data="{{ $dialog }}_component" x-bind:style="{ zIndex: ($store?.libs?.getLastIndex ? $store.libs.getLastIndex() + 1 : 1000) }" class="dialog" :class="data?.digPosition"
        x-bind:onload="dialogInit">
        <div class="dialog-wrapper">
            <div class="dialog-container" :class="data?.class">
                {{ $slot }}
            </div>
        </div>
    </div>
</template>
<script>
    (function() {
        const initDialogComponent = () => {
            Alpine.data('{{ $dialog }}_component', () => ({
                data:{},
                init(){
                    this.data = this.$store?.{{ $dialog }}?.data;
                    const target = this.$root.querySelector('.dialog-container');
                    if (target && this.$store?.libs?.playAnimateOnLoad) {
                        this.$store.libs.playAnimateOnLoad(target);
                    }
                    if (this.$store?.{{ $dialog }}) {
                        this.$store.{{ $dialog }}.target = target;
                    }
                },
                dialogInit() {
                    const target = this.$root.querySelector('.dialog-container');
                    if (target && this.$store?.libs?.playAnimateOnLoad) {
                        this.$store.libs.playAnimateOnLoad(target);
                    }
                    if (this.$store?.{{ $dialog }}) {
                        this.$store.{{ $dialog }}.target = target;
                    }
                }
            }));
            if (!Alpine.store('{{ $dialog }}')) {
                Alpine.store('{{ $dialog }}', {
                    target: null,
                    data: null,
                    show: false,
                    afterClosed: () => {},
                    open(options=null) {
                        this.data = options?.data;
                        this.afterClosed = options?.afterClosed ?? null;
                        this.show = true;
                    },
                    close(data = null) {
                        const doClose = () => {
                            this.show = false;
                            if (typeof this.afterClosed === 'function') {
                                this.afterClosed(data);
                            }
                        };
                        if (this.target && Alpine.store('animate')?.leave) {
                            Alpine.store('animate').leave(this.target, doClose);
                        } else {
                            doClose();
                        }
                    },
                });
            }
        };

        if (window.Alpine) {
            initDialogComponent();
        } else {
            document.addEventListener('alpine:init', initDialogComponent);
        }
    })();
</script>

