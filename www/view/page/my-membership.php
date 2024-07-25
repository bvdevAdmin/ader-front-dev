<main class="my">
	<?php include 'inc/my.summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/membership">멤버십</a></li>
		</ul>
	</nav>
	<section class="customer membership">
		<article>
			<h1>멤버십</h1>
			<div class="--describe">
				<p>
					<big data-field="member_name"></big> <small>님은</small><br>
					<big data-field="member_membership"></big> <small>등급입니다.</small>
				</p>
				<p>
					구매 금액 : <span data-field="member_buytotal">0</span> 원<br>
					다음 등급까지 구매금액 <u data-field="member_nextlevel_to_buy">0원</u> 남았습니다.
				</p>
				<p>
					등급 적용일 : <span data-field="member_level_accept_date">2023.10.01</span><br>
					산정 기간 : <span data-field="member_level_accept_date_from">2022.09.01</span> - <span data-field="member_level_accept_date_to">2023.09.30</span>
				</p>
			</div>
			<h2>등급 혜택 안내</h2>
			<ul class="benefits">
				<li>
					<div class="level">
						<big>BLUE</big>
						최근 1년간 1회 이상 구매
					</div>
					<ul class="dot">
						<li>구매금액 1% 적립</li>
						<li>10% off 생일 바우처 제공</li>
					</ul>
				</li>
				<li>
					<div class="level">
						<big>BRONZE</big>
						최근 1년간 50만원 이상 구매
					</div>
					<ul class="dot">
						<li>구매금액 2% 적립</li>
						<li>10% off 생일 바우처 제공</li>
						<li>연 1회 무료반품</li>
					</ul>
				</li>
				<li>
					<div class="level">
						<big>SILVER</big>
						최근 1년간 300만원 이상 구매
					</div>
					<ul class="dot">
						<li>구매금액 3% 적립</li>
						<li>15% off 생일 바우처 제공</li>
						<li>연 1회 무료반품</li>
					</ul>
				</li>
				<li>
					<div class="level">
						<big>GOLD</big>
						최근 1년간 500만원 이상 구매
					</div>
					<ul class="dot">
						<li>구매금액 4% 적립</li>
						<li>15% off 생일 바우처 제공</li>
						<li>연 2회 무료반품</li>
					</ul>
				</li>
				<li>
					<div class="level">
						<big>BLACK</big>
						최근 1년간 1,000만원 이상 구매
					</div>
					<ul class="dot">
						<li>구매금액 7% 적립</li>
						<li>20% off 생일 바우처 제공</li>
						<li>연 1회 무료반품</li>
					</ul>
				</li>
			</ul>
			<ul class="dot">
				<li>누적 구매금액은 주문 건의 실제 결제 금액 기준입니다.</li>
				<li>누적 구매금액은 배송 완료로 변경된 후 7일 후부터 반영됩니다.</li>
				<li>적립금은 배송 완료일로부터 7일 후 사용 가능하도록 적립됩니다.</li>
				<li>부정한 목적과 방법으로 본 서비스를 이용하거나, 다른 고객의 쇼핑 경험에 부정적인 영향을 줄 경우 서비스 이용 제한 등이 발생할 수 있습니다.</li>
			</ul>

		</article>
	</section>
</main>