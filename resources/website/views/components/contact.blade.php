<style>
    .bgContactCov {
        height: 100%;
        position: absolute;
        top: 0;
        width: 100%;
        z-index: 1;
        background: black;
        opacity: 0.4;
    }
</style>
<div class="webContentListLayout paddingTop_Bot60 bgYellow" style="color: #fff;padding: 35px 0;background: url({{ isset($imgUrl) ? $imgUrl : '' }});background-size: cover; height: 350px; position: relative;">
    <div class="bgContactCov"></div>
    <div class="webContentList" style="align-items: center;z-index: 2;">
        <div class="hLeft" data-aos="zoom-in" data-aos-delay="200">
            <h3 class="margin_bot20">{{isset($text1) ? $text1:''}}</h3>
            <a href="{{route('web-contact')}}" class="homeContactus" style="text-decoration: none;">
                <div class="btnContactUs">
                    <i class='bx bx-phone' style="font-size: 25px;"></i>
                    <spna>Contact Us</span>
                </div>
            </a>
        </div>
    </div>
</div>
