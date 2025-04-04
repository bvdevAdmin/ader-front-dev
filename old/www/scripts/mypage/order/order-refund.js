let deliveryCompany = null;

document.addEventListener('DOMContentLoaded', function() {
	let country = getLanguage();
	if (country != "KR") {
		let delivery_type_apl = document.querySelector('.delivery_type_APL_btn');
		delivery_type_apl.style.display = 'none';
		let delivery_type_drc = document.querySelector('.delivery_type_DRC_btn');
		delivery_type_drc.style.width = '100%';
	}
	
	delivery_info = getDeliveryCompany();
	setDeliveryCompany(delivery_info);
	getOrderProductListByIdx(null,false);
	clickDeliveryTypeBtn();
	appendDeliInfo();
	
	clickBtnInitPopup();
	clickBtnPutOrderProduct();
	clickBtnTmpOrder();
});

switch (getLanguage()) {
    case "KR" :
        selectDeliveryPlaceholder = "배송업체를 선택해주세요.";
        break;

    case "EN" :
        selectDeliveryPlaceholder = "Please select a delivery company.";   
        break;

    case "CN" :
        selectDeliveryPlaceholder = "请选择快递公司。";
        break;
}

function getDeliveryCompany() {
	let delivery_info = null;
	
	$.ajax({
		type: "post",
		url: api_location + "mypage/order/delivery",
		dataType: "json",
		async:false,
		error: function (d) {
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null && data.length > 0) {
					delivery_info = data;
				}
			}
		}
	});
	
	return delivery_info;
}

function setDeliveryCompany(delivery_info) {
	deliveryCompany = new tui.SelectBox('.deli-company-list', {
		placeholder: selectDeliveryPlaceholder,
		data: delivery_info,
		autofocus: false
	});
}

function clickBtnInitPopup() {
	let btn_init_order_popup = document.querySelectorAll('.btn_init_order_popup');
	btn_init_order_popup.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let param_status = el.dataset.param_status;
			initOrderPopup(param_status);
		});
	});
}

function initOrderPopup(order_status) {
	let popup = document.querySelector(".order-popup-container-" + order_status);
	
	if (order_status == "OEX") {
		popup.querySelector(".same-size-btn").classList.remove("bk");
		popup.querySelector(".same-size-btn").classList.add("wh");
		
		popup.querySelector(".check-size-btn").classList.remove("bk");
		popup.querySelector(".check-size-btn").classList.add("wh");
	}
	
	popup.classList.add("hidden");
}

function openPopup(order_status,order_product_code) {
	let exchange_popup = document.querySelector(".order-popup-container-OEX");
	let refund_popup = document.querySelector(".order-popup-container-ORF");
	
	if (order_status == "OEX") {
		exchange_popup.classList.remove("hidden");
		refund_popup.classList.add("hidden");
	} else if (order_status == "ORF") {
		exchange_popup.classList.add("hidden");
		refund_popup.classList.remove("hidden");
	}
	
	$('.reason_memo').val('');
	
	$.ajax({
		type: "post",
		url: api_location + "mypage/order/pg/get",
		data: {
				"order_product_code" : order_product_code
		},
		dataType: "json",
		error: function (d) {
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;

				if (data != null) {
					getOrderUpdateReason(order_status,0);
					
					let product_color_html = productColorHtml(data.color,data.color_rgb);
					
					document.querySelector(".order-popup-container-" + order_status + " .current-product-idx").value = data.order_product_code;

					let order_body = document.querySelector(".order-popup-container-" + order_status + " .order-popup .order-body");
					order_body.innerHTML = "";
					
					let order_product_box = document.createElement("div");
					order_product_box.classList.add("order-product-box");
					
					let div_order_product = "";

					if(order_status == "OEX") {
						div_order_product += `
							<a href="">
								<img class="order-product-img" src="${cdn_img}${data.img_location}">
							</a>
							<ul>
								<div>
									<li class="product-name">${data.product_name}</li>
									<li class="product-price">${data.product_price}</li>
									<li class="product-color">${product_color_html}</li>
									<li class="product-size option_idx" data-option_idx="${data.option_idx}">${data.option_name}</li>
								</div>
								<div>
									<li class="product-qty">
										Qty:
										<span class="qty-cnt">${data.product_qty}</span>
									</li>
								</div>
								<div class="option-size-wrap">
									<p data-i18n="o_exchange_option"></p>
									<div class="option-size-box">
										<input class="exchange_option" type="hidden" value="0">
						`;
						
						let size_info = data.size_info;
						
						size_info.forEach(size => {
							div_order_product += `
								<div class="exchange-size-option exchange_size_btn" data-option_idx="${size.option_idx}" data-option_name="${size.option_name}">
									${size.option_name}
								</div>
							`
						});
						
						div_order_product += `
									</div>
								</div>
							</ul>
						`;
					} else {
						div_order_product = `
							<a href="">
								<img class="order-product-img" src="${cdn_img}${data.img_location}">
							</a>
							<ul>
								<div>
									<li class="product-name">${data.product_name}</li>
									<li class="product-price">${data.product_price}</li>
									<li class="product-color">${product_color_html}</li>
									<li class="product-size">${data.option_name}</li>
								</div>
								<div>
									<li class="product-qty">Qty:<span class="qty-cnt">${data.product_qty}</span></li>
								</div>
							</ul>
						`;
					}
					
					order_product_box.innerHTML = div_order_product;
					order_body.appendChild(order_product_box);
					
					if (order_status == "OEX") {
						selectExchangeOption();
					}
				}
			}
		}
	});
}

