let order_code = get_query_string('order_code');

let order_reason = {
    OEX: {
        depth_1: [],
        depth_2: []
    },
    ORF: {
        depth_1: [],
        depth_2: []
    }
};

let tui_reason_depth1 = null;
let tui_reason_depth2 = null;
let tui_housing_company = null;

let delivery_price = 0;

let select_product = {
	OEX : [],
	ORF : []
}

let param_OEX = [];
let param_ORF = [];

$(document).ready(function () {
	if (order_code != null || order_code != undefined) {
		getUpdate_product(order_code);
	} else {
		let msg_alert = {
			KR : "교환/반품 하려는 주문내역을 다시 선택해주세요.",
			EN : "Please select the order details you want to exchange/refund"
		};

		alert(
			msg_alert[config.language],
			function() {
				location.href = `${config.base_url}`;
			}
		);
	}
	
	if (config.language != "KR") {
		$('.btn_delivery.APL').css('display','none');
		$('.btn_delivery.DRC').css('width','100%');
	}
	
	clickBTN_put();
});

/* 교환/반품 접수 가능 상품 표시 */
function getUpdate_product(order_code) {
	$.ajax({
        url: config.api + "order/update/list",
		headers : {
			country : config.language
		},
        data: {
            'order_code'		:order_code
        },
        error: function () {
            makeMsgNoti('MSG_F_ERR_0046', null);
        },
        success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				
				$(".order-number-value").text(d.data.order_info.order_code);
				$(".order-date-value").text(d.data.order_info.create_date);

				let order_body = document.querySelector(".order-body");
				
				let tmp_idx = 0;
				
				let order_product = data.order_product;
				if (order_product != null && order_product.length > 0) {
					order_product.forEach(function(row) {
						tmp_idx++;
						
						let div_product = document.createElement("div");
						div_product.classList.add("order-product-box");
						div_product.dataset.tmp_idx = tmp_idx;
						
						let disabled = "";
						if (row.product_type == "S") {
							disabled = "disabled";
						}
						
						let data_product = {
							'tmp_idx'			:tmp_idx,
							'op_idx'			:row.op_idx,
							'product_name'		:row.product_name,
							'img_location'		:row.img_location,
							'color'				:row.color,
							'color_rgb'			:row.color_rgb,
							
							'option_idx'		:row.option_idx,
							'option_name'		:row.option_name,
							
							'product_idx'		:row.product_idx,
							'product_qty'		:row.product_qty,
							'product_price'		:row.product_price,
							't_product_price'	:row.t_product_price
						};
						
						let str_order_product = setOrder_product(data_product,false,null);
						
						div_product.innerHTML = str_order_product;
						
						order_body.appendChild(div_product);
					});
					
					/* 교환/반품 신청 버튼 클릭 처리 */
					clickBTN_popup();
				}
				
				/* 주문 교환 사유 */
				let reason_exchange	= data.reason_exchange;
				order_reason['OEX']['depth_1'] = reason_exchange.reason_d1;
				order_reason['OEX']['depth_2'] = reason_exchange.reason_d2;
				
				/* 주문 반품 사유 */
				let reason_refund	= data.reason_refund;
				order_reason['ORF']['depth_1'] = reason_refund.reason_d1;
				order_reason['ORF']['depth_2'] = reason_refund.reason_d2;
				
				/* 반송회사 */
				setDelivery_company(data.delivery_company);
				
				/* 반송방법 버튼 클릭 */
				clickBTN_delivery();
			} else {
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
}

function setOrder_product(data,tmp_flg,param_status) {
	let option_name = "";
	if (param_status == "OEX") {
		option_name += `
			<div class="order_exchange_option">
				<span>${data.prev_name}</span> >  <span>${data.option_name}</span>
			</div>
		`;
	} else {
		option_name += `
			${data.option_name}
		`;
	}
	
	let t_btn = {
		KR : {
			't_01' : "교환신청",
			't_02' : "반품신청",
			't_03' : "교환취소",
			't_04' : "반품취소"
		},
		EN : {
			't_01' : "Exchange",
			't_02' : "Refund",
			't_03' : "Cancel",
			't_04' : "Cancel"
		}
	}

	let btn = "";
	if (tmp_flg != true) {
		btn = `
			<div class="order-exchange-btn btn_popup exchange" data-order_status="OEX" data-op_idx="${data.op_idx}" data-tmp_idx="${data.tmp_idx}">
				<span data-i18n="o_apply_exchange">${t_btn[config.language]['t_01']}</span>
			</div>
			
			<div class="order-exchange-btn btn_popup return" data-order_status="ORF" data-op_idx="${data.op_idx}" data-tmp_idx="${data.tmp_idx}">
				<span data-i18n="o_apply_return">${t_btn[config.language]['t_02']}</span>
			</div>
		`;
	} else {
		let btn_name = "";
		if (param_status == "OEX") {
			btn_name = t_btn[config.language]['t_03'];
		} else if (param_status == "ORF") {
			btn_name = t_btn[config.language]['t_04'];
		}
		
		btn = `
			<div class="order-exchange-btn btn_delete_tmp_order bk" data-param_status="${param_status}" data-op_idx="${data.op_idx}" data-tmp_idx="${data.tmp_idx}">
				<span data-i18n="o_apply_exchange">${btn_name}</span>
			</div>
		`;
	}
	
	let str_order_product = `
		<a href="${config.base_url}/shop/${data.product_idx}">
			<img class="order-product-img" src="${config.cdn}${data.img_location}" data-img_location="${data.img_location}">
		</a>
		<ul>
			<div class="tmp_idx" data-tmp_idx="${data.tmp_idx}">
				<li class="product-name">${data.product_name}</li>
				<li class="product-price">${data.t_product_price}</li>
				<li>
					<span class="name">${data.color}</span>
					<span class="colorchip ${(data.color_rgb == '#ffffff')?'white':''}" style="background-color:${data.color_rgb}"></span>
				</li>
				<li class="product-size">
					${option_name}
				</li>
			</div>
			<div>
				<li class="product-qty" data-product_qty="${data.product_qty}">
					Qty:<span class="qty-cnt">${data.product_qty}</span>
				</li>
			</div>
		</ul>
		<div class="order-status-box cancel">
			<div class="order-exchange-box">
				${btn}
			</div>
		</div>
	`;
	
	return str_order_product;
}

/* 반송회사 */
function setDelivery_company(data) {
	if (data != null && data.length > 0) {
		let delivery_company = [];
		
		$('.deli-company-list').html('');
		
		let t_place_holder = {
			KR : "배송업체를 선택해주세요.",
			EN : "Please select a delivery company."
		}
		
		data.forEach(company => {
			let tmp_data = {
				'value'		:company.delivery_idx,
				'label'		:company.delivery_company
			};
			
			delivery_company.push(tmp_data);
		});
		
		tui_housing_company = new tui.SelectBox('.deli-company-list', {
			placeholder: t_place_holder[config.language],
			data: delivery_company,
			autofocus: false
		});
	}
}

/* 교환/반품 버튼 클릭 처리 */
function clickBTN_popup() {
	let btn_popup = document.querySelectorAll('.btn_popup');
	btn_popup.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			
			let param_status	= el.dataset.order_status;
			let tmp_idx			= el.dataset.tmp_idx;
			let op_idx			= el.dataset.op_idx;
			
			let popup_OEX = document.querySelector(".order-popup-container-OEX");
			let popup_ORF = document.querySelector(".order-popup-container-ORF");
			
			popup_OEX.classList.add("hidden");
			popup_ORF.classList.add("hidden");
			
			$('.reason_memo').val('');
			
			$.ajax({
				type: "post",
				url: config.api + "order/update/get",
				headers : {
					country : config.language
				},
				data: {
					"op_idx"		:op_idx,
					'param_status'	:param_status
				},
				dataType: "json",
				error: function (d) {
				},
				success: function (d) {
					if (d.code == 200) {
						let data = d.data;
						
						let t_popup = {
							KR : {
								't_01':"변경할 옵션을 선택해 주세요.",
								't_02':""
							},
							EN : {
								't_01':"Please select the option to change.",
								't_02':""
							}
						}

						if (data != null) {
							let popup = document.querySelector(`.order-popup-container-${param_status}`);
							popup.dataset.op_idx = data.op_idx;
							
							let popup_container = popup.querySelector('.order-popup .order-body');
							popup_container.innerHTML = "";
							
							let popup_product = document.createElement("div");
							popup_product.classList.add("order-product-box");
							
							let div_product = "";
							
							let div_exchange = "";
							
							let exchange_size = data.exchange_size;
							if (exchange_size != "Set") {
								if (exchange_size != null && exchange_size.length > 0) {
									exchange_size.forEach(size => {
										div_exchange += `
											<div class="exchange-size-option exchange_size_btn" data-option_idx="${size.option_idx}" data-option_name="${size.option_name}">
												${size.option_name}
											</div>
										`
									});
									
									div_exchange = `
										<div class="option-size-wrap">
											<p data-i18n="o_exchange_option">${t_popup[config.language]['t_01']}</p>
											<div class="option-size-box">
												<input class="exchange_option" type="hidden" value="0">
												${div_exchange}
											</div>
										</div>
									`;
								}
							} else {
								div_exchange = `
									<div class="option-size-wrap">
										<div class="option-size-box">
											<input class="exchange_option" type="hidden" value="0">
											
											<div class="exchange-size-option exchange_size_btn" data-option_idx="0" data-option_name="Set">
												${exchange_size}
											</div>
										</div>
									</div>
								`;
							}

							div_product += `
								<a href="javascript:void(0);">
									<img class="order-product-img" data-img_location="${data.img_location}" src="${config.cdn}${data.img_location}">
								</a>	
								<ul>
									<div class="tmp_idx" data-tmp_idx="${tmp_idx}">
										<li class="product-name" data-product_name="${data.product_name}">${data.product_name}</li>
										<li class="product-price" data-product_price="${data.product_price}">${data.t_product_price}</li>
										<li class="product-color" data-color="${data.color}" data-color_rgb="${data.color_rgb}">
											<span class="name">${data.color}</span>
											<span class="colorchip ${(data.color_rgb == '#ffffff')?'white':''}" style="background-color:${data.color_rgb}"></span>
										</li>
										<li class="product-size" data-option_idx="${data.option_idx}" data-option_name="${data.option_name}">
											${data.option_name}
										</li>
									</div>
									<div>
										<li class="product-qty" data-product_qty="${data.product_qty}">
											Qty:
											<span class="qty-cnt">${data.product_qty}</span>
										</li>
									</div>
									${div_exchange}
								</ul>
							`;
							
							popup_product.innerHTML = div_product;
							popup_container.appendChild(popup_product);
							
							popup.classList.remove("hidden");
						}
						
						/* 교환 팝업 닫기 버튼 클릭 */
						clickBTN_init(param_status);
						
						/* 교환 팝업 사이즈 버튼 클릭 */
						clickBTN_exchange();
						
						/* 교환/반품 사유 표시 */
						setOrder_reason(param_status);
						
						/* 저장 버튼 클릭 */
						clickBTN_save(param_status);
						
						if (param_status == "OEX") {
							document.querySelector('.same-size-btn').dataset.option_idx = data.option_idx;
						}
					} else {
						alert(d.msg);
					}
				}
			});
		});
	});
}

