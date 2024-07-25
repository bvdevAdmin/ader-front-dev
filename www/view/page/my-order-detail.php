<main class="my order">
	<?php include 'inc/my.summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/order">주문내역</a></li>
			<li><a>자세히보기</a></li>
		</ul>
	</nav>
	<section class="order">
		<article class="detail">
			<dl class="detail-info">
				<dt>
					주문 번호 <span id="order-number"></span><br>
					신청 날짜 <span id="order-date"></span>
				</dt>
				<dd class="goods">
					<div class="image"></div>
					<div class="status">
						<div class="now">결제 완료</div>
						제품 준비중 상태가 되면 취소가 불가합니다
					</div>
					<big>Montblanc beanie</big>
					<div class="price-qty">
						<div class="price">159,000</div><div class="qty">1</div>
					</div>
					<div class="color">Noir<span class="colorchip" style="background-color:#000"></span></div>
					<div class="size">A1</div>
				</dd>
				<dd class="info">
					<h2>결제 정보</h2>
					<dl>
						<dt>제품 합계</dt><dd>159,000</dd>
						<dt>배송비</dt><dd>0</dd>
						<dt>바우처</dt><dd>-15,900<small>(7월 생일 바우처 / 10%)</small></dd>
						<dt>적립금</dt><dd>-10,000</dd>
						<dt class="total">총 합계</dt><dd>133,100</dd>
						<dt>결제 수단</dt><dd>1234 **** **** 9999</dd>
						<dt>결제 일시</dt><dd>2023.06.01 13:34:55</dd>
					</dl>
					<div class="receipt">
						<button type="button">영수증 보기</button>
					</div>
				</dd>
				<dd class="info">
					<h2>배송 정보</h2>
					<div class="modify">
						<button type="button">변경</button>
						<div>배송지 변경은 결제 완료 상태에서만 가능합니다.</div>
					</div>
					<address>
					</address>
				</dd>
			</dl>
			
			<h2 class="border">주문 취소 안내</h2>
			<ul class="dot">
				<li>결제 완료 단계에서만 취소가 가능합니다.</li>
				<li>주문 취소는 [마이페이지] - [주문내역] - [자세히 보기] 메뉴에서 직접 취소가 가능합니다.</li>
			</ul>
			<div class="buttons">
				<button type="button" id="btn-order-cancel">주문 취소하기</button>
			</div>
			
			<h2 class="border">교환 및 반품 안내</h2>
			<ul class="dot">
				<li>교환 및 반품 접수는 제품 수령일로부터 7일 이내 신청 가능합니다.</li>
				<li>주문 상태가 '배송 완료'일 경우에 [마이페이지] - [주문내역] - [자세히 보기] 메뉴에서 교환 및 반품 신청이 가능합니다.</li>
				<li>주문 상태가 '배송 중'으로 보일 경우, 1:1 문의를 통해 접수 부탁드립니다.</li>
			</ul>
			
			<h2 class="border">바우처 안내</h2>
			<ul class="dot">
				<li>바우처 사용 이후, 배송 준비 중 상태로 변경된 이후 복원이 불가합니다.</li>
				<li>바우처 사용 이후, 결제 완료 상태에서 주문 전체 취소 이후 복원이 가능합니다.</li>
			</ul>
		</article>
	</section>
</main>