function selectExchangeOption() {
	let option_idx = document.querySelector(".option_idx").dataset.option_idx;
	let exchange_popup = document.querySelector(".order-popup-container-OEX");
	
	let same_size_btn = exchange_popup.querySelector('.same-size-btn');
	let check_size_btn = exchange_popup.querySelector('.check-size-btn');
	
	let exchange_size_btn = exchange_popup.querySelectorAll('.exchange_size_btn');

	exchange_size_btn.forEach(size_btn => {
		size_btn.addEventListener("click",function() {
			let tmp_option_idx = 0;

			if (!size_btn.classList.contains("selected")) {
				let tmp_size_btn = exchange_popup.querySelectorAll('.exchange_size_btn');
				
				tmp_size_btn.forEach(tmp => {
					tmp.classList.remove("selected");
				});
				
				size_btn.classList.add("selected");
				
				tmp_option_idx = size_btn.dataset.option_idx;
			} else {
				size_btn.classList.remove("selected");
				
				same_size_btn.classList.remove('bk');
				same_size_btn.classList.add('wh');
				check_size_btn.classList.remove('bk');
				check_size_btn.classList.add('wh');
				
				$('.exchange_option').val(0);
			}
			
			if (tmp_option_idx > 0) {
				$('.exchange_option').val(tmp_option_idx);
				let same_size_btn = exchange_popup.querySelector('.same-size-btn');
				let check_size_btn = exchange_popup.querySelector('.check-size-btn');
				
				if (option_idx == tmp_option_idx) {
					same_size_btn.classList.remove('wh');
					same_size_btn.classList.add('bk');
					
					check_size_btn.classList.remove('bk');
					check_size_btn.classList.add('wh');
				} else {
					same_size_btn.classList.add('wh');
					same_size_btn.classList.remove('bk');
					
					check_size_btn.classList.add('bk');
					check_size_btn.classList.remove('wh');
				}
			}
		});
	});
}

function clickBtnTmpOrder() {
	let btn_tmp_order = document.querySelectorAll('.btn_tmp_order');
	btn_tmp_order.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let param_status = el.dataset.param_status;
			addTmpOrderTable(param_status);
		});
	});
}

function addTmpOrderTable(order_status) {
	let order_popup = document.querySelector(".order-popup-container-" + order_status);
	
	let order_idx = getUrlParamValue("order_idx");
	let order_product_code = order_popup.querySelector(".current-product-idx").value;
	let option_idx = 0;
	
	if (order_status == "OEX") {
		option_idx = $('.exchange_option').val();
		
		if (option_idx == 0) {
			makeMsgNoti(getLanguage(), "MSG_F_WRN_0036", null);
			// notiModal('변경하고자 하는 옵션을 선택해주세요.');
		}
	}
	
	let order_code = getUrlParamValue("order_code");
	
	let depth1_idx = order_popup.querySelector('.reason_depth1_' + order_status + ' .tui-select-box-selected').dataset.value;
	let depth2_idx = order_popup.querySelector('.reason_depth2_' + order_status + ' .tui-select-box-selected').dataset.value;
	let reason_memo = order_popup.querySelector('.reason_memo');
	if (reason_memo.value.length < 5) {
		reason_memo.setAttribute("data-i18n-placeholder", "o_reason_apply");
		reason_memo.value = "";
		
		return false;
	}
	
	let param_order_product = {
		'order_status' : order_status,
		'order_product_code' : order_product_code,
		'option_idx' : option_idx,
		'product_qty' : 1
	};
	
	$.ajax({
		type: "post",
		url: api_location + "mypage/order/order_tmp",
		data: {
			"order_status" : order_status,
			"order_code": order_code,
			"param_order_product" : param_order_product,
			"depth1_idx" : depth1_idx,
			"depth2_idx" : depth2_idx,
			"reason_memo" : reason_memo.value
		},
		dataType: "json",
		async:false,
		error: function (d) {
		},
		success: function (d) {
			if (d.code == 200) {
				let order_update_code = d.data;
				getOrderProductListByIdx(order_status,true);
				initOrderPopup(order_status);
				
			} else {
				notiModal(d.msg);
			}
			changeLanguageR();
		}
	});
}

