const getOrderMainTitle = (param) => {
	const title = document.createElement("div");
	title.className = 'order-main-title';
	title.innerHTML = param;

	title.setAttribute('data-i18n', param);
	
	clickSetToggle();
	return title;
}

//상품 컬러칩 생성
const productColorHtml = (color, color_rgb) => {
	let productColorHtml = "";

	if (!color_rgb) {
		return null;
	} else {
		let multi = color_rgb.split(";");

		if (multi.length === 2) {
			productColorHtml += `
				<div class="color-line"	style="--background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
					<p class="color-name">${color}</p>
					<div class="color multi" data-title="${color}"></div>
				</div>
			`;
		} else {
			productColorHtml += `
				<div class="color-line"	data-title="${color}" style="--background:${multi[0]}">
					<p class="color-name">${color}</p>
					<div class="color" data-title="${color}"></div>
				</div>
			`;
		}
	}

	return productColorHtml;
};

//주문별 주문상품 리스트 append
function writeOrderProductListHtml(data) {
	let div_order_product = "";

	data.forEach(row => {
		let product_color_html = productColorHtml(row.color, row.color_rgb);
		let option_name = "";

		if (row.order_status == "OEX" || row.order_status == "OEH" || row.order_status == "OEP" || row.order_status == "OEE") {
			option_name = `
				<div class="order_exchange_option">
					<span>${row.prev_option_name}</span>
					<img src="/images/mypage/mypage_order_change.svg">
					<span>${row.option_name}</span>
				</div>
			`;
		} else {
			option_name = row.option_name;
		}

		div_order_product += `
			<div class="order-product-box">
				<a href="javascript:void(0);" class="docs-creator">
					<img class="order-product-img" src="${cdn_img}${row.img_location}">
				</a>
				<ul>
					<div>
						<li class="product-name">${row.product_name}</li>
						<li class="product-price">${row.txt_product_price}</li>
						<li class="product-color">${product_color_html}</li>
						<li class="product-size">${option_name}</li>
					</div>
					<div>
						<li class="product-qty">
							Qty:<span class="qty-cnt">${row.product_qty}</span>
						</li>
					</div>
				</ul>
			<div class="order-status-box">
		`;

		let div_set_toggle = "";

		if (row.product_type == "S") {
			div_set_toggle = `
				<img class="set_toggle" data-order_product_code="${row.order_product_code}" data-action_type="show" src="/images/mypage/mypage_down_tab_btn.svg">
			`;
		}
		
		let order_status_msg = getOrderStatusMsg(row.order_status);
		let order_set_toggle = document.querySelector('.order_set_toggle');

		if (row.order_status == "OEE" || row.order_status == "ORE") {
			div_order_product += `
				<div class="order-status">
					<div>${div_set_toggle}</div>
					<div>${row.txt_order_status}</div>
				</div>
			`;
		} else if (row.order_status == "PCP" || row.order_status == "PPR" || row.order_status == "DPR" || row.order_status == "OCC") {
			div_order_product += `
				<div class="order-status">
					<div>${div_set_toggle}</div>
					<div>${row.txt_order_status}</div>
				</div>
			`;
		} else {
			// 교환 / 반품 회수중
			/*
			if(row.order_status == "OEH" || row.order_status == "OET" || row.order_status == "ORH" || row.order_status == "ORT") {
				div_order_product += `
					<div class="order-status">
						<div>${div_set_toggle}</div>
						<div>${row.txt_order_status}</div>
					</div>
				`;
				
				// 고객의 직접 발송시 -> 고객이 발송한 회사명, 송장번호
				// 수거 요청시 -> 수거한 택배 회사명, 송장번호
			}
			*/
			
			let url_delivery = "";
			if (row.url_delivery != null && row.url_delivery.length > 0) {
				url_delivery += `
					window.open('${row.url_delivery}','배송추적','width=400,height=800');
				`;
			}

			div_order_product += `
				<div class="order-status delivery">
					<div class="order_set_toggle">${div_set_toggle}</div>
					<div>${row.txt_order_status}</div>
					<div class="order-company-info" onClick="${url_delivery}">
						<div>${row.company_name}</div>
						<div>${row.delivery_num}</div>
					</div>
				</div>
				<div class="order-status-msg">${order_status_msg}</div>
			`;

			if(div_set_toggle == null) {
				order_set_toggle.style.display ='none'; 
			}

		} 
		
		div_order_product += `
				</div>
			</div>
		`;

		let div_set_product = "";

		if (row.product_type == "S") {
			let set_product = row.set_product;

			if (set_product != null && set_product.length > 0) {
				set_product.forEach(set => {
					let set_color_html = productColorHtml(set.color, set.color_rgb);

					div_set_product += `
						<div class="order-product-box set_product hidden" data-parent_code="${set.parent_code}">
							<a href="javascript:void(0);" class="docs-creator">
								<img class="order-product-img" src="${cdn_img}${set.img_location}">
							</a>
							<ul>
								<div>
									<li class="product-name">${set.product_name}</li>
									<li class="product-price"></li>
									<li class="product-color">${set_color_html}</li>
									<li class="product-size">${set.option_name}</li>
								</div>
								<div>
									<li class="product-qty"></li>
								</div>
							</ul>
						</div>
					`;
				});

				div_order_product += div_set_product;
			}
		}
	});

	return div_order_product;
}

