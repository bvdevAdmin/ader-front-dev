<link rel=stylesheet href='/css/product/detail_tmp.css' type='text/css'>
<link rel=stylesheet href='/css/module/styling.css' type='text/css'>
<link rel=stylesheet href='/css/module/foryou.css' type='text/css'>
<style>
	main {
		overflow-x: initial;
	}

	/* .quickview__box {display: none;} */
</style>
<?php
function getUrlParamter($url, $sch_tag)
{
	$parts = parse_url($url);
	parse_str($parts['query'], $query);
	return $query[$sch_tag];
}

$page_url = $_SERVER['REQUEST_URI'];
$product_idx = getUrlParamter($page_url, 'product_idx');
?>
<main data-productidx="<?= $product_idx ?>" data-country="KR">
	<section class="detail__wrapper">
		<div class="detail__box">
			<div class="navigation__wrap"></div>
			<div class="detail__img__wrap">
				<div id="main__swiper-detail" class="main__swiper swiper">
					<div class="swiper-wrapper main_img_wrapper"></div>
					<div class="swiper-pagination swiper-pagination-detail-fraction"></div>
					<div class="swiper-pagination swiper-pagination-detail-bullets"></div>
				</div>
			</div>
		</div>
		<div class="info__wrap product"></div>

	</section>
	<section class="rM-detail-containner">
		<div class="detail__btn__wrap">
			<div class="detail__btn__box">
				<div class="detail__btn__row mobile">
					<div class="img-box">
						<img src="/images/svg/sizeguide.svg" alt="">
					</div>
					<div class="btn-title" data-i18n="pd_size_guide">사이즈가이드</div>
				</div>
				<div class="detail__btn__row mobile">
					<div class="img-box">
						<img src="/images/svg/material.svg" alt="">
					</div>
					<div class="btn-title" data-i18n="pd_material">소재</div>
				</div>
				<div class="detail__btn__row mobile">
					<div class="img-box">
						<img src="/images/svg/information.svg" alt="">
					</div>
					<div class="btn-title" data-i18n="pd_details">상세정보</div>
				</div>
				<div class="detail__btn__row mobile">
					<div class="img-box">
						<img src="/images/svg/precaution.svg" alt="">
					</div>
					<div class="btn-title" data-i18n="pd_care">취급 유의사항</div>
				</div>
			</div>
			<div class="detail__refund__msg"></div>
			<div class="detail__content__box">
				<div class="detail-content">
					<div class="content-header"><span data-i18n=""></span></div>
					<div class="content-body"></div>
				</div>
				<div class="detail__btn__control">
					<div class="detail-btn-prev"><img src="/images/svg/arrow-left.svg" alt=""></div>
					<div class="detail-btn-next"><img src="/images/svg/arrow-left.svg" alt=""></div>
				</div>
			</div>
		</div>
	</section>
	<section class="styling-with-wrap"></section>
	<section class="recommend-wrap"></section>
</main>
<script src="/scripts/product/detail_tmp.js"></script>
	