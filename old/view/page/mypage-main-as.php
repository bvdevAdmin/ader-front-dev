<link rel="stylesheet" href="/css/mypage/as.css">

<div class="as__wrap as_container">
    <ul class="as_tab_btn">
        <li class="as_tab on" data-tab_num="one" form-id="as__notice__wrap" data-i18n="as_notice">
            약관 및 요금
		</li>
        <li class="as_tab" data-tab_num="two" form-id="as__apply__wrap" data-i18n="as_request">A/S 신청</li>
        <li class="as_tab" data-tab_num="three" form-id="as__condition__wrap" data-i18n="as_status">A/S 현황</li>
        <li class="as_tab" data-tab_num="four" form-id="as__history__wrap" data-i18n="as_history">A/S 내역</li>
    </ul>
	
    <div class="swiper tab__btn">
        <div class="swiper-wrapper">
            <div class="swiper-slide tab__btn__item as_tab" data-tab_num="one" form-id="as__notice__wrap">
                <span data-i18n="as_notice">약관 및 요금</span>
            </div>
            
			<div class="swiper-slide tab__btn__item as_tab" data-tab_num="two" form-id="as__apply__wrap">
                <span data-i18n="as_request">A/S 신청</span>
            </div>
            
			<div class="swiper-slide tab__btn__item as_tab" data-tab_num="three" form-id="as__condition__wrap">
                <span data-i18n="as_status">A/S 현황</span>
            </div>
            
			<div class="swiper-slide tab__btn__item as_tab" data-tab_num="four" form-id="as__history__wrap">
                <span data-i18n="as_history">A/S 내역</span>
            </div>
        </div>
    </div>
	
    <div class="as__tab__wrap">
        <?php include_once("mypage-main-as-price.php"); ?>
    </div>
    <div class="as__tab__wrap">
        <?php include_once("mypage-main-as-apply.php"); ?>
    </div>
    <div class="as__tab__wrap">
        <?php include_once("mypage-main-as-current.php"); ?>
    </div>
    <div class="as__tab__wrap">
        <?php include_once("mypage-main-as-complete.php"); ?>
    </div>
	
    <div class="apply_complete__wrap">
        <div class="apply_complete" style="display: none;">
            <div class="as_com_title" data-i18n="as_submitted">A/S 서비스 신청이 완료되었습니다.</div>
            <div class="as_com_contents">
                <p data-i18n="as_status_info">상단의 A/S 현황 탭에서 해당 제품의 A/S 진행 과정을 열람하실 수 있습니다.</p>
                <p style="margin-top: 10px;">·&nbsp;제품 회수 후에는 A/S 신청을 취소하실 수 없습니다.</p>
            </div>
        </div>
    </div>
</div>

<script src="/scripts/mypage/as/as-common.js"></script>