function clickSetToggle() {
	let set_toggle = document.querySelectorAll('.set_toggle');
	set_toggle.forEach(toggle => {
		toggle.addEventListener('click', function (e) {
			let toggle_btn = e.currentTarget;

			let order_product_code = toggle_btn.dataset.order_product_code;
			let action_type = toggle_btn.dataset.action_type;

			let set_product = document.querySelectorAll('.set_product');

			set_product.forEach(set => {
				if (set.dataset.parent_code == order_product_code) {
					set.classList.toggle('hidden');
				}
			});

			if (action_type == "show") {
				toggle_btn.dataset.action_type = "hide";
				toggle_btn.src = "/images/mypage/mypage_up_tab_btn.svg";
			} else if (action_type == "hide") {
				toggle_btn.dataset.action_type = "show";
				toggle_btn.src = "/images/mypage/mypage_down_tab_btn.svg";
			}
		});
	});
}

//주문 상세내역 - 주문상품정보 section
const getOrderProductSection = (data, param_status) => {
	let div_product = "";
	let div_product_cancel = "";
	let div_product_exchange = "";
	let div_product_refund = "";

	if (param_status == "ALL") {
		div_product				= writeOrderProductListHtml(data.order_product, data.company_name, data.delivery_num);
		div_product_cancel		= writeOrderProductListHtml(data.order_product_cancel, data.company_name, data.delivery_num);
		div_product_exchange	= writeOrderProductListHtml(data.order_product_exchange, data.company_name, data.delivery_num);
		div_product_refund		= writeOrderProductListHtml(data.order_product_refund, data.company_name, data.delivery_num);
	} else {
		switch (param_status) {
			case "OCC":
				div_product_cancel = writeOrderProductListHtml(data.order_product_cancel, data.company_name, data.delivery_num);

				break;

			case "OEX":
				div_product_exchange = writeOrderProductListHtml(data.order_product_exchange, data.company_name, data.delivery_num);

				break;

			case "ORF":
				div_product_refund = writeOrderProductListHtml(data.order_product_refund, data.company_name, data.delivery_num);

				break;
		}
	}

	const section = document.createElement("section");

	section.className = 'order-detail-section order';

	section.innerHTML = `
		<div class="order-list-container">
			<div class="order-list-box">
				<div class="order-header">
					<div class="order-info">
						<div class="order-number">
							<span data-i18n="m_order_number"></span>
							<a href="javascript:void(0);" class="docs-creator">
								<span class="order-number-value">${data.order_code}</span>
							</a>
						</div>
						
						<div class="order-date">
							<span data-i18n="o_order_date"></span>
							<a href="javascript:void(0);" class="docs-creator">
								<span class="order-date-value">${data.create_date}</span>
							</a>
						</div>
					</div>
				</div>
				
				<div class="order-body">
					${div_product}
					${div_product_cancel}
					${div_product_exchange}
					${div_product_refund}
				</div>
			</div>
		</div>
	`;

	return section;
};

// 주문 상세내역 - 배송정보 section
const getOrderDeliverySection = (data) => {
	const section = document.createElement("section");
	section.className = 'order-detail-section delivery'

	section.innerHTML = `
		<div class="order-header">
			<span class="header-tilte" data-i18n="o_shipment_info">배송 정보</span>
		</div>
		
		<div class="order-body">
			<div class="order-detail-box delivery-info">
				<div><span>${data.to_name}</span></div>
				<div><span>${data.to_mobile}</span></div>
				<div>
					<span>${data.to_zipcode}</span>
					<span>${data.to_addr} ${data.to_detail_addr}</span>
				</div>
				
				<div>
					<span></span>
					<span>${data.order_memo ? data.order_memo : ''}</span>
				</div>
			</div>
		</div>
	`;

	return section;
};

