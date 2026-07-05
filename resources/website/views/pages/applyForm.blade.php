<style>
    .wb-header {
        color: #545454 !important;
    }

    .wb-header .header .h-left .logo .first {
        display: none !important;
    }

    .wb-header .header .h-left .logo .last {
        display: block !important;
    }

    .buttonApply {
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
        cursor: pointer;
    }

    .buttonApply>i {
        font-size: 24px;
    }

    .buttonApply:disabled {
        background: #57545442 !important;
    }

    .loading {
        height: 100vh;
        display: flex;
        align-items: center;
        width: 100%;
        overflow: hidden;
        justify-content: center;
        z-index: 999;
        position: fixed;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
    }

    .loading>.ldbg {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        background: #1d1c1c;
        opacity: 0.5;

    }

    .loading>.ldText {
        z-index: 991;
        text-align: center;
        display: flex;
        flex-direction: column;
        grid-gap: 15px;
    }

    .loading>.ldText>i {
        font-size: 70px;
        color: #ff9900;

    }

    .loading>.ldText>p {
        color: #fff;
        filter: drop-shadow(2px 4px 6px #ff9900);
    }

    .textChooseFile {
        display: flex;
        align-items: center;
    }
</style>
@extends('website::shared.layout')
@section('layout')
    <div class="wb-home-layout">
        {{-- listJob --}}
        <div class="webContentListLayout" x-data="xApplyForm">
            <div class="webContentList">
                <div class="applyContainer">
                    <h2>Application Form</h2>
                    <div class="applyGp">
                        <div class="appLeft">
                            <div class="inputWithLabelLy">
                                <label for="" class="inputTag">Job<span>*</span></label>
                                <input type="text" name="job_title" placeholder="Insert job title" class="inputField"
                                    :class="validateForm?.job_title ? 'error' : ''" x-model="formData.job_title"
                                    :readonly="true" :disabled="true">
                                <p class="inputError" x-text="validateForm?.job_title"></p>
                            </div>
                            <div class="inputWithLabelLy">
                                <label for="" class="inputTag">Full Name<span>*</span></label>
                                <input type="text" name="name" placeholder="Insert full name" class="inputField"
                                    x-model="formData.name" :class="validateForm?.name ? 'error' : ''"
                                    :readonly="loading">
                                <p class="inputError" x-text="validateForm?.name"></p>
                            </div>
                            <div class="inputWithLabelLy">
                                <label for="" class="inputTag">Phone<span>*</span></label>
                                <input type="text" placeholder="Insert you phone" class="inputField"
                                    x-model="formData.phone" :class="validateForm?.phone ? 'error' : ''"
                                    :readonly="loading">
                                <p class="inputError" x-text="validateForm?.phone"></p>
                            </div>
                            <div class="inputWithLabelLy">
                                <label for="" class="inputTag">Email<span>*</span></label>
                                <input type="text" placeholder="Insert you email" class="inputField"
                                    x-model="formData.email" :class="validateForm?.email ? 'error' : ''"
                                    :readonly="loading">
                                <p class="inputError" x-text="validateForm?.email"></p>
                            </div>
                            <div class="inputWithLabelLy">
                                <label for="" class="inputTag">Description (Optional)</label>
                                <textarea _ngcontent-pyh-c10="" class="contact__form-input ng-pristine ng-valid ng-touched inputField" cols="30"
                                    id="" name="description"
                                    placeholder="Tell us a little bit about yourself and why you're applying for this role" rows="10"
                                    x-model="formData.description" :class="validateForm?.description ? 'error' : ''" :readonly="loading"></textarea>
                                <p class="inputError" x-text="validateForm?.description"></p>
                            </div>
                        </div>
                        <div class="appRight">
                            <div class="fileUploadLayout" :class="files.length > 0 ? 'boxShadow' : ''">
                                <span class="title fontWeight">Upload a File</span>
                                <p class="message">Select a file to upload from your computer or device.</p>

                                <div class="actions">
                                    <label for="file" class="button upload-btn">
                                        <div style="display: flex;align-items: center;grid-gap: 7px;">
                                            <svg viewBox="0 0 640 512" height="1em" style="font-size: 18px;">
                                                <path
                                                    d="M144 480C64.5 480 0 415.5 0 336c0-62.8 40.2-116.2 96.2-135.9c-.1-2.7-.2-5.4-.2-8.1c0-88.4 71.6-160 160-160c59.3 0 111 32.2 138.7 80.2C409.9 102 428.3 96 448 96c53 0 96 43 96 96c0 12.2-2.3 23.8-6.4 34.6C596 238.4 640 290.1 640 352c0 70.7-57.3 128-128 128H144zm79-217c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l39-39V392c0 13.3 10.7 24 24 24s24-10.7 24-24V257.9l39 39c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-80-80c-9.4-9.4-24.6-9.4-33.9 0l-80 80z">
                                                </path>
                                            </svg>
                                            <div class="textChooseFile">
                                                <span> Choose a File</span>
                                                <span class="fontWeight">limit of 2MB for one file</span>
                                            </div>
                                        </div>
                                        <input hidden type="file" id="file" multiple accept="application/pdf"
                                            @input="selectFile($event)" :disabled="loading">
                                    </label>
                                </div>
                                <template x-if="validateForm?.files">
                                    <div class="inputWithLabelLy" style="margin-bottom: 15px;">
                                        <p class="inputError" x-text="validateForm?.files"></p>
                                    </div>
                                </template>
                                <template x-if="fileMess?.error">
                                    <div class="inputWithLabelLy" style="margin-bottom: 15px;">
                                        <p class="inputError" x-text="fileMess?.message"></p>
                                    </div>
                                </template>

                                <div class="resultFile">
                                    <template x-for="(item,index) in files" :key="index">
                                        <div class="file-uploaded">
                                            <p>
                                                <i :class="item?.icon ?? 'bx bx-file'"></i>
                                                <span x-text="item?.name"></span>
                                            </p>
                                            <i class='bx bx-x' style="width: 30px;display: flex;justify-content: flex-end;"
                                                @click="deleteFile(index)"></i>
                                        </div>
                                    </template>
                                </div>
                                <div x-show="uploading">
                                    <div>
                                        Upload Progress: <span x-text="uploadPercentage + '%'"></span>
                                    </div>
                                    <div style="width: 100%; background: #e0e0e0;">
                                        <div
                                            :style="{ width: uploadPercentage + '%', background: '#76c7c0', height: '20px' }">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <form class="file-upload-form">
                                <label for="file" class="file-upload-label">
                                    <div class="file-upload-design">
                                        <svg viewBox="0 0 640 512" height="1em">
                                            <path
                                                d="M144 480C64.5 480 0 415.5 0 336c0-62.8 40.2-116.2 96.2-135.9c-.1-2.7-.2-5.4-.2-8.1c0-88.4 71.6-160 160-160c59.3 0 111 32.2 138.7 80.2C409.9 102 428.3 96 448 96c53 0 96 43 96 96c0 12.2-2.3 23.8-6.4 34.6C596 238.4 640 290.1 640 352c0 70.7-57.3 128-128 128H144zm79-217c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l39-39V392c0 13.3 10.7 24 24 24s24-10.7 24-24V257.9l39 39c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-80-80c-9.4-9.4-24.6-9.4-33.9 0l-80 80z">
                                            </path>
                                        </svg>
                                        <p>Drag and Drop</p>
                                        <p>or</p>
                                        <span class="browse-button">Browse file</span>
                                    </div>
                                    <input id="file" type="file" />
                                </label>
                            </form> --}}

                            {{-- <div class="container">
                                <div class="folder">
                                    <div class="front-side">
                                        <div class="tip"></div>
                                        <div class="cover"></div>
                                    </div>
                                    <div class="back-side cover"></div>
                                </div>
                                <label class="custom-file-upload">
                                    <input class="title" type="file" />
                                    Choose a file
                                </label>
                            </div> --}}
                        </div>
                    </div>
                    <div class="applyGp btnActionGp">
                        <div class="appLeft">
                            <div style="display: flex;align-items: center;margin-bottom: 25px;">
                                <label class="material-checkbox">
                                    <input type="checkbox" @click="agreeCheck()" :disabled="loading">
                                    <span class="checkmark"></span>
                                </label>
                                <span>I agree to the terms and conditions and privacy policy</span>
                            </div>
                            <button type="button" class="buttonApply" :disabled="isAgree || loading"
                                @click="submitFiles()">
                                Apply Now
                                {{-- <i class='bx bx-loader-alt bx-spin' x-show="loading"></i> --}}
                                <svg class="button__icon" xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M14.2199 21.9352C13.0399 21.9352 11.3699 21.1052 10.0499
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
                                                                                                                                                                                                                                                                                                                4.49525L7.63988 7.33525Z"
                                        fill="var(--container-color)">
                                    </path>
                                    <path
                                        d="M10.11 14.7052C9.92005 14.7052 9.73005 14.6352 9.58005
                                                                                                                                                                                                                                                                                                                14.4852C9.29005 14.1952 9.29005 13.7152 9.58005 13.4252L13.16
                                                                                                                                                                                                                                                                                                                9.83518C13.45 9.54518 13.93 9.54518 14.22 9.83518C14.51 10.1252 14.51
                                                                                                                                                                                                                                                                                                                10.6052 14.22 10.8952L10.64 14.4852C10.5 14.6352 10.3 14.7052 10.11
                                                                                                                                                                                                                                                                                                                14.7052Z"
                                        fill="var(--container-color)">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <template x-if="loading">
                <div class="loading">
                    <div class="ldbg"></div>
                    <div class="ldText">
                        <i class='bx bx-loader-alt bx-spin'></i>
                        <p>Please wait. in progress apply internship ...</p>
                    </div>
                </div>
            </template>
        </div>
    </div>
    @include('website::pages.applyDialog')
