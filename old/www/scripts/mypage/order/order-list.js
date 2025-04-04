document.addEventListener('DOMContentLoaded', function () {
	$('.order_status_btn').click(function () {
		let calendar = document.querySelector(".orderlist-calendar-wrap");

		calendar.classList.remove("hidden");
		changeOrderStatusTab($(this).attr('action-type'));
		changeLanguageR();
	});

	makeCalendar("orderlist-start");
	makeCalendar("orderlist-end");
	searchBtnEventHandler();
	setDefaultDate();
});


function setDefaultDate() {
	$('.calendar-date-btn[data-search_date="3M"]').click();
};

function searchBtnEventHandler() {
	$('.orderlist-calendar-wrap .date-search-btn').on('click', function () {
		searchOrderInfoList();
		addShowDetailEvent();
	});
	
	$('.orderlist-calendar-wrap .calendar-date-btn').on('click', function() {
		initCalendar();
		searchOrderInfoListByBtn(this);
		addShowDetailEvent();
	});
}

// 주문건수별 데이터 불러오는 API
function getOrderInfoList(order_status) {
	$.ajax({
		type: "post",
		url: api_location + "mypage/order/list/get",
		data: {
			'order_status': order_status
		},
		dataType: "json",
		async: false,
		error: function (d) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0096", null);
			// alert('주문정보 리스트 조회처리중 오류가 발생했습니다.');
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				
				writeOrderInfoListHtml(order_status, data);
				
				clickSetToggle();
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function searchOrderInfoList() {
	let order_status = $('#param_status').val();
	let order_from = $('.selected-date.start').val();
	let order_to = $('.selected-date.end').val();

	$.ajax({
		type: "post",
		url: api_location + "mypage/order/list/get",
		data: {
			'order_status': order_status,
			'order_to': order_to,
			'order_from': order_from
		},
		dataType: "json",
		async: false,
		error: function (d) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0096", null);
			// alert('주문정보 리스트 조회처리중 오류가 발생했습니다.');
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;

				writeOrderInfoListHtml(order_status, data);
				
				clickSetToggle();
				$(".calendar-date-btn").removeClass("selected");
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function searchOrderInfoListByBtn(obj) {
	let order_status = $('#param_status').val();
	let search_date = obj.dataset.search_date;

	$(".calendar-date-btn").not($(obj)).removeClass("selected");
	$(obj).addClass("selected");

	$.ajax({
		type: "post",
		url: api_location + "mypage/order/list/get",
		data: {
			"order_status": order_status,
			"search_date": search_date
		},
		dataType: "json",
		async: false,
		error: function (d) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0096", null);
			// alert('주문정보 리스트 조회처리중 오류가 발생했습니다.');
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;

				writeOrderInfoListHtml(order_status, data);
				
				clickSetToggle();
				
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function addShowDetailEvent() {
	let orderInfoBtn = document.querySelectorAll(".order-info-btn");

	orderInfoBtn.forEach(btn => {
		btn.addEventListener("click", function() {
			let orderIdx = btn.dataset.order_idx;
			showOrderDetail(orderIdx);
		});
	});
}

//주문상태별 페이지 변경
function changeOrderStatusTab(order_status) {
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

	document.querySelector('.tab_' + order_status).classList.remove('hidden');
	document.querySelector('.order_list_' + order_status).classList.remove('hidden');

	let order_detail = document.querySelector("#mypage_order_detail");

	if (!order_detail.classList.contains('hidden')) {
		order_detail.classList.add('hidden');
	}

	initCalendar();

	getOrderInfoList(order_status);

	addShowDetailEvent();


	$('#param_status').val(order_status);
	clickSetToggle();

	$('.calendar-date-btn[data-search_date="3M"]').click();
}

function initCalendar() {
	let calendarDateBtn = document.querySelectorAll(".calendar-date-btn");

	calendarDateBtn.forEach(btn => btn.classList.remove("selected"));
	
	let dateChoiceBtn = document.querySelectorAll(".date-choice-btn");

	dateChoiceBtn.forEach(btn => btn.innerHTML = `
			<img class="orderlist-calendar-img" src="/images/mypage/mypage_calendar_icon.png">
			<span data-i18n="o_calendar_select">날짜 선택</span>
	`);

	let selectedDate = document.querySelectorAll(".selected-date");

	selectedDate.forEach(date => date.value = "");
}

//주문정보 리스트 append
function writeOrderInfoListHtml(order_status, data) {
	let order_list_tab = document.querySelector(".tab_" + order_status);
	let order_list_container = order_list_tab.querySelector('.order-list-container');

	order_list_container.innerText = "";

	if (data != null && data.length > 0) {
		data.forEach(row => {
			let div_product = "";
			let div_product_cancel = "";
			let div_product_exchange = "";
			let div_product_refund = "";

			let order_product = row.order_product;

			if (order_product != null && order_product.length > 0) {
				//div_product = writeOrderProductListHtml(order_product, row.company_name, row.delivery_num);
				div_product = writeOrderProductListHtml(order_product);
			}

			let order_product_cancel = row.order_product_cancel;

			if (order_product_cancel != null && order_product_cancel.length > 0) {
				//div_product_cancel = writeOrderProductListHtml(order_product_cancel, row.company_name, row.delivery_num);
				div_product_cancel = writeOrderProductListHtml(order_product_cancel);
			}

			let order_product_exchange = row.order_product_exchange;

			if (order_product_exchange != null && order_product_exchange.length > 0) {
				//div_product_exchange = writeOrderProductListHtml(order_product_exchange, row.company_name, row.delivery_num);
				div_product_exchange = writeOrderProductListHtml(order_product_exchange);
			}

			let order_product_refund = row.order_product_refund;

			if (order_product_refund != null && order_product_refund.length > 0) {
				//div_product_refund = writeOrderProductListHtml(order_product_refund, row.company_name, row.delivery_num);
				div_product_refund = writeOrderProductListHtml(order_product_refund);
			}

			let div_order_info = document.createElement('div');
			div_order_info.classList.add('order-list-box');

			let txt_order_detail_btn = getTxtOrderDetailBtn(order_status);
			
			div_order_info.innerHTML = `
				<div class="order-header">
					<div class="order-info">
						<div class="order-number">
							<span data-i18n="m_order_number">주문번호</span>
							<a href="javascript:void(0);" class="docs-creator">
								<span class="order-number-value">${row.order_code}</span>
							</a>
						</div>
						<div class="order-date">
							<span data-i18n="o_order_date">주문날짜</span>
							<a href="javascript:void(0);" class="docs-creator">
								<span class="order-date-value">${row.create_date}</span>
							</a>
						</div>
					</div>
					<div class="order-info-btn" data-order_idx=${row.order_idx}>
						<span>${txt_order_detail_btn}</span>
					</div>
				</div>
				<div class="order-body">
					${div_product}
					${div_product_cancel}
					${div_product_exchange}
					${div_product_refund}
				</div>
			`;

			order_list_container.appendChild(div_order_info);
		});
	} else {
		let oderlistNone = document.createElement("div");
		oderlistNone.className = "oderlist-none-box";

		let no_order_history = "";
		let no_order_cancel_history = "";
		let no_order_exchange_history = "";
		let no_order_return_history = "";

		switch (getLanguage()) {
			case "KR" :
				no_order_history = "<p>주문 내역이 없습니다.</p>";
				no_order_cancel_history = "<p>주문 취소 내역이 없습니다.</p>";
				no_order_exchange_history = "<p>주문 교환 내역이 없습니다.</p>";
				no_order_return_history = "<p>주문 반품 내역이 없습니다.</p>";

				break;

			case "EN" :
				no_order_history = "<p>There are no order history.</p>";
				no_order_cancel_history = "<p>There are no order cancellation history.</p>";
				no_order_exchange_history = "<p>There are no order exchange history.</p>";
				no_order_return_history = "<p>There are no order return history.</p>";
				
				break;

			case "CN" :
				no_order_history = "<p>没有订单记录。</p>";
				no_order_cancel_history = "<p>没有订单取消记录。</p>";
				no_order_exchange_history = "<p>没有订单交换记录。</p>";
				no_order_return_history = "<p>没有订单退货记录。</p>";
				
				break;
		}

		if (order_status == "ALL") {
			oderlistNone.innerHTML = no_order_history;
			
		} else if (order_status == "OCC") {
			oderlistNone.innerHTML = no_order_cancel_history;
		
		} else if (order_status == "OEX") {
			oderlistNone.innerHTML = no_order_exchange_history;
			
		} else if (order_status == "ORF") {
			oderlistNone.innerHTML = no_order_return_history;
			
		}
		order_list_container.appendChild(oderlistNone);
	}
	changeLanguageR();
}

/*------------------- 상품 건 -------------------*/

let orderlistPage = {
	rows: 5,
	page: 1
}

function getTxtOrderDetailBtn(order_status) {
	let txt_order_detail_btn = "";
	
	let country = getLanguage();
	switch (order_status) {
		case "OCC" :
			switch (country) {
				case "KR" :
					txt_order_detail_btn = "주문 취소 내역";
					break;
				
				case "EN" :
					txt_order_detail_btn = "Order cancellation history";
					break;
				
				case "CN" :
					txt_order_detail_btn = "取消订单记录";
					break;
			}
			
			break;
		
		case "OEX" :
			switch (country) {
				case "KR" :
					txt_order_detail_btn = "주문 교환 내역";
					break;
				
				case "EN" :
					txt_order_detail_btn = "Order exchange history";
					break;
				
				case "CN" :
					txt_order_detail_btn = "交换订单记录";
					break;
			}
			
			break;
		
		case "ORF" :
			switch (country) {
				case "KR" :
					txt_order_detail_btn = "주문 반품 내역";
					break;
				
				case "EN" :
					txt_order_detail_btn = "Order return history";
					break;
				
				case "CN" :
					txt_order_detail_btn = "退货订单记录";
					break;
			}
			
			break;
		
		default :
			switch (country) {
				case "KR" :
					txt_order_detail_btn = "주문 상세 내역";
					break;
				
				case "EN" :
					txt_order_detail_btn = "Order detail history";
					break;
				
				case "CN" :
					txt_order_detail_btn = "详情订单记录";
					break;
			}
			break;
	}
	
	return txt_order_detail_btn;
}
