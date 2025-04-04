<main class="my">
	<?php include $_CONFIG['PATH']['PAGE'].'kr/my/_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/as">A/S</a></li>
			<li><a href="/kr/my/as/status">내역</a></li>
		</ul>
	</nav>
	<section class="as status">
		<h2 class="no-border">A/S 내역</h2>
		<article class="list">
			<ul class="list" id="list"></ul>
            <div class="paging"></div>
		</article>
	</section>
</main>