/* 교환 팝업 닫기 버튼 클릭 */
function clickBTN_init(param_status) {
	let btn_init = document.querySelector(`.order-popup-container-${param_status} .btn_init_order_popup`);
	if (btn_init != null) {
		btn_init.addEventListener('click',function(e) {
			let el = e.currentTarget;
			
			let popup = document.querySelector(`.order-popup-container-${param_status}`);
			popup.classList.add("hidden");
			
			if (param_status == "OEX") {
				popup.querySelector(".same-size-btn").classList.remove("bk");
				popup.querySelector(".same-size-btn").classList.add("wh");
				
				popup.querySelector(".check-size-btn").classList.remove("bk");
				popup.querySelector(".check-size-btn").classList.add("wh");
			}
		});
	}
}

/* 교환 팝업 사이즈 버튼 클릭 */
function clickBTN_exchange() {
	let popup	= document.querySelector(".order-popup-container-OEX");
	
	let btn_same	= $('.same-size-btn');
	let btn_check	= $('.check-size-btn');
	
	let btn_option = document.querySelectorAll('.exchange_size_btn');
	
	/* 변경 사이즈 버튼 */
	btn_option.forEach(btn => {
		btn.addEventListener("click",function() {
			let prev_idx	= popup.querySelector(".product-size").dataset.option_idx;
			
			if (!btn.classList.contains("selected")) {
				$('.exchange_size_btn').removeClass('selected');
				btn.classList.add("selected");
				
				tmp_idx = btn.dataset.option_idx;
			} else {
				btn.classList.remove("selected");
				
				btn_same.removeClass('bk');
				btn_same.addClass('wh');
				
				btn_check.removeClass('bk');
				btn_check.addClass('wh');
				
				$('.exchange_option').val(0);
			}
			
			if (tmp_idx >= 0) {
				$('.exchange_option').val(tmp_idx);
				
				if (prev_idx == tmp_idx) {
					btn_same.removeClass('wh');
					btn_same.addClass('bk');
					
					btn_check.removeClass('bk');
					btn_check.addClass('wh');
				} else {
					btn_same.removeClass('bk');
					btn_same.addClass('wh');
					
					btn_check.addClass('bk');
					btn_check.removeClass('wh');
				}
			}
		});
	});
	
	/* 동일 사이즈 버튼 */
	if (btn_same != null) {
		btn_same.unbind();
		btn_same.click(function() {
			let option_idx = parseInt($(this).attr('data-option_idx'));
			btn_check.removeClass('bk');
			btn_check.addClass('wh');
			
			if (!btn_same.hasClass('bk')) {
				$('.exchange_size_btn').removeClass('selected');
				
				let btn_option = document.querySelectorAll('.exchange_size_btn');
				btn_option.forEach(btn => {
					let tmp_idx = parseInt(btn.dataset.option_idx);
					if (option_idx == tmp_idx) {
						btn.classList.add('selected');
					}
				});
				
				btn_same.addClass('bk');
				btn_same.removeClass('wh');
				
				$('.exchange_option').val(option_idx);
			} else {
				$('.exchange_size_btn').removeClass('selected');
				
				btn_same.removeClass('bk');
				btn_same.addClass('wh');
				
				$('.exchange_option').val(0);
			}
		});
	}
}

