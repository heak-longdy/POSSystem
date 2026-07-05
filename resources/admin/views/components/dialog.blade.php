<template x-if="$store.{{ $dialog }}.show">
    <div x-data="{{ $dialog }}_component" x-bind:style="{ zIndex: $store.libs.getLastIndex() + 1 }" class="dialog" :class="data?.digPosition"
        x-bind:onload="dialogInit">
        <div class="dialog-wrapper">
            <div class="dialog-container" :class="data?.class">
                {{ $slot }}
            </div>
        </div>
    </div>
</template>
<script>
    Alpine.data('{{ $dialog }}_component', () => ({
        data:{},
        init(){
            this.data = this.$store.{{ $dialog }}.data;
        },
        dialogInit() {
            // feather.replace();
            const target = this.$root.querySelector('.dialog-container');
            this.$store.libs.playAnimateOnLoad(target);
            this.$store.{{ $dialog }}.target = target;
        }
    }));
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
            Alpine.store('animate').leave(this.target, () => {
                this.show = false;
                if (typeof this.afterClosed === 'function') {
                    this.afterClosed(data);
                }
            });
        },
    });
</script>
