let total_qty = 0;

// 배송메모 셀렉트 박스 설정
let order_memo_tui = new tui.SelectBox('.addr-message-select-box', {
	placeholder: '배송시 요청사항을 선택해주세요.',
	data: [
		// {
		// 	label: document.querySelector("#recent_order_msg").value,
		// 	value: '1'
		// },
		{
			label: '부재 시 문 앞에 놓아주세요.',
			value: '1'
		},
		{
			label: '택배함에 넣어주세요.',
			value: '2'
		},
		{
			label: '파손 위험이 있는 제품입니다. 배송 시 주의 부탁드립니다.',
			value: '3'
		},
		{
			label: '배송 전 연락 주세요.',
			value: '4'
		},
		{
			label: '직접입력',
			value: 'direct'
		}
	],
	autofocus: false
});

//버튼
const calc_wrap = document.querySelector(".calculation-wrap");

const product_toggle_btn = document.querySelector(".product-toggle-btn");

const order_to_list_btn = document.querySelector(".list_addr_btn");
const update_order_to_btn = document.querySelector(".address-info .edit-btn");

const next_step_btn = document.querySelector(".step-btn.next");
const prev_step_btn = document.querySelector(".step-btn.pre");

const $group1 = document.querySelectorAll(".wrapper[data-group='1']");
const $group2 = document.querySelector(".wrapper[data-group='2']");
const $group3 = document.querySelector(".wrapper[data-group='3']");
const $group4 = document.querySelector(".terms-service[data-group='4']");

const calc_point_box = document.querySelector(".calculation-box .point-box");

(function () {
	//let orderWrap = document.querySelector(".product-toggle-btn").offsetParent;
	product_toggle_btn.addEventListener("click", function () {
		document.querySelector(".order-product").querySelector(".product-wrap").classList.toggle("hidden");
	});
})();

//전화번호 하이푼 자동 입렵
const phoneAutoHyphen = (target) => {
	target.value = target.value
		.replace(/[^0-9]/g, '')
		.replace(/^(\d{0,3})(\d{0,4})(\d{0,4})$/g, "$1-$2-$3").replace(/(\-{1,2})$/g, "");
}

document.addEventListener("DOMContentLoaded", function () {
	//paymentWidget.renderPaymentMethods('#payment-method', 15000);
	
	const url_params = new URL(location.href).searchParams;
	const param_value = url_params.get('basket_idx');
	let basket_idx = param_value.split(",");
	
	const post_result = document.createElement("div")
	post_result.classList.add("post-result");
	document.getElementById("postcodify").appendChild(post_result);
	
	getBasketOrderList(basket_idx);
	
	clickUpdateOrderTo();
	postCodifyHandler();
	clickAddOrderTo();
	getOrderToInfoList();
	closeOrderTo();
	changeOrderMemo();
	
	clickTotalMileage();
	
	clickCheckTerms();
	stepBtnHandler();
	
	resizeEvent();
});


window.addEventListener("resize", function () {
	resizeEvent();
});