function clickDeliveryTypeBtn() {
	let delivery_type_APL_btn = document.querySelector(".delivery_type_APL_btn");
	let delivery_type_DRC_btn = document.querySelector(".delivery_type_DRC_btn");
	
	let delivery_type_APL_desc = document.querySelector(".order-description-pickup");
	let delivery_type_DRC_desc = document.querySelector(".order-description-direct");
	
	let delivery_charge_APL_desc = document.querySelector(".charge_description_APL");
	let delivery_charge_DRC_desc = document.querySelector(".charge_description_DRC");

	delivery_type_APL_btn.addEventListener("click",function() {
		if (delivery_type_APL_btn.classList.contains('bk')) {
			delivery_type_APL_btn.classList.remove("bk");
			delivery_type_APL_btn.classList.add("wh");
			
			delivery_type_APL_desc.classList.add("hidden");
		} else {
			delivery_type_APL_btn.classList.remove("wh");
			delivery_type_APL_btn.classList.add("bk");
			
			delivery_type_APL_desc.classList.remove("hidden");
			
			delivery_type_DRC_btn.classList.remove("bk");
			delivery_type_DRC_btn.classList.add("wh");
			
			delivery_type_DRC_desc.classList.add("hidden");
			
			$('.delivery_type').val('APL');
			
			checkOrderDeliveryPg();
		}
	});
	
	delivery_type_DRC_btn.addEventListener("click",function() {
		if (delivery_type_DRC_btn.classList.contains('bk')) {
			delivery_type_DRC_btn.classList.remove("bk");
			delivery_type_DRC_btn.classList.add("wh");
			
			delivery_type_DRC_desc.classList.add("hidden");
		} else {
			delivery_type_DRC_btn.classList.remove("wh");
			delivery_type_DRC_btn.classList.add("bk");
			
			delivery_type_DRC_desc.classList.remove("hidden");
			
			delivery_type_APL_btn.classList.remove("bk");
			delivery_type_APL_btn.classList.add("wh");
			
			delivery_type_APL_desc.classList.add("hidden");
			
			$('.delivery_type').val('DRC');
			
			checkOrderDeliveryPg();
		}
	});
}

function clickBtnPutOrderProduct() {
	let btn_put_order_product = document.querySelector('.btn_put_order_product');
	btn_put_order_product.addEventListener('click',function() {
		putOrderProduct();
	});
}