// 주문 상세내역 - 교환 / 반품 수거, 반송지 정보 section
function getRefundAddressSection(data) {
	const section = document.createElement("section");
	section.className = 'order-detail-section delivery';

	let title = "";
	let addressHtml = "";

	// if(data.flg = true) {
	switch (getLanguage()) {
		case "KR":
			title = "수거지 정보";
			break;

		case "EN":
			title = "Pickup information";
			break;

		case "CN":
			title = "取货信息";
			break;
	}

	addressHtml = `
		<div class="order-detail-box delivery-info">
			<div><span>${data.member_name}</span></div>
			<div><span>${data.member_mobile}</span></div>
			<div>
				<span>${data.to_zipcode}</span>
				<span>${data.to_addr} ${data.to_detail_addr}</span>
			</div>
		</div>
	`;

	// } else {
	// 	title = "반송 주소";
	// 	addressHtml = 
	// 		`
	// 			<div class="order-detail-box delivery-info">
	// 				<div>
	// 					<span>수령인</span>
	// 					<span>ADER</span>
	// 				</div>
	// 				<div>
	// 					<span>연락처</span>
	// 					<span>02-792-2232</span>
	// 				</div>
	// 				<div>
	// 					<span>주소</span>
	// 					<span>(17135) 경기도 용인시 처인구 이동읍 백옥대로 84-37</span>
	// 				</div>
	// 				<div>
	// 					<span></span>
	// 					<span>* 반송 시 주소지를 잘못 기입하거나 정확한 배송 정보가 등록되지 않을 경우 입고 및 검수 처리가 늦어질 수 있습니다.</span>
	// 				</div>
	// 			</div>
	// 		`;
	// }


	section.innerHTML =
		`
			<div class="order-header">
				<span>${title}</span>
			</div>

			<div class="order-body">
				${addressHtml}
			</div>
		`;

	return section;
}

//주문 상세내역 - 결제정보 section
const getOrderPaymentSection = (order_price) => {
	const section = document.createElement("section");
	section.className = 'order-detail-section payment';

	let paymeny_info_html = `
		<div class="order-header">
			<span class="header-tilte" data-i18n="o_payment_info">결제 정보</span>
		</div>
		
		<div class="order-body">
			<div class="order-detail-box payment-info">
				<div class="order-detail-row">
					<span data-i18n="o_subtotal">제품합계</span>
					<span>${order_price.price_product}</span>
				</div>
				<div class="order-detail-row">
					<span data-i18n="o_shipping_total">배송비</span>
					<span>${order_price.price_delivery}</span>
				</div>
				<div class="order-detail-row">
					<span data-i18n="m_voucher">바우처</span>
					<span>${order_price.price_discount}</span>
				</div>
				<div class="order-detail-row">
					<span data-i18n="m_mileage">적립포인트</span>
					<span>${order_price.price_mileage_point}</span>
				</div>
			</div>
		</div>
		
		<div class="order-detail-footer">
			<span data-i18n="o_order_total">합계</span>
			<span>${order_price.price_total}</span>
		</div>
	`;

	section.innerHTML = paymeny_info_html;
	return section;
};

// 주문 상세내역 - 취소 환불내역 section33020.3
function setRefundPaymentSection(order_price) {
	const refund_payment_section = document.createElement("section");
	refund_payment_section.className = 'order-detail-section payment';

	let refund_payment_html = `
		<div class="order-header">
			<span class="header-tilte" data-i18n="o_refund_list"></span>
		</div>
		
		<div class="order-body">
			<div class="order-detail-box payment-info">
				<div class="order-detail-row">
					<span data-i18n="o_subtotal"></span><span>${order_price.price_product}</span>
				</div>
				<div class="order-detail-row">
					<span data-i18n="o_shipping_total"></span>
					<span>${order_price.price_delivery}</span>
				</div>
				<div class="order-detail-row">
					<span data-i18n="m_voucher"></span>
					<span>${order_price.price_discount}</span>
				</div>
				<div class="order-detail-row">
					<span data-i18n="m_mileage"></span>
					<span>${order_price.price_mileage_point}</span>
				</div>
			</div>
		</div>
		
		<div class="order-detail-footer">
			<span data-i18n="o_order_total"></span>
			<span>${order_price.price_cancel}</span>
		</div>
	`;

	refund_payment_section.innerHTML = refund_payment_html;

	return refund_payment_section;
}

