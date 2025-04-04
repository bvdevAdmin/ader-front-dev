<link rel="stylesheet" href="/css/product/best.css" type="text/css">

<?php
function getUrlParamter($url, $sch_tag)
{
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    return $query[$sch_tag];
}

$page_url = $_SERVER['REQUEST_URI'];
$menu_type = getUrlParamter($page_url, 'menu_type');
$menu_idx = getUrlParamter($page_url, 'menu_idx');
$page_idx = getUrlParamter($page_url, 'page_idx');
?>
<main>
	<section class="product_best_wrap" data-menu_idx="<?=$menu_idx?>" data-menu_type="<?=$menu_type?>" data-page_idx="<?=$page_idx?>">
		<div class="product_best_category">
			<div class="category_item">
				<div class="bestCategory-swiper swiper">
					<div class="swiper-wrapper best_menu_wrapper"></div>
					<div class="swiper-pagination"></div>
				</div>
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
			</div>
		</div>
		<div class="product_best_body">
			<!-- <div class="best_list_box"></div> -->
		</div>
	</section>
</main>

<script src="/scripts/product/best.js"></script>