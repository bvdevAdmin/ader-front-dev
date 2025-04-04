<?php
if (!isset($_SESSION['MEMBER_IDX'])) {
	echo "
		<script>
			location.href = config.base_url;
		</script>
	";
}
?>

<main class="my">
    <?php include 'my/_summary.php'; ?>
	<nav>
		<ul>
			<li>Mypage</li>
			<li>HOME</li>
		</ul>
	</nav>
	<section class="my-main summary-recently">
		<header>
			<h2>Recently viewed<a href="/en/recently">more</a></h2>
		</header>
		<article>
			<div class="swiper-container" id="swiper-recently">
				<div class="swiper-wrapper goods">
                </div>
				<button type="button" class="swiper-button-prev"></button>
				<button type="button" class="swiper-button-next"></button>
			</div>
		</article>
	</section>
	<section class="my-main summary-wishlist">
		<header>
			<h2>Wishlist<a href="/en/my/wishlist">more</a></h2>
		</header>
		<article>
			<div class="swiper-container" id="swiper-wishlist">
				<div class="swiper-wrapper goods">
                </div>
				<button type="button" class="swiper-button-prev"></button>
				<button type="button" class="swiper-button-next"></button>
			</div>
		</article>
	</section>
</main>