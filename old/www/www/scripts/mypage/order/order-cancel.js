document.addEventListener('DOMContentLoaded', function () {
	/* 주문취소 대상 리스트 조회 */
	getOrderProductListByIdx("OCC", false);
	
	/* 적립금 사용금액 토글처리 */
	let org_price_mileage = document.querySelector('.org_price_mileage');
	if (parseInt(org_price_mileage.dataset.price_mileage)> 0) {
		document.querySelector('.order_refund_price_wrap.cancel_price_wrap').classList.remove('hidden');
		document.querySelector('.div_confirm_price_product').classList.add('hidden');
	}
	
	/* 바우처 할인금액 토글처리 */
	let org_price_discount = document.querySelector('.org_price_discount');
	if (parseInt(org_price_discount.dataset.price_discount) > 0) {
		document.querySelector('.msg_order_cancel_voucher').classList.remove('hidden');
		document.querySelector('.div_confirm_price_product').classList.remove('hidden');
	}
	
	/* 개별/전체 선택버튼 클릭 이벤트 */
	clickBtnCancel();
	
	/* 주문취소 사유 취득처리 */
	getOrderUpdateReason("OCC", 0);
	
	$('.input_mileage').change(function () {
		convertMileageFloor(this);
		/* 주문취소 시 입력한 적립금 계산처리 */
		calcMileageInput();
	});
	
	clickBtnComplete();
	
	/* 주문취소 시 입력한 적립금 계산처리 */
	calcMileageInput();
});

/* 개별/전체 선택버튼 클릭 이벤트 */
function clickBtnCancel() {
	/* 버튼 - 전체선택 */
	let btn_cancel_all = document.querySelector(".order-cancel-btn.all");
	
	/* 버튼 - 개별선택 */
	let btn_cancel = document.querySelectorAll(".order-cancel-btn.self");
	
	let btn_cnt = btn_cancel.length;

	/* 버튼 - 전체선택 클릭 이벤트 */
	btn_cancel_all.addEventListener("click", function() {
		if (btn_cancel_all.classList.contains("wh")) {
			btn_cancel_all.classList.remove("wh");
			btn_cancel_all.classList.add("bk");
			btn_cancel_all.querySelector("span").dataset.i18n = "o_deselectall_cancel"; /*전체 선택 해제*/

			btn_cancel.forEach(btn => {
				btn.classList.remove("wh");
				btn.classList.add("bk");
				btn.querySelector("span").dataset.i18n = "o_deselect_cancel";/*선택 해제*/
			});
		} else {
			btn_cancel_all.classList.remove("bk");
			btn_cancel_all.classList.add("wh");
			btn_cancel_all.querySelector("span").dataset.i18n = "o_selectall_cancel"; /*전체 선택*/
			
			btn_cancel.forEach(btn => {
				btn.classList.remove("bk");
				btn.classList.add("wh");
				btn.querySelector("span").dataset.i18n = "o_select_cancel"; /*선택*/
			});
		}
		
		calcCancelProduct();
		changeLanguageR();
	});
	
	/* 버튼 - 개별선택 클릭 이벤트 */
	btn_cancel.forEach(btn => {
		btn.addEventListener("click", () => {
			if (btn.classList.contains("wh")) {
				//주문취소 선택
				btn.classList.remove("wh");
				btn.classList.add("bk");
				btn.querySelector("span").dataset.i18n = "o_deselect_cancel"; /*선택 해제*/
			} else {
				//주문취소 선택 해제
				btn.classList.remove("bk");
				btn.classList.add("wh");
				btn.querySelector("span").dataset.i18n = "o_select_cancel"; /*선택*/		
			}

			let checked_cnt = document.querySelectorAll(".order-cancel-btn.self.bk").length;
			
			if (btn_cnt == checked_cnt) {
				btn_cancel_all.classList.remove("wh");
				btn_cancel_all.classList.add("bk");
				btn_cancel_all.querySelector("span").dataset.i18n = "o_deselectall_cancel";/*전체선택 해제*/
			} else {
				btn_cancel_all.classList.remove("bk");
				btn_cancel_all.classList.add("wh");
				btn_cancel_all.querySelector("span").dataset.i18n = "o_selectall_cancel"; /*전체선택*/
			}
			
			calcCancelProduct();
			changeLanguageR();
		});
	});
}

