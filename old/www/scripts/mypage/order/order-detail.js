/* 주문 자세히보기  */
function showOrderDetail(order_idx) {

	let param_status = document.querySelector("#param_status").value;
	let calendar = document.querySelector(".orderlist-calendar-wrap");

	calendar.classList.add("hidden");

	let order_list_wrap = document.querySelectorAll(".orderlist__tab__wrap");
	order_list_wrap.forEach(wrap => {
		if (!wrap.classList.contains('hidden')) {
			wrap.classList.add('hidden');
		}

		let order_list = wrap.querySelector('.order__list');
		if (!order_list.classList.contains('hidden')) {
			order_list.classList.add('hidden');
		}
	});

	document.querySelector(".order__detail").classList.remove('hidden');

	getOrderInfo(order_idx, param_status);
}

function addHrefEvent(order_idx,order_code) {
	let orderCancelBtn = document.querySelector(".order_detail_cancel_btn");
	let orderRefundBtn = document.querySelector(".order_detail_refund_btn");

	if (orderCancelBtn != null) {
		orderCancelBtn.addEventListener("click", function () {
			location.href = `/mypage/main/orderlist/cancel?order_idx=${order_idx}&order_code=${order_code}`;
		});
	} else if (orderRefundBtn != null) {
		orderRefundBtn.addEventListener("click", function () {
			location.href = `/mypage/main/orderlist/refund?order_idx=${order_idx}&order_code=${order_code}`;
		});
	}
}

function addGoToListBtnEvent(param_status) {
	let toListBtn = document.querySelector(".to_order_list_btn");
	let detailTab = document.querySelector(".order__detail");
	let calendar = document.querySelector(".orderlist-calendar-wrap");
  
	toListBtn.addEventListener("click", function () {
	  let order_status = param_status;
	  let listTab = document.querySelector(".orderlist__tab__wrap.tab_" + order_status);
	  let list = listTab.querySelector(".order__list");
  
	  let dateChoiceBtn = listTab.querySelectorAll(".date-choice-btn");
	  let selectedDate = listTab.querySelectorAll(".selected-date"); 
  
	  dateChoiceBtn.forEach(btn => {
		btn.innerHTML =
		  `
			<img class="orderlist-calendar-img" src="/images/mypage/mypage_calendar_icon.png">
			<span data-i18n="o_calendar_select"></span>
		  `;
	  });
  
	  selectedDate.forEach(date => date.value = "");
  
	  listTab.classList.remove("hidden");
	  list.classList.remove("hidden");
	  calendar.classList.remove("hidden");
	  detailTab.classList.add("hidden");
  
	  window.scrollTo(0, 0);
	});
  } 

function getOrderInfo(order_idx, param_status) {
	$.ajax({
		type: "post",
		url: api_location + "mypage/order/get",
		data: {
			"order_idx": order_idx,
			'param_status': param_status
		},
		dataType: "json",
		async: false,
		error: function (d) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0010", null);
			// notiModal('주문 상세내역 조회처리중 오류가 발생했습니다.');
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null) {
					$('#mypage_order_detail').html('');
					
					const virtual_dom = document.createDocumentFragment();
					const main = document.createElement('main');
					
					let order_product_cnt = 0;
					
					let pg_data = {
						"pg_card_number": data.pg_card_number,
						"pg_date": data.pg_date,
						"pg_issue_code": data.pg_issue_code,
						"pg_payment": data.pg_payment,
						"pg_receipt_url": data.pg_receipt_url,
						"txt_issue_name": data.txt_issue_name
					};
					
					let order_extra_price = data.order_extra_price;
					
					switch (param_status) {
						case "ALL":
							order_product_cnt = data.order_product.length;

							main.appendChild(getOrderMainTitle('o_detail_order'));
							main.appendChild(getOrderProductSection(data, param_status));
							main.appendChild(getOrderDeliverySection(data));
							
							main.appendChild(getOrderPaymentSection(data.order_price));
							
							if (order_extra_price != null && order_extra_price.total_extra != null && parseInt(order_extra_price.total_extra) > 0) {
								main.appendChild(setExtrapaymentSection(order_extra_price));
							}

							// if(data.order_cancel_price != null) {
							// 	main.appendChild(setRefundPaymentSection(data.order_cancel_price));
							// }
							// if(data.order_exchange_price != null) {
							// 	main.appendChild(setRefundPaymentSection(data.order_exchange_price));
							// }
							// if(data.order_refund_price != null) {
							// 	main.appendChild(setRefundPaymentSection(data.order_refund_price));
							// }
							
							main.appendChild(getOrderPaymentAndToListSection(pg_data, "A"));
							
							if (order_product_cnt > 0) {
								main.appendChild(getOrderPaymentCancelSection(param_status,order_product_cnt,data.order_status,data.update_flg,data.order_code));
							}

							break;

						case "OCC":
							order_product_cnt = data.order_product_cancel.length;

							main.appendChild(getOrderMainTitle('o_detail_cancel'));
							main.appendChild(getOrderProductSection(data, param_status));
							main.appendChild(getOrderDeliverySection(data));
							// main.appendChild(getOrderPaymentSection(data.order_price));
							main.appendChild(setRefundPaymentSection(data.order_cancel_price));
							
							if (order_extra_price != null) {
								main.appendChild(setExtrapaymentSection(order_extra_price));
							}
							
							main.appendChild(getOrderPaymentAndToListSection(pg_data, "C"));

							break;

						case "OEX":
							order_product_cnt = data.order_product_exchange.length;

							main.appendChild(getOrderMainTitle('o_detail_exchange'));
							main.appendChild(getOrderProductSection(data, param_status));
							main.appendChild(getRefundAddressSection(data));
							// main.appendChild(getOrderPaymentSection(data.order_price));
							main.appendChild(setRefundPaymentSection(data.order_exchange_price));
							
							if (order_extra_price != null) {
								main.appendChild(setExtrapaymentSection(order_extra_price));
							}
							
							main.appendChild(getOrderPaymentAndToListSection(pg_data, "E"));

							break;

						case "ORF":
							order_product_cnt = data.order_product_refund.length;

							main.appendChild(getOrderMainTitle('o_detail_refund'));
							main.appendChild(getOrderProductSection(data, param_status));
							main.appendChild(getRefundAddressSection(data));
							// main.appendChild(getOrderPaymentSection(data.order_price));
							main.appendChild(setRefundPaymentSection(data.order_refund_price));
							
							if (order_extra_price != null) {
								main.appendChild(setExtrapaymentSection(order_extra_price));
							}
							
							main.appendChild(getOrderPaymentAndToListSection(pg_data, "R"));
							
							break;
					}

					virtual_dom.appendChild(main);
					document.querySelector('#mypage_order_detail').appendChild(virtual_dom);

					clickSetToggle();
					addHrefEvent(order_idx,data.order_code);
					addGoToListBtnEvent(param_status);
					changeLanguageR();

					window.scrollTo(0, 0);
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}

