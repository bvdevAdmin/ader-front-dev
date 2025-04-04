<?php
	if (!isset($_SESSION['MEMBER_IDX'])) {
		echo "
					<script>
						location.href = '/login';
					</script>
			";
	}
	
	function getUrlParamter($url, $sch_tag) {
		$parts = parse_url($url);
		parse_str($parts['query'], $query);
		return $query[$sch_tag];
	}
	
	$page_url = $_SERVER['REQUEST_URI'];
	$mypage_type = getUrlParamter($page_url, 'mypage_type');
?>
<link rel="stylesheet" href="/css/mypage/main.css">
<link rel="stylesheet" href="/css/module/foryou.css">
<link rel="stylesheet" href="/css/module/wishlist.css">
<main>
    <input type="hidden" id="mypage_type" value="<?=$mypage_type?>">
    <div class="mypage__wrap">
        <div class="mypage__container">
            <div class="mypage__items profile">
                <div class="member__contents">
                    <img src="/images/mypage/mypage_member_icon.svg">
                </div>
                <div class="profile__member__name">
                    <p id="mypage_member_name"></p>
                </div>
                <div class="profile__member__id">
                    <p id="mypage_member_id"></p>
                </div>
            </div>
            <div class="mypage__items profile_info">
                <div class="point__item icon__item" btn-type="orderlist">
                    <div class="point__title" data-i18n="m_order_cnt"></div>
                    <div class="point__value" id="order_value"></div>
                </div>
                <div class="point__item icon__item" btn-type="mileage">
                    <div class="point__title" data-i18n="m_mileage"></div>
                    <div class="point__value" id="mileage_value"></div>
                </div>
                <div class="point__item icon__item" btn-type="voucher">
                    <div class="point__title" data-i18n="m_voucher"></div>
                    <div class="point__value" id="voucher_cnt"></div>
                </div>
            </div>
            <div class="mypage__items btn__items">
                <div class="click__icon__item icon__item" btn-type="home">
                    <div class="icon">
                        <img src="/images/mypage/mypage_home_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_my-page"></p>
                    </div>
                </div>
                <div id="orderlist_icon" class="icon__item" btn-type="orderlist">
                    <div class="icon">
                        <img src="/images/mypage/mypage_orderlist_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_order_history"></p>
                    </div>
                </div>
                <div id="mileage_icon" class="icon__item" btn-type="mileage">
                    <div class="icon">
                        <img src="/images/mypage/mypage_point_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_mileage_charging"></p>
                    </div>
                </div>
                <div id="charging_icon" class="icon__item" btn-type="charging">
                    <div class="icon">
                        <img src="/images/mypage/mypage_charging_point_icon.png">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_prepaid_mileage"></p>
                    </div>
                </div>
                <div id="voucher_icon" class="icon__item" btn-type="voucher">
                    <div class="icon">
                        <img src="/images/mypage/mypage_voucher_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_voucher"></p>
                    </div>
                </div>
                <div class="icon__item" btn-type="bluemark">
                    <div class="icon">
                        <img src="/images/mypage/mypage_bluemark_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_blue_mark"></p>
                    </div>
                </div>
                <div class="icon__item" btn-type="stanby">
                    <div class="icon">
                        <img src="/images/mypage/mypage_stanby_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_standby"></p>
                    </div>
                </div>
                <div class="icon__item" btn-type="preorder">
                    <div class="icon">
                        <img src="/images/mypage/mypage_preorder_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_preorder"></p>
                    </div>
                </div>
                <div class="icon__item" btn-type="reorder">
                    <div class="icon">
                        <img src="/images/mypage/mypage_reorder_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_notify_me"></p>
                    </div>
                </div>
                <!-- <div class="icon__item" btn-type="draw">
                    <div class="icon">
                        <img src="/images/mypage/mypage_draw_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_draw">드로우</p>
                    </div>
                </div> -->
                <div class="icon__item" btn-type="membership">
                    <div class="icon">
                        <img src="/images/mypage/mypage_membership_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_membership"></p>
                    </div>
                </div>
                <div class="icon__item" btn-type="inquiry">
                    <div class="icon">
                        <img src="/images/mypage/mypage_inquiry_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_inquiry"></p>
                    </div>
                </div>
                <div class="icon__item" btn-type="as">
                    <div class="icon">
                        <img src="/images/mypage/mypage_as_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p>A/S</p>
                    </div>
                </div>
                <div class="icon__item" btn-type="service">
                    <div class="icon">
                        <img src="/images/mypage/mypage_service_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_customer_care"></p>
                    </div>
                </div>
                <div class="icon__item" btn-type="profile">
                    <div class="icon">
                        <img src="/images/mypage/mypage_profile_icon.svg">
                    </div>
                    <div class="icon__title">
                        <p data-i18n="m_account"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="swiper icon">
        <div class="swiper-wrapper">
            <div class="swiper-slide icon__item click__icon__item" btn-type="home">
                <div class="icon">
                    <img src="/images/mypage/mypage_home_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_my-page"></p>
                </div>
            </div>
            <div class="swiper-slide icon__item" btn-type="orderlist">
                <div class="icon">
                    <img src="/images/mypage/mypage_orderlist_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_order_history"></p>
                </div>
            </div>
            <div id="mileage_icon" class="swiper-slide icon__item" btn-type="mileage">
                <div class="icon">
                    <img src="/images/mypage/mypage_point_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_mileage_charging"></p>
                </div>
            </div>
            <!--
            <div id="charging_icon" class="swiper-slide icon__item" btn-type="charging">
                <div class="icon">
                    <img src="/images/mypage/mypage_charging_point_icon.png">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_prepaid_mileage">충전포인트</p>
                </div>
            </div>
