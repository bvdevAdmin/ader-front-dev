<main class="my order">
    <?php include '_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/order">주문내역</a></li>
		</ul>
	</nav>
	<section class="order">
		<h1>주문내역</h1>
		<article>
			<div class="date-search">
				<form id="frm-order-search">
					<ul class="term">
						<li><button type="button">1주일</button></li>
						<li><button type="button">1개월</button></li>
						<li><button type="button">3개월</button></li>
						<li><button type="button">최근 1년</button></li>
					</ul>
					<div class="inp">
						<div class="item">
							<input type="date" name="sdate">
							<div class="select">날짜 선택</div>
							<div class="calendar"></div>
						</div>
						<span class="t">-</span>
						<div class="item">
							<input type="date" name="edate">
							<div class="select">날짜 선택</div>
							<div class="calendar"></div>
						</div>
					</div>
					<button type="submit" class="btn">조회</button>
				</form>
			</div>
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>진행중</li>
						<li>완료</li>
					</ul>
				</div>
				<section>
					<ul class="list" id="list"></ul>
				</section>
				<section>
				</section>
			</div>
		</article>
	</section>
</main>