/* 교환/반품 사유 표시 */
function setOrder_reason(param_status) {
	let data_depth_1 = [];
	let data_depth_2 = [];
	
	$(`.reason_depth1_${param_status}`).html('');
	
	let reason_depth_1 = order_reason[param_status]['depth_1'];
	if (reason_depth_1 != null && reason_depth_1.length > 0) {
		reason_depth_1.forEach(depth_1 => {
			let tmp_data = {
				'value'		:depth_1.d1_idx,
				'label'		:depth_1.reason_txt
			};

			data_depth_1.push(tmp_data);
		});

		tui_reason_depth1 = new tui.SelectBox(`.reason_depth1_${param_status}`, {
			placeholder: data_depth_1[0].reason_txt,
			data: data_depth_1,
			autofocus: false
		});
	}
	
	let eq_0 = reason_depth_1[0]['d1_idx'];
	
	$(`.reason_depth2_${param_status}`).html('');
	
	let reason_depth_2 = order_reason[param_status]['depth_2'][eq_0];
	if (reason_depth_2 != null && reason_depth_2.length > 0) {
		reason_depth_2.forEach(depth_2 => {
			let tmp_data = {
				'value'		:depth_2.d2_idx,
				'label'		:depth_2.reason_txt
			}

			data_depth_2.push(tmp_data);
		});

		tui_reason_depth2 = new tui.SelectBox(`.reason_depth2_${param_status}`, {
			placeholder: data_depth_2[0].reason_txt,
			data: data_depth_2,
			autofocus: false
		});
	}
	
	/* 교환/반품 사유 변경 */
	changeOrder_reason(param_status);
}

