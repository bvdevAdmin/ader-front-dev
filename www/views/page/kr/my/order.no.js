$(document).ready(function() {
	$.ajax({
		url : config.api + "order/get",
		headers : {
			country : config.language
		},
		data : {
			order_idx : location.pathname.split("/")[4]
		},
		success : function(d) {
			if (d.code == 200) {
				let order_info		= d.data.order_info
				let order_cancel	= d.data.order_cancel;
				let order_refund	= d.data.order_refund;
				let order_recent	= d.data.order_recent;

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
				
				let cnt_pg = 0;

				/* 주문 취소 금액 */
				if (order_cancel != null && Object.keys(order_cancel).length > 0) {
					cnt_pg++;
					$("#c_price-total").text(order_cancel.t_price_product);			// 제품 합계
					$("#c_price-member").text(order_cancel.t_price_member);			// 회원 할인 합계
					$("#c_use-voucher").html(order_cancel.t_price_discount);		// 바우처 사용
					$("#c_use-point").text(order_cancel.t_price_mileage);			// 적립금 사용
					$("#c_price-delivery").text(order_cancel.t_price_delivery);		// 추가 배송비
					$("#c_delivery-return").text(order_cancel.t_delivery_return);	// 반환 배송비
					$("#c_price-cancel").text(order_cancel.t_price_cancel);			// 결제 취소 금액
				} else {
					$('.fold-cancel').hide();
				}
				
				/* 주문 반품 금액 */
				if (order_refund != null && Object.keys(order_refund).length > 0) {
					cnt_pg++;
					$("#r_price-total").text(order_refund.t_price_product);			// 제품 합계
					$("#r_price-member").text(order_refund.t_price_member);			// 회원 할인 합계
					$("#r_use-voucher").html(order_refund.t_price_discount);		// 바우처 사용
					$("#r_use-point").text(order_refund.t_price_mileage);			// 적립금 사용
					$("#r_price-delivery").text(order_refund.t_price_delivery);		// 추가 배송비
					$("#r_delivery-return").text(order_refund.t_delivery_return);	// 반환 배송비
					$("#r_price-cancel").text(order_refund.t_price_cancel);			// 결제 취소 금액
				} else {
					$('.fold-refund').hide();
				}

				/* 결제 현황 */
				if (cnt_pg > 0 && order_recent != null && Object.keys(order_recent).length > 0) {
					$("#t_price-total").text(order_recent.price_product);			// 제품 합계
					$("#t_price-member").text(order_recent.price_member);			// 회원 할인 합계
					$("#t_use-voucher").html(order_recent.price_discount);			// 바우처 사용
					$("#t_use-point").text(order_recent.price_mileage);				// 적립금 사용
					$("#t_price-delivery").text(order_recent.price_delivery);		// 추가 배송비
					$("#t_delivery-return").text(order_recent.delivery_return);		// 반환 배송비
					$("#t_price-cancel").text(order_recent.price_cancel);			// 결제 취소 금액
					$("#t_remain_price").text(order_recent.price_remain);			// 잔여 결제금액
				} else {
					$('.fold-recent').hide();
				}

				$('#to_place').text(order_info.to_place);
				$('#to_name').text(order_info.to_name);
				$('#to_mobile').text(order_info.to_mobile);
				$('#to_zipcode').text(order_info.to_zipcode);
				$('#to_addr').text(order_info.to_addr);
				$('#to_detail_addr').text(order_info.to_detail_addr);

				if (order_info.cnt_remain > 0) {
					//배송 정보
					$("#delivery-status").text(order_info.delivery_status);			// 배송 상태
					$("#delivery-company").text(order_info.company_name);			// 배송회사
					$("#delivery-num").text(order_info.delivery_num);				// 운송장번호
					if (order_info.url_delivery != null) {
						$('#delivery-num').click(function() {
							javascript:void(window.open(`${order_info.url_delivery}`,'','width=800, height=1600'));
						});
					}
					$("#delivery-start-date").text(order_info.delivery_start_date);	// 배송 시작일
					$("#delivery-date").text(order_info.delivery_date);				// 배송 예정일
					$("#delivery-end-date").text(order_info.delivery_end_date);		// 배송 종료일
				} else {
					$('.fold-delivery').hide();
				}
				
				// 주문 제품
				setOrder_product('list-product',d.data.order_product);

				setOrder_product('list-cancel',d.data.cancel_product);

				setOrder_product('list-exchange',d.data.exchange_product);

				setOrder_product('list-refund',d.data.refund_product);
				
				let btn_update = document.querySelector('.btn_update');
				if (d.data.order_info.update_flg == true) {
					if (btn_update != null) {
						btn_update.addEventListener('click',function() {
							location.href = `${config.base_url}/my/order-update?order_code=${d.data.order_info.order_code}`;
						});
					}
				} else {
					$(btn_update).remove();
				}
				
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
					d.msg,
					function() {
						if (d.code == 401) {
							sessionStorage.setItem('r_url',location.href);
							location.href = `${config.base_url}/login`;
						}
					}
				);
			}
		}
	});
});

function setOrder_product(div_name,data) {
	let div_list = $(`#${div_name}`);
	
	if (data != null && data.length > 0) {
		data.forEach(row => {
			let option_name = "";
			if (row.prev_option_name != null && row.option_name != null) {
				option_name = `${row.prev_option_name} > ${row.option_name}`;
			} else {
				option_name = row.option_name;
			}

			const $parentLi = $(`
				<li>
					<span class="image" style="background-image:url('${config.cdn}${row.img_location}')" data-no="${row.product_idx}"></span>
					<div class="name">${row.product_name}</div>
					<div class="price">
						<span class="price">${row.product_price}</span>
						<br>
						<span class="qty">Qty: ${row.product_qty}</span>
					</div>
					<div class="color">${row.color}
						<span class="colorchip" style="background-color:${row.color_rgb}"></span>
					</div>
					<div class="size">${option_name}</div>
					<div class="status">${row.t_order_status}</div>
				</li>
			`);
	
			let children = []
			if (row.product_type == 'S') {
				row.product_set.forEach(row2 => {
					const $childLi = $(`
						<li class="${row.order_code}" style="display: none;">
							<span class="image" style="background-image:url('${config.cdn + row2.img_location}')" data-no="${row2.product_idx}"></span>
							<div class="name">${row2.product_name}</div>
							<div class="color">${row2.color}
								<span class="colorchip" style="background-color:${row2.color_rgb}"></span>
							</div>
							<div class="size">${row2.option_name}</div>
						</li>
					`);
					children.push($childLi)
				});
	
				$parentLi.css('cursor','pointer')
				$parentLi.on('click', function () {
					$(`.${row.order_code}`).stop().slideToggle(300); // 300ms 동안 슬라이드 효과
				});
			}
	
			// 최종적으로 리스트에 추가
			div_list.append($parentLi);
			children.forEach(children => {
				div_list.append(children)
			});
		});
	} else {
		div_list.parent().parent().hide();
	}

	let div_product = document.querySelectorAll(`#${div_name} li .image`);
	if (div_product != null && div_product.length > 0) {
		div_product.forEach(div => {
			div.addEventListener('click',function(e) {
				let el = e.currentTarget

				if (el.dataset.no != null) {
					location.href = `${config.base_url}/shop/${el.dataset.no}`;
				}
			});
		})
	}
}