let order_code = get_query_string('order_code');

let tui_reason_depth1 = null;
let tui_reason_depth2 = null;

let order_reason = {
	depth_1: [],
	depth_2: []
};

let param_idx = [];

$(document).ready(function () {
	if (order_code != null || order_code != undefined) {
		getCancel_product(order_code);
	} else {
		let msg_alert = {
			KR : "취소 하려는 주문 내역을 다시 선택해주세요.",
			EN : "Please select again the orders to cancel"
		}
		alert(
			msg_alert[config.language],
			function() {
				location.href = `${config.base_url}`;
			}
		);
	}
});

function getCancel_product(order_code) {
	$.ajax({
        url: config.api + "order/cancel/get",
		headers : {
			country : config.language
		},
        data: {
            'order_code'		:order_code
        },
        error: function () {
            makeMsgNoti(config.language,'MSG_F_ERR_0046', null);
        },
        success: function (d) {
            if (d.code == 200) {
                let data = d.data;
				
				let order_body = document.querySelector(".order-body");
				
				let remain = data.order_remain;
				if (remain != null) {
					document.querySelector(".order-number-value").textContent	= remain.order_code;
					document.querySelector(".order-date-value").textContent		= remain.create_date;

					document.querySelector('.o_product').textContent	= remain.t_product;
					document.querySelector('.o_product').dataset.price	= remain.product;
					
					document.querySelector('.o_member').textContent		= remain.t_member;
					document.querySelector('.o_member').dataset.price	= remain.member;
					
					document.querySelector('.o_delivery').textContent	= remain.t_delivery;
					document.querySelector('.o_delivery').dataset.price	= remain.delivery;

					document.querySelector('.o_return').textContent		= remain.t_return;
					document.querySelector('.o_return').dataset.price	= remain.return;

					document.querySelector('.o_discount').textContent	= remain.t_discount;
					document.querySelector('.o_discount').dataset.price	= remain.discount;
					
					document.querySelector('.o_mileage').textContent	= remain.t_mileage;
					document.querySelector('.o_mileage').dataset.price	= remain.mileage;
					
					document.querySelector('.o_cancel').textContent		= remain.t_cancel;
					document.querySelector('.o_cancel').dataset.price	= remain.cancel;

					document.querySelector('.o_refund').textContent		= remain.t_cancel;
					document.querySelector('.o_refund').dataset.price	= remain.cancel;
					
					let div_cancel	= document.querySelector('.order_refund_price_wrap.cancel_price_wrap');
					let msg_voucher	= document.querySelector('.msg_order_cancel_voucher');
					let div_confirm	= document.querySelector('.div_confirm_price_product');
					
					document.querySelector('.input_mileage').addEventListener('keyup',function(e) {
						let el = e.currentTarget;
						
						let min = parseInt(el.dataset.min);
						let max = parseInt(el.dataset.max);
						
						if (parseInt(el.value) < min) {
							el.value = min;
						}
						
						if (parseInt(el.value) > max) {
							el.value = max;
						}
						
						calcProduct_cancel();
					});
					
					/* 적립금 사용금액 토글처리 */
					if (remain.mileage > 0) {
						div_cancel.classList.remove('hidden');
						div_confirm.classList.add('hidden');
					}
					
					/* 바우처 할인금액 토글처리 */
					if (remain.discount > 0) {
						msg_voucher.classList.remove('hidden');
						div_confirm.classList.remove('hidden');
					}
				}
				
				let order_product = data.order_product;
				if (order_product != null && order_product.length > 0) {
					order_product.forEach(function(row) {
						let div_product = document.createElement("div");
						div_product.classList.add("order-product-box");
						
						let t_btn = {
							KR : "선택",
							EN : "Select"
						}
						div_product.innerHTML = `
							<a href="${config.base_url}/shop/${row.product_idx}">
								<img class="order-product-img" src="${config.cdn}${row.img_location}">
							</a>
							<ul>
								<div>
									<li class="product-name">${row.product_name}</li>
									<li class="product-price">${row.t_product_price}</li>
									<li>
										<span class="name">${row.color}</span>
										<span class="colorchip ${(row.color_rgb == '#ffffff')?'white':''}" style="background-color:${row.color_rgb}"></span>
									</li>
									<li class="product-size">${row.option_name}</li>
								</div>
								<div>
									<li class="product-qty">
										Qty:<span class="qty-cnt">${row.product_qty}</span>
									</li>
								</div>
							</ul>
							<div class="order-status-box cancel">
								<div class="order-cancel-btn self wh" data-op_idx="${row.op_idx}" data-product_price="${row.product_price}">
									<span data-i18n="o_select_cancel">${t_btn[config.language]}</span>
								</div>
							</div>
						`;
						
						order_body.appendChild(div_product);
					});
					
					/* 개별/전체 선택버튼 클릭 이벤트 */
					clickBTN_select();
				} else {
					makeMsgNoti(config.language,'MSG_B_ERR_0085', null);

					$('.modal.alert.on .close').click(function() {
						location.href = `${config.base_url}/my/order`;
					});
				}
				
				/* 주문 취소 사유 */
				order_reason['depth_1'] = data.order_reason.reason_d1;
				order_reason['depth_2'] = data.order_reason.reason_d2;
				
				setOrder_reason();
				
				clickBTN_cancel();
            } else {
				alert(
					d.msg,
					function() {
						if (d.code == 300) {
							location.href = `${config.base_url}/my/order`;
						} else if (d.code == 401) {
							sessionStorage.setItem('r_url',location.href);
							location.href = `${config.base_url}/login`;
						}
					}
				);
			}
        }
    });
}