/* 교환/반품 사유 변경 */
function changeOrder_reason(param_status) {
	let select_depth_1 = document.querySelectorAll(`.reason_depth1_${param_status} .tui-select-box-item`);
	select_depth_1.forEach(select => {
		select.addEventListener("click", function () {
			let d1_idx = select.dataset.value;
			
			$(`.reason_depth2_${param_status}`).html('');
			
			let data_depth_2 = [];
			
			let reason_depth_2 = order_reason[param_status]['depth_2'][d1_idx];
			if (reason_depth_2 != null && reason_depth_2.length > 0) {
				reason_depth_2.forEach(depth_2 => {
					let tmp_data = {
						'label': depth_2.reason_txt,
						'value': depth_2.d2_idx
					}

					data_depth_2.push(tmp_data);
				});

				tui_reason_depth2 = new tui.SelectBox(`.reason_depth2_${param_status}`, {
					placeholder: data_depth_2[0].reason_txt,
					data: data_depth_2,
					autofocus: false
				});
			}
		});
	});
}

/* 저장 버튼 클릭 */
function clickBTN_save(param_status) {
	let btn_save = $(`.order-popup-container-${param_status} .btn_tmp_order`);
	if (btn_save != null) {
		btn_save.unbind();
		
		btn_save.click(function() {
			let popup = document.querySelector(`.order-popup-container-${param_status}`);
			
			let tmp_idx			= popup.querySelector('.tmp_idx').dataset.tmp_idx;
			let op_idx			= popup.dataset.op_idx;
			
			let option_idx		= 0;
			let option_name		= "";
			
			let prev_idx		= 0;
			let prev_name		= "";
			
			let product_price	= popup.querySelector('.product-price').dataset.product_price;
			let t_product_price	= setLocale(config.language,product_price);
			
			let t_msg = {
				KR : {
					't_01' : "교환하려는 상품의 사이즈를 선택해주세요.",
					't_01' : "상세 사유를 입력해주세요. (5글자 이상)"
				},
				EN : {
					't_01' : "Please select the size to change.",
					't_02' : "Please enter a detailed reason. (5 characters or more)"
				}
			}

			if (param_status == "OEX") {
				if (popup.querySelector('.exchange_size_btn.selected') != null) {
					prev_idx		= popup.querySelector('.product-size').dataset.option_idx;
					prev_name		= popup.querySelector('.product-size').dataset.option_name;
					
					option_idx		= popup.querySelector('.exchange_option').value;
					option_name		= popup.querySelector('.exchange_size_btn.selected').dataset.option_name;
				} else {
					alert(t_msg[config.language]['t_01']);
					return false;
				}
			} else {
				option_idx		= popup.querySelector('.product-size').dataset.option_idx;
				option_name		= popup.querySelector('.product-size').dataset.option_name;
			}
			
			let selected_d1		= tui_reason_depth1.getSelectedItem();
			let d1_idx			= parseInt(selected_d1.value);
			
			let depth_1			= order_reason[param_status]['depth_1'];
			let d1_pg_flg		= 0;
			
			if (depth_1 != null && depth_1.length > 0) {
				depth_1.forEach(d1 => {
					if (d1_idx == d1.d1_idx) {
						d1_pg_flg = d1.pg_flg;
					}
				});
			}
			
			let selected_d2		= tui_reason_depth2.getSelectedItem();
			let d2_idx			= parseInt(selected_d2.value);
			
			let depth_2			= order_reason[param_status]['depth_2'][d1_idx];
			let d2_pg_flg		= 0;
			
			if (depth_2 != null && depth_2.length > 0) {
				depth_2.forEach(d2 => {
					if (d2_idx == d2.d2_idx) {
						d2_pg_flg = d2.pg_flg;
					}
				});
			}
			
			let reason_memo	= popup.querySelector('.reason_memo');
			if (reason_memo.value.length < 5) {
				reason_memo.classList.add('error');
				reason_memo.setAttribute("placeholder",t_msg[config.language]['t_02']);
				reason_memo.value = "";
				
				return false;
			} else {
				reason_memo.classList.remove('error');
			}
			
			let tmp_select = {
				'tmp_idx'			:tmp_idx,
				'op_idx'			:op_idx,
				'product_name'		:popup.querySelector('.product-name').dataset.product_name,
				'img_location'		:popup.querySelector('.order-product-img').dataset.img_location,
				'color'				:popup.querySelector('.product-color').dataset.color,
				'color_rgb'			:popup.querySelector('.product-color').dataset.color_rgb,
				
				'prev_idx'			:prev_idx,
				'prev_name'			:prev_name,
				
				'option_idx'		:option_idx,
				'option_name'		:option_name,
				
				'product_qty'		:popup.querySelector('.product-qty').dataset.product_qty,
				'product_price'		:product_price,
				't_product_price'	:t_product_price,
				
				'd1_idx'			:d1_idx,
				'd1_pg_flg'			:d1_pg_flg,
				
				'd2_idx'			:d2_idx,
				'd2_pg_flg'			:d2_pg_flg,
				
				'reason_memo'		:reason_memo.value
			};
			
			let tmp_data = {
				'op_idx'			:op_idx,
				
				'prev_idx'			:prev_idx,
				'option_idx'		:option_idx,
				
				'd1_idx'			:d1_idx,
				'd1_pg_flg'			:d1_pg_flg,
				
				'd2_idx'			:d2_idx,
				'd2_pg_flg'			:d2_pg_flg,
				
				'reason_memo'		:reason_memo.value
			}
			
			if (!select_product[param_status]) {
				select_product[param_status] = {};
			}

			if (!select_product[param_status][tmp_idx]) {
				select_product[param_status][tmp_idx] = {};
			}

			select_product[param_status][tmp_idx] = tmp_select;
			
			if (param_status == "OEX") {
				param_OEX[tmp_idx] = tmp_data;
			} else if (param_status == "ORF") {
				param_ORF[tmp_idx] = tmp_data;
			}
			
			/* 임시 접수 상품 화면 표시 */
			let order_body = document.querySelector(".order-body");
			
			let div_product = document.createElement("div");
			div_product.classList.add("order-product-box");
			div_product.classList.add('update');
			div_product.classList.add(tmp_idx);
			
			let str_order_product = setOrder_product(tmp_select,true,param_status);
			
			div_product.innerHTML = str_order_product;
			
			order_body.appendChild(div_product);
			
			/* 접수 완료 상품 수량 차감 */
			let order_product = document.querySelectorAll('.order-product-box');
			if (order_product != null && order_product.length > 0) {
				order_product.forEach(product => {
					if (!product.classList.contains('update')) {
						let check_idx = product.querySelector('.tmp_idx').dataset.tmp_idx;
						if (check_idx == tmp_idx) {
							let div_product_qty = product.querySelector('.product-qty');
							
							let product_qty = parseInt(div_product_qty.dataset.product_qty);
							product_qty--;
							
							div_product_qty.dataset.product_qty = product_qty;
							div_product_qty.querySelector('.qty-cnt').textContent = product_qty;
							
							product.classList.add('hidden');
						}
					}
				});
			}
			
			/* 팝업 숨김 처리 */
			popup.classList.add("hidden");
			
			if (param_status == "OEX") {
				popup.querySelector(".same-size-btn").classList.remove("bk");
				popup.querySelector(".same-size-btn").classList.add("wh");
				
				popup.querySelector(".check-size-btn").classList.remove("bk");
				popup.querySelector(".check-size-btn").classList.add("wh");
			}
			
			/* 접수 취소 */
			clickBTN_delete();

			/* 교환/반품 배송비 계산 */
			checkDelivery_price();
		});
	}
}