function setExtrapaymentSection(data) {
	const refund_payment_section = document.createElement("section");
	refund_payment_section.className = 'order-detail-section extra';

	let refund_payment_html = `
		<div class="order-header">
			<span class="header-tilte" data-i18n="extra_price"></span>
		</div>
		
		<div class="order-body">
			<div class="order-detail-box payment-info">
	`;
	
	let extra_price = data.extra_price;
	if (extra_price != null) {
		extra_price.forEach(function(row) {
			refund_payment_html += `
					<div class="order-detail-row">
			`;
			
			let order_status = row.order_status;
			if (order_status == "OE") {
				refund_payment_html += `
						<span data-i18n="extra_price_exchange"></span>
				`;
			} else if (order_status == "OR") {
				refund_payment_html += `
						<span data-i18n="extra_price_refund"></span>
				`;
			}
						
			refund_payment_html += `
						<span>
							<a href="${row.extra_url}" target="blank" rel="noopener noreferrer" style="text-decoration:underline;">${row.extra_price}</a>
						</span>
					</div>
			`;
		});
	}
	
	refund_payment_html += `
			</div>
		</div>
		
		<div class="order-detail-footer">
			<span data-i18n="o_order_total"></span>
			<span>${data.total_extra}</span>
		</div>
	`;

	refund_payment_section.innerHTML = refund_payment_html;

	return refund_payment_section;
}

// 결제 수단, 리스트 버튼 
function getOrderPaymentAndToListSection(pg_data, type) {
	const section = document.createElement("section");
	section.className = 'order-detail-section to-list';

	let pg_payment = pg_data.pg_payment;
	let pg_card_number = "";
	let payment = "";
	let hidden_class = "";

	switch(pg_payment) {
		case "카드":
			payment = pg_data.txt_issue_name;
			pg_card_number = " " + pg_data.pg_card_number;
			break;

		case "간편결제":
			payment = "간편결제";
			break;

		case "적립금":
			payment = "적립금";
			hidden_class = "hidden";
			break;
	}

	let paymeny_info_html = `
		<div class="order-detail-payment-info-wrap">
			<div class="order-detail-payment-info">
				<div class="order-detail-payment">
					<div>결제 수단</div>
					<div>${payment}${pg_card_number}</div>
				</div>
				<div class="order-detail-payment-date">
					<div>결제 일시</div>
					<div>${pg_data.pg_date}</div>
				</div>
			</div>
			<div class="order-detail-payment-receipt ${hidden_class}">
				<a href="${pg_data.pg_receipt_url}" target="_blank" rel="noopener noreferrer">영수증 보기</a>
			</div>
		</div>
	`;

	switch(type) {
		case "A":
			paymeny_info_html += `
				<div class="order-detail-btn to_order_list_btn">
					<span class="header-tilte" data-i18n="o_order_history_list"></span>
				</div>
			`;
			break;

		case "C":
			paymeny_info_html += `
				<div class="order-detail-btn to_order_list_btn">
					<span class="header-tilte" data-i18n="o_cancel_history_list"></span>
				</div>
			`;
			break;

		case "E":
			paymeny_info_html += `
				<div class="order-detail-btn to_order_list_btn">
					<span class="header-tilte" data-i18n="o_exchange_history_list"></span>
				</div>
			`;
			break;

		case "R":
			paymeny_info_html += `
				<div class="order-detail-btn to_order_list_btn">
					<span class="header-tilte" data-i18n="o_refund_history_list"></span>
				</div>
			`;
			break;
	}

	section.innerHTML = paymeny_info_html;
	return section;
}

// 주문 상세내역 - 취소 신청사유 section
function setCancelReasonSection(data) {
	const cancel_reason_section = document.createElement("section");
	cancel_reason_section.className = 'order-detail-section exc';

	let cancel_reason_html = `
		<div class="order-header">
			<span class="header-tilte" data-i18n="o_reason_cancel"></span>
		</div>

		<div class="order-body">
			<div class="refund_reason_area">
				<div class="refund_reason_title">
					${data.reason1_txt}<br/>
					${data.reason2_txt}
				</div>
				<div class="refund_reason_content">
					${xssDecode(data.reason_memo)}
				</div>
			</div>
		</div>
	`;

	/*
	<div class="order-detail-btn to_order_list_btn">
		<span class="header-tilte">교환 내역 목록 보기</span>
	</div>
	*/

	cancel_reason_section.innerHTML = cancel_reason_html;

	return cancel_reason_section;
}

// 주문 상세내역 - 교환 신청사유 section
function setExchangeReasonSection(data) {
	const exchange_reason_section = document.createElement("section");
	exchange_reason_section.className = 'order-detail-section exchange_reason';

	let exchange_reason_html = `
		<div class="order-header">
			<span class="header-tilte" data-i18n="o_return_reason">교환 신청 사유</span>
		</div>

		<div class="order-body">
			<div class="refund_reason_area">
				<div class="refund_reason_title">
					${data.reason1_txt}<br/>
					${data.reason2_txt}
				</div>
				<div class="refund_reason_content">
					${xssDecode(data.reason_memo)}
				</div>
			</div>
		</div>

		<div class="order-detail-btn to_order_list_btn">
			<span class="header-tilte" data-i18n="o_exchange_history_list">교환 내역 목록 보기</span>
		</div>
	`;

	exchange_reason_section.innerHTML = exchange_reason_html;

	return exchange_reason_section;
}

