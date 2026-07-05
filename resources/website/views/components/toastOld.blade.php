<div class="Toast" x-data="toastDialog">
    <template x-for="(item,i) in dataToasts">
        <template x-if="item.show">
            <div class="Toast-container" x-bind:id="'toast-' + item.id" x-bind:status="item.status"
                x-on:mouseenter="onFocusIn(item.id)" x-on:mouseleave="onFocusOut(item.id)" x-bind:onload="onReady(item)">
                <div class="Toast-header c5353535">
                    <h3 x-text="item?.title"></h3>
                    <button class="button-close" x-show="item?.allowClose" style="display: none"
                        @click="closeByButton(item.id)">
                        <i class='bx bx-x'></i>
                    </button>
                </div>
                <div class="Toast-body">
                    <p x-text="item.message"></p>
                </div>
                <template x-if="item.autoClose">
                    <div class="duration-bar" x-bind:style="'width:' + item.progress + '%'"></div>
                </template>
            </div>
        </template>
    </template>
    <script>
        Alpine.data('toastDialog', () => ({
            queue: {},
            dataToasts: [],
            init() {
                window.Toast = (options) => {
                    const defaultOptions = Alpine.store('toast').options;
                    setTimeout(() => {
                        this.dataToasts.push({
                            ...defaultOptions,
                            ...options,
                            id: Math.random().toString(36).substr(2, 9),
                        });
                        let item = this.dataToasts[this.dataToasts.length - 1];
                        item.show = item.hasOwnProperty('show') ? item.show : true;
                    }, options.delay ?? 0);
                    console.log(options.delay,'options.delay ');
                }

                Alpine.store('toast').dataToasts.map((item) => {
                    setTimeout(() => {
                        this.dataToasts.push(item);
                        item.show = item.hasOwnProperty('show') ? item.show : true;
                    }, item.delay ?? 0);
                });
            },
            onReady(item) {
                Alpine
                    .store('animate')
                    .enter(`#toast-${item.id}`, (res) => {
                        item['progress'] = 100;
                        if (!item.autoClose) return;
                        this.queue[item.id] = anime({
                            duration: item.duration,
                            timing: 'linear',
                            ease: 'forwards',
                            change: (progress) => {
                                feather.replace();
                                item.progress = 100 - progress
                                    .progress;
                            },
                            complete: () => {
                                this.onClose(item.id);
                            },
                        });
                    });
            },
            closeByButton(id) {
                this.queue[id]?.pause();
                this.onClose(id);
            },
            onFocusIn(id) {
                this.queue[id]?.pause();
            },
            onFocusOut(id) {
                this.queue[id]?.play();
            },
            onClose(target_id) {
                Alpine
                    .store('animate')
                    .leave('#toast-' + target_id, (res) => {
                        anime({
                            targets: res.animatables[0]?.target,
                            keyframes: [{
                                marginTop: '0px'
                            }, {
                                marginTop: `-${res.animatables[0]?.target.offsetHeight}px`
                            }, ],
                            direction: "forwards",
                            easing: "easeInSine",
                            duration: 300,
                            complete: (res) => {
                                this.dataToasts.map(item => {
                                    if (item.id === target_id) {
                                        item.show = false;
                                        item.afterClose();
                                    }
                                });
                            }
                        })
                    });
            },
        }));
    </script>
</div>
<script>
    Alpine.store('toast', {
        dataToasts: [],
        options: {
            title: 'Toast Title',
            // message: 'Toast message goes here ...',
            status: 'info',
            duration: 5000,
            delay: 0,
            stopOnFocus: true,
            allowClose: true,
            autoClose: true,
            onReady: () => {},
            afterClose: () => {}
        }
    });

    window.Toast = (options) => {
        console.log('tttttttttt');
        const defaultOptions = Alpine.store('toast').options;
        Alpine.store('toast').dataToasts.push({
            ...defaultOptions,
            ...options,
            id: Math.random().toString(36).substr(2, 9),
        });
    }
</script>