/* 접수 취소 */
function clickBTN_delete() {
	let btn_delete = $('.btn_delete_tmp_order');
	if (btn_delete != null && btn_delete.length > 0) {
		btn_delete.unbind();
		btn_delete.click(function() {
			let param_status = $(this).data('param_status');
			let tmp_idx = $(this).data('tmp_idx');
			if (tmp_idx != null) {
				let order_product = document.querySelectorAll('.order-product-box');
				if (order_product != null && order_product.length > 0) {
					order_product.forEach(product => {
						let check_idx = product.querySelector('.tmp_idx').dataset.tmp_idx;
						if (check_idx == tmp_idx) {
							select_product[param_status][tmp_idx] = [];
							let div_product_qty = product.querySelector('.product-qty');
							
							let product_qty = parseInt(div_product_qty.dataset.product_qty);
							product_qty++;
							
							div_product_qty.dataset.product_qty = product_qty;
							div_product_qty.querySelector('.qty-cnt').textContent = product_qty;
							
							product.classList.remove('hidden');
							
							$(`.order-product-box.update.${tmp_idx}`).remove();
						}	
					});
				}
				
				delete select_product[param_status][tmp_idx];
				delete param_OEX[tmp_idx];
				delete param_ORF[tmp_idx];

				checkDelivery_price();

				$.ajax({
					url: config.api + "order/update/delete",
					headers : {
						country : config.language
					},
					data: {
						'order_code'		:order_code
					},
					error: function () {
						makeMsgNoti('MSG_F_ERR_0046', null);
					},
					success: function (d) {
						if (d.code == 200) {
							
						}
					}
				});
			}
		});
	}
}

