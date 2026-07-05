@props(['url' => null])

<template x-data="{}" x-if="$store?.popup?.active">
    <div class="dialog">
        <div class="dialog-container">
            <div x-data="popupContainer" id="popup-container">
                <div class="popup-header">
                    <h3 x-text="'QR Code ' + (data?'('+data.name+')':'')"></h3>
                </div>
                <div class="popup-body">
                    <div id="qr-code-form">
                        <div class="input-group">
                            <input type="text" placeholder="Enter link here" x-model="link" :value="link">
                            <div class="generate-btn" @click="generateQrcode()">Generate</div>
                        </div>
                        <img x-bind:src="data.qrcode" alt=""/>
                    </div>
                </div>
                <div class="popup-footer">
                    <button @click="onClose()" id="popup-close">
                        <i data-feather="x"></i>
                        <span>Close</span>
                    </button>
                </div>
            </div>
        </div>
        <script>
            Alpine.store('animate').enter(".dialog-container", (res) => {
                res?.animatables[0]?.target?.removeAttribute('style');
            });
        </script>
    </div>
</template>

<script>
    Alpine.store('popup', {
        active: false,
        id: null,
        name: null,
        options: {
            multiple: false,
        },
    });

    Alpine.store('animate', {
        enter: (target, fn) => {
            if (!target) return;
            anime({
                targets: target, //this.$root.children[0]
                scale: [0.9, 1],
                opacity: [0, 1],
                direction: 'forwards',
                easing: 'easeInSine',
                duration: 200,
                complete: (res) => {
                    fn ? fn(res) : null;
                },
            });
            feather.replace();
        },
        leave: (target, fn) => {
            if (!target) return;
            anime({
                targets: target,
                scale: [1, 0.9],
                opacity: [1, 0],
                direction: 'forwards',
                easing: 'easeOutSine',
                duration: 200,
                complete: (res) => {
                    fn ? fn(res) : null;
                },
            });
        }
    });

    Alpine.data('popupContainer', () => ({
        data: null,
        link: null,
        url: null,
        init(){
            var qrcode = null;
            this.url = '{!! $url !!}';

            this.data = {
                id: Alpine.store('popup').id,
                name: Alpine.store('popup').name,
                qrcode: Alpine.store('popup').qrcode
            };
        },
        onClose() {
            Alpine.store('animate').leave(".dialog-container", (res) => {
                Alpine.store('popup').active = false;
            });
        },
        async generateQrcode(){
            var qrcode = this.data.qrcode;
            await $.ajax({
                url: this.url,
                type: "POST",
                dataType: "json",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": this.data.id,
                    "link": this.link
                },
                success: function (data) {
                    if(data.message == true){
                        toastr.success("Generate Successfully","Success Message", {timeOut: 3000});
                        qrcode = data.qrcode;
                    }else{
                        toastr.warning("Input can't be empty!","Warming Message", {timeOut: 3000});   
                    }
                }
            });
            this.link = null;
            this.data.qrcode = qrcode;
        }
    }));
    const openPopup = async (payload) => {
        await Alpine.store('popup', {
            active: true,
            id: payload.id,
            name: payload.name.en,
            qrcode: payload.qrcode
        });
    };
</script>