// 주문 상세내역 - 반품 신청사유 section
function setRefundReasonSection(data) {
	const refund_reason_section = document.createElement("section");
	refund_reason_section.className = 'order-detail-section exchange_reason';

	let refund_reason_html = `
		<div class="order-header">
			<span class="header-tilte" data-i18n="o_refund_reason">반품 신청 사유</span>
		</div>

		<div class="order-body">
			<div class="refund_reason_area">
				<div class="refund_reason_title">
					${data.reason1_txt}<br/>
					${data.reason2_txt}
				</div>
				<div class="refund_reason_content">
					${xssDecode(data.reason_memo)}
				</div>
			</div>
			<div class="refund_notice">
				<p data-i18n="o_refund_noti_01"></p>
				<p data-i18n="o_refund_noti_02"></p>
				<p data-i18n="o_refund_noti_03"></p>
			</div>
		</div>

		<div class="order-detail-btn to_order_list_btn">
			<span class="header-tilte" data-i18n="o_refund_history_list">반품 내역 목록 보기</span>
		</div>
	`;

	refund_reason_section.innerHTML = refund_reason_html;

	return refund_reason_section;
}

//주문 상세내역 - 결제취소정보 section
const getOrderPaymentCancelSection = (param_status, order_product_cnt, order_status, update_flg, order_code) => {
	const section = document.createElement("section");
	section.className = 'order-detail-section order-cancel';
	
	let orderCancelBtnHtml = "";
	let orderRefundBtnHtml = "";

	if (param_status == "ALL") {
		if (order_status == "PCP" && order_product_cnt > 0) {
			orderCancelBtnHtml =
				`
					<div class="order-detail-btn wh order_detail_cancel_btn" data-order_code="${order_code}">
						<span class="header-tilte" data-i18n="o_cancel_order"></span>
					</div>
				`;
		}

		if (update_flg == true && order_product_cnt > 0) {
			orderRefundBtnHtml = `
				<div class="order-detail-btn wh order_detail_refund_btn" data-order_code="${order_code}">
					<span class="header-tilte" data-i18n="o_apply_exchange_return"></span>
				</div>
			`;
		}
	}

	section.innerHTML =
		`
			<div class="order-body">
				<div class="order-noti-wrap order-cancel-noti">
					<span class="noti-title" data-i18n="o_order_cancel_msg"></span>	
					<ul>
						<li data-i18n="o_order_cancel_msg_01"></li>
						<li data-i18n="o_order_cancel_msg_02"></li>
					</ul>
					${orderCancelBtnHtml}
				</div>
				<div class="order-noti-wrap order-exchange-noti">
					<span class="noti-title" data-i18n="o_info_exchange_return"></span>	
					<ul>
						<li data-i18n="o_order_list_info_01"></li>
						<li data-i18n="o_order_list_info_02"></li>
						<li data-i18n="o_order_list_info_03"></li>
					</ul>
					${orderRefundBtnHtml}
				</div>
			</div>
		`;

	return section;
};