/* 개별/전체 선택버튼 클릭 이벤트 */
function clickBTN_select() {
	let btn_select = document.querySelectorAll('.order-cancel-btn');
	if (btn_select != null && btn_select.length > 0) {
		btn_select.forEach(btn => {
			btn.addEventListener('click',function(e) {
				let el = e.currentTarget;

				let t_btn = {
					KR : {
						't_01' : "전체 선택 해제",
						't_02' : "선택 해제",
						't_03' : "전체 선택",
						't_04' : "선택"
					},
					EN : {
						't_01' : "Deselect all",
						't_02' : "Deselect",
						't_03' : "Select all",
						't_04' : "Select"
					}
				}
				
				if (el.classList.contains('all')) {
					/* 전체선택 버튼 클릭 처리 */
					
					if (el.classList.contains("wh")) {
						/* 전체 선택 */
						el.classList.remove("wh");
						el.classList.add("bk");
						el.querySelector("span").textContent = t_btn[config.language]['t_01'];
						
						$('.order-cancel-btn.self').removeClass('wh');
						$('.order-cancel-btn.self').addClass('bk');
						$('.order-cancel-btn.self span').text(t_btn[config.language]['t_02']);
						
						param_idx = [];
						btn_select.forEach(btn => {
							let op_idx = btn.dataset.op_idx;
							if (op_idx != null) {
								param_idx.push(parseInt(op_idx));
							}
						});
					} else {
						/* 전체 선택 해제 */
						el.classList.remove("bk");
						el.classList.add("wh");
						el.querySelector("span").textContent = t_btn[config.language]['t_03'];
						
						$('.order-cancel-btn.self').removeClass('bk');
						$('.order-cancel-btn.self').addClass('wh');
						$('.order-cancel-btn.self span').text(t_btn[config.language]['t_04']);
						
						param_idx = [];
					}
				} else {
					/* 개별선택 버튼 클릭 처리 */
					let op_idx = $(this).data('op_idx');
					
					if (el.classList.contains("wh")) {
						/* 개별 선택 */
						el.classList.remove("wh");
						el.classList.add("bk");
						el.querySelector("span").textContent = t_btn[config.language]['t_02'];
						
						param_idx.push(parseInt(op_idx));
					} else {
						/* 개별 해제 */
						el.classList.remove("bk");
						el.classList.add("wh");
						el.querySelector("span").textContent = t_btn[config.language]['t_04'];
						
						let tmp_idx = param_idx.indexOf(parseInt(op_idx));
						param_idx.splice(tmp_idx,1);
					}
					
					let cnt_check = document.querySelectorAll(".order-cancel-btn.self.bk").length;
					if (cnt_check == $('.order-cancel-btn.self').length) {
						$('.order-cancel-btn.all').removeClass("wh");
						$('.order-cancel-btn.all').addClass("bk");
						$('.order-cancel-btn.all span').textContent = t_btn[config.language]['t_01'];
					} else {
						$('.order-cancel-btn.all').removeClass("bk");
						$('.order-cancel-btn.all').addClass("wh");
						$('.order-cancel-btn.all span').textContent = t_btn[config.language]['t_03'];
					}
				}
				
				calcProduct_cancel();
			});
		});
	}
}

