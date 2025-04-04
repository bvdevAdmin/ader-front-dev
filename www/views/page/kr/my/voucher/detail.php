<main class="my">
    <?php include $_CONFIG['PATH']['PAGE'].'kr/my/_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/voucher">바우처</a></li>
		</ul>
	</nav>
	<section class="voucher wrap-720">
		<article class="detail">
			<h2>바우처 내역</h2>
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>사용 가능</li>
						<li>사용 완료</li>
					</ul>
				</div>
				<section>
					<ul class="voucher-list" id="list-1"></ul>
				</section>
				<section>
					<ul class="voucher-list" id="list-2"></ul>
				</section>
			</div>
		</article>
	</section>
</main>