-->
            <div id="voucher_icon" class="swiper-slide icon__item" btn-type="voucher">
                <div class="icon">
                    <img src="/images/mypage/mypage_voucher_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_voucher"></p>
                </div>
            </div>
            <div class="swiper-slide icon__item" btn-type="bluemark">
                <div class="icon">
                    <img src="/images/mypage/mypage_bluemark_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_blue_mark"></p>
                </div>
            </div>
            <div class="swiper-slide icon__item" btn-type="stanby">
                <div class="icon">
                    <img src="/images/mypage/mypage_stanby_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_standby"></p>
                </div>
            </div>
            <div class="swiper-slide icon__item" btn-type="preorder">
                <div class="icon">
                    <img src="/images/mypage/mypage_preorder_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_preorder"></p>
                </div>
            </div>
            <div class="swiper-slide icon__item" btn-type="reorder">
                <div class="icon">
                    <img src="/images/mypage/mypage_reorder_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_notify_me"></p>
                </div>
            </div>
            <!-- <div class="swiper-slide icon__item" btn-type="draw">
                <div class="icon">
                    <img src="/images/mypage/mypage_draw_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_draw">드로우</p>
                </div>
            </div> -->
            <div class="swiper-slide icon__item" btn-type="membership">
                <div class="icon">
                    <img src="/images/mypage/mypage_membership_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_membership"></p>
                </div>
            </div>
            <div class="swiper-slide icon__item" btn-type="inquiry">
                <div class="icon">
                    <img src="/images/mypage/mypage_inquiry_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_inquiry"></p>
                </div>
            </div>
            <div class="swiper-slide icon__item" btn-type="as">
                <div class="icon">
                    <img src="/images/mypage/mypage_as_icon.svg">
                </div>
                <div class="icon__title">
                    <p>A/S</p>
                </div>
            </div>
            <div class="swiper-slide icon__item" btn-type="service">
                <div class="icon">
                    <img src="/images/mypage/mypage_service_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_customer_care"></p>
                </div>
            </div>
            <div class="swiper-slide icon__item" btn-type="profile">
                <div class="icon">
                    <img src="/images/mypage/mypage_profile_icon.svg">
                </div>
                <div class="icon__title">
                    <p data-i18n="m_account"></p>
                </div>
            </div>
        </div>
    </div>
    <input id="btn_type" type="hidden" value="home">

    <div class="mypage__tab__container">
        <div id="mypage_tab_stanby" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-stanby.php"); ?>
        </div>
        <div id="mypage_tab_preorder" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-preorder.php"); ?>
        </div>
        <div id="mypage_tab_reorder" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-reorder.php"); ?>
        </div>
        <div id="mypage_tab_draw" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-draw.php"); ?>
        </div>
        <div id="mypage_tab_membership" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-membership.php"); ?>
        </div>
        <div id="mypage_tab_inquiry" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-inquiry.php"); ?>
        </div>
        <div id="mypage_tab_as" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-as.php"); ?>
        </div>
        <div id="mypage_tab_service" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-service.php"); ?>
        </div>
        <div id="mypage_tab_home" class="menu__tab">
            <?php include_once("mypage-main-home.php"); ?>
        </div>
        <div id="mypage_tab_orderlist" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-orderlist.php"); ?>
        </div>
        <div id="mypage_tab_mileage" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-mileage.php"); ?>
        </div>
        <div id="mypage_tab_charging" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-charging.php"); ?>
        </div>
        <div id="mypage_tab_voucher" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-voucher.php"); ?>
        </div>
        <div id="mypage_tab_profile" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-profile.php"); ?>
        </div>
        <div id="mypage_tab_bluemark" class="menu__tab non__display__tab">
            <?php include_once("mypage-main-bluemark.php"); ?>
        </div>
    </div>
    <div class="recommend-wrap"></div>
</main>
<script src="/scripts/mypage/main.js"></script>