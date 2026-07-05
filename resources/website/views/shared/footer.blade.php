<footer>
    <div class="footer-container">
        <div class="footer-logo">
            <div class="logo" data-aos="fade-right" data-aos-delay="200">
                <img src="{{ asset('images/logo/Logo.png') }}"
                    style="">
                <label>ISEA</label>
            </div>
        </div>
        <div class="footer-section" data-aos="fade-right" data-aos-delay="200">
            <h4>Explore</h4>
            <ul>
                <li><a href="{{route('web-job')}}">Internships</a></li>
                <li><a href="{{route('web-about')}}">About Us</a></li>
                <li><a href="{{route('web-blog')}}">Blog</a></li>
            </ul>
        </div>
        <div class="footer-section" data-aos="fade-left" data-aos-delay="200">
            <h4>Contact Us</h4>
            <p>{{$contact?->email_4}}</p>
            <p>{{$contact?->phone_kh}}</p>
            <p>
                <span>{{$contact?->address_kh}}</span>
            </p>
        </div>
        <div class="footer-section" data-aos="fade-left" data-aos-delay="200">
            <h4>Follow Us</h4>
            <div class="social-icons">
                @if ($contact?->facebook)
                <a href="{{$contact?->facebook}}" target="_blank"><i class='bx bxl-facebook'></i></a>
                @endif
                @if ($contact?->link_in)
                <a href="{{$contact?->link_in}}" target="_blank"><i class='bx bxl-linkedin'></i></a>
                @endif
                @if ($contact?->instagram)
                <a href="{{$contact?->instagram}}" target="_blank"><i class='bx bxl-instagram'></i></a>
                @endif
                @if ($contact?->youtube)
                <a href="{{$contact?->youtube}}" target="_blank"><i class='bx bxl-youtube'></i></a>
                @endif
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2024 by Internship ISEA</p>
    </div>
</footer>
