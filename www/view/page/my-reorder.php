<main class="my as">
	<?php include 'inc/my.summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/reorder">재입고알림</a></li>
		</ul>
	</nav>
	<section class="reorder wrap-720">
		<h2>재입고알림</h2>
		<article>
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>신청 내역</li>
						<li>알림 완료</li>
					</ul>
				</div>
				<section>
					<ul class="list" id="list-1"></ul>
				</section>
				<section>
					<ul class="list" id="list-2"></ul>
				</section>
			</div>
			<ul class="dot">
				<li>해당 제품이 입고되면 메시지를 발송해 드립니다.</li>
				<li>스팸 메세제로 등록 시 메지 수신이 제한될 수 있습니다.</li>
				<li>재입고 알림은 SMS 수신 동의 여부와 관계없이 발송됩니다.</li>
				<li>알림 완료 내역은 3개월 경과 후 자동 삭제 됩니다. </li>
			</ul>
		</article>
	</section>
</main>