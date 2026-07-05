<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Document</title>
    <link rel="shortcut icon" href="{!! asset('images/logo/ISEA_webicon.png') !!}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    {!! HTML::style('admin-public/css/select2.min.css') !!}
    {!! HTML::style('website/css/app.css') !!}
    {!! HTML::style('css/iziToast.css') !!}

    {!! HTML::script('js/iziToast.js') !!}
    {!! HTML::script('website/js/app.js') !!}
    {!! HTML::script('admin-public/js/select2.min.js') !!}
    {!! HTML::script('admin-public/js/owl.carousel.min.js') !!}

</head>

<body>
    @include('admin::components.iziToast')
    <div x-data="XVerifyDoc" class="VerifyDocLayout">
        <div class="VerifyDoc">
            <div class="SearchGp">
                <input type="text" name="inputSearch" placeholder="Enter id ..." />
                <button type="button" :disabled="loading">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path
                            d="M19.023 16.977a35.13 35.13 0 0 1-1.367-1.384c-.372-.378-.596-.653-.596-.653l-2.8-1.337A6.962 6.962 0 0 0 16 9c0-3.859-3.14-7-7-7S2 5.141 2 9s3.14 7 7 7c1.763 0 3.37-.66 4.603-1.739l1.337 2.8s.275.224.653.596c.387.363.896.854 1.384 1.367l1.358 1.392.604.646 2.121-2.121-.646-.604c-.379-.372-.885-.866-1.391-1.36zM9 14c-2.757 0-5-2.243-5-5s2.243-5 5-5 5 2.243 5 5-2.243 5-5 5z">
                        </path>
                    </svg>Search</button>
            </div>
            <div class="VerifyDocBody">
                <div class="fontWeight">Out of&nbsp;<span class="colorGreen" x-text="DataDocuments?.length"></span>
                    items in the document,&nbsp;<span class="colorGreen" x-text="DocumentCompletes?.length"></span> have
                    been completed<span x-show="(DataDocuments?.length - DocumentCompletes?.length) > 0 ">,
                        and&nbsp;<span class="colorRed"
                            x-text="DataDocuments?.length - DocumentCompletes?.length"></span> are
                        pending.</span></div>
                <div class="VerifyItemDocGp">
                    <template x-for="item in DataDocuments">
                        <div class="VerifyItem">
                            <div class="VerImgGp">
                                <img :src="item?.file" />
                            </div>
                            <div class="VerRight">
                                <p class="VerTitle" x-text="item?.name??''"></p>
                                <p class="VerDate">2014-12-01</p>
                                <div class="VerBtnGp">
                                    <template x-if="!item?.check">
                                        <button type="button" class="itemCheck VerBtn btnCheck"
                                            @click="itemCheck(item)" :disabled="loading">
                                            <div class="btnCheckText">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="m10 15.586-3.293-3.293-1.414 1.414L10 18.414l9.707-9.707-1.414-1.414z">
                                                    </path>
                                                </svg>
                                                <span class="uv-checkbox-text">Verify</span>
                                            </div>
                                        </button>
                                    </template>
                                    <template x-if="item?.check">
                                        <button type="button" class="itemCheck VerBtn btnUndo" @click="undoCheck(item)"
                                            :disabled="loading">
                                            <div class="btnCheckText">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="m16.192 6.344-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242z">
                                                    </path>
                                                </svg>
                                                <span class="uv-checkbox-text">Undo</span>
                                            </div>
                                        </button>
                                    </template>
                                    <button type="button" class="itemCheck VerBtn btnPreview" data-fancybox
                                        :data-src="item?.file" :disabled="loading">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M14 12c-1.095 0-2-.905-2-2 0-.354.103-.683.268-.973C12.178 9.02 12.092 9 12 9a3.02 3.02 0 0 0-3 3c0 1.642 1.358 3 3 3 1.641 0 3-1.358 3-3 0-.092-.02-.178-.027-.268-.29.165-.619.268-.973.268z">
                                            </path>
                                            <path
                                                d="M12 5c-7.633 0-9.927 6.617-9.948 6.684L1.946 12l.105.316C2.073 12.383 4.367 19 12 19s9.927-6.617 9.948-6.684l.106-.316-.105-.316C21.927 11.617 19.633 5 12 5zm0 12c-5.351 0-7.424-3.846-7.926-5C4.578 10.842 6.652 7 12 7c5.351 0 7.424 3.846 7.926 5-.504 1.158-2.578 5-7.926 5z">
                                            </path>
                                        </svg>
                                        <span class="uv-checkbox-text">Preview</span>
                                    </button>
                                </div>
                            </div>
                            <div class="VerifyInputCheckBoxIcon" x-show="item?.check" x-transition:enter.duration.500ms
                                x-transition:leave.duration.400ms>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path d="m10 15.586-3.293-3.293-1.414 1.414L10 18.414l9.707-9.707-1.414-1.414z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="VerifyDocFooter">
                <button type="button" class="fontWeight" @click="submitVerify" :disabled="loading">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;" x-show="!loading">
                        <path
                            d="m21.426 11.095-17-8A.999.999 0 0 0 3.03 4.242L4.969 12 3.03 19.758a.998.998 0 0 0 1.396 1.147l17-8a1 1 0 0 0 0-1.81zM5.481 18.197l.839-3.357L12 12 6.32 9.16l-.839-3.357L18.651 12l-13.17 6.197z">
                        </path>
                    </svg>
                    <div class="loading" x-show="loading"></div>
                    Submit
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data("XVerifyDoc", () => ({
                DataDocuments: [],
                DocumentCompletes: [{
                    "id": 14,
                    "name": "Petty cash #001",
                    "file": "https://buffer.com/library/content/images/size/w1200/2023/10/free-images.jpg"
                }],
                loading: false,
                init() {
                    this.DataDocuments = [{
                            "id": 10,
                            "name": "Petty cash #001",
                            "file": "https://buffer.com/library/content/images/size/w1200/2023/10/free-images.jpg",
                            "check": false
                        },
                        {
                            "id": 12,
                            "name": "Name_0111",
                            "file": "https://buffer.com/library/content/images/size/w1200/2023/10/free-images.jpg",
                            "check": false
                        },
                        {
                            "id": 13,
                            "name": "Name_0111",
                            "file": "https://buffer.com/library/content/images/size/w1200/2023/10/free-images.jpg",
                            "check": false
                        },
                        {
                            "id": 14,
                            "name": "Petty cash #001",
                            "file": "https://buffer.com/library/content/images/size/w1200/2023/10/free-images.jpg",
                            "check": false
                        },
                    ];
                    this.checkFileComplete();
                },
                checkFileComplete() {
                    if (this.DataDocuments) {
                        this.DataDocuments.forEach(item => {
                            let findItem = ""
                            if (this.DocumentCompletes?.length) {
                                findItem = this.DocumentCompletes.find(val => val.id == item
                                    .id);
                            }
                            item.check = findItem ? true : false
                        });
                    }
                },
                itemCheck(item) {
                    this.DocumentCompletes.push(item);
                    this.checkFileComplete();
                },
                undoCheck(item) {
                    this.DocumentCompletes = this.DocumentCompletes.filter(val => val.id != item?.id);
                    this.checkFileComplete();
                },
                submitVerify() {
                    this.loading = true;
                    setTimeout(() => {
                        console.log(this.DocumentCompletes, "this.DocumentCompletes");
                        this.loading = false;
                    }, 1500);
                }
            }));
        });
    </script>


    {!! HTML::script('website/js/body.js') !!}
</body>

</html>
