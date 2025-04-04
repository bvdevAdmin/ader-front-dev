<link rel="stylesheet" href="/css/mypage/mileage.css">
<div class="mileage__wrap">
    <div class="tab__btn__container">
        <div class="tab__btn__item" form-id="mileage__total__wrap">
            <span data-i18n="ml_all">전체</span>
        </div>
        <div class="tab__btn__item" form-id="mileage__save__wrap">
            <span data-i18n="ml_earned">적립</span>
        </div>
        <div class="tab__btn__item" form-id="mileage__use__wrap">
            <span data-i18n="ml_used_a">사용</span>
        </div>
        <div class="tab__btn__item" form-id="mileage__notice__wrap">
            <span data-i18n="ml_notice">유의사항</span>
        </div>
    </div>
    <div class="mileage__tab__wrap">
        <?php include_once("mypage-main-mileage-total.php"); ?>
        <?php include_once("mypage-main-mileage-save.php"); ?>
        <?php include_once("mypage-main-mileage-use.php"); ?>
        <?php include_once("mypage-main-mileage-notice.php"); ?>
    </div>
</div>
<script src="/scripts/mypage/mypage-common.js"></script>
<script src="/scripts/mypage/mileage/mileage-common.js"></script>