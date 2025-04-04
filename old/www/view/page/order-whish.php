<?php
$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
    $member_idx = $_SESSION['MEMBER_IDX'];
}

if ($member_idx == 0) {
    echo "
			<script>
				location.href='/login?r_url=/order/whish';
			</script>
		";
}
?>

<link rel="stylesheet" href="/css/module/order-wish.css">
<link rel="stylesheet" href="/css/module/foryou.css">

<main data-basketStr="<?= $basket_idx ?>" data-country="<?= $country ?>">
    <div class="banner-wrap">
        <div class="banner-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="12.499" viewBox="0 0 15 12.499">
                <path data-name="패스 6645"
                    d="M72.632 66.861a4.25 4.25 0 0 0-4.154-4.34 4.111 4.111 0 0 0-3.338 1.717 4.113 4.113 0 0 0-3.327-1.738 4.249 4.249 0 0 0-4.181 4.313 4.389 4.389 0 0 0 1.446 3.287l4.856 4.9 1.81-1.61.8.856 4.7-4.168a4.386 4.386 0 0 0 1.388-3.217z"
                    transform="translate(-57.632 -62.5)" style="fill:var(--bk)" />
            </svg>
            <span class="banner-title" data-i18n="w_wishlist"></span>
        </div>
    </div>
	
    <section class="wishlist-section">
        <input id="wish-product-cnt" type="hidden" />
        <div class="temp-div"></div>
        
		<div class="content left">
            <div class="body-wrap list"></div>
        </div>
        
		<div class="content right">
            <div class="add-list-wrap">
                <div class="header-wrap">
                    <div class="header-box">
                        <span class="hd-title" data-i18n="w_uncheckall"></span>
                    </div>
                </div>
				
                <div class="quick-menu-wrap">
                    <div class="swiper mySwiper quick-swiper">
                        <div class="swiper-wrapper"></div>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="btn-basket-wrap">
						<div class="basket-link-btn btn_basket_add">
							<span data-i18n="w_basket_msg_03"></span>
						</div>
					</div>
                </div>
				
				<div class = "add-basket-wrap">
					<div class="body-wrap"></div>
					
					<div class="btn-basket-wrap">
						<div class="basket-link-btn btn_basket_add">
							<span data-i18n="w_basket_msg_03"></span>
						</div>
					</div>
				</div>
            </div>
        </div>
    </section>
    <section class="recommend-wrap"></section>
</main>
<script src="/scripts/module/wish.js"></script>