@stop
@section('script')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data("xApplyForm", () => ({
                baseImageUrl: "{{ asset('file_manager') }}",
                image: "",
                files: [],
                fileMess: {
                    error: false,
                    message: ""
                },
                formData: {
                    job_id: "",
                    job_title: "",
                    name: "",
                    phone: "",
                    email: "",
                    description: ""
                },
                uploading: false,
                uploadPercentage: 0,
                validateForm: {},
                isAgree: true,
                loading: false,
                init() {
                    console.log('hiii@@@@i');
                    const data = @json($job);
                    console.log(data, 'fsfsfsfsf');
                    if (data) {
                        this.formData.job_id = data.id;
                        this.formData.job_title = data.title;
                    }
                    // this.$store.applyDialog.open({
                    //     data: {
                    //         'digPosition': 'top',
                    //     },
                    //     afterClosed: (result) => {
                    //         console.log("resssss", result);
                    //         if (result) {
                    //             const dataRes = result?.data ?? {};
                    //             const url = `{{ route('web-job') }}`;
                    //             console.log(url, 'urlllll');
                    //             // reloadData(url);
                    //             // update realtime data List
                    //         }
                    //     }
                    // });
                },
                selectFile(event) {
                    const newFiles = Array.from(event.target.files);
                    this.fileMess.error = false;
                    // this.validateForm?.files = "";
                    if (this.files.length + newFiles.length > 2) {
                        this.fileMess.error = true;
                        this.fileMess.message = "You can only select up to 2 files.";
                        return; // Stop further processing
                    }

                    // Check for file size exceeding 2MB (2 * 1024 * 1024 bytes)
                    for (let file of newFiles) {
                        if (file.size > 2 * 1024 * 1024) {
                            this.fileMess.error = true;
                            this.fileMess.message = "File size greater than 2MB.";
                            return; // Stop further processing
                        }
                    }

                    if (this.files.length > 0) {
                        this.files.push(...newFiles);
                    } else {
                        this.files = newFiles;
                    }

                    if (this.files.length > 0) {
                        this.files = this.files.filter(file => file.type === 'application/pdf');
                        this.files.map(file => {
                            file.fileSize = formatFileSize(file.size);
                            file.icon = file.type === 'application/pdf' ? 'bx bxs-file-pdf' : ''
                        });
                    }
                    console.log(this.files, 'file');
                },
                deleteFile(index) {
                    this.files.splice(index, 1);
                },
                agreeCheck() {
                    this.isAgree = !this.isAgree;
                },
                submitFiles(is_send = "active") {
                    this.validateForm = "";
                    this.loading = true;

                    // Create FormData and append files
                    let formData = new FormData();
                    this.files.forEach(file => {
                        formData.append('files[]', file); // Append each file to FormData
                    });

                    // Append additional form fields if necessary
                    formData.append('name', this.formData.name);
                    formData.append('email', this.formData.email);
                    formData.append('phone', this.formData.phone);
                    formData.append('job_title', this.formData.job_title);
                    formData.append('job_id', this.formData.job_id);
                    formData.append('description', this.formData.description);
                    formData.append('is_send', is_send);
                    let delayQuery = null;
                    clearTimeout(this.delayQuery);
                    delayQuery = setTimeout(() => {
                        Axios({
                                method: 'post',
                                url: '{{ route('web-apply') }}',
                                data: formData
                            })
                            .then((response) => {
                                this.loading = false;
                                this.$store.applyDialog.open({
                                    data: {
                                        'digPosition': 'top',
                                    },
                                    afterClosed: (result) => {
                                        console.log("resssss", result);
                                        if (result) {
                                            const dataRes = result?.data ?? {};
                                            const url =
                                                `{{ route('web-job') }}`;
                                            reloadData(url);
                                        }
                                    }
                                });
                            })
                            .catch((error) => {
                                this.validateForm = error?.response?.data?.errors;
                                this.loading = false;
                                console.log(this.validateForm, 'dataError');
                            });
                    }, 1500);
                },
            }));
        });
    </script>
@stop
