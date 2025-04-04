<?php $session = "false";if (isset($_SESSION['MEMBER_IDX'])) {$session = "true";}?>
<!-- <div class="notice__wrap">
    <Marquee width="90%">
        <div class="notice__marquee">
            <div class="notice__title">cs 및 배송 시스템 개편 안내</div>
            <div class="notice__a"><u>자세히보기</u></div>
            <div class="notice__svg"><img src="/images/landing/left-arrow.svg" alt=""></div>
        </div>
    </Marquee>
    <div class="notice__close"><img src="/images/landing/close.svg" alt=""></div>
</div> -->
<nav class="header__wrap"></nav>
<nav class="hidden lg:hidden" id="web">
    <div class="right-0 w-full mt-2 origin-top-right">
        <div class="grid w-full pt-2 pb-4 h-96 gap-x-8" style="grid-template-columns: repeat(16,1fr);gap: 10px;">
            <div style="grid-column: 4/5;">
                <div class="">컬렉션</div>
            </div>
            <div>오리진</div>
            <div>잡화</div>
        </div>
    </div>
</nav>
<nav class="lg:hidden" id="mobile">
    <div class="lg:hidden side__menu"></div>
</nav>
<script src="/scripts/nav.js"></script>

<script>sessionStorage.setItem('login_session', <?php echo $session ?>);</script>