/* 반송방법 버튼 클릭 */
function clickBTN_delivery() {
	let btn_delivery = document.querySelectorAll('.btn_delivery');
	if (btn_delivery != null && btn_delivery.length > 0) {
		btn_delivery.forEach(btn => {
			btn.addEventListener('click',function(e) {
				let el = e.currentTarget;
				
				let btn_APL = $('.btn_delivery.APL');
				let btn_DRC = $('.btn_delivery.DRC');
				
				div_APL = document.querySelector(".order-description-pickup");
				div_DRC = document.querySelector(".order-description-direct");
				
				let delivery_type = el.dataset.delivery_type;
				if (delivery_type == "APL") {
					/* 반송 - 수거신청 */
					if (!el.classList.contains('bk')) {
						btn_APL.removeClass('wh');
						btn_APL.addClass('bk');
						
						btn_DRC.addClass('wh');
						btn_DRC.removeClass('bk');
						
						div_APL.classList.remove("hidden");
						div_DRC.classList.add("hidden");
					} else {
						btn_APL.addClass('wh');
						btn_APL.removeClass('bk');
						
						div_APL.classList.add("hidden");
						div_DRC.classList.add("hidden");
					}
				} else if (delivery_type == "DRC") {
					/* 반송 - 직접발송 */
					if (!el.classList.contains('bk')) {
						btn_APL.addClass('wh');
						btn_APL.removeClass('bk');
						
						btn_DRC.removeClass('wh');
						btn_DRC.addClass('bk');
						
						div_APL.classList.add("hidden");
						div_DRC.classList.remove("hidden");
					} else {
						btn_DRC.addClass('wh');
						btn_DRC.removeClass('bk');
						
						div_APL.classList.add("hidden");
						div_DRC.classList.add("hidden");
					}
				}
			});
		});
	}
}

