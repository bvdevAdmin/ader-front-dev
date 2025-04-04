<main class="my">
    <?php include $_CONFIG['PATH']['PAGE'].'en/my/_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/voucher">Voucher</a></li>
		</ul>
	</nav>
	<section class="voucher wrap-720">
		<article class="detail">
			<h2>Voucher history</h2>
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>Available</li>
						<li>Used</li>
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