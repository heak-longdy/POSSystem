@component('website::components.dialog', ['dialog' => 'applyDialog'])
    <div x-data="applyDialog" class="dialog-form" style="width: 17rem">
        <style>
            .digBody {
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
                grid-gap: 25px;
                text-align: center;
            }

            .digBody>i {
                font-size: 70px;
                color: green;
            }

            .closeDiglog {
                height: 40px;
                background: #ff000085 !important;
                color: #fff;
                border: none;
                display: flex;
                align-items: center;
                padding: 0 20px !important;
                border-radius: 20px !important;
                cursor: pointer;
            }

            .countdown {
                text-align: right;
                color: #808080a1;
                margin-bottom: 14px;
            }

            .successTitle {
                font-size: 15px;
                font-weight: 550;
                color: green;
            }

            .dgTitle {
                color: #3a3737b8;
            }
        </style>
        <div class="countdown">
            <p>Will close in <span x-text="countdown"></span> seconds.</p>
        </div>
        <div class="dialog-form-body">
            <div class="digBody">
                <i class='bx bx-check-circle bx-tada'></i>
                <label class="successTitle">Apply job successfully </label>
                <label class="dgTitle">Thank you for your interest in the job.<br/> We will contact you soon...</label>
            </div>
        </div>
        <div class="dialog-form-footer" style="justify-content: center;margin: 13px 0;">
            <button type="button" class="closeDiglog" @click="$store.applyDialog.close(true)"
                x-bind:disabled="disabled || loading">
                <i class='bx bx-x'></i>
                <span x-text="data?.btnClose || 'Close'"></span>
            </button>
        </div>

    </div>
    <script>
        Alpine.data("applyDialog", () => ({
            data: null,
            disabled: false,
            loading: false,
            image: null,
            baseImageUrl: "{{ asset('file_manager') }}",
            title: "",
            id: "",
            validateForm: {},
            countdown: 0,
            init() {
                this.data = this.$store.applyDialog?.data;
                console.log(this.data);
                this.countdown = 5; // Set the countdown duration
                const interval = setInterval(() => {
                    this.countdown--;
                    if (this.countdown <= 0) {
                        clearInterval(interval);
                        this.$store.applyDialog.close(true);
                    }
                }, 1000);
            },
            onConfirm() {
                this.disabled = true;
                this.$store.applyDialog.close(true);
            }
        }))
    </script>
@endcomponent