/* 교환/반품 배송비 계산 */
function checkDelivery_price() {
	let cnt_OEX_T = 0;
	let cnt_OEX_F = 0;
	
	let cnt_ORF_T = 0;
	let cnt_ORF_F = 0;

	let cnt_total = 0;

	let product_OEX = select_product['OEX'];
	if (product_OEX.length > 0) {
		product_OEX.forEach(product => {
			if (product.d1_pg_flg == true) {
				cnt_OEX_T++;
			} else {
				cnt_OEX_F++;
			}
			
			if (product.d2_pg_flg == true) {
				cnt_OEX_T++;
			} else {
				cnt_OEX_F++;
			}

			cnt_total++;
		});
	}
	
	let product_ORF = select_product['ORF'];
	if (product_ORF.length > 0) {
		product_ORF.forEach(product => {
			if (product.d1_pg_flg == true) {
				cnt_ORF_T++;
			} else {
				cnt_ORF_F++;
			}
			
			if (product.d2_pg_flg == true) {
				cnt_ORF_T++;
			} else {
				cnt_ORF_F++;
			}

			cnt_total++;
		});
	}

	if (cnt_total > 0) {
		if (cnt_OEX_F == 0 && cnt_ORF_F == 0) {
			if (cnt_OEX_T > 0) {
				delivery_price = 5000;
			} else {
				if (cnt_ORF_T > 0) {
					delivery_price = 2500;
				}
			}
		} else {
			delivery_price = 0;
		}
	} else {
		delivery_price = 0;
	}
	
	t_delivery_price = setLocale(config.language,delivery_price);
	
	let div_APL = $('.order-description-pickup .charge-description');
	let div_DRC = $('.order-description-direct .charge-description');

	let desc_APL = $('.charge_description_APL');
	let desc_DRC = $('.charge_description_DRC');
	
	let msg_APL = "";
	let msg_DRC = "";

	if (delivery_price > 0) {
		div_APL.removeClass('hidden');
		div_DRC.removeClass('hidden');

		msg_APL = `구매자 책임 사유에 의한 교환/반품이므로 배송비 ${t_delivery_price}원을 구매자가 부담합니다.`;
		msg_DRC = `구매자 책임 사유에 의한 교환이므로 구매자의 선불 발송이 필요합니다.`;
		
		if (config.language != "KR") {
			msg_APL = "";
			msg_DRC = `The exchange is due to the buyer's responsibility, so the buyer's prepaid shipment is necessary.`;
		}
	} else {
		div_APL.addClass('hidden');
		div_DRC.addClass('hidden');
	}

	desc_APL.html(msg_APL);
	desc_DRC.html(msg_DRC);
}
	