function calcProduct_cancel() {
	let o_product	= parseInt($('.o_product').data('price'));
	let o_discount	= parseInt($('.o_discount').data('price'));
	let o_mileage	= parseInt($('.o_mileage').data('price'));
	let o_delivery	= parseInt($('.o_delivery').data('price'));
	let o_return	= parseInt($('.o_return').data('price'));
	let o_refund	= parseInt($('.o_refund').data('price'));
	
	/* 적립금 사용 주문 환불금액 */
	let c_refund	= document.querySelector('.c_refund');
	
	let min_mileage = 0;
	let max_mileage = 0;
	
	let r_price		= document.querySelector('.r_price');
	let r_cancel	= document.querySelector('.r_cancel');
	let r_mileage	= document.querySelector('.r_mileage');
	
	/* 적립금 미사용 주문 환불금액 */
	let c_price		= document.querySelector('.c_price');
	
	let btn_select = document.querySelectorAll(".order-cancel-btn.self");
	
	let total_product	= 0;
	let price_product	= 0;
	let cnt_cancel		= 0;
	let delivery_price	= 0;
	
	let c_discount = 0;

	btn_select.forEach(btn => {
		let tmp_price = parseInt(btn.dataset.product_price);

		total_product += tmp_price;
		if (btn.classList.contains('bk')) {
			price_product += tmp_price;

			cnt_cancel++;
		}
	});

	if (price_product > 0 && o_discount > 0) {
		c_discount += (parseInt(price_product) / total_product) * o_discount;
	}
	
	if (price_product > 0) {
		if (document.querySelector('.order-cancel-btn.all.bk') != null) {
			/* [전체 취소] - 배송비 반환 처리 */
			price_product += o_delivery;
		} else {
			/* [부분 취소] */
			if (config.language == "KR") {
				delivery_price = 2500;
			} else {
				delivery_price = o_delivery;
			}
		}
	}
	
	let c_price_product = price_product - c_discount;

	if (config.language == "KR") {
		if ((o_product - price_product) > 0) {
			if ((o_product - price_product) < 80000) {
				c_price_product -= delivery_price
			}
		} else {
			c_price_product += delivery_price
		}
	} else if (config.language == "EN") {
		if ((o_product - c_price_product) > 0 && (o_product - c_price_product) < 300) {
			c_price_product -= delivery_price
		} else {
			c_price_product += delivery_price
		}
	}
	
	c_price.textContent		= number_format(c_price_product);
	c_refund.textContent	= number_format(c_price_product);
	r_price.textContent		= number_format(c_price_product);
	
	/*
	최소값: 0 ~ (선택 상품금액 - 실결제금액 : 실결제금액을 벗어나는 금액은 반드시 적립금으로 환불 받아야된다.) 중 큰값
	최대값: 잔여 적립금(잔여 적립금보다 큰 금액을 반환받을 수 없다.) ~ 선택 상품금액 중 작은값
	*/

	if (o_mileage > 0) {
		if (o_mileage > price_product) {
			min_mileage = 0;
			if ((o_mileage - price_product) == (total_product - price_product)) {
				min_mileage = price_product;
			}
			max_mileage = price_product;
		} else {
			if (o_mileage < (total_product - price_product)) {
				min_mileage = 0;
				max_mileage = o_mileage
			} else {
				min_mileage = o_mileage - (total_product - price_product);
				max_mileage = o_mileage;
			}
		}
	}
	
	let input_mileage = document.querySelector('.input_mileage');
	input_mileage.dataset.min = min_mileage;
	input_mileage.dataset.max = max_mileage;
	
	if (input_mileage.value < min_mileage) {
		input_mileage.value = min_mileage;
	} else if (input_mileage.value > max_mileage) {
		input_mileage.value = max_mileage;
	}
	
	$('.min_mileage').text(number_format(min_mileage));
	$('.max_mileage').text(number_format(max_mileage));
	
	r_cancel.textContent	= number_format(c_price_product - input_mileage.value - c_discount);
	r_mileage.textContent	= number_format(input_mileage.value);
}

