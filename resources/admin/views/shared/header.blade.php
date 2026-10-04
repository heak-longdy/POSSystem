@section('title')
    | {{ $header_name }}
@stop
<style>
    /* .search-label {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        position: relative;
        border: 1px solid transparent;
        border-radius: 12px;
        overflow: hidden;
        background: #3D3D3D;
        padding: 9px;
        cursor: text;
        height: 35px;
        font-size: 14px;
        margin-right: 15px;
    }

    .search-label:hover {
        border-color: gray;
    }

    .search-label:focus-within {
        background: #464646;
        border-color: gray;
    }

    .search-label input {
        outline: none;
        width: 100%;
        border: none;
        background: none;
        color: rgb(162, 162, 162);
    }

    .search-label input:focus+.slash-icon,
    .search-label input:valid+.slash-icon {
        display: none;
    }

    .search-label input:valid~.search-icon {
        display: block;
    }

    .search-label input:valid {
        width: calc(100% - 22px);
        transform: translateX(20px);
    }

    .search-label svg,
    .slash-icon {
        position: absolute;
        color: #7e7e7e;
    }

    .search-icon {
        display: none;
        width: 12px;
        height: auto;
    }

    .slash-icon {
        right: 7px;
        border: 1px solid #393838;
        background: linear-gradient(-225deg, #343434, #6d6d6d);
        border-radius: 3px;
        text-align: center;
        box-shadow: inset 0 -2px 0 0 #3f3f3f, inset 0 0 1px 1px rgb(94, 93, 93), 0 1px 2px 1px rgba(28, 28, 29, 0.4);
        cursor: pointer;
        font-size: 12px;
        width: 15px;
    }

    .slash-icon:active {
        box-shadow: inset 0 1px 0 0 #3f3f3f, inset 0 0 1px 1px rgb(94, 93, 93), 0 1px 2px 0 rgba(28, 28, 29, 0.4);
        text-shadow: 0 1px 0 #7e7e7e;
        color: transparent;
    } */
    /* .containergggg {
        position: relative;
        --size-button: 40px;
        color: white;
    }

    .input {
        padding-left: var(--size-button);
        height: var(--size-button);
        font-size: 15px;
        border: none;
        color: #fff;
        outline: none;
        width: var(--size-button);
        transition: all ease 0.3s;
        background-color: #191A1E;
        box-shadow: 1.5px 1.5px 3px #0e0e0e, -1.5px -1.5px 3px rgb(95 94 94 / 25%), inset 0px 0px 0px #0e0e0e, inset 0px -0px 0px #5f5e5e;
        border-radius: 50px;
        cursor: pointer;
    }

    .input:focus,
    .input:not(:invalid) {
        width: 200px;
        cursor: text;
        box-shadow: 0px 0px 0px #0e0e0e, 0px 0px 0px rgb(95 94 94 / 25%), inset 1.5px 1.5px 3px #0e0e0e, inset -1.5px -1.5px 3px #5f5e5e;
    }

    .input:focus+.icon,
    .input:not(:invalid)+.icon {
        pointer-events: all;
        cursor: pointer;
    }

    .containergggg .icon {
        position: absolute;
        width: var(--size-button);
        height: var(--size-button);
        top: 0;
        left: 0;
        padding: 8px;
        pointer-events: none;
    }

    .containergggg .icon svg {
        width: 100%;
        height: 100%;
    } */
    /* From Uiverse.io by Shaidend */
    .InputContainer {
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border-radius: 30px;
        overflow: hidden;
        cursor: pointer;
        padding-left: 12px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }
    .InputContainer:hover {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .input {
        width: 125px;
        height: 100%;
        border: none;
        outline: none;
        caret-color: #3b82f6;
        font-size: 13.5px;
        color: #475569;
        cursor: pointer;
        background: transparent;
    }

    .labelforsearch {
        cursor: pointer;
        padding: 0px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
    }
    .labelforsearch>svg {
        width: 17px;
        height: 17px;
    }
    .header-cmd-badge {
        font-size: 10.5px;
        font-weight: 600;
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 1px 5px;
        line-height: 1.2;
    }

    .searchIcon {
        width: 19px;
    }

    .border {
        height: 40%;
        width: 1.3px;
        background-color: rgb(223, 223, 223);
    }

    .micIcon {
        width: 12px;
    }

    .micButton {
        padding: 0px 15px 0px 12px;
        border: none;
        background-color: transparent;
        height: 40px;
        cursor: pointer;
        transition-duration: 0.3s;
    }

    .searchIcon path {
        fill: rgb(114, 114, 114);
    }

    .micIcon path {
        fill: rgb(255, 81, 0);
    }

    .micButton:hover {
        background-color: rgb(255, 230, 230);
        transition-duration: 0.3s;
    }


    /* profile */
    /* * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    } */

    /* body {
        font-family: 'Arial', sans-serif;
        background-color: #f8f9fd;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    } */

    html,
    body {
        overflow-x: clip !important;
    }

    .container {
        min-height: 100vh !important;
        height: auto !important;
    }

    .container .container-wrapper {
        min-height: 100vh !important;
        height: auto !important;
    }

    #content,
    .content {
        min-height: 100vh !important;
        height: auto !important;
    }

    .main-header-navbar {
        position: -webkit-sticky !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 1000 !important;
        background: #ffffff !important;
        /* box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); */
    }

    nav .profile {
        position: relative;
    }

    nav .profile .profile-link {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        border-radius: 12px;
        opacity: 0;
        pointer-events: none;
        transition: all .25s ease;
        z-index: 99999 !important;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    nav .profile .profile-link.show {
        opacity: 1;
        pointer-events: auto;
        top: 100%;
        margin-top: 10px;
    }

    .user-info {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        text-align: left;
    }

    .user-info .avatar {
        width: 60px !important;
        height: 60px !important;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .user-details {
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: left;
    }

    .user-details h2 {
        font-size: 16px;
        font-weight: bold;
        color: #333;
        margin: 0 0 4px 0;
        white-space: nowrap;
    }

    .user-details .title {
        font-size: 13px;
        color: #888;
        margin: 0 0 2px 0;
    }

    .user-details .email {
        font-size: 13px;
        color: #4A90E2;
        margin: 0;
        word-break: break-all;
    }

    .profile-card {
        background-color: #fff;
        min-width: 300px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
    }

    .user-details h2 {
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }

    .user-details .title {
        font-size: 14px;
        color: #888;
    }

    .user-details .email {
        font-size: 14px;
        color: #4A90E2;
    }

    hr {
        margin: 20px 0;
        border: none;
        height: 1px;
        background-color: #eaeaea;
    }

    .profile-menu {
        list-style: none;
        text-align: left;
    }

    .profile-menu li span {
        color: #888;
    }

    .upgrade-card {
        background-color: #f0f4ff;
        padding: 15px;
        border-radius: 10px;
        margin: 20px 0;
        text-align: center;
    }

    .upgrade-card p {
        margin-bottom: 10px;
        color: #333;
        font-weight: bold;
    }

    .upgrade-btn {
        background-color: #4A90E2;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 20px;
        cursor: pointer;
    }

    .upgrade-btn:hover {
        background-color: #357ABD;
    }

    .notification-body {
        position: absolute;
        background: rebeccapurple;
        top: calc(100% + 10px);
        opacity: 0;
        background-color: #fff;
        width: 300px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
    }

    .notification-body.show {
        opacity: 1;
        pointer-events: visible;
        top: 100%;
        margin-top: 10px;
        right: 0;
    }
</style>
<div class="header-name-meta" data-header-name="{{ $header_name }}" style="display:none;"></div>
@include('admin::components.search_menu')
<script>
    // document.addEventListener('DOMContentLoaded', function() {
    //     const nav = document.querySelector('.header');
    //     const navMenu = document.querySelector('.header .nav-menu');
    //     const toggleMenu = document.querySelector('.toggle-menu');
    //     const sidebar = document.querySelector(".sidebar");
    //     const closeBtn = document.querySelector(".close-btn");
    //     const wbContainer = document.querySelector(".content");
    //     const formAdmin = document.querySelector(".form-admin");

    //     console.log(nav,'fff');

    //     // Function to handle scrolling
    //     function handleScroll() {
    //         if (formAdmin.scrollY > 20) {
    //             console.log('gguuuuu');
    //             nav.classList.add('active');
    //         } else {
    //             nav.classList.remove('active');
    //         }
    //     }

    //     // Initial check if the page is already scrolled
    //     handleScroll();

    //     // Add scroll event listener
    //     window.addEventListener('scroll', handleScroll);

    //     // Toggle menu visibility on click
    //     // toggleMenu.addEventListener('click', function() {
    //     //     navMenu.classList.toggle('show');
    //     //     sidebar.classList.toggle("show-sidebar");
    //     //     wbContainer.classList.toggle("ddd");
    //     //     if (navMenu.classList.contains('show')) {
    //     //         nav.classList.add('active');
    //     //     } else {
    //     //         // Only remove the 'active' class if the window scroll is less than 20
    //     //         if (window.scrollY < 20) {
    //     //             nav.classList.remove('active');
    //     //         }
    //     //     }
    //     // });
    //     // closeBtn.addEventListener("click", function() {
    //     //     sidebar.classList.remove("show-sidebar");
    //     //     navMenu.classList.remove("show");
    //     //     wbContainer.classList.remove("ddd");
    //     // });
    // });

    document.addEventListener('DOMContentLoaded', function() {
        const nav = document.querySelector('.header');
        const formAdmin = document.querySelector(".form-admin");

        if (nav && formAdmin) {
            function handleScroll() {
                if (formAdmin.scrollTop > 20) {
                    nav.classList.add('active');
                } else {
                    nav.classList.remove('active');
                }
            }

            handleScroll();
            formAdmin.addEventListener('scroll', handleScroll);
        }
    });
</script>

<script>
    Alpine.data('xHeader', () => ({
        open: false,
        init() {},
        toggle() {
            this.open = !this.open
        },
        signOut() {
            console.log('wrwrwrwr');
            var queueSearch = null;
            logOut({
                title: "Select Service",
                placeholder: "@lang('global.form.filter.search')",
                onReady: (callback_data) => {
                    Axios({
                            url: `#`,
                            method: 'GET'
                        })
                        .then(response => {
                            const data = response?.data?.data.map(item => {
                                return {
                                    _id: item.id,
                                    _title: item.name?.en,
                                    _image: this.baseImageUrl + item.image,
                                    _description: '@service',
                                    ...item,
                                }
                            });
                            callback_data(data);
                        });
                },
                afterClose: (res) => {
                    if (res) {
                        // this.table.reload();
                        this.service = res;
                        this.form.service_id = res.name?.en;
                    }
                }
            });
        },
        profileInformation() {
            var queueSearch = null;
            dialogProfile({
                title: "Select Service",
                placeholder: "@lang('global.form.filter.search')",
                width: "700px",
                onReady: (callback_data) => {
                    Axios({
                            url: `#`,
                            method: 'GET'
                        })
                        .then(response => {
                            const data = response?.data?.data.map(item => {
                                return {
                                    _id: item.id,
                                    _title: item.name?.en,
                                    _image: this.baseImageUrl + item.image,
                                    _description: '@service',
                                    ...item,
                                }
                            });
                            callback_data(data);
                        });
                },
                onSearch: (value, callback_data) => {
                    clearTimeout(queueSearch);
                    queueSearch = setTimeout(() => {
                        Axios({
                                url: `#`,
                                params: {
                                    search: value
                                },
                                method: 'GET'
                            })
                            .then(response => {
                                const data = response?.data?.data.map(
                                    item => {
                                        return {
                                            _id: item.id,
                                            _title: item.name?.en,
                                            _image: this.baseImageUrl + item
                                                .image,
                                            _description: '@service',
                                            ...item,
                                        }
                                    });
                                callback_data(data);
                            });
                    }, 1000);
                },
                afterClose: (res) => {
                    if (res) {
                        // this.table.reload();
                        this.service = res;
                        this.form.service_id = res.name?.en;
                    }
                }
            });
        },
        selectShop() {
            this.openQuickSearch();
        },
        openQuickSearch() {
            window.SearchMenu({
                title: @json(__('global.header.quick_search_menu')),
                placeholder: @json(__('global.header.quick_search_placeholder'))
            });
        },
        editUser(id) {
            const url = `/admin/user/edit/${id}`;
            reloadData(url);
        }
    }));

    window.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (Alpine.store('componentSearchMenu')?.active) {
                Alpine.store('componentSearchMenu').active = false;
            } else {
                window.SearchMenu({
                    title: @json(__('global.header.quick_search_menu')),
                    placeholder: @json(__('global.header.quick_search_placeholder'))
                });
            }
        }
    });
</script>
