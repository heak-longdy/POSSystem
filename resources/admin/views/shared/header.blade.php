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
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgb(255, 255, 255);
        border-radius: 24px;
        overflow: hidden;
        cursor: pointer;
        padding-left: 15px;
        /* box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.075); */
        /* border: 1px solid rgba(51, 51, 51, 0.2); */
        border-radius: 10px;
        /* border: 1px solid rgba(152, 152, 152, 0.2); */
        box-shadow: 0 0 3px rgba(0, 0, 0, 0.1);
    }

    .input {
        width: 95px;
        height: 100%;
        border: none;
        outline: none;
        font-size: 0.9em;
        caret-color: rgb(255, 81, 0);
        font-size: 15px;
    }

    .labelforsearch {
        cursor: text;
        padding: 0px 12px;
        display: flex;
        align-items: center;
    }
    .labelforsearch>svg{
        width: 20px;
        height: 20px;
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
<div class="header {{ isset($customClass) ? $customClass : '' }}" x-data="xHeader">
    <div class="header-wrapper">
        <div class="left">
            <nav>
                <div class="navHeaderRight">{!! $header_name !!}
                    {{-- <input type="checkbox" id="switch-mode" hidden>
                    <label for="switch-mode" class="switch-mode"></label>
                    <a href="#" class="notification">
                        <i class='bx bxs-bell'></i>
                        <span class="num">8</span>
                    </a> --}}
                </div>
            </nav>
        </div>
        {{-- <span class="right">
            <div class="btn-auth">
                <div class="dropdown">
                    <i data-feather="user" class="action-btn" id="dropdownMenuButton" data-mdb-toggle="dropdown"
                        aria-expanded="false">
                    </i>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                            <a class="dropdown-item sign-out-btn" data-url="#">
                                <i data-feather="log-out"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </span> --}}
        <div class="right">
            <nav>
                <div class="navHeaderRight">
                    {{-- <input type="checkbox" id="switch-mode" hidden>
                <label for="switch-mode" class="switch-mode"></label> --}}
                    <div style=" display: flex;justify-content: center;align-items: center; grid-gap: 20px;">
                        <div class="InputContainer">
                            <input placeholder="Search Menu" id="input" class="input" name="text" type="text"
                                @click="selectShop()" />
                            <label class="labelforsearch" for="input">
                                {{-- <i class='bx bx-command' style="font-size: 20px;color: #666565;"></i> --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 20.247 6-16.5" />
                                  </svg>
                                  
                            </label>
                        </div>
                        <div class="notificationGp" style="position: relative;">
                            <a href="#" class="notification">
                                {{-- <i class='bx bxs-bell'></i> --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5" />
                                </svg>

                                {{-- <span class="num">0</span> --}}
                            </a>
                            <ul class="notification-body">
                                <div class="notification-card" style="width: 100%;">
                                    <div class="notification-header">
                                        <h2>Notifications</h2>
                                        <div class="badge">0 new</div>
                                    </div>
                                    <ul class="notification-list">
                                        <div style="padding: 35px;">
                                            <img src="{{ asset('images/logo/em.svg') }}"
                                                style="width: 140px;height: 140px;margin-right: 0;border-radius: 0;" />
                                            <div class="message" style="display: flex;flex-direction: column;">
                                                <span class="title"
                                                    style="color: #333;font-size: 18px;font-weight: 700;">Coming
                                                    Soon...</span>
                                                <span class="des" style="font-size: 14px;color: #989898;">This
                                                    feature is coming soon.</span>
                                            </div>
                                        </div>
                                        {{-- <li>
                                            <img src="{{ asset('images/user.jpg') }}" alt="Avatar">
                                            <div class="notification-content">
                                                <p><strong>Roman Joined the Team!</strong></p>
                                                <p>Congratulate him</p>
                                            </div>
                                        </li>
                                        <li>
                                            <img src="{{ asset('images/user.jpg') }}" alt="Avatar">
                                            <div class="notification-content">
                                                <p><strong>New message received</strong></p>
                                                <p>Salma sent you a new message</p>
                                            </div>
                                        </li>
                                        <li>
                                            <img src="{{ asset('images/user.jpg') }}" alt="Avatar">
                                            <div class="notification-content">
                                                <p><strong>New Payment received</strong></p>
                                                <p>Check your earnings</p>
                                            </div>
                                        </li>
                                        <li>
                                            <img src="{{ asset('images/user.jpg') }}" alt="Avatar">
                                            <div class="notification-content">
                                                <p><strong>Jolly completed tasks</strong></p>
                                                <p>Assign her new tasks</p>
                                            </div>
                                        </li>
                                        <li>
                                            <img src="{{ asset('images/user.jpg') }}" alt="Avatar">
                                            <div class="notification-content">
                                                <p><strong>Roman Joined the Team!</strong></p>
                                                <p>Congratulate him</p>
                                            </div>
                                        </li> --}}
                                    </ul>
                                    <div class="see-all-btn">
                                        <button type="button" style="grid-gap: 10px;"><i class='bx bx-right-arrow-alt'
                                                style="font-size: 23px;"></i><span>See all Notifications</span></button>
                                    </div>
                                </div>
                            </ul>
                        </div>
                    </div>
                    {{-- search --}}
                    {{-- <label class="search-label">
                        <input type="text" name="text" class="input" required="" placeholder="Type here...">
                        <kbd class="slash-icon">/</kbd>
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" version="1.1"
                            xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0"
                            viewBox="0 0 56.966 56.966" style="enable-background:new 0 0 512 512" xml:space="preserve">
                            <g>
                                <path
                                    d="M55.146 51.887 41.588 37.786A22.926 22.926 0 0 0 46.984 23c0-12.682-10.318-23-23-23s-23 10.318-23 23 10.318 23 23 23c4.761 0 9.298-1.436 13.177-4.162l13.661 14.208c.571.593 1.339.92 2.162.92.779 0 1.518-.297 2.079-.837a3.004 3.004 0 0 0 .083-4.242zM23.984 6c9.374 0 17 7.626 17 17s-7.626 17-17 17-17-7.626-17-17 7.626-17 17-17z"
                                    fill="currentColor" data-original="#000000" class=""></path>
                            </g>
                        </svg>
                    </label> --}}
                    {{-- <div class="containergggg">
                        <input type="text" name="text" class="input" required=""
                            placeholder="Type to search...">
                        <div class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                                <title>Search</title>
                                <path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z"
                                    fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32">
                                </path>
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10"
                                    stroke-width="32" d="M338.29 338.29L448 448"></path>
                            </svg>
                        </div>
                    </div> --}}

                    {{-- {{ dd(Auth::user()) }} --}}
                    {{-- <div class="sidebar-profile" title="{{ Auth::user() ? Auth::user()->username : '' }}">
                        <div class="profile-img">
                            <img src="{{ Auth::user() ? Auth::user()->path : asset('backend/image/user.png') }}" alt=""
                                onerror="this.onerror=null;this.src='{{ asset('backend/image/user.png') }}';">
                        </div>
                        <div class="profile-info" title="{{ Auth::user() ? Auth::user()->username : '' }}" style="white-space: nowrap;overflow: hidden;">
                            <h3 style="text-transform: uppercase;">{{ Auth::user() ? Auth::user()->username : 'User' }}</h3>
                            <p>{{ Auth::user() ? Auth::user()->email : '---' }}</p>
                        </div>
                        <div class="profile-button sign-out-btn" data-url="{{ route('sign-out') }}">
                            <span class="material-icons-outlined">
                                logout
                            </span>
                        </div>
                    </div> --}}


                    <div class="profile" x-data="xHeader">
                        {{-- <img src="{{ asset('admin-public/logo/profile.png') }}" alt="" /> --}}
                        <img src="{{ Auth::user()?->image_url ?? asset('admin-public/logo/profile.png') }}"
                            alt=""
                            onerror="this.onerror=null;this.src='{{ asset('admin-public/logo/profile.png') }}';" />
                        <ul class="profile-link">
                            <div class="profile-card">
                                <div class="user-info">
                                    <img class="avatar"
                                        src="{{ Auth::user()?->image_url ?? asset('admin-public/logo/profile.png') }}"
                                        alt="Profile Picture"
                                        onerror="this.onerror=null;this.src='{{ asset('admin-public/logo/profile.png') }}';">
                                    <div class="user-details">
                                        <h2>{{ Auth::user()?->name }}</h2>
                                        <p class="title">Administrator</p>
                                        <p class="email"><i class="email-icon"></i>{{ Auth::user()?->email }}</p>
                                    </div>
                                </div>
                                <hr>
                                <ul class="profile-menu">
                                    <li @click="editUser({{ Auth::user()?->id }})">
                                        <i class='bx bx-user'></i>
                                        <div>
                                            <p>My Profile </p>
                                            <span>Account Settings</span>
                                        </div>
                                    </li>
                                    <li>
                                        <i class="material-symbols-outlined"> alternate_email</i>
                                        <div>
                                            <p>My Inbox </p>
                                            <span>Messages & Emails</span>
                                        </div>
                                    </li>
                                    <li>
                                        <i class='bx bx-task'></i>
                                        <div>
                                            <p>My Tasks </p>
                                            <span>To-do and Daily Tasks</span>
                                        </div>
                                    </li>
                                </ul>
                                {{-- <div class="upgrade-card">
                                    <p>Unlimited Access</p>
                                    <button class="upgrade-btn">Upgrade</button>
                                </div> --}}
                                <button class="logout-btn" @click="signOut">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                    </svg>
                                    <span>Logout</span>
                                </button>
                            </div>
                            {{-- <div class="profileImageTextLayout">
                                <div class="imgProfile">
                                    <img class="img" src="{{ asset('admin-public/logo/profile.png') }}"
                                        alt="" />
                                    <div class="profileText">
                                        <div class="profileName">Longdy Heak</div>
                                        <div class="profileEmail">longdyheak9999@gmail.com</div>
                                    </div>
                                </div>
                                <div class="btnProfile" @click="profileInformation">Manage You Account</div>
                            </div>
                            <div class="profileAddAccount">
                                <i class='bx bx-user-plus'></i>
                                <div>Add other account</div>
                            </div>
                            <div class="profileActionLayout" @click="signOut">
                                <i class='bx bx-log-out'></i>
                                <div>Sing Out</div>
                            </div> --}}
                        </ul>
                    </div>

                </div>
            </nav>
        </div>
    </div>
</div>
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
        const navMenu = document.querySelector('.header .nav-menu');
        const toggleMenu = document.querySelector('.toggle-menu');
        const sidebar = document.querySelector(".sidebar");
        const closeBtn = document.querySelector(".close-btn");
        const wbContainer = document.querySelector(".content");
        const formAdmin = document.querySelector(".form-admin");

        // Function to handle scrolling within the .form-admin element
        function handleScroll() {
            // Check if .form-admin is scrolled more than 20px
            if (formAdmin?.scrollTop > 20) {
                nav.classList.add('active');
            } else {
                nav.classList.remove('active');
            }
        }

        // Initial check if the .form-admin is already scrolled
        handleScroll();

        // Add scroll event listener to .form-admin
        formAdmin?.addEventListener('scroll', handleScroll);
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
            console.log('hiiiiiiiii');
            let dataMenu = @json(config('menu'));
            var queueSearch = 500;
            SearchMenu({
                title: "Quict Search Menu",
                placeholder: "Search Menu ...",
                onReady: (callback_data) => {
                    console.log(dataMenu, 'dataMenu');
                    callback_data(dataMenu);
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
                                            _title: item?.invoice_number ? item
                                                .invoice_number : "",
                                            _image: this.imageLogoSelectOption,
                                            _description: item?.purchase
                                                ?.project?.name ?? '',
                                            ...item,
                                        }
                                    });
                                callback_data(data);
                            });
                    }, 500);
                },
                afterClose: (res) => {
                    if (res) {
                        console.log(res, 'ressss');
                        this.formSubmitData.invoice_ref_id = res.id;
                        this.formSubmitData.invoice_number = res.invoice_number;
                    }
                }
            });
        },
        editUser(id) {
            const url = `/admin/user/edit/${id}`;
            reloadData(url);
        }
    }));
</script>