function getOrderProductListByIdx(order_status,tmp_flg) {
	let param_order_code = getUrlParamValue("order_code");
	
	let order_code = document.querySelector(".order-number-value");
	let create_date = document.querySelector(".order-date-value");
	let order_body = document.querySelector(".order-body");

	$.ajax({
		type: "post",
		url: api_location + "mypage/order/pg/list/get",
		data: {
			"order_status": order_status,
			"order_code": param_order_code,
			"tmp_flg": tmp_flg
		},
		dataType: "json",
		async: false,
		error: function (d) {
			notiModal(d.msg);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;

				if (data != null) {
					order_code.innerText = param_order_code;
					create_date.innerText = data.create_date;

					order_body.innerText = "";

					let order_product = data.order_product;

					if (order_product != null && order_product.length > 0) {
						order_product.forEach(product => {
							let order_btn = "";

							if (order_status == "OCC") {
								order_btn = `
									<div class="order-cancel-btn self wh" data-order_idx="${data.order_idx}" data-order_product_idx="${product.order_product_idx}" data-order_status="${order_status}" data-product_price="${product.product_price}">
										<span data-i18n="o_select_cancel"></span>
									</div>
								`;
								
								let recent_pg_info = data.recent_pg_info;
								if (recent_pg_info != null) {
									document.querySelector('.org_price_product').textContent = recent_pg_info.price_product;
									document.querySelector('.org_price_product').dataset.price_product = recent_pg_info.price_product;
									
									document.querySelector('.org_price_delivery').textContent = recent_pg_info.price_delivery;
									document.querySelector('.org_price_delivery').dataset.price_delivery = recent_pg_info.price_delivery;
									
									document.querySelector('.org_price_discount').textContent = recent_pg_info.price_discount;
									document.querySelector('.org_price_discount').dataset.price_discount = recent_pg_info.price_discount;
									
									document.querySelector('.org_price_mileage').textContent = recent_pg_info.txt_price_mileage_point;
									document.querySelector('.org_price_mileage').dataset.price_mileage = recent_pg_info.price_mileage_point;
									
									document.querySelector('.org_price_refund').textContent = recent_pg_info.pg_remain_price;
									document.querySelector('.org_price_refund').dataset.price_refund = recent_pg_info.pg_remain_price;
								}
							} else {
								let disabled = "";

								if (product.product_type == "S") {
									disabled = "disabled";
								}

								order_btn = `
									<div class="order-exchange-box">
										<div class="order-exchange-btn exchange btn_open_popup ${disabled}" data-order_status="OEX" data-order_product_code="${product.order_product_code}">
											<span data-i18n="o_apply_exchange"></span>
										</div>
										<div class="order-exchange-btn btn_open_popup return" data-order_status="ORF" data-order_product_code="${product.order_product_code}">
											<span data-i18n="o_apply_return"></span>
										</div>
									</div>
								`;
							}

							let div_order_product = document.createElement("div");
							div_order_product.classList.add("order-product-box");

							let product_color_html = productColorHtml(product.color, product.color_rgb);

							div_order_product.innerHTML = `
								<a href="javascript:void(0);">
									<img class="order-product-img" src="${cdn_img}${product.img_location}">
								</a>
								<ul>
									<div>
										<li class="product-name">${product.product_name}</li>
										<li class="product-price">${product.txt_product_price}</li>
										<li class="product-color">${product_color_html}</li>
										<li class="product-size">${product.option_name}</li>
									</div>
									<div>
										<li class="product-qty">Qty:<span class="qty-cnt">${product.product_qty}</span></li>
									</div>
								</ul>
								<div class="order-status-box cancel">
									${order_btn}
								</div>
							`;

							order_body.appendChild(div_order_product);
							
							clickBtnOpenPopup();
						});
					}

					if (order_status != "OCC") {
						let order_product_exchange = data.tmp_product_exchange;
						if (order_product_exchange != null && order_product_exchange.length > 0) {
							order_product_exchange.forEach(exchange => {
								let order_btn = `
									<div class="order-exchange-box">
										<div class="order-exchange-btn btn_delete_tmp_order exchange-${exchange.order_product_code} bk" data-param_status="OEX" data-order_product_code="${exchange.order_product_code}">
											<span data-i18n="o_apply_exchange">교환 신청</span>
										</div>
										<div class="order-exchange-btn return-${exchange.order_product_code}">
											<span data-i18n="o_apply_return">반품 신청</span>
										</div>
									</div>
								`;

								let option_name_html = "";

								let prev_option_name = exchange.prev_option_name;
								if (prev_option_name != null) {
									option_name_html = `
										<div class="order_exchange_option">
											<span>${exchange.prev_option_name}</span>
											<img src="/images/mypage/mypage_order_change.svg">
											<span>${exchange.option_name}</span>
										</div>
									`;
								} else {
									option_name_html = `<li class="product-size">${exchange.option_name}</li>`;
								}

								let div_order_product = document.createElement("div");
								div_order_product.classList.add("order-product-box");

								let product_color_html = productColorHtml(exchange.color, exchange.color_rgb);

								div_order_product.innerHTML = `
									<a href="javascript:void(0);">
										<img class="order-product-img" src="${cdn_img}${exchange.img_location}">
									</a>
									<ul>
										<div>
											<li class="product-name">${exchange.product_name}</li>
											<li class="product-price">${exchange.txt_product_price}</li>
											<li class="product-color">${product_color_html}</li>
											${option_name_html}
										</div>
										<div>
											<li class="product-qty">Qty:<span class="qty-cnt">${exchange.product_qty}</span></li>
										</div>
									</ul>
									<div class="order-status-box">
										${order_btn}
									</div>
								`;

								order_body.appendChild(div_order_product);
							});
						}

						let order_product_refund = data.tmp_product_refund;

						if (order_product_refund != null && order_product_refund.length > 0) {
							order_product_refund.forEach(refund => {
								let order_btn = `
									<div class="order-exchange-box">
										<div class="order-exchange-btn exchange-${refund.order_product_code}">
											<span data-i18n="o_apply_exchange">교환 신청</span>
										</div>
										<div class="order-exchange-btn btn_delete_tmp_order return-${refund.order_product_code} bk" data-param_status="ORF" data-order_product_code="${refund.order_product_code}">
											<span data-i18n="o_apply_return">반품 신청</span>
										</div>
									</div>
								`;

								let div_order_product = document.createElement("div");
								div_order_product.classList.add("order-product-box");

								let product_color_html = productColorHtml(refund.color, refund.color_rgb);

								div_order_product.innerHTML = `
									<a href="javascript:void(0);">
										<img class="order-product-img" src="${cdn_img}${refund.img_location}">
									</a>
									<ul>
										<div>
											<li class="product-name">${refund.product_name}</li>
											<li class="product-price">${refund.txt_product_price}</li>
											<li class="product-color">${product_color_html}</li>
											<li class="product-size">${refund.option_name}</li>
										</div>
										<div>
											<li class="product-qty">Qty:<span class="qty-cnt">${refund.product_qty}</span></li>
										</div>
									</ul>
									<div class="order-status-box">
										${order_btn}
									</div>
								`;

								order_body.appendChild(div_order_product);
							});
						}
						
						changeLanguageR();
						clickBtnDeleteTmpOrder();
					}
				}
			}
		}
	});
}

