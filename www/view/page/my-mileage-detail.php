<main class="my">
	<?php include 'inc/my.summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/mileage">적립금</a></li>
			<li><a href="/my/mileage/detail">자세히 보기</a></li>
		</ul>
	</nav>
	<section class="mileage wrap-720">
		<article>
			<h1>적립금 내역</h1>
			<div class="date-search">
				<form id="frm">
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
					<ul id="tab">
						<li>적립</li>
						<li>사용</li>
					</ul>
				</div>
				<section>
					<ul class="list" id="list-1">
						<li class="empty">적립 내역이 없습니다.</li>
					</ul>
				</section>
				<section>
					<ul class="list" id="list-2">
						<li class="empty">사용 내역이 없습니다.</li>
					</ul>
				</section>
			</div>
		</article>
	</section>
</main>