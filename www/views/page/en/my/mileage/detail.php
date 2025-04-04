<main class="my">
	<?php include $_CONFIG['PATH']['PAGE'].'en/my/_summary.php'; ?>

	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/mileage">Mileage</a></li>
			<li><a href="/en/my/mileage/detail">Mileage history</a></li>
		</ul>
	</nav>
	<section class="mileage-log on">
		<article class="history">
			<h2>
				Mileage history
			</h2>
			<section>
				<ul class="list" id="list-log"></ul>
				<div class="paging" id="list-log-paging"></div>
			</section>
		</article>
	</section>
</main>