function deleteTmpOrderTable(order_status, order_product_code) {
	let order_code = getUrlParamValue("order_code");
	
	$.ajax({
		type: "post",
		url: api_location + "mypage/order/pg/delete",
		data: {
			"order_status": order_status,
			"order_code": order_code,
			"order_product_code": order_product_code
		},
		dataType: "json",
		error: function (d) {

		},
		success: function (d) {
			getOrderProductListByIdx(order_status, true);
		}
	});
}

function addOrderExchangeBtnEvent() {
	let exchangeBtn = document.querySelectorAll(".order-exchange-btn[data-order_status='OEX']");
	let returnBtn = document.querySelectorAll(".order-exchange-btn[data-order_status='ORF']");

	exchangeBtn.forEach(btn => btn.addEventListener("click", function () {
		let orderProductCode = btn.dataset.order_product_code;

		openPopup("OEX", orderProductCode);
	}));
	returnBtn.forEach(btn => btn.addEventListener("click", function () {
		let orderProductCode = btn.dataset.order_product_code;

		openPopup("ORF", orderProductCode);
	}));
}

function getOrderUpdateReason(order_status, depth1_idx) {
	$.ajax({
		type: "post",
		url: api_location + "mypage/order/pg/reason/get",
		data: {
			"order_status": order_status,
			"depth1_idx": depth1_idx
		},
		dataType: "json",
		error: function (d) {
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null) {
					let depth1_data = [];
					let depth2_data = [];

					let reason_depth1 = data.reason_depth1;
					if (reason_depth1 != null && reason_depth1.length > 0) {
						$('.reason_depth1_' + order_status).html('');

						reason_depth1.forEach(function (depth1) {
							let tmp_data = {
								'label': depth1.reason_txt,
								'value': depth1.depth1_idx
							};

							depth1_data.push(tmp_data);
						});

						let tui_reason_depth1 = new tui.SelectBox('.reason_depth1_' + order_status, {
							placeholder: depth1_data[0].reason_txt,
							data: depth1_data,
							autofocus: false
						});
					}

					let reason_depth2 = data.reason_depth2;
					if (reason_depth2 != null && reason_depth2.length > 0) {
						$('.reason_depth2_' + order_status).html('');

						reason_depth2.forEach(function (depth2) {
							let tmp_data = {
								'label': depth2.reason_txt,
								'value': depth2.depth2_idx
							}

							depth2_data.push(tmp_data);
						});

						let tui_reason_depth2 = new tui.SelectBox('.reason_depth2_' + order_status, {
							placeholder: depth2_data[0].reason_txt,
							data: depth2_data,
							autofocus: false
						});
					}

					changeOrderUpdateReason(order_status);
				}
			}
		}
	});
}

function changeOrderUpdateReason(order_status) {
	let select_reason_depth1 = document.querySelectorAll('.reason_depth1_' + order_status + ' .tui-select-box-item');
	select_reason_depth1.forEach(reason => {
		reason.addEventListener("click", function () {
			let depth1_idx = reason.dataset.value;

			getOrderUpdateReason(order_status, depth1_idx);
		});
	});
}

