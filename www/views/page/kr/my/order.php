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
						<li><button type="button" id="one-week">1주일</button></li>
						<li><button type="button" id="one-month">1개월</button></li>
						<li><button type="button" id="three-month">3개월</button></li>
						<li><button type="button" id="one-year">최근 1년</button></li>
					</ul>
					<div class="inp">
						<div class="item-date">
							<input type="date" id="date_from" class="input_date" name="date_from" placeholder="YYYY-MM-DD">
						</div>
						<span class="t">-</span>
						<div class="item-date">
							<input type="date" id="date_to" class="input_date" name="date_to" placeholder="YYYY-MM-DD">
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
                    <div class="paging" id="list-paging"></div>
				</section>
				<section>
                    <ul class="list" id="list2"></ul>
                    <div class="paging" id="list2-paging"></div>
				</section>
			</div>
		</article>
	</section>
</main>