function setOrder_reason() {
	let data_depth_1 = [];
	let data_depth_2 = [];
	
	$('.reason_depth1_OCC').html('');
	
	let reason_depth_1 = order_reason['depth_1'];
	if (reason_depth_1 != null && reason_depth_1.length > 0) {
		reason_depth_1.forEach(depth_1 => {
			let tmp_data = {
				'value'		:depth_1.d1_idx,
				'label'		:depth_1.reason_txt
			};

			data_depth_1.push(tmp_data);
		});

		tui_reason_depth1 = new tui.SelectBox('.reason_depth1_OCC', {
			placeholder: data_depth_1[0].reason_txt,
			data: data_depth_1,
			autofocus: false
		});
	}
	
	let eq_0 = reason_depth_1[0]['d1_idx'];
	
	$('.reason_depth2_OCC').html('');
	
	let reason_depth_2 = order_reason['depth_2'][eq_0];
	if (reason_depth_2 != null && reason_depth_2.length > 0) {
		reason_depth_2.forEach(depth_2 => {
			let tmp_data = {
				'value'		:depth_2.d2_idx,
				'label'		:depth_2.reason_txt
			}

			data_depth_2.push(tmp_data);
		});

		tui_reason_depth2 = new tui.SelectBox('.reason_depth2_OCC', {
			placeholder: data_depth_2[0].reason_txt,
			data: data_depth_2,
			autofocus: false
		});
	}
	
	/* 취소 사유 변경 */
	changeOrder_reason();
}

/* 취소 사유 변경 */
function changeOrder_reason() {
	let select_depth_1 = document.querySelectorAll('.reason_depth1_OCC .tui-select-box-item');
	select_depth_1.forEach(select => {
		select.addEventListener("click", function () {
			let d1_idx = select.dataset.value;
			
			$('.reason_depth2_OCC').html('');
			
			let data_depth_2 = [];
			
			let reason_depth_2 = order_reason['depth_2'][d1_idx];
			if (reason_depth_2 != null && reason_depth_2.length > 0) {
				reason_depth_2.forEach(depth_2 => {
					let tmp_data = {
						'label': depth_2.reason_txt,
						'value': depth_2.d2_idx
					}
					
					data_depth_2.push(tmp_data);
				});

				let tui_reason_depth2 = new tui.SelectBox('.reason_depth2_OCC', {
					placeholder: data_depth_2[0].reason_txt,
					data: data_depth_2,
					autofocus: false
				});
			}
		});
	});
}

function clickBTN_cancel() {
	let btn_cancel = document.querySelector('.cancel-complete-btn');
	if (btn_cancel != null) {
		btn_cancel.addEventListener('click',function() {
			let msg_alert = {
				KR : {
					't_01' : "주문 취소 사유를 선택해주세요.",
					't_02' : "상세 사유를 입력해주세요. (5글자 이상)",
					't_03' : "주문 취소 하려는 상품을 선택해주세요."
				},
				EN : {
					't_01' : "Please select the reason to cancel",
					't_02' : "Please enter the detail reason. (5 characters or more)",
					't_03' : "Please select the product to cancel."
				}
			}
			
			if (param_idx != null && param_idx.length > 0) {
				let d1_idx = 0;
				
				let selected_d1		= tui_reason_depth1.getSelectedItem();
				if (!isNaN(selected_d1.value)) {
					d1_idx = parseInt(selected_d1.value);
				}
				
				if (d1_idx == 0) {
					alert(msg_alert[config.language]['t_01']);
					return false;
				}
				
				let d2_idx = 0;
				
				let selected_d2		= tui_reason_depth2.getSelectedItem();
				if (!isNaN(selected_d2.value)) {
					d2_idx = parseInt(selected_d2.value);
				}
				
				if (d2_idx == 0) {
					alert(msg_alert[config.language]['t_01']);
					return false;
				}
				
				let reason_memo	= document.querySelector('#order-cancel-reason');
				if (reason_memo.value.length < 5) {
					reason_memo.classList.add('error');
					reason_memo.setAttribute("placeholder",msg_alert[config.language]['t_02']);
					reason_memo.value = "";
					
					return false;
				} else {
					reason_memo.classList.remove('error');
				}
				
				$.ajax({
					url: config.api + "order/cancel/add",
					headers : {
						country : config.language
					},
					data: {
						'order_code'		:order_code,
						'param_idx'			:param_idx,
						'mileage_price'		:$('.input_mileage').val(),
						'depth1_idx'		:d1_idx,
						'depth2_idx'		:d2_idx,
						'reason_memo'		:reason_memo.value
					},
					beforeSend: function() {
						loadingMASK();
					},
					error: function () {
						makeMsgNoti('MSG_F_ERR_0046', null);
					},
					success: function (d) {
						closeMASK();
						
						if (d.code == 200) {
							location.href = `${config.base_url}/my/order-cancel-ok?update_code=${d.data}`;
						} else {
							alert(d.msg);
						}
					}
				});
			} else {
				alert(msg_alert[config.language]['t_03']);
				return false;
			}
		});
	}
}