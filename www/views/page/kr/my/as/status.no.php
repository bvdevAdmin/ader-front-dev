<main class="my">
    <?php include '../_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/as">A/S</a></li>
			<li><a href="/kr/my/as/status">내역</a></li>
			<li><a>현황</a></li>
		</ul>
	</nav>
	<section class="as status">
		<h2 class="no-border">A/S 현황</h2>
		<article class="detail">
			<div class="info">
				<div class="as-detail">
					<dl><dt>A/S번호</dt><dd>KR-20230401-168747609</dd></dl>
					<dl><dt>신청 날짜</dt><dd>2023.04.01</dd></dl>
				</div>
				<div class="goods">
					<div class="image"></div>
					<div class="goods">
						<div class="title">${row.title}</div>
						<div class="price">${number_format(row.price)}</div>
						<div class="color">${row.color}</div>
						<div class="size">${row.size}</div>
					</div>
					<div class="buy">
						<dl><dt>구매처</dt><dd>${row.store}</dd></dl>
						<dl><dt>Bluemark 시리얼코드</dt><dd>${row.bluemark}</dd></dl>
						<dl><dt>Bluemark 인증 날짜</dt><dd>${row.reg_date}</dd></dl>
					</div>
					<div class="status">검토 대기중</div>
				</div>
			</div>
			<ul class="progress">
				<li>01 신청</li>
				<li>02 회수</li>
				<li>03 수선</li>
				<li>04 결제</li>
				<li>05 배송</li>
				<li>06 완료</li>
			</ul>
			<div class="request">
				<h4>요청내용</h4>
				<div class="contents"></div>
				<h4>첨부파일</h4>
				<ul class="attached-images"></ul>
			</div>
		</article>
	</section>
</main>