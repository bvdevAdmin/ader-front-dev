$(document).ready(function() {
	$("#price-total").text(number_format(10000)); // 제품 합계
	$("#use-voucher").html(number_format(10000) + `<small>(8월 생일 바우처 / 10%)</small>`); // 바우처 사용
	$("#use-point").text(number_format(10000)); // 적립금 사용
	$("#price-delivery").text(number_format(10000)); // 배송비
	$("#price-pay-total").text(number_format(10000)); // 최종 결제 금액
	$("#pay-method").text(number_format(10000)); // 결제 수단
	$("#pay-date").text(number_format(10000)); // 결제 일시

	return;

	// 결제 정보 표시
	$.ajax({
		url : config.api + "order/get",
		data : { order_idx : order_no },
		success : function(d) {
			if(d.code == 200) {
				$("#order-number").text(number_format(10000)); // 주문 번호
				$("#order-date").text(number_format(10000)); // 주문 날짜 
				$("#price-total").text(number_format(10000)); // 제품 합계
				$("#use-voucher").html(number_format(10000) + `<small>(8월 생일 바우처 / 10%)</small>`); // 바우처 사용
				$("#use-point").text(number_format(10000)); // 적립금 사용
				$("#price-delivery").text(number_format(10000)); // 배송비
				$("#price-pay-total").text(number_format(10000)); // 최종 결제 금액
				$("#pay-method").text(number_format(10000)); // 결제 수단
				$("#pay-date").text(number_format(10000)); // 결제 일시

				// 주문 제품
				d.data.product_info.forEach(row => {
					$("#list").append(`
						<li>
							<span class="image" style="background-image:url('${config.cdn + row.img_location}')"></span>
							<div class="name">${row.product_name}</div>
							<div class="price">
								<span class="price">${number_format(row.product_price)}</span>
								<span class="qty">${row.product_qty}</span>
							</div>
							<div class="color">${row.color}<span class="colorchip ${row.color.toLowerCase()}"></span></div>
						</li>
					`);
				});

				// 영수증 보기
				$("#btn-view-receipt").click(function() {
				});
			}
			else {
				alert(d.msg);
			}
		}
	});
});