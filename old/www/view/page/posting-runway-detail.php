<link rel="stylesheet" href="/css/story/runway.css">
<link rel=stylesheet href='/css/module/styling.css' type='text/css'>

<?php
	function getUrlParamter($url, $sch_tag) {
		$parts = parse_url($url);
		parse_str($parts['query'], $query);
		return $query[$sch_tag];
	}
	
	$page_url = $_SERVER['REQUEST_URI'];
	
	$page_idx = getUrlParamter($page_url, 'page_idx');
	$size_type = getUrlParamter($page_url, 'size_type');
?>

<main>
	<input type="hidden" id="page_idx" value="<?=$page_idx?>">
	<input type="hidden" id="size_type" value="<?=$size_type?>">
	
	<section class="runway-detail-wrap">
		<div class="back-btn web" onclick="location.href='/posting/runway'"><img src="/images/svg/arrow-back.svg" alt=""></div>
		<article class="controller-swiper-wrap">
			<div class="runway-controller-swiper swiper">
				<div class="swiper-wrapper"></div>
			</div>
			<div class="swiper-pagination"></div>
		</article>
		
		<article class="preview-swiper-wrap">
			<div class="lock-wrap">
				<div class="runway-preview-swiper swiper">
					<div class="swiper-wrapper"></div>
				</div>
			</div>
			<div class="swiper-pagination"></div>
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
		</article>
		
		<article class="runway-content-wrap">
			<h5 class="runway-contet-title">2022 F/W 'Phenomenon Communication'</h5>
			<p class="runway-contet-subtitle">
				The TENIT line draws a new way of<br>
				garment experiences which consisted of<br>
				handbag, outerwear, knitwear and deformed silhouettes<br>
				based on the brand signature tetris pattern
			</p>
		</article>
	</section>
	<section class="styling-with-wrap"></section>
</main>
<script src="/scripts/story/runway/runway-common.js"></script>
<script src="/scripts/story/runway/runway-detail.js"></script>