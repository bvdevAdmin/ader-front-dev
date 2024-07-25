$(document).ready(function() {
	if(sessionStorage.getItem("cart_no") == null) {
		alert("결제할 상품이 없습니다.");
		history.back();
		return;
	}
		
	$.ajax({
		url: config.api + 'order/pre',
		data: { basket_idx : JSON.parse(sessionStorage.getItem("cart_no")) },
		success: function(d) {
			if(d.code == 200) {	
				if(d.data.product_info.length == 0) {
					alert("결제할 상품이 없습니다.",() => { history.back() });
					return;
				}
				
				// 배송 정보
				d.data.order_memo_info.forEach(row => {
					$("#frm select[name='develiry_msg']").append(`
						<option value="${row.memo_idx}">${row.memo_txt}</option>
					`);
					$("#frm select[name='develiry_msg'] > option").last().prop("selected",row.placeholder_flg);
					
					// 직접 입력
					if(row.direct_flg == 1) {
						$("#frm select[name='develiry_msg'] > option").last().click(function() {
						});
					}
				});
				$("#btn-delivery-change").click(function() {
					modal('delivery');
				});
				
				// 배송 메시지
				d.data.order_memo_info.forEach(row => {
					$("#frm select[name='order_memo']").append(`
						<option value="${row.memo_idx}" data-direct="${(row.direct_flg == 1) ? 'true' : 'false' }">${row.memo_txt}</option>
					`);
				});

				// 주문 제품
				let goods_title = d.data.product_info[0].product_name
					, goods_total = 0
					, boucher = 0
					, mileage = 0
					, delivery = 0;
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
					goods_total += row.product_price;
				});
				if(d.data.product_info.length > 1) {
					if(config.language == 'KR') {
						goods_title += ` 외 ${d.data.product_info.length-1}건`;
					}
					else {
						goods_title += ` etc ${d.data.product_info.length-1}`;
					}
				}
				
				// 바우처
				$("#boucher-useful").text(d.data.voucher_info.length);
				$("#boucher-has").text(d.data.voucher_cnt);
				
				// 적립금
				

				// 제품 합계
				$("#result-goods-total").text(number_format(goods_total));
				// 바우처 사용</dt>
				$("#result-use-boucher").text(number_format(boucher));
				// 적립금 사용</dt>
				$("#result-use-mileage").text(number_format(mileage));
				// 배송비</dt>
				$("#result-delivery-fee").text(number_format(delivery));
				// 최종 결제 금액</dt>
				const total_amount = goods_total - boucher - mileage + delivery;
				$("#result-total").text(number_format(total_amount));

				// 결제하기
				let toss_payments = TossPayments(config.member.pg.key); //결제위젯용
				const payment_widget = PaymentWidget(config.member.pg.key, config.member.id);  // 결제위젯 초기화

				$("#frm").submit(function() {
					if($(this).find("input[name='terms_agree']:checked").length == 0) {
						alert("이용약관, 개인정보수집 및 이용 에 동의해주세요.");
						return false;
					}

					let data = new FormData($(this).get(0));
					//data.append("basket_idx", cart_no.join(","));

					$.ajax({
						url: config.api + "order/set",
						async: true,
						enctype: "multipart/form-data",
						processData: false,
						contentType: false,
						error: function () {
							makeMsgNoti("MSG_F_ERR_0023", null);
						},
						success: function (od) {
							if(od.code == 200) {
								pay = (f) => {
									// 결제 정보 설정
									let pay_method = '카드'
										, pay_data = {
											amount: od.data.price_total,
											orderId: od.data.order_code,
											orderName: goods_title,
											customerName: od.data.member_name,
											successUrl: location.origin + '/pay/ok',
											failUrl: location.origin + '/pay',
										};

									if (config.language != "KR") { // 해외 결제일 경우
										pay_method = '해외간편결제';
										pay_data.useInternationalCardOnly = true;
										pay_data.provider = "PAYPAL";
										pay_data.currency = "USD";
										pay_data.country = "US";
									}

									// 토스 결제모듈 호출
									toss_payments.requestPayment(pay_method, pay_data);
								};
								
								if(false) { // 결제 수단 등록이 안되어 있을 경우 등록 유도
									confirm({
										title : "결제수단 등록",
										body : `
											<p>현재 결제 정보를 기본 결제 수단으로 등록하시면 ADERERROR의 제품들을 빠르고 간편하게 구매하실 수 있습니다.</p>
											<p>현재는 카드 정보 저장을 통한 기본 결제 수단 등록이 가능합니다.​</p>
											<p>등록하신 정보는 전자 보안 시스템을 통하여 암호화된 이후 안전하게 보관됩니다.<br>(ISO27001, ISO27701, PCI-DSS, ISMS)</p>
										`,
										button : {
											ok : "결제수단 등록하기"
										},
										ok : () => {
										},
										cancel : () => {
											pay($(this));
										}
									});
								}
								else {
									pay($(this));
								}
							}
							else {
								alert(od.msg);
							}
						}
					});

					return false;
				});

			}
			else {
				alert(od.msg);
			}
		}
	});
});