function getOrderStatusMsg(order_status) {
	let order_status_msg = "";
	
	let country = getLanguage();

	switch (order_status) {
		// 결제 완료
		case "PCP" :
			switch (country) {
				case "KR" :
					order_status_msg = "제품 준비 중 상태로 변경될 경우<br>취소가 불가합니다.";
					break;
				
				case "EN" :
					order_status_msg = "Once the status changes to product preparation in progress, <br>cancellation is not possible.";
					break;
				
				case "CN" :
					order_status_msg = "如果状态更改为正在准备产品，<br>则无法取消。";
					break;
			}
			
			break;

		// 제품 준비중
		case "PPR" :
			switch (country) {
				case "KR" :
					order_status_msg = "제품 준비가 시작되어 수령 이후<br/>교환 및 반품 신청이 가능합니다.";
					break;
				
				case "EN" :
					order_status_msg = "After product preparation begins and you receive it<br/>you can apply for exchange or return.";
					break;
				
				case "CN" :
					order_status_msg = "产品准备开始并收到后<br/>您可以申请换货或退货。";
					break;
			}
			
			break;

		// 배송 준비중
		case "DPR" :
			switch (country) {
				case "KR" :
					order_status_msg = "배송 준비가 시작되어 수령 이후<br/>교환 및 반품 신청이 가능합니다.";
					break;
				
				case "EN" :
					order_status_msg = "After delivery preparations begin and you receive it<br/>you can apply for exchange or return.";
					break;
				
				case "CN" :
					order_status_msg = "发货准备工作开始并收到后<br/>您可以申请换货或退货。";
					break;
			}
			
			break;
		
		// 배송 완료
		case "DCP" :
			switch (country) {
				case "KR" :
					order_status_msg = "교환/반품 신청은 배송 완료일로부터<br/>7일 이내 신청 가능합니다.";
					break;
				
				case "EN" :
					order_status_msg = "Exchange/return requests can be made within 7 days <br/>from the delivery completion date.";
					break;
				
				case "CN" :
					order_status_msg = "自交货完成日期起，<br/>可在7天内申请交换/退货。";
					break;
			}
			
			break;
			
		// 취소 완료
		case "OCC" :
			switch (country) {
				case "KR" :
					order_status_msg = "취소 내역은 자세히 보기를 <br/>눌러 확인 가능합니다.";
					break;
				
				case "EN" :
					order_status_msg = "Cancellation details can be<br/>checked by clicking View details.";
					break;
				
				case "CN" :
					order_status_msg = "可以通过单击“查看详细信息<br/>”来检查取消详细信息。";
					break;
			}
			
			break;
		
		// 교환 회수
		case "OEH" :
			switch (country) {
				case "KR" :
					order_status_msg = "반송 제품 입고 이후 검수 절차를<br/>거쳐 교환 처리됩니다.";
					break;
				
				case "EN" :
					order_status_msg = "After receiving the returned product,<br/>it will be exchanged through inspection procedures.";
					break;
				
				case "CN" :
					order_status_msg = "收到退回的产品后，<br/>将通过检验程序进行调换。";
					break;
			}
			
			break;

		// 교환 신청
		case "OEX" :
			switch (country) {
				case "KR" :
					order_status_msg = "반송 제품 입고 이후 검수 절차를 거쳐 교환 처리됩니다.";
					break;
				
				case "EN" :
					order_status_msg = "After receiving the returned product,<br/>it will be exchanged through inspection procedures.";
					break;
				
				case "CN" :
					order_status_msg = "收到退回的产品后，将通过检验程序进行调换。";
					break;
			}
			
			break;


		// 반품 회수
		case "ORH" :
			switch (country) {
				case "KR" :
					order_status_msg = "반송 제품 입고 이후 검수 절차를 거쳐 반품 처리됩니다.";
					break;
				
				case "EN" :
					order_status_msg = "After receiving the returned product,<br/>it will be returned through inspection procedures.";
					break;
				
				case "CN" :
					order_status_msg = "收到退回的产品后，将通过检验程序退回。";
					break;
			}
			
			break;

		// 반품 신청
		case "ORF" :
			switch (country) {
				case "KR" :
					order_status_msg = "반송 제품 입고 이후 검수 절차를 거쳐 반품 처리됩니다.";
					break;
				
				case "EN" :
					order_status_msg = "After receiving the returned product,<br/>it will be returned through inspection procedures.";
					break;
				
				case "CN" :
					order_status_msg = "收到退回的产品后，将通过检验程序退回。";
					break;
			}
			
			break;
	}
	
	return order_status_msg;
}

function clickBtnOpenPopup() {
	let btn_open_popup = document.querySelectorAll('.btn_open_popup');
	btn_open_popup.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			
			let param_status = el.dataset.order_status;
			let order_product_code = el.dataset.order_product_code;
			
			openPopup(param_status,order_product_code);
		});
	});
}

function clickBtnDeleteTmpOrder() {
	let btn_delete_tmp_order = document.querySelectorAll('.btn_delete_tmp_order');
	btn_delete_tmp_order.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let param_status = el.dataset.param_status;
			let order_product_code = el.dataset.order_product_code;
			
			deleteTmpOrderTable(param_status,order_product_code);
		});
	});
}