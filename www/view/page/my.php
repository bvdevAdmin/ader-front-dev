<main class="my">
	<?php include 'inc/my.summary.php'; ?>
	<nav>
		<ul>
			<li>마이페이지</li>
			<li>HOME</li>
		</ul>
	</nav>
	<section class="my-main summary-recently">
		<header>
			<h2>최근 본 제품<a href="/recently">전체보기</a></h2>
		</header>
		<article>
			<div class="swiper-container" id="swiper-recently">
				<div class="swiper-wrapper goods"></div>
				<button type="button" class="swiper-button-prev"></button>
				<button type="button" class="swiper-button-next"></button>
			</div>
		</article>
	</section>
	<section class="my-main summary-wishlist">
		<header>
			<h2>위시리스트<a href="/my/wishlist">전체보기</a></h2>
		</header>
		<article>
			<div class="swiper-container" id="swiper-wishlist">
				<div class="swiper-wrapper goods"></div>
				<button type="button" class="swiper-button-prev"></button>
				<button type="button" class="swiper-button-next"></button>
			</div>
		</article>
	</section>
</main>