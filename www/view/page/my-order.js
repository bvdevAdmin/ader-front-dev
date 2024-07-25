$(document).ready(function() {
	return;
		$("#list").append(`
			<li>
				<div class="info">
					<dl>
						<dt>주문 번호</dt><dd>${row.store}</dd>
						<dt>주문 날짜</dt><dd>${row.store}</dd>
					</dl>
					<a href="/my/order/detail" class="not-mobile btn">자세히보기</a>
				</div>
				<div class="goods">
					<div class="image" style="background-image:url('${row.image}')">+1</div>
					<dl>
						<dt>제품정보</dt><dd>${row.store}</dd>
						<dt>결제 완료</dt><dd>${row.store}</dd>
						<dt>전체 금액</dt><dd>${row.store}</dd>
					</dl>
				</div>
				<a href="/my/order/detail" class="mobile btn">자세히보기</a>
			</li>
		`);
});