/* 교환/반품 접수 */
function clickBTN_put() {
	let btn_put = document.querySelector('.btn_put_order_product');
	btn_put.addEventListener('click',function() {
		if (select_product['OEX'].length > 0 || select_product['ORF'].length > 0) {
			let housing_type	= "APL";
			let housing_idx		= 0;
			let housing_num		= null;
			
			let t_msg = {
				KR : {
					't_01' : "배송 업체를 선택해주세요.",
					't_02' : "운송장번호를 입력해주세요.",
					't_03' : "주문 교환/접수가 완료되었습니다.",
					't_04' : "제품 반송 방법을 선택해주세요.",
					't_05' : "교환/반품 하려는 주문 상품을 선택해주세요."
				},
				EN : {
					't_01' : "Please select the delivery company.",
					't_02' : "Please enter the shipping number.",
					't_03' : "Order exchange/refund has been completed.",
					't_04' : "Please select returning method.",
					't_05' : "Please select the products to exchange/refund."
				}
			}

			let btn_delivery = document.querySelector('.btn_delivery.bk');
			if (btn_delivery != null) {
				if (btn_delivery.classList.contains('DRC')) {
					housing_type = "DRC";
					/* 직접배송 - 배송업체 예외처리 */
					
					let selected_company = tui_housing_company.getSelectedItem();
					if (selected_company != null) {
						housing_idx = parseInt(selected_company.value);
						if (isNaN(housing_idx)) {
							alert(t_msg[config.language]['t_01']);
							return false;
						}
					} else {
						alert(t_msg[config.language]['t_01']);
						return false;
					}
					
					housing_num = document.querySelector('.housing_num').value;
					if (housing_num == null || housing_num == "") {
						alert(t_msg[config.language]['t_02']);
						return false;
					}
				}
				
				let data_OEX = [];
				if (param_OEX != null && param_OEX.length > 0) {
					param_OEX.forEach(function(row) {
						data_OEX.push(row);
					})
				}
				
				let data_ORF = [];
				if (param_ORF != null && param_ORF.length > 0) {
					param_ORF.forEach(function(row) {
						data_ORF.push(row);
					})
				}
				
				$.ajax({
					url: config.api + "order/update/add",
					headers : {
						country : config.language
					},
					data: {
						'order_code'		:order_code,
						'param_OEX'			:data_OEX,
						'param_ORF'			:data_ORF,
						
						'housing_type'		:housing_type,
						'housing_idx'		:housing_idx,
						'housing_num'		:housing_num,

						'param_delivery'	:delivery_price
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
							let data = d.data;
							if (data.delivery_type != null && data.delivery_price > 0) {
								setToss_payment(data.member_id,data.update_code,data.delivery_price);
							} else {
								alert(
									t_msg[config.language]['t_03'],
									function() {
										location.href = `${config.base_url}/my/order`;
									}
								)
							}
						} else {
							alert(d.msg);
						}
					}
				});
			} else {
				alert(t_msg[config.language]['t_04']);
			}
		} else {
			alert(t_msg[config.language]['t_05']);
		}
	});
}

function setToss_payment(member_id,order_update_code,price_delivery) {
	let toss_payments = TossPayments(config.member.pg.key); //결제위젯용
	
	// 결제 정보 설정
	let pay_method = '카드'
		,pay_data = {
			amount			: price_delivery,
			orderId			: order_update_code,
			orderName		: order_update_code,
			customerName	: member_id,
			successUrl		: `${location.origin}${config.base_url}/my/order-refund`,
			failUrl			: `${location.origin}${config.base_url}/my/order`,
		};

	if (config.language != "KR") { // 해외 결제일 경우
		pay_method = '해외간편결제';
		pay_data.useInternationalCardOnly = true;
		pay_data.provider	= "PAYPAL";
		pay_data.currency	= "USD";
		pay_data.country	= "US";
	}

	/* 토스 결제모듈 호출 */
	toss_payments.requestPayment(pay_method,pay_data);
}

function setLocale(country,num) {
	let result = 0;
	if (!isNaN(num)) {
		if (country == "KR") {
			result = num.toLocaleString('ko-KR');
		} else {
			result = num.toLocaleString('ko-KR',{minimumFractionDigits:1});
		}
	}

	return result;
}