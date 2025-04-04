<link rel="stylesheet" href="/css/mypage/profile.css">
<div class="profile__wrap">
    <div class="tab__btn__container">
        <div class="tab__btn__item" form-id="profile__set__wrap">
            <span data-i18n="p_information">계정설정</span>
        </div>
        <div class="tab__btn__item" form-id="profile__payment__wrap">
            <span data-i18n="p_payment_method">결제수단</span>
        </div>
        <div class="tab__btn__item" form-id="profile__customize__purchase__wrap">
            <span data-i18n="p_preference">맞춤구매</span>
        </div>
        <div class="tab__btn__item" form-id="profile__delivery__wrap">
            <span data-i18n="p_address">배송지목록</span>
        </div>
        <div class="tab__btn__item" form-id="profile__marketing__wrap">
            <span data-i18n="p_subscription">마케팅설정</span>
        </div>
    </div>
    <div class="swiper tab__btn">
        <div class="swiper-wrapper">
            <div class="swiper-slide tab__btn__item" form-id="profile__set__wrap">
                <span data-i18n="p_information">계정설정</span>
            </div>
            <div class="swiper-slide tab__btn__item" form-id="profile__payment__wrap">
                <span data-i18n="p_payment_method">결제수단</span>
            </div>
            <div class="swiper-slide tab__btn__item" form-id="profile__customize__purchase__wrap">
                <span data-i18n="p_preference">맞춤구매</span>
            </div>
            <div class="swiper-slide tab__btn__item" form-id="profile__delivery__wrap">
                <span data-i18n="p_address">배송지목록</span>
            </div>
            <div class="swiper-slide tab__btn__item" form-id="profile__marketing__wrap">
                <span data-i18n="p_subscription">마케팅설정</span>
            </div>
        </div>
    </div>
    <div class="profile__tab__wrap">
        <?php include_once("mypage-main-profile-account.php"); ?>
        <?php include_once("mypage-main-profile-payment.php"); ?>
        <?php include_once("mypage-main-profile-customize.php"); ?>
        <?php include_once("mypage-main-profile-delivery.php"); ?>
        <?php include_once("mypage-main-profile-marketing.php"); ?>
    </div>
</div>