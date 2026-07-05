<style>
    .logo {
        position: relative;
        display: flex;
        align-items: center;
        height: 100%;
    }

    .logo>img {
        height: 50px;
        object-fit: contain;
    }

    .logo>label {
        position: absolute;
        right: -25px;
        font-size: 25px;
        width: fit-content
    }
</style>
<div class="wb-header" style="height: 85px;">
    <div class="header nav-menu">
        <div class="h-left" style="height: 100%;">
            <a href="{{ route('web-index') }}">
                {{-- <div class="logo">
                    <img src="{{ asset('images/logo/Logo.png') }}">
                    <label>ISEA</label>
                </div> --}}
                <div class="logo">
                    <img src="{{ asset('images/logo/LogoWhite.png') }}" class="first">
                    <img src="{{ asset('images/logo/Logo2.png') }}" class="last">
                </div>
            </a>

        </div>
        <div class="h-right">
            <a href="{{ route('web-index') }}">
                <div class="h-item {{ routeActive('/') ? 'active' : '' }}">
                    <i class='bx bx-home-alt-2 margin-2'></i>
                    <label>Home</label>
                </div>
            </a>
            <a href="{{ route('web-job') }}">
                <div class="h-item {{ routeActive('internship*') ? 'active' : '' }}">
                    <i class='bx bx-search'></i>
                    <label>Internships</label>
                </div>
            </a>
            <a href="{{ route('web-blog') }}">
                <div class="h-item {{ routeActive('blog*') ? 'active' : '' }}">
                    <label>Blogs</label>
                </div>
            </a>
            <a href="{!! route('web-our-service') !!}">
                <div class="h-item {{ routeActive('our-service') ? 'active' : '' }}">
                    <label>Our Service</label>
                </div>
            </a>
            <a href="{!! route('web-about') !!}">
                <div class="h-item {{ routeActive('about') ? 'active' : '' }}">
                    <label>About</label>
                </div>
            </a>
            <a href="{!! route('web-contact') !!}">
                <div class="h-item {{ routeActive('contact') ? 'active' : '' }}">
                    <label>Contact</label>
                </div>
            </a>
            

            <i class='bx bx-menu toggle-menu navResponsiveMenu'></i>
        </div>
        {{-- <i class='bx bx-menu toggle-menu'></i> --}}

        {{-- navResponsive --}}
        <aside class="sidebar">
            {{-- <div class="bgNavSidebar"></div> --}}
            <div class="sidebarGp">
                <div class="sidebar-header">
                    {{-- <img src="logo.svg" class="logo" alt="" /> --}}
                    <a href="{{ route('web-index') }}">
                        <div class="logo">
                            <img src="{{ asset('images/logo/Logo.png') }}">
                            <label>ISEA</label>
                        </div>
                    </a>
                    <button class="close-btn"><i class='bx bx-x'></i></button>
                </div>
                <!-- links -->
                <ul class="links">
                    <li>
                        <a href="{{ route('web-index') }}" class="resMenu {{ routeActive('/') ? 'active' : '' }}">
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('web-job') }}" class="resMenu {{ routeActive('internship*') ? 'active' : '' }}">Internships</a>
                    </li>
                    <li>
                        <a href="{{ route('web-blog') }}" class="resMenu {{ routeActive('blog*') ? 'active' : '' }}">Blogs</a>
                    </li>
                    <li>
                        <a href="{{ route('web-our-service') }}" class="resMenu {{ routeActive('our-service') ? 'active' : '' }}">Our Service</a>
                    </li>
                    <li>
                        <a href="{!! route('web-about') !!}" class="resMenu {{ routeActive('about') ? 'active' : '' }}">About</a>
                    </li>
                    <li>
                        <a href="{!! route('web-contact') !!}" class="resMenu {{ routeActive('contact') ? 'active' : '' }}">Contact</a>
                    </li>
                    
                </ul>
                <!-- social media -->
                <ul class="social-icons">
                    <li>
                        <a href="https://www.twitter.com">
                            <i class='bx bxl-facebook'></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.twitter.com">
                            <i class='bx bxl-linkedin'></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.twitter.com">
                            <i class='bx bxl-instagram'></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.twitter.com">
                            <i class='bx bxl-youtube'></i>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>


    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nav = document.querySelector('.wb-header');
        const navMenu = document.querySelector('.wb-header .nav-menu');
        const toggleMenu = document.querySelector('.toggle-menu');
        const sidebar = document.querySelector(".sidebar");
        const closeBtn = document.querySelector(".close-btn");
        const wbContainer = document.querySelector(".content");

        // Function to handle scrolling
        function handleScroll() {
            if (window.scrollY > 20) {
                nav.classList.add('active');
            } else {
                nav.classList.remove('active');
            }
        }

        // Initial check if the page is already scrolled
        handleScroll();

        // Add scroll event listener
        window.addEventListener('scroll', handleScroll);

        // Toggle menu visibility on click
        toggleMenu.addEventListener('click', function() {
            navMenu.classList.toggle('show');
            sidebar.classList.toggle("show-sidebar");
            wbContainer.classList.toggle("ovrHidden");
            // if (navMenu.classList.contains('show')) {
            //     nav.classList.add('active');
            // } else {
            //     // Only remove the 'active' class if the window scroll is less than 20
            //     if (window.scrollY < 20) {
            //         nav.classList.remove('active');
            //     }
            // }
        });
        closeBtn.addEventListener("click", function() {
            sidebar.classList.remove("show-sidebar");
            navMenu.classList.remove("show");
            wbContainer.classList.remove("ovrHidden");
        });
    });
</script>
