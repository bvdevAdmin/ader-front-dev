<main class="my">
	<?php include $_CONFIG['PATH']['PAGE'].'kr/my/_summary.php'; ?>

	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/mileage">적립금</a></li>
			<li><a href="/kr/my/mileage-log">적립금 내역</a></li>
		</ul>
	</nav>
	<section class="mileage-log on">
		<article class="history">
			<h2>
				적립금 내역
			</h2>
			<section>
				<ul class="list" id="list-log"></ul>
				<div class="paging" id="list-log-paging"></div>
			</section>
		</article>
	</section>
</main>