function calcMileageInput(){
	let price_product = 0;
	let order_cancel_btn = document.querySelectorAll('.order-cancel-btn.self.bk');
	if (order_cancel_btn != null) {
		order_cancel_btn.forEach(btn => {
			let product_price = parseInt(btn.dataset.product_price);
			price_product += product_price;
		});
	}
	
	let org_price_discount_val = parseInt(document.querySelector('.org_price_discount').dataset.price_discount.replace(/,/gi, ''));
	let org_price_mileage_val = parseInt(document.querySelector('.org_price_mileage').dataset.price_mileage.replace(/,/gi, ''));
	let org_price_delivery_val = parseInt(document.querySelector('.org_price_delivery').dataset.price_delivery.replace(/,/gi, ''));
	let org_price_refund_val = parseInt(document.querySelector('.org_price_refund').dataset.price_refund.replace(/,/gi, ''));
	
	let country = getLanguage();
	let delivery_price = 0;
	
	if (price_product > 0) {
		if (document.querySelector('.order-cancel-btn.all.bk') != null) {
			//[주문 전체 취소]의 경우
			if (org_price_delivery_val > 0) {
				delivery_price += org_price_delivery_val;
			}
		} else {
			//[주문 부분 취소]의 경우
			if (country == "KR") {
				if (((org_price_refund_val + org_price_discount_val + org_price_mileage_val) - price_product) < 80000) {
					delivery_price = 2500;
				}
			} else {
				delivery_price = org_price_delivery_val;
			}
		}
	}
	
	let calc_price_product_val = price_product - delivery_price;
	
	let min_mileage = parseInt($('.min_mileage').text().replace(/,/gi, ''));
	let max_mileage = parseInt($('.max_mileage').text().replace(/,/gi, ''));
	
	let input_mileage = $('.input_mileage');
	let param_mileage = input_mileage.val();
	let return_mileage = 0;
	
	let res_price_cancel = document.querySelector('.res_price_cancel');
	let res_price_mileage = document.querySelector('.res_price_mileage');
	
	if (param_mileage < min_mileage){
		return_mileage = min_mileage;
	} else if (param_mileage > max_mileage){
		return_mileage = max_mileage;
	} else {
		return_mileage = param_mileage;
	}
	
	input_mileage.val(return_mileage);
	res_price_cancel.textContent = (calc_price_product_val - return_mileage).toLocaleString('ko-KR');
	res_price_mileage.textContent = parseInt(return_mileage).toLocaleString('ko-KR');

}

function calcCancelProduct(){
	//총 환불금액
	let org_price_product = document.querySelector('.org_price_product');
	let org_price_delivery = document.querySelector('.org_price_delivery');
	let org_price_mileage = document.querySelector('.org_price_mileage');
	let org_price_discount = document.querySelector('.org_price_discount');
	let org_price_refund = document.querySelector('.org_price_refund');
	
	let calc_price_product = document.querySelector('.calc_price_product');
	
	let res_price_product = document.querySelector('.res_price_product');
	let res_price_cancel = document.querySelector('.res_price_cancel');
	let res_price_mileage = document.querySelector('.res_price_mileage');
	
	let confirm_price_product = document.querySelector('.confirm_price_product');
	
	let min_mileage = document.querySelector('.min_mileage');
	let max_mileage = document.querySelector('.max_mileage');
	
	let btn_cancel = document.querySelectorAll(".order-cancel-btn.self");
	let input_mileage = $('.input_mileage');
	
	let org_price_refund_val = parseInt(org_price_refund.dataset.price_refund.replace(/,/gi, ''));
	let org_price_mileage_val = parseInt(org_price_mileage.dataset.price_mileage.replace(/,/gi, ''));
	let org_price_discount_val = parseInt(org_price_discount.dataset.price_discount.replace(/,/gi, ''));
	let org_price_delivery_val = parseInt(org_price_delivery.dataset.price_delivery.replace(/,/gi, ''));
	
	let price_product = 0;
	let cancel_product_cnt = 0;
	
	//선택 상품 금액 : price_product
	btn_cancel.forEach(btn => {
		if (btn.classList.contains('bk')) {
			let product_price = parseInt(btn.dataset.product_price);
			price_product += product_price;
			cancel_product_cnt++;
		}
	});
	
	let country = getLanguage();
	let delivery_price = 0;
	
	if (price_product > 0) {
		let btn_cancel_all = document.querySelector('.order-cancel-btn.all.bk');
		if (btn_cancel_all != null) {
			//[주문 전체 취소]의 경우
			if (org_price_delivery_val > 0) {
				price_product += org_price_delivery_val;
			}
		} else {
			//[주문 부분 취소]의 경우
			if (country == "KR") {
				if (((org_price_refund_val + org_price_discount_val + org_price_mileage_val) - price_product) < 80000) {
					delivery_price = 2500;
				}
			} else {
				delivery_price = org_price_delivery_val;
			}
		}
	}
	
	let calc_price_product_val = price_product - delivery_price;
	
	confirm_price_product.textContent = calc_price_product_val.toLocaleString('ko-KR');
	calc_price_product.textContent = calc_price_product_val.toLocaleString('ko-KR');
	res_price_product.textContent = calc_price_product_val.toLocaleString('ko-KR');
	
	/*
		최소값: 0 ~ (선택 상품금액 - 실결제금액 : 실결제금액을 벗어나는 금액은 반드시 적립금으로 환불 받아야된다.) 중 큰값
		최대값: 잔여 적립금(잔여 적립금보다 큰 금액을 반환받을 수 없다.) ~ 선택 상품금액 중 작은값
	*/
	let min_mileage_val = 0;
	let max_mileage_val = 0;
	if (org_price_mileage_val > 0) {
		if (org_price_mileage_val > price_product) {
			max_mileage_val = price_product - delivery_price;
		} else {
			max_mileage_val = org_price_mileage_val;
		}
		
		if ((price_product - org_price_refund_val) > 0){
			min_mileage_val = price_product - org_price_refund_val - delivery_price;
		}
		
		if (min_mileage_val < price_product){
			max_return_mileage = org_price_mileage_val;
		}
	}
	
	min_mileage.textContent = min_mileage_val.toLocaleString('ko-KR');
	max_mileage.textContent = max_mileage_val.toLocaleString('ko-KR');
	input_mileage.val(min_mileage_val);
	
	res_price_cancel.textContent = (calc_price_product_val - min_mileage_val).toLocaleString('ko-KR');
	res_price_mileage.textContent = min_mileage_val.toLocaleString('ko-KR');
}