function checkOrderDeliveryPg() {
	let order_idx = getUrlParamValue("order_idx");
	let order_code = getUrlParamValue("order_code");
	
	let checked_cnt = 0;
	let order_update_btn = document.querySelectorAll('.order-exchange-btn');
	order_update_btn.forEach(btn => {
		if (btn.classList.contains('bk')) {
			checked_cnt++;
		}
	});
	
	if (!checked_cnt > 0) {
		makeMsgNoti(getLanguage(), "MSG_F_WRN_0053", null);
		// notiModal("교환 / 반품 신청하려는 제품을 선택해주세요.");
		return false;
	}
	
	let delivery_type = $('.delivery_type').val();
	if (delivery_type.length == 0) {
		makeMsgNoti(getLanguage(), "MSG_F_WRN_0011", null);
		// notiModal("제품 반송 방법을 선택해주세요.");
		return false;
	}
	
	$.ajax({
		type: "post",
		url: api_location + "mypage/order/check",
		data: {
			"order_idx" : order_idx,
			"order_code" : order_code,
			"housing_type" : delivery_type,
			"add_tmp_flg" : "F",
		},
		dataType: "json",
		async:false,
		error: function (d) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0053", null);
			// notiModal('배송비 결제여부 체크처리중 오류가 발생했습니다.');
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null) {
					let reason_pg_flg = data.reason_pg_flg;
					let price_delivery = data.price_delivery					
					
					if (reason_pg_flg == true) {
						let div_desc_charge_APL = document.querySelector('.charge_description_APL');
						let desc_charge_APL = `구매자 책임 사유에 의한 교환/반품이므로 배송비 ${data.txt_price_delivery}원을 구매자가 부담합니다.`;
						
						div_desc_charge_APL.innerHTML = desc_charge_APL;
						
						document.querySelector('.order-description-pickup .charge-description').classList.remove('hidden');
						
						let div_desc_charge_DRC = document.querySelector('.charge_description_DRC');
						let desc_charge_DRC = "";
						switch (getLanguage()) {
							case "KR" :
								desc_charge_DRC = `구매자 책임 사유에 의한 교환이므로 구매자의 선불 발송이 필요합니다.`;
								break;
							
							case "EN" :
								desc_charge_DRC = `The exchange is due to the buyer's responsibility, so the buyer's prepaid shipment is necessary.`;
								break;
							
							case "CN" :
								desc_charge_DRC = `由于交换是购买者的责任，所以需要购买者预付货物。`;
								break;
						}
						
						div_desc_charge_DRC.innerHTML = desc_charge_DRC;
						
						document.querySelector('.order-description-direct .charge-description').classList.remove('hidden');
					} else {
						document.querySelector('.order-description-pickup .charge-description').classList.add('hidden');
						document.querySelector('.order-description-direct .charge-description').classList.add('hidden');
					}
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function putOrderProduct() {
	let order_idx = getUrlParamValue("order_idx");
	let order_code = getUrlParamValue("order_code");
	
	let checked_cnt = 0;
	let order_update_btn = document.querySelectorAll('.order-exchange-btn');
	order_update_btn.forEach(btn => {
		if (btn.classList.contains('bk')) {
			checked_cnt++;
		}
	});
	
	if (!checked_cnt > 0) {
		makeMsgNoti(getLanguage(), "MSG_F_WRN_0053", null);
		// notiModal("교환 / 반품 신청하려는 제품을 선택해주세요.");
		return false;
	}
	
	let delivery_type = $('.delivery_type').val();
	if (delivery_type.length == 0) {
		makeMsgNoti(getLanguage(), "MSG_F_WRN_0011", null);
		// notiModal("제품 반송 방법을 선택해주세요.");
		return false;
	}
	
	let tui_housing_company = document.querySelector('.deli-company-list');
	let tmp_housing_company = tui_housing_company.querySelector('.tui-select-box-selected');
	
	let housing_company = null;
	let housing_num = null;
	
	if (delivery_type == "DRC") {
		if (tmp_housing_company != null) {
			housing_company = tmp_housing_company.textContent;
		} else {
			makeMsgNoti(getLanguage(), "MSG_F_WRN_0042", null);
			// notiModal('배송 업체를 선택해주세요.');
			return false;
		}
		
		housing_num = $('.housing_num').val();
		if (housing_num == null || housing_num.length == 0) {
			makeMsgNoti(getLanguage(), "MSG_F_WRN_0018", null);
			// notiModal('운송장 번호를 입력해주세요.');
			return false;
		}
	}
	
	$.ajax({
		type: "post",
		url: api_location + "mypage/order/check",
		data: {
			"order_idx" : order_idx,
			"order_code" : order_code,
			"housing_type" : delivery_type,
			"housing_company" : housing_company,
			"housing_num" : housing_num,
			"add_tmp_flg" : "T",
		},
		dataType: "json",
		async:false,
		error: function (d) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0053", null);
			// notiModal('배송비 결제여부 체크처리중 오류가 발생했습니다.');
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data
				if (data != null) {
					let order_update_code = data.order_update_code;
					let reason_pg_flg = data.reason_pg_flg;
					if (reason_pg_flg == true) {
						setTossPayment(order_code,order_update_code,data.price_delivery);
					} else {
						location.href="/order/refund?order_code=" + order_code;
					}
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}


function setTossPayment(order_code,order_update_code,price_delivery) {
	tossPayments.requestPayment('카드', {
		amount: price_delivery,
		orderId: order_update_code,
		orderName: "교환/반품 추가 배송비",
		customerName: "<?=$_SESSION['MEMBER_NAME']?>",
		successUrl: domain_url + '/order/refund?order_code=' + order_code,
		failUrl: domain_url + '/order/refund?order_code=' + order_code,
	});
}

function appendDeliInfo() {
	let order_idx = getUrlParamValue("order_idx");
	$.ajax({
		type: "post",
		url: api_location + "mypage/order/pg/deliver/get",
		data: {
			"order_idx": order_idx
		},
		dataType: "json",
		async: false,
		error: function (d) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0022", null);
			// notiModal('수거지정보 조회중 오류가 발생했습니다.');
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				
				if (data != null) {
					let deli_section = document.querySelector(".deli-section");
					deli_section.appendChild(getOrderDeliverySection(data));
					deli_section.querySelector(".header-tilte").innerText = "수거지 정보";
				}				
			} else {
				notiModal(d.msg);
			}
		}
	});
}