function getBasketOrderList(basket_idx) {
	$.ajax({
		type: "post",
		url: "/_api/order/pg/get",
		data: {
			"basket_idx": basket_idx,
		},
		dataType: "json",
		error: function () {
			notiModal("결제하기 화면정보 조회처리에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				let data = d.data;
				
				let put_addr = document.querySelector(".edit-box");
				let get_addr = document.querySelector(".save-box");
				
				let product_info = data.product_info;
				if (product_info != null && product_info.length > 0) {
					getProductForGA(product_info);
					setOrderPgInfoList(product_info);
				}
				
				let member_info = data.member_info;
				if (member_info != null) {
					setMemberInfo(member_info);
				}
				
				let order_to_info = data.order_to_info;
				if (order_to_info != null) {
					get_addr.classList.remove("hidden");
					setOrderToInfo(order_to_info);
				} else {
					put_addr.classList.remove("hidden");
				}
				
				setVoucherInfoList(data.voucher_cnt, data.voucher_info);
				
				setMileagePrice();
				//setTotalMileagePrice(false);
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function getProductForGA(items) {
	var products = [];
	if (items != null) {
		items.forEach(function (item) {
			var product = {
				'item_name': item.product_name,
				'item_variant': item.option_name,
				'price': item.sales_price,
				'quantity': item.product_qty,
				'brand': item.brand,
				'item_category': ''
			}
			products.push(product);
		})
	}
	dataLayer.push({
		'event': 'begin_checkout',
		'ecommerce': {
			'items': products
		}
	});
}

function setOrderPgInfoList(data) {
	const body_wrap = document.querySelector(".order-product .body-wrap");
	
	let dom_frag = document.createDocumentFragment();
	
	let width = document.querySelector("body").offsetWidth;
	
	let product_wrap = document.createElement("div");
	product_wrap.classList.add("product-wrap");
	if (width <= 1024) {
		product_wrap.classList.add("hidden");
	}
	
	let product_list_html = "";
	data.forEach(el => {
		let set_toggle_html = "";
		
		let product_type = el.product_type;
		if (product_type != "B") {
			set_toggle_html += `<img class="set_toggle" data-basket_idx="${el.basket_idx}" data-action_type="show" src="/images/mypage/mypage_down_tab_btn.svg">`;
		}
		
		let product_qty = parseInt(el.product_qty);
		total_qty += product_qty;
		
		product_list_html += `
			<div class="body-list product">
				<div class="product-info">
					<a href="" class="docs-creator">
						<img class="prd-img" cnt="1" src="${cdn_img}${el.img_location}" alt="">
					</a>
					<div class="info-box">
						<div class="info-row" data-refund="${el.refund_flg}">
							<div class="name" data-soldout="">
								<span>${el.product_name}</span>
							</div>
						</div>
						<div class="info-row mobile-saleprice">
							<div class="product-price">
								${el.txt_sales_price}
							</div>
						</div>
						<div class="info-row">
							<div class="color-title">
								<span>${el.color}</span>
							</div>
							<div class="color__box" data-maxcount="" data-colorcount="1">
								<div class="color" data-color="${el.color_rgb}" data-soldout="STIN" style="background-color:${el.color_rgb}"></div>
							</div>
						</div>
						<div class="info-row">
							<div class="size__box">
								<li class="size" data-sizetype="" data-soldout="STIN">${el.option_name}</li>
							</div>
						</div>
					</div>
				</div>
				
				<div class="list-row web-saleprice">
					<span class="product-price">
						${el.txt_sales_price}
					</span>
				</div>
				<div class="list-row web-qty">
					<span class="product-count">
						${el.product_qty}
					</span>
				</div>
				<div class="list-row">
					<span class="total_price" data-total_price="${el.total_price}">
						${el.txt_total_price}
					</span>
					${set_toggle_html}
				</div>
			</div>
		`;
		
		if (product_type == "S") {
			let set_product_info = el.set_product_info;
			if (set_product_info != null && set_product_info.length > 0) {
				set_product_info.forEach(function(set) {
					product_list_html += `
						<div class="body-list product set_product hidden" data-parent_idx="${set.parent_idx}">
							<div class="product-info">
								<a href="" class="docs-creator">
									<img class="prd-img" cnt="1" src="${cdn_img}${set.img_location}" alt="">
								</a>
								<div class="info-box">
									<div class="info-row" data-refund="${set.refund_flg}">
										<div class="name" data-soldout="">
											<span>${set.product_name}</span>
										</div>
									</div>
									<div class="info-row mobile-saleprice">
										<div class="product-price"></div>
									</div>
									<div class="info-row">
										<div class="color-title">
											<span>${set.color}</span>
										</div>
										<div class="color__box" data-maxcount="" data-colorcount="1">
											<div class="color" data-color="${set.color_rgb}" data-soldout="STIN" style="background-color:${set.color_rgb}"></div>
										</div>
									</div>
									<div class="info-row">
										<div class="size__box">
											<li class="size" data-sizetype="" data-soldout="STIN">${set.option_name}</li>
										</div>
									</div>
								</div>
							</div>
							
							<div class="list-row web-saleprice"></div>
							<div class="list-row"></div>
						</div>
					`;
				});
			}	
		}
	});

	product_wrap.innerHTML = product_list_html;
	dom_frag.appendChild(product_wrap);
	body_wrap.prepend(dom_frag);
	
	calcPriceProduct();
	clickSetToggle();
}

function clickSetToggle() {
	let set_toggle = document.querySelectorAll('.set_toggle');
	set_toggle.forEach(toggle => {
		toggle.addEventListener('click',function(e) {
			let toggle_btn = e.currentTarget;
			
			let basket_idx = toggle_btn.dataset.basket_idx;
			let action_type = toggle_btn.dataset.action_type;
			
			let set_product = document.querySelectorAll('.set_product');
			set_product.forEach(set => {
				if (set.dataset.parent_idx == basket_idx) {
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

function setMemberInfo(data) {
	document.querySelector(".member_info .member_name").innerHTML = data.member_name;
	document.querySelector(".member_info .member_mobile").innerHTML = data.member_mobile;
	document.querySelector(".member_info .member_email").innerHTML = data.member_email;
}

function setOrderToInfo(data) {
	document.querySelector(".address-info .save-box .to_place").innerHTML = data.to_place;
	document.querySelector(".address-info .save-box .to_name").innerHTML = data.to_name;
	document.querySelector(".address-info .save-box .to_mobile").innerHTML = data.to_mobile;
	document.querySelector(".address-info .save-box .to_zipcode").innerHTML = data.to_zipcode;

	let to_addr = null;
	if (data.to_road_addr == "" || data.to_road_addr == "" == null) {
		to_addr = data.to_lot_addr;
	} else {
		to_addr = data.to_road_addr;
	}

	document.querySelector(".address-info .save-box .to_addr").innerHTML = to_addr;
	document.querySelector(".address-info .save-box .to_detail_addr").innerHTML = data.to_detail_addr;

	$('#to_place').val(data.to_place);
	$('#to_name').val(data.to_name);
	$('#to_mobile').val(data.to_mobile);
	$('#to_zipcode').val(data.to_zipcode);
	$('#to_road_addr').val(data.to_road_addr);
	$('#to_lot_addr').val(data.to_lot_addr);
	$('#to_detail_addr').val(data.to_detail_addr);
}

function clickUpdateOrderTo() {
	update_order_to_btn.addEventListener("click", function () {
		let put_addr_wrap = document.querySelector(".edit-box");
		put_addr_wrap.classList.remove("hidden");
		
		let get_addr_wrap = document.querySelector(".save-box");
		get_addr_wrap.classList.add("hidden");
		
		let list_addr_wrap = document.querySelector(".list-box");
		list_addr_wrap.classList.add("hidden");
		
		$(".edit_box").removeClass("hidden");
	});
}

function postCodifyHandler() {
	$("#postcodify").postcodify({
		insertPostcode: ".tmp_to_zipcode",
		insertAddress: ".tmp_to_road_addr",
		insertExtraInfo: ".tmp_to_lot_addr",
		hideOldAddresses: false,
		results: ".post-change-result",
		hideSummary: true,
		useFullJibeon: true,
		onReady: function () {
			document.querySelector(".post-change-result").style.display = "none";
			$(".postcodify_search_controls .keyword").attr("placeholder", "예) 성동구 연무장길 53, 성수동2가 315-57");
			// $(".post-change-result").hide();
		},
		onSuccess: function () {
			document.querySelector(".post-change-result").style.display = "block";
			$("#postcodify div.postcode_search_status.too_many").hide();
			// $(".post-change-result").hide();
		},
		afterSelect: function (selectedEntry) {
			$("#postcodify div.postcode_search_result").remove();
			$("#postcodify div.postcode_search_status.too_many").hide();
			$("#postcodify div.postcode_search_status.summary").hide();

			document.querySelector(".post-change-result").style.display = "none";
			$("#entry_box").show();
			$("#entry_details").focus();
			$(".postcodify_search_controls .keyword").val($(".tmp_to_road_addr").val());
		}
	});
}

function getOrderToInfoList() {
	order_to_list_btn.addEventListener('click',function() {
		$.ajax({
			type: "post",
			url: "pi/order/pg/to/get",
			dataType: "json",
			error: function () {
				notiModal("배송지 목록 조회처리에 실패했습니다.");
			},
			success: function (d) {
				let code = d.code;
				if (code == 200) {
					let data = d.data;

					let order_to_body = document.querySelector(".addrList-body");
					order_to_body.innerHTML = "";
					
					if (data != null) {
						let get_addr_wrap = document.querySelector(".save-box");
						get_addr_wrap.classList.add("hidden");
						
						let put_addr_wrap = document.querySelector(".edit-box");
						put_addr_wrap.classList.add("hidden");
						
						let order_to_list_wrap = document.querySelector(".list-box");
						
						data.forEach(function (row) {
							let order_to_list_content = document.createElement("div");
							order_to_list_content.className = "addrList-content";

							let order_to_list_html = "";
							order_to_list_html = `
								<div class="to-place">
									${row.to_place}
								</div>
								<div class="addr-list-wrap">
									<div class="cn-box set_order_to" data-order_to_idx="${row.order_to_idx}">
										<div class="addrList-row">
											<span class="to-name">
												${row.to_name}
											</span>
											/
											<span class="to-phone">
												${row.to_mobile}
											</span>
										</div>
										
										<div class="addrList-row">
											(<span class="to-zipcode">
												${row.to_zipcode}
											</span>)
											<span class="to-addr">
												${row.to_road_addr}
											</span>
											<span class="to-detail">
												${row.to_detail_addr}
											</span>
										</div>
									</div>
									<div class="delete-addr" data-order_to_idx="${row.order_to_idx}">
										<u>삭제하기</u>
									</div>
								</div>
							`;

							order_to_list_content.innerHTML = order_to_list_html;
							order_to_body.appendChild(order_to_list_content)
						});
						
						order_to_list_wrap.classList.remove("hidden");
						
						clickSetOrderTo();
						clickDeleteOrderTo();
					} else {
						order_to_list_wrap.classList.add('hidden');
					}
				} else {
					notiModal(d.msg);
				}
			}
		});
	});
}

function closeOrderTo() {
	let close_order_to = document.querySelector('.close_order_to');
	close_order_to.addEventListener('click',function() {
		document.querySelector(".list-box").classList.add("hidden");
		document.querySelector(".save-box").classList.remove("hidden");
	});
}

function clickSetOrderTo(order_to_idx) {
	let set_order_to = document.querySelectorAll('.set_order_to');
	set_order_to.forEach(order_to => {
		order_to.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let order_to_idx = el.dataset.order_to_idx;
			
			if (order_to_idx != null) {
				$.ajax({
					type: "post",
					url: "pi/order/pg/to/get",
					data: {
						'order_to_idx': order_to_idx
					},
					dataType: "json",
					error: function () {
						notiModal("배송지 조회처리에 실패했습니다.");
					},
					success: function (d) {
						let code = d.code;
						if (code == 200) {
							let data = d.data;
							
							if (data != null) {
								let get_addr_wrap = document.querySelector(".save-box");
								let put_addr_wrap = document.querySelector(".edit-box");
								
								let order_to_list_wrap = document.querySelector(".list-box");
								
								data.forEach(function (row) {
									let to_addr = null;
									if (row.to_road_addr == "" || row.to_road_addr == null) {
										to_addr = row.to_lot_addr;
									} else {
										to_addr = row.to_lot_addr;
									}
									
									get_addr_wrap.querySelector(".to_place").innerHTML = row.to_place;
									get_addr_wrap.querySelector(".to_name").innerHTML = row.to_name;
									get_addr_wrap.querySelector(".to_mobile").innerHTML = row.to_mobile;
									get_addr_wrap.querySelector(".to_zipcode").innerHTML = row.to_zipcode;
									get_addr_wrap.querySelector(".to_addr").innerHTML = to_addr;
									get_addr_wrap.querySelector(".to_detail_addr").innerHTML = row.to_detail_addr;

									$('#to_place').val(row.to_place);
									$('#to_name').val(row.to_name);
									$('#to_mobile').val(row.to_mobile);
									$('#to_zipcode').val(row.to_zipcode);
									$('#to_road_addr').val(row.to_road_addr);
									$('#to_lot_addr').val(row.to_lot_addr);
									$('#to_detail_addr').val(row.to_detail_addr);

									get_addr_wrap.classList.remove("hidden");
									put_addr_wrap.classList.add("hidden");
									order_to_list_wrap.classList.add("hidden");
								});
							}
						} else {
							notiModal(d.msg);
						}
					}
				});
			}
		});
	});
}

function clickDeleteOrderTo() {
	let delete_order_to = document.querySelectorAll('.delete-addr');
	delete_order_to.forEach(order_to => {
		order_to.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let order_to_idx = el.dataset.order_to_idx;
			
			if (order_to_idx != null) {
				$.ajax({
					type: "post",
					data: {
						"order_to_idx": to_idx
					},
					dataType: "json",
					url: "/_api/order/pg/to/delete",
					error: function () {
						notiModal("배송지 정보 삭제 처리에 실패했습니다.");
					},
					success: function (d) {
						let code = d.code;
						if (code == 200) {
							getOrderToInfoList();
							notiModal("배송지 정보 삭제 처리에 성공했습니다.");
						} else {
							notiModal(d.msg);
						}
					}
				});
			}
		});
	})
}

function clickAddOrderTo() {
	let add_order_to = document.querySelector(".address-info .save-btn"); // 저장 버튼
	add_order_to.addEventListener("click", function () {
		let put_addr_wrap = document.querySelector(".edit-box");
		let get_addr_wrap = document.querySelector(".save-box");
		let list_addr_wrap = document.querySelector(".list-box");
		let to_place = document.querySelector(".tmp_to_place");
		let to_name = document.querySelector(".tmp_to_name");
		let to_mobile = document.querySelector(".tmp_to_mobile");

		let addrSearch = document.querySelector(".postcodify_search_controls .keyword");

		if (to_place.value === "" || to_place.value == null) {
			to_place.previousElementSibling.classList.add("check");
			return false;
		} else {
			to_place.previousElementSibling.classList.remove("check")
		}

		if (to_name.value === "" || to_name.value == null) {
			to_name.previousElementSibling.classList.add("check");
			return false;
		} else {
			to_name.previousElementSibling.classList.remove("check");
		}

		if (to_mobile.value === "" || to_mobile == null) {
			to_mobile.previousElementSibling.classList.add("check");
			return false;
		} else {
			to_mobile.previousElementSibling.classList.remove("check");
		}

		if (addrSearch.value.length == 0) {
			if (document.querySelector(".tmp_to_zipcode").value === document.querySelector(".postcodify_search_controls .keyword").value) {
				notiModal("배송지를 선택해주세요.");
			}
			return false;
		}

		addOrderToInfo();
		
		document.querySelector("#to_place").value = to_place.value;
		document.querySelector("#to_name").value = to_name.value;
		document.querySelector("#to_mobile").value = to_mobile.value;
		document.querySelector("#to_zipcode").value = put_addr_wrap.querySelector(".tmp_to_zipcode").value;
		document.querySelector("#to_road_addr").value = put_addr_wrap.querySelector(".tmp_to_road_addr").value;
		document.querySelector("#to_lot_addr").value = put_addr_wrap.querySelector(".tmp_to_lot_addr").value;
		document.querySelector("#to_detail_addr").value = put_addr_wrap.querySelector(".tmp_to_detail_addr").value;

		let resetInput = document.querySelectorAll(".edit-box .input-row input");
		resetInput.forEach((el) => {
			el.value = "";
		});
		$('.add_flg').prop('checked', false);

		get_addr_wrap.classList.remove("hidden");
		put_addr_wrap.classList.add("hidden");
		list_addr_wrap.classList.add("hidden");
	});
}

function addOrderToInfo() {
	let put_addr_wrap = document.querySelector(".edit-box");
	let to_place = put_addr_wrap.querySelector(".tmp_to_place").value;
	let to_name = put_addr_wrap.querySelector(".tmp_to_name").value;
	let to_mobile = put_addr_wrap.querySelector(".tmp_to_mobile").value;
	let to_zipcode = put_addr_wrap.querySelector(".tmp_to_zipcode").value;
	let to_road_addr = put_addr_wrap.querySelector(".tmp_to_road_addr").value;
	let to_lot_addr = put_addr_wrap.querySelector(".tmp_to_lot_addr").value;
	let to_detail_addr = put_addr_wrap.querySelector(".tmp_to_detail_addr").value;

	//변경된 주소 박스 
	let to_place_text = document.querySelector(".save-box .to_place");
	let to_name_text = document.querySelector(".save-box .to_name");
	let to_mobile_text = document.querySelector(".save-box .to_mobile");
	let to_zipcode_text = document.querySelector(".save-box .to_zipcode");
	let to_road_addr_text = document.querySelector(".save-box .to_addr");
	let to_detail_addr_text = document.querySelector(".save-box .to_detail_addr");

	to_place_text.innerHTML = to_place;
	to_name_text.innerHTML = to_name;
	to_mobile_text.innerHTML = to_mobile;
	to_zipcode_text.innerHTML = to_zipcode;
	to_road_addr_text.innerHTML = to_road_addr;
	to_detail_addr_text.innerHTML = to_detail_addr;

	if ($('.add_flg').prop('checked') == true) {
		$.ajax({
			type: "POST",
			data: {
				'to_place': to_place,
				'to_name': to_name,
				'to_mobile': to_mobile,
				'to_zipcode': to_zipcode,
				'to_road_addr': to_road_addr,
				'to_lot_addr': to_lot_addr,
				'to_detail_addr': to_detail_addr,
			},
			dataType: "json",
			url: "/_api/order/pg/to/add",
			error: function () {
				notiModal("배송지 저장중 오류가 발생했습니다.");
			},
			success: function (d) {
				notiModal("배송지 저장에 성공했습니다.");
			}
		});
	}
}

function changeOrderMemo() {
	order_memo_tui.on('change', ev => {
		let tmp_order_memo = document.querySelector("#tmp_order_memo");
		let message_value = document.querySelector(".save-message-box .save-message-value");
		
		let select_label = ev.curr.getLabel();
		let select_value = ev.curr.getValue();
		
		if (select_value === "direct") {
			tmp_order_memo.style.display = "block";
			tmp_order_memo.addEventListener("input", function (e) {
				message_value.value = e.target.value;
			});
		} else {
			tmp_order_memo.style.display = "none";
			message_value.value = select_label;
		}
	});
}

// 바우처 정보 셀렉트 박스 설정
function setVoucherInfoList(voucher_cnt,voucher_info) {
	let usable_cnt = 0;
	
	let tui_select_data = [
		{
			'label': '선택안함',
			'value': false
		}
	];

	let voucher_info_tui = null;
	if (voucher_cnt > 0 && voucher_info.length > 0 && voucher_info != null) {
		usable_cnt = voucher_info.length;
		
		voucher_info.forEach(function (voucher) {
			let voucher_data = {
				'voucher_idx': voucher.voucher_idx,
				'sale_type': voucher.sale_type,
				'sale_price': voucher.sale_price,
				'mileage_flg': voucher.mileage_flg
			};

			let tui_voucher_data = {
				'label': voucher.voucher_name,
				'value': JSON.stringify(voucher_data)
			};

			tui_select_data.push(tui_voucher_data);
		});		
	}
	
	voucher_info_tui = new tui.SelectBox('.voucher-select-box',
		{
			placeholder: '사용가능 쿠폰 ' + voucher_cnt + '장 / 보유 ' + usable_cnt + '장',
			data: tui_select_data
		}
	);

	voucher_info_tui.on('change', ev => {
		let select_label = ev.curr.getLabel();
		let select_value = ev.curr.getValue();
		
		let price_discount = document.querySelector(".calculation-wrap .price_discount");
		
		if (select_value != "false") {
			let voucher_info = JSON.parse(select_value);

			$('#voucher_idx').val(voucher_info['voucher_idx']);

			let sale_type = voucher_info['sale_type'];
			let sale_price = voucher_info['sale_price'];

			let tmp_discount = 0;
			if (sale_type == "PRC") {
				tmp_discount = voucher_info['sale_prices'];
			} else if (sale_type == "PER") {
				let price_product = document.querySelector(".calculation-wrap .price_product_wrap .price_product").dataset.price_product;
				tmp_discount = (price_product * (parseInt(sale_price) / 100));
			}
			
			price_discount.innerHTML = tmp_discount.toLocaleString('ko-KR');
			price_discount.dataset.price_discount = tmp_discount;

			let mileage_flg = voucher_info['mileage_flg'];
			if (mileage_flg == false) {
				//마일리지 중복 사용 가능
				$('#use_mileage').attr('disabled', false);
				$('.mileage_point_btn').attr('onClick','setTotalMileagePrice(true);');
			} else {
				//마일리지 중복 사용 불가
				$('#use_mileage').attr('disabled', true);
				$('.mileage_point_btn').attr('onClick','return false;');
				
				$('#use_mileage').val(0);
				$('#price_mileage_point').val(0);
				
				let price_mileage_point = document.querySelector(".price_mileage_point");
				price_mileage_point.dataset.price_mileage_point = 0;
				price_mileage_point.innerHTML = 0;
			}
		} else {
			$('#voucher_idx').val(0);
			
			$('#use_mileage').attr('disabled', false);
			$('.mileage_point_btn').attr('onClick','setTotalMileagePrice(true);');
			
			price_discount.dataset.price_discount = 0;
			price_discount.innerHTML = 0;
		}
		
		calcPriceTotal();
	});
}

function setMileagePrice() {
	$('#use_mileage').keyup(function () {
		let basket_idx = $('#basket_idx').val().split(",");
		var param_mileage = parseInt($('#use_mileage').val().replace(',',''));
		var tmp_mileage_price = "";

		if (isNaN(param_mileage)) {
			tmp_mileage_price = 0;
		} else {
			tmp_mileage_price = param_mileage;
		}

		$.ajax({
			type: "post",
			url: "/_api/mileage/check",
			data: {
				'param_mileage': tmp_mileage_price,
				'basket_idx': basket_idx
			},
			dataType: "json",
			error: function () {
				notiModal("적립금 정보 조회처리중 오류가 발생했습니다.");
			},
			success: function (d) {
				let price_mileage_point = document.querySelector(".price_mileage_point");
				
				if (d.code == 200) {
					let mileage_point = 0;
					let txt_mileage_point = 0;
					
					let data = d.data;
					if (data != null) {
						mileage_point = data.mileage_point;
						txt_mileage_point = data.txt_mileage_point;
					}
					
					price_mileage_point.dataset.price_mileage_point = mileage_point;
					price_mileage_point.innerHTML = txt_mileage_point;
					
					$('#use_mileage').val(txt_mileage_point);
					$('#price_mileage_point').val(mileage_point);
				} else {
					$('#use_mileage').val('');
					$('#price_mileage_point').val('');
					
					notiModal(d.msg);
				}
				
				calcPriceTotal();
			}
		});
	});
}

function clickTotalMileage() {
	let total_mileage = document.querySelector('.total_mileage');
	total_mileage.addEventListener('click',function() {
		setTotalMileagePrice(true);
	});
}

function setTotalMileagePrice(calc_flg) {
	let basket_idx = $('#basket_idx').val().split(",");
	
	$.ajax({
		type: "post",
		data: {
			'basket_idx' : basket_idx
		},
		dataType: "json",
		url: "/_api/mileage/get",
		error: function () {
			notiModal("적립금 정보 조회처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null) {
					let price_mileage_point = document.querySelector(".price_mileage_point");
					
					if (calc_flg == true) {
						$('#use_mileage').val(data.total_mileage_point);
						$('#txt_total_mileage').text(data.txt_mileage_point);
						
						$('#price_mileage_point').val(data.total_mileage_point);
						
						price_mileage_point.dataset.price_mileage_point = data.total_mileage_point;
						price_mileage_point.innerHTML = data.txt_mileage_point;
						
						$('.mileage_point_msg').text('적용취소');
						$('.mileage_point_btn').attr('onClick','setTotalMileagePrice(false);');
					} else {
						$('#use_mileage').val(0);
						$('#txt_total_mileage').text('');
						
						$('#price_mileage_point').val(0);
						
						price_mileage_point.dataset.price_mileage_point = 0;
						price_mileage_point.innerHTML = 0;
						
						$('.mileage_point_msg').text('모두적용');
						$('.mileage_point_btn').attr('onClick','setTotalMileagePrice(true);');
					}
					
					calcPriceTotal();
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function clickCheckTerms() {
	let order_section = document.querySelector(".order-section");
	
	let check_terms = document.querySelectorAll('.check_terms');
	check_terms.forEach(check => {
		check.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let check_type = el.dataset.check_type;
			
			let order_status = "";
			if (check_type == "ALL") {
				if (el.checked) {
					$('.check_terms').prop('checked',true);
					order_status = "T";
				} else {
					$('.check_terms').prop('checked',false);
					order_status = "F";
				}
			} else if (check_type == "ESS") {
				if (el.checked) {
					order_status = "T";
				} else {
					order_status = "F";
				}
			}
			
			order_section.dataset.status = order_status;
		});
	});
}

function stepBtnHandler() {
	/* 다음단계, 이전단계 */
	prev_step_btn.addEventListener("click", function () {
		let prev_step_level = prev_step_btn.dataset.step;
		let next_step_level = next_step_btn.dataset.step;
		
		if (prev_step_level == "0") {
			window.location.href = "/order/basket/list";
		} else {
			prev_step_btn.dataset.step = "0";
			next_step_btn.dataset.step = "1";
			
			calc_wrap.dataset.step = "1";
			next_step_btn.querySelector("span").innerHTML = "다음 단계";
			
			let header_box_btn = document.querySelector('.header-box-btn');
			header_box_btn.classList.remove("hidden");
			
			update_order_to_btn.classList.remove("hidden");
			order_to_list_btn.classList.remove("hidden");
			
			$group1.forEach(el => {
				el.classList.remove("next");
			});
			
			//terms-service
			$group4.classList.add("hidden");
			
			//배송메시지 박스 
			document.querySelector(".edit-message-box").classList.remove("hidden");
			document.querySelector(".save-message-box").classList.add("hidden");

			calc_point_box.classList.add("hidden");
			calcPriceTotal();
		}
	});

	next_step_btn.addEventListener("click", function () {
		let prev_step_level = prev_step_btn.dataset.step;
		let next_step_level = next_step_btn.dataset.step;
		
		let put_addr_wrap = document.querySelector(".edit-box");
		let get_addr_wrap = document.querySelector(".save-box");
		
		let order_section = document.querySelector(".order-section");
		
		let to_name = document.querySelector("#to_name").value;
		let to_mobile = document.querySelector("#to_mobile").value;
		let to_zipcode = document.querySelector("#to_zipcode").value;
		
		if (to_name == '' || to_mobile == '' || to_zipcode == '') {
			notiModal("빈칸 없이 모두 기입해주세요.");
			return false;
		}
		
		if (next_step_level == 2) {
			if (order_section.dataset.status === "F") {
				notiModal("이용약관에 동의가 필요합니다.");
				return false;
			} else if (order_section.dataset.status === "T") {
				addTmpOrderInfo();
			}
		} else {
			next_step_btn.dataset.step = 2;
			prev_step_btn.dataset.step = 1;
			
			calc_wrap.dataset.step = 2;
			
			order_section.dataset.status = "F";
			$('.check_terms').prop('checked',false);

			if (next_step_btn.dataset.step === "2") {
				next_step_btn.querySelector("span").innerHTML = "결제하기";
			}
			
			$group1.forEach(el => {
				el.classList.add("next");
			});
			
			$group2.classList.add("next");
			
			$group3.classList.add("next");
			if ($group3.classList.contains("next")) {
				document.querySelector(".address-info.next .header-box-btn").classList.add("hidden");
			}

			//terms-service
			$group4.classList.remove("hidden");

			//배송메시지 박스 
			document.querySelector(".edit-message-box").classList.add("hidden");
			document.querySelector(".save-message-box").classList.remove("hidden");
			
			calc_point_box.classList.remove("hidden");
			
			get_addr_wrap.classList.remove("hidden");
			put_addr_wrap.classList.add("hidden");
			
			// 배송메시지
			let messageContent = document.querySelector(".save-message-box .message-content");
			let messageValue = document.querySelector(".save-message-box .save-message-value").value;
			if (messageValue.length > 0) {
				messageContent.innerHTML = messageValue;
				$('#order_memo').val(messageValue);
				// 최근 메세지 
				// let resent_msg_strg = document.querySelector("#recent_order_msg").value;
				// let resent_msg_label = order_memo_tui.dropdown.items[0].label;
				// document.querySelector("#recent_order_msg").value = $('#order_memo').val();
				// console.log(document.querySelector("#recent_order_msg").value);
				// order_memo_tui.dropdown.items[0].label = '(최근) ' + document.querySelector("#recent_order_msg").value;
			}
			
			calcPriceTotal();
		}
	});
}

function calcPriceProduct() {
	let product_price = 0;
	
	let total_price = document.querySelectorAll(".product .total_price");
	total_price.forEach(price => {
		product_price += parseInt(price.dataset.total_price);
	}) ;
	
	//합산
	let price_product = document.querySelector(".calculation-wrap .price_product_wrap .price_product");
	price_product.dataset.price_product = product_price;
	price_product.innerHTML = product_price.toLocaleString("ko-KR");
	
	let product_qty = document.querySelector(".calculation-wrap .product-qty");
	product_qty.innerHTML = total_qty;
	
	//배송비 처리
	let delivery_price = 0;
	if (product_price < 80000) {
		delivery_price = 2500;
	}
	
	let price_delivery = document.querySelector(".calculation-wrap .price_delivery");
	price_delivery.dataset.price_delivery = delivery_price;
	price_delivery.innerHTML = delivery_price.toLocaleString("ko-KR");;

	calcPriceTotal();
}

function calcPriceTotal() {
	let step = calc_wrap.dataset.step;
	
	let price_product = parseInt(document.querySelector(".calculation-wrap .price_product_wrap .price_product").dataset.price_product);
	let price_discount = parseInt(document.querySelector(".calculation-wrap .price_discount").dataset.price_discount);
	let price_mileage_point = parseInt(document.querySelector(".calculation-wrap .price_mileage_point").dataset.price_mileage_point);
	// let price_charge_point = parseInt(document.querySelector(".calculation-wrap .price_charge_point").dataset.price_charge_point);
	let price_delivery = parseInt(document.querySelector(".calculation-wrap .price_delivery").dataset.price_delivery);
	
	let calc_result = price_product - price_discount - price_mileage_point + price_delivery;
	
	let price_total = document.querySelector(".calculation-wrap .price_total");
	price_total.innerHTML = calc_result.toLocaleString("ko-KR");
}

function addTmpOrderInfo() {
	let frm = $("#frm-check")[0];
	let formData = new FormData(frm);

	/*
	$.ajax({
		type: "post",
		url: "/_api/order/pg/tmp",
		data: formData,
		dataType: "json",
		async: true,
		enctype: "multipart/form-data",
		processData: false,
		contentType: false,
		error: function () {
			notiModal("주문 정보 등록처리에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				let order_info = d.data;
				setTossPayment(order_info);
			}
		}
	});
	*/
	tossWidgetModal();
	
	//paymentWidget.renderAgreement('#agreement');
	
	const paymentWidget = PaymentWidget(client_key, customerKey);  // 결제위젯 초기화
	paymentWidget.renderPaymentMethods(
		"#payment-method", {
			value: 1_5000
		}
	);
	
	/*
	paymentWidget.requestPayment({
		orderId: "zDOSvwlPbEF1Wod0c1_0c",
		orderName: "토스 티셔츠 외 2건",
		successUrl: "https://my-store.com/success",
		failUrl: "https://my-store.com/fail",
		customerEmail: "customer123@gmail.com",
		customerName: "김토스"
	})
	*/
}

function setTossPayment(order_info) {
	tossPayments.requestPayment('카드', {
		amount: order_info.price_total,
		orderId: order_info.order_code,
		orderName: order_info.order_title,
		customerName: order_info.member_name,
		successUrl: '/order/check',
		failUrl: '/order/check',
	});
}

function resizeEvent() {
	const web_content = document.querySelector(".content.web");
	const mobile_content = document.querySelector(".content.mobile");
	
	const change_order_wrap = document.querySelector(".content .order-product");
	const body_width = document.querySelector("body").offsetWidth;
	const header_list = document.querySelector(".order-product").querySelector(".header-list")
	
	if (1024 <= body_width) {
		web_content.appendChild(change_order_wrap);
		
		product_toggle_btn.classList.add("hidden");
		header_list.classList.remove("hidden");
	} else if (1024 >= body_width) {
		mobile_content.appendChild(change_order_wrap);
		
		product_toggle_btn.classList.remove("hidden");
		header_list.classList.add("hidden");
		
	}
}