/* 취소 신청 완료 버튼 이벤트 */
function clickBtnComplete() {
	let btn_complete = document.querySelector(".cancel-complete-btn");
	btn_complete.addEventListener("click", function () {
		if (!btn_complete.classList.contains('disabled')) {
			/* 취소하려는 주문번호 */
			let order_code = getUrlParamValue("order_code");
			
			/* 취소하려는 주문상품 */
			let cancel_product = document.querySelectorAll(".order-cancel-btn.self.bk");
			
			let param_order_product = [];
			
			let order_product_idx = [];
			let product_qty = [];

			let price_mileage = document.querySelector(".input_mileage").value;
			let price_product = 0;

			cancel_product.forEach(cancel => {
				price_product += parseInt(cancel.dataset.product_price);

				let tmp_idx = cancel.dataset.order_product_idx;

				if (order_product_idx.indexOf(tmp_idx) < 0) {
					order_product_idx.push(tmp_idx)
				}

				let tmp_qty = product_qty[tmp_idx];
				if (tmp_qty > 0) {
					tmp_qty++;
				} else {
					tmp_qty = 1;
				}

				product_qty[tmp_idx] = tmp_qty;
			});

			if (parseInt(price_mileage) > price_product) {
				let org_price_delivery_val = parseInt(document.querySelector('.org_price_delivery').dataset.price_delivery.replace(/,/gi, ''));
				
				if (price_mileage > price_product + org_price_delivery_val) {
					return false;
				}
			}

			if (order_product_idx.length > 0 && product_qty.length > 0) {
				for (let i = 0; i < order_product_idx.length; i++) {
					let order_product = {
						'param_idx': order_product_idx[i],
						'product_qty': product_qty[order_product_idx[i]]
					};

					param_order_product.push(order_product);
				}
			} else {
				makeMsgNoti(getLanguage(), "MSG_F_WRN_0005", null);
				// notiModal("취소하려는 주문상품을 선택해주세요.");
				return false;
			}

			let depth1_idx = document.querySelector('.reason_depth1_OCC .tui-select-box-selected').dataset.value;
			let depth2_idx = document.querySelector('.reason_depth2_OCC .tui-select-box-selected').dataset.value;

			let order_cancel_reason = document.querySelector('#order-cancel-reason');
			let reason_memo = order_cancel_reason.value;

			if (reason_memo.length < 5) {
				order_cancel_reason.classList.add('reason_alert');
				// order_cancel_reason.placeholder = "* 취소 신청 상세 사유를 입력해주세요. (5글자 이상)";
				order_cancel_reason.setAttribute = ("data-i18n-placeholder");
				order_cancel_reason.value = "";

				return false;
			} else {
				order_cancel_reason.classList.remove('reason_alert');
				order_cancel_reason.setAttribute = ("data-i18n-placeholder");
				// order_cancel_reason.placeholder = "상세 사유를 입력하세요. (5글자 이상)";
			}
			
			btn_complete.classList.add('disabled');
			
			$.ajax({
				type: "post",
				url: api_location + "mypage/order/order_cancel",
				data: {
					"order_code" : order_code,
					"param_order_product" : param_order_product,
					"price_mileage" : price_mileage,
					"depth1_idx" : depth1_idx,
					"depth2_idx" : depth2_idx,
					"reason_memo" : reason_memo
				},
				dataType: "json",
				error: function (d) {
					makeMsgNoti(getLanguage(), "MSG_F_ERR_0007", null);
					// notiModal('주문상품 취소처리중 오류가 발생했습니다.');
				},
				success: function (d) {
					if (d.code == 200) {
						location.href="/mypage?mypage_type=orderlist";
					} else {
						notiModal(d.msg);
					}
				}
			});
		}
	});
}

function addToOrderListBtnEvent() {
	let btn = document.querySelector(".to_orderlist_btn");

	btn.addEventListener("click", function () {
		location.href = "/mypage?mypage_type=orderlist";
	});
}
