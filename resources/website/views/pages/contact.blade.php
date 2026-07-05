@extends('website::shared.layout')
@section('layout')
    <div class="wb-home-layout">
        @include('website::components.banner', [
            'header_name' => '',
            'imgUrl' => '../../website/img/blog.webp',
            'text1' => 'Get in Touch',
            'text2' => 'Whether you’re an intern eager to explore life-changing opportunities in Cambodia or a business looking to find talented interns and post opportunities, we’d love to hear from you.',
            'search' => 'disable',
            'clickhere' => 'disable',
        ])

        {{-- listJob --}}
        <style>
            .buttonSend {
                border: unset;
                color: var(--text-color);
                outline: none;
                border-radius: 0.75rem;
                /* padding: 1.5rem; */
                z-index: 1;
                display: flex;
                align-items: center;
                height: 55px;
                padding: 0 17px;
                grid-gap: 10px;
                background: #ff9900;
                color: #fff;
            }

            .buttonSend:disabled {
                background: #57545442 !important;
            }
        </style>
        <div class="webContentListLayout">
            <div class="webContentList">
                <section class="contact section" id="contact" style="margin: 70px 0" x-data="xContact">
                    {{-- <h2 class="section__title" style="margin: 60px 0;">Contact Information</h2> --}}
                    {{-- <span class="section__subtitle">Contact Me</span> --}}

                    <div class="contact__container container grid" style="display: flex;" data-aos="zoom-in"
                        data-aos-delay="200">
                        <div class="contact__content">
                            <h3 class="contact__title fontWeight">Contact Information</h3>
                            <div class="contact__info">
                                <div class="contact__card">
                                    {{-- <i class="bx bx-mail-send contact__card-icon"></i> --}}
                                    {{-- <h3 class="contact__card-title fontWeight">Email</h3> --}}
                                    <div class="contact__card-data">
                                        <i class='bx bx-phone'></i>
                                        <div>{{ $contact?->phone_kh }}</div>
                                    </div>
                                    <div class="contact__card-data">
                                        <i class='bx bx-envelope'></i>
                                        <div>{{ $contact?->email_1 }}</div>
                                    </div>
                                    <div class="contact__card-data">
                                        <i class='bx bx-map' ></i>
                                        <div>
                                            <label>Cambodia Oﬀice:</label>
                                            <p>{{ $contact?->address_kh }}</p>
                                        </div>
                                    </div>
                                    <div class="contact__card-data">
                                        <i class='bx bx-map' ></i>
                                        <div>
                                            <label>UK Oﬀice:</label>
                                            <p>{{ $contact?->address_uk }}</p>
                                        </div>
                                    </div>
                                    
                                    {{-- <div class="contact__card-data">For Internship Applications: {{ $contact?->email_2 }}</div>
                                    <div class="contact__card-data">For Internship Providers: {{ $contact?->email_3 }}</div> --}}
                                </div>
                                {{-- <div class="contact__card">
                                    <h3 class="contact__card-title fontWeight">Phone</h3>
                                    <span class="contact__card-data">Cambodia Oﬀice: {{ $contact?->phone_kh }} </span>
                                    <span class="contact__card-data">Oﬀice Hours:</span>
                                    <span class="contact__card-data">UK Oﬀice: {{ $contact?->office_hour_uk }}</span>
                                    <span class="contact__card-data">Cambodia Oﬀice: {{ $contact?->office_hour_kh }}</span>
                                </div>
                                <div class="contact__card">
                                    <h3 class="contact__card-title fontWeight">Address</h3>
                                    <span class="contact__card-data">UK Oﬀice: {{ $contact?->address_uk }}</span>
                                    <span class="contact__card-data">Cambodia Oﬀice: {{ $contact?->address_kh }}</span>
                                </div> --}}
                            </div>
                        </div>
                        <div class="contact__content">
                            {{-- <h3 class="contact__title">Write me your project</h3> --}}
                            <div class="contact__form">
                                <div class="inputWithLabelLy">
                                    <label for="" class="inputTag">Name<span>*</span></label>
                                    <input type="text" name="name" placeholder="Insert name" class="inputField"
                                        :class="validateForm?.name ? 'error' : ''" x-model="formData.name"
                                        :readonly="loading">
                                    <p class="inputError" x-text="validateForm?.name"></p>
                                </div>
                                <div class="inputWithLabelLy">
                                    <label for="" class="inputTag">Email<span>*</span></label>
                                    <input type="text" name="email" placeholder="Insert email" class="inputField"
                                        :class="validateForm?.email ? 'error' : ''" x-model="formData.email"
                                        :readonly="loading">
                                    <p class="inputError" x-text="validateForm?.email"></p>
                                </div>
                                <div class="inputWithLabelLy">
                                    <label for="" class="inputTag">Subject<span>*</span></label>
                                    <input type="text" name="subject" placeholder="Insert subject" class="inputField"
                                        :class="validateForm?.subject ? 'error' : ''" x-model="formData.subject"
                                        :readonly="loading">
                                    <p class="inputError" x-text="validateForm?.subject"></p>
                                </div>
                                <div class="inputWithLabelLy">
                                    <label for="" class="inputTag">Description<span>*</span></label>
                                    <textarea _ngcontent-pyh-c10="" class="contact__form-input ng-pristine ng-valid ng-touched inputField" cols="30"
                                        id="" name="description" placeholder="Insert Description" rows="10" x-model="formData.description"
                                        :class="validateForm?.description ? 'error' : ''" :readonly="loading"></textarea>
                                    <p class="inputError" x-text="validateForm?.description"></p>
                                </div>
                                <button type="button" class="buttonSend" :disabled="loading" @click="contactSend()"
                                    style="cursor: pointer;">
                                    Send Message
                                    <i class='bx bx-loader-alt bx-spin' x-show="loading"></i>
                                    <svg class="button__icon" xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" x-show="!loading">
                                        <path d="M14.2199 21.9352C13.0399 21.9352 11.3699 21.1052 10.0499
                                                                17.1352L9.32988 14.9752L7.16988 14.2552C3.20988 12.9352 2.37988 11.2652
                                                                2.37988 10.0852C2.37988 8.91525 3.20988 7.23525 7.16988 5.90525L15.6599
                                                                3.07525C17.7799 2.36525 19.5499 2.57525 20.6399 3.65525C21.7299 4.73525
                                                                21.9399 6.51525 21.2299 8.63525L18.3999 17.1252C17.0699 21.1052 15.3999
                                                                21.9352 14.2199 21.9352ZM7.63988 7.33525C4.85988 8.26525 3.86988 9.36525
                                                                3.86988 10.0852C3.86988 10.8052 4.85988 11.9052 7.63988 12.8252L10.1599
                                                                13.6652C10.3799 13.7352 10.5599 13.9152 10.6299 14.1352L11.4699
                                                                16.6552C12.3899 19.4352 13.4999 20.4252 14.2199 20.4252C14.9399 20.4252
                                                                16.0399 19.4352 16.9699 16.6552L19.7999 8.16525C20.3099 6.62525 20.2199
                                                                5.36525 19.5699 4.71525C18.9199 4.06525 17.6599 3.98525 16.1299
                                                                4.49525L7.63988 7.33525Z" fill="var(--container-color)">
                                        </path>
                                        <path d="M10.11 14.7052C9.92005 14.7052 9.73005 14.6352 9.58005
                                                                14.4852C9.29005 14.1952 9.29005 13.7152 9.58005 13.4252L13.16
                                                                9.83518C13.45 9.54518 13.93 9.54518 14.22 9.83518C14.51 10.1252 14.51
                                                                10.6052 14.22 10.8952L10.64 14.4852C10.5 14.6352 10.3 14.7052 10.11
                                                                14.7052Z" fill="var(--container-color)"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data("xContact", () => ({
                formData: {
                    name: "",
                    email: "",
                    subject:"",
                    description: ""
                },
                validateForm: {},
                isAgree: true,
                loading: false,
                init() {},
                contactSend() {
                    this.loading = true;
                    this.validateForm = {};
                    let formData = new FormData();
                    // Append additional form fields if necessary
                    formData.append('name', this.formData.name);
                    formData.append('email', this.formData.email);
                    formData.append('subject', this.formData.subject);
                    formData.append('description', this.formData.description);
                    let delayQuery = null;
                    clearTimeout(this.delayQuery);
                    delayQuery = setTimeout(() => {
                        Axios({
                                method: 'post',
                                url: '{{ route('web-contact-send') }}',
                                data: formData
                            })
                            .then((response) => {
                                this.loading = false;
                                this.formData = {
                                    name: "",
                                    email: "",
                                    subject:"",
                                    description: ""
                                };
                                iziToast.success({
                                    title: "Done",
                                    message: "Contact send message successfully",
                                    position: 'topRight',
                                    timeout: 2500,
                                    animateInside: true,
                                    transitionIn: 'fadeIn',
                                    progressBarEasing: 'linear',
                                    pauseOnHover: true
                                });
                            })
                            .catch((error) => {
                                this.validateForm = error?.response?.data?.errors;
                                this.loading = false;
                                console.log(this.validateForm, 'dataError');
                            });
                    },1500);
                },
            }));
        });
    </script>
@stop
