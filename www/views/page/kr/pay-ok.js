$(document).ready(function() {
	let order_idx = $("#order-number").data("no")

	$.ajax({
		url : config.api + "order/get",
		headers : {
			country : config.language
		},
		data : {
			order_idx
		},
		success : function(d) {
			if(d.code == 200) {
				let order_info = d.data.order_info
				//결제 정보
				$("#order-number").text(order_info.order_code);					// 주문 번호
				$("#order-date").text(order_info.create_date);					// 주문 날짜
				
				$("#price-total").text(order_info.t_price_product);				// 제품 합계
				$("#price-member").text(order_info.t_price_member);				// 회원 할인 합계
				$("#use-voucher").html(order_info.t_price_discount);			// 바우처 사용
				$("#use-point").text(order_info.t_price_mileage);				// 적립금 사용
				$("#price-delivery").text(order_info.t_price_delivery);			// 배송비
				$("#price-pay-total").text(order_info.t_price_total);			// 최종 결제 금액

				$("#pay-method").text(order_info.pg_payment);					// 결제 수단
				$("#pay-date").text(order_info.pg_date);						// 결제 일시
				
				// 주문 제품
				d.data.order_product.forEach(row => {
					const $parentLi = $(`
						<li>
							<span class="image" style="background-image:url('${config.cdn + row.img_location}')"></span>
							<div class="name">${row.product_name}</div>
							<div class="price">
								<span class="price">${row.product_price}</span>
								<br>
								<span class="qty">Qty: ${row.product_qty}</span>
							</div>
							<div class="color">
								${row.color}
								<span class="colorchip" style="background-color:${row.color_rgb}"></span>
							</div>
							<div class="size">${row.option_name}</div>
						</li>
					`);

					let children = []
					if (row.product_type == 'S') {
						row.product_set.forEach(row2 => {
							const $childLi = $(`
								<li class="${row.order_code}" style="display: none;">
									<span class="image" style="background-image:url('${config.cdn + row2.img_location}')"></span>
									<div class="name">${row2.product_name}</div>									
									<div class="color">
										${row2.color}
										<span class="colorchip" style="background-color:${row2.color_rgb}"></span>
									</div>
									<div class="size">${row2.option_name}</div>
								</li>
							`);
							children.push($childLi)
						});

						$parentLi.css('cursor', 'pointer')
						$parentLi.on('click', function () {
							$(`.${row.order_code}`).stop().slideToggle(300); // 300ms 동안 슬라이드 효과
						});
					}

					// 최종적으로 리스트에 추가
					$("#list").append($parentLi);
					children.forEach(children => { $("#list").append(children) })
				});

				// 영수증 보기
				$("#btn-view-receipt").click(function() {
					console.log(d.data.order_info.pg_currency);
					console.log(d.data.order_info.pg_receipt_url);
					if (d.data.order_info.pg_currency != "MLG") {
						let pg_url = d.data.order_info.pg_receipt_url;
						if (pg_url) {
							javascript:void(window.open(`${pg_url}`,'','width=800, height=1600'));
						}
					}
				});
			}
			else {
				alert(
					'주문 내역이 존재하지 않습니다',
					function() {
						location.href = `${config.base_url}`;
					}
				);
			}
		}
	});
});