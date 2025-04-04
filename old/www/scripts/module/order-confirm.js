let total_qty = 0;

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

let tui_voucher_info = null;
let tui_order_memo = null;

(function () {
	//let orderWrap = document.querySelector(".product-toggle-btn").offsetParent;
	product_toggle_btn.addEventListener("click", function () {
		document.querySelector(".order-product").querySelector(".product-wrap").classList.toggle("hidden");
	});
})();


function addMobileHyprenEvent() {
	let mobileInput = document.querySelector(".order-section .tmp_to_mobile");

	mobileInput.addEventListener("input", function (e) {
		phoneAutoHyphen(e.target);
	});
}

//전화번호 하이푼 자동 입력
const phoneAutoHyphen = (target) => {
	target.value = target.value
		.replace(/[^0-9]/g, '')
		.replace(/^(\d{0,3})(\d{0,4})(\d{0,4})$/g, "$1-$2-$3").replace(/(\-{1,2})$/g, "");
}

document.addEventListener("DOMContentLoaded", function () {
	//paymentWidget.renderPaymentMethods('#payment-method', 15000);
	const url_params = new URL(location.href).searchParams;
	const param_value = url_params.get('basket_idx');
	
	let basket_idx = new Array();
	if (param_value != null) {
		basket_idx = param_value.split(",");
	}

	if (country == 'KR') {
		$('.order-to-EN').remove();
		$('.order-to-CN').remove();
		const post_result = document.createElement("div")
		post_result.classList.add("post-result");
		document.getElementById("postcodify").appendChild(post_result);
	} else if (country == 'EN') {
		$('.order-to-KR').remove();
		$('.order-to-CN').remove();

		getCountryInfo('EN', null, null);
	} else if (country == 'CN') {
		$('.order-to-KR').remove();
		$('.order-to-EN').remove();

		getCountryInfo('CN', null, null);
	}

	getBasketOrderList(basket_idx);

	clickUpdateOrderTo();
	postCodifyHandler();
	clickAddOrderTo();
	getOrderToInfoBtnEventHandler();
	closeOrderTo();

	//clickTotalMileage();
	clickMileagePointBtn();
	checkMileagePrice();

	clickCheckTerms();
	stepBtnHandler();

	resizeEvent();

	initTotalMileage();
	addMobileHyprenEvent();
});


window.addEventListener("resize", function () {
	resizeEvent();
});

let countrySelectBox = null;

function getCountryInfo(country, selCountry, selProvince) {
	let countryInfo = [];
	let countryDisabled = false;
	let detaultCountry = null;

	$.ajax({
		url: api_location + "account/country/get",
		type: 'POST',
		headers: {
			"country": country
		},
		dataType: 'json',
		error: function (d) {
			// notiModal("해외 국가 정보얻기에 실패 했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0002', null);
		},
		success: function (d) {

			if (d.code == "200") {
				if (d.data != null) {
					countryInfo = d.data;
					if (selCountry == null) {
						detaultCountry = d.data[0].value;
					} else {
						detaultCountry = selCountry;
					}
				}
			} else {
				notiModal(d.msg);
			}

			if (country == 'CN') {
				countryDisabled = true;
			}

			$('.country-select-box').html('');
			countrySelectBox = new tui.SelectBox('.country-select-box', {
				data: countryInfo,
				autofocus: false,
				disabled: countryDisabled
			});

			countrySelectBox.select(detaultCountry);
			$('.order-to-' + country + ' input.tmp_country_code').val(detaultCountry);
			getProvinceInfo(detaultCountry, selProvince);
			countrySelectBox.on("change", ev => {
				let country_value = ev.curr.getValue();
				$('.order-to-' + country + ' input.tmp_country_code').val(country_value);
				getProvinceInfo(country_value, selProvince);
			});
		}
	});
}

let provinceSelectBox = null;

function getProvinceInfo(country_code, province_idx) {
	let provinceInfo = [];
	let provinceFlg = true;
	let detaultProvince = null;
	$.ajax(
		{
			url: api_location + "account/province/get",
			type: 'POST',
			data: {
				'country_code': country_code
			},
			dataType: 'json',
			error: function () {
				// notiModal("해외 시/도 정보얻기에 실패 했습니다.");
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0001', null);
			},
			success: function (d) {

				if (d.code == "200") {
					provinceFlg = !d.province_flg;
					if (d.province_flg == true) {
						if (d.data != null) {
							provinceInfo = d.data;
							if (province_idx == null) {
								detaultProvince = d.data[0].value;
							} else {
								detaultProvince = province_idx;
							}
						}
					}
				}
				else {
					notiModal(d.msg);
				}
				$('.order-to-' + country + ' input.tmp_province_idx').val(detaultProvince);
				$('.province-select-box').html('');
				provinceSelectBox = new tui.SelectBox('.province-select-box', {
					data: provinceInfo,
					autofocus: false,
					disabled: provinceFlg
				});

				provinceSelectBox.select(province_idx);

				provinceSelectBox.on("change", ev => {
					let province_value = ev.curr.getValue();
					$('.order-to-' + country + ' input.tmp_province_idx').val(province_value);
				});
			}
		}
	);
}
function getBasketOrderList(basket_idx) {
	$.ajax({
		type: "post",
		url: api_location + "order/pg/get",
		data: {
			"basket_idx": basket_idx,
		},
		dataType: "json",
		async: false,
		error: function () {
			// notiModal("결제하기 화면정보 조회처리에 실패했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0073', null);
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				let data = d.data;

				let put_addr = document.querySelector(".edit-box");
				let putBtn = document.querySelector(".header-btn.edit-btn");
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

				if (Array.isArray(order_to_info) && order_to_info.length == 0) {
					//initOrderInput();
					initOrderTo();
					put_addr.classList.remove("hidden");
					get_addr.classList.add("hidden");
					putBtn.dataset.i18n = "o_cancel";
					putBtn.dataset.addr_filled = "false";
				} else {
					get_addr.classList.remove("hidden");
					setOrderToInfo(order_to_info);
					putBtn.dataset.i18n = "p_edit";
					putBtn.dataset.addr_filled = "true";
				}
				
				setVoucherInfoList(data.cnt_voucher_total,data.cnt_voucher_usable,data.voucher_info);

				setOrderMemoInfoList(data.order_memo_info);

				//setMileagePrice();
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

function createColorBox(data, status) {
	let colorBoxHtml = "";
	let colorMulti = data.color_rgb.split(";");

	if (colorMulti.length > 1) {
		colorBoxHtml = `
			<div class="color__box" data-maxcount="" data-colorcount="1" style="--background:linear-gradient(90deg, ${colorMulti[0]} 50%, ${colorMulti[1]} 50%);">
					<div class="color-title">
							<span>${data.color}</span>
					</div>
					<div class="color multi" data-color="${data.color_rgb}" data-soldout="${status}"></div>
			</div>
		`;
	} else {
		colorBoxHtml = `
			<div class="color__box" data-maxcount="" data-colorcount="1" style="--background-color:${colorMulti[0]}">
					<div class="color-title">
							<span>${data.color}</span>
					</div>
					<div class="color" data-color="${data.color_rgb}" data-soldout="${status}"></div>
			</div>
		`;
	}

	return colorBoxHtml;
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
		let colorBoxHtml = createColorBox(el, "STIN");
		// <div class="color__box" data-maxcount="" data-colorcount="1">
		// 						<div class="color" data-color="${el.color_rgb}" data-soldout="STIN" style="background-color:${el.color_rgb}"></div>
		// 					</div>`

		let product_type = el.product_type;
		if (product_type != "B") {
			set_toggle_html += `<img class="set_toggle" data-basket_idx="${el.basket_idx}" data-action_type="show" src="/images/mypage/mypage_down_tab_btn.svg">`;
		}

		let product_qty = parseInt(el.product_qty);
		total_qty += product_qty;
		
		let refund_msg = "";
		
		let txt_refund_flg = "";
		if (el.refund_flg == true) {
			txt_refund_flg = "true";
			refund_msg = `<span class="refund_msg" data-i18n="s_no_ex_re"></span>`;
		} else {
			txt_refund_flg = "false";
		}

		product_list_html += `
			<div class="body-list product">
				<div class="product-info">
					<a href="" class="docs-creator">
						<img class="prd-img" cnt="1" src="${cdn_img}${el.img_location}" alt="">
					</a>
					<div class="info-box">
						<div class="info-row" data-refund="${txt_refund_flg}">
							<div class="name" data-soldout="">
								<span class="prod_name">${el.product_name}</span>
								${refund_msg}
							</div>
						</div>
						<div class="info-row mobile-saleprice">
							<div class="product-price">
								${el.txt_sales_price}
							</div>
						</div>
						<div class="info-row">
							${colorBoxHtml}
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
					<span class="total_price" data-total_price="${el.product_price}">
						${el.txt_product_price}
					</span>
					${set_toggle_html}
				</div>
			</div>
		`;

		if (product_type == "S") {
			let set_product_info = el.set_product_info;
			if (set_product_info != null && set_product_info.length > 0) {
				set_product_info.forEach(function (set) {
					let colorBoxHtml = createColorBox(set, "STIN");
					// <div class="color__box" data-maxcount="" data-colorcount="1">
					// 						<div class="color" data-color="${set.color_rgb}" data-soldout="STIN" style="background-color:${set.color_rgb}"></div>
					// 					</div>

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
										${colorBoxHtml}
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
		toggle.addEventListener('click', function (e) {
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
	let to_detail_addr = null;
	if (country == 'KR') {
		if (data.to_road_addr == "" || data.to_road_addr == "" == null) {
			to_addr = data.to_lot_addr;
		} else {
			to_addr = data.to_road_addr;
		}
		to_detail_addr = data.to_detail_addr;
	} else if (country == 'EN' || country == "CN") {
		to_detail_addr = data.to_address;
		to_addr = `${data.to_city}, ${data.to_province_name}, ${data.to_country_name}`;
	}

	document.querySelector(".address-info .save-box .to_addr").innerHTML = to_addr;
	document.querySelector(".address-info .save-box .to_detail_addr").innerHTML = to_detail_addr;

	$('#to_place').val(data.to_place);
	$('#to_name').val(data.to_name);
	$('#to_mobile').val(data.to_mobile);
	$('#to_zipcode').val(data.to_zipcode);
	$('#to_road_addr').val(data.to_road_addr);
	$('#to_lot_addr').val(data.to_lot_addr);
	$('#to_detail_addr').val(data.to_detail_addr);

	$('#to_country_code').val(data.to_country_code);
	$('#to_province_idx').val(data.to_province_idx);
	$('#to_city').val(data.to_city);
	$('#to_address').val(data.to_address);
}

function clickUpdateOrderTo() {
	update_order_to_btn.addEventListener("click", function () {
		let country = getLanguage();

		//initOrderInput();

		if (update_order_to_btn.dataset.addr_filled == "false") {
			// notiModal("배송지를 입력해주세요.");
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0040', null);
			return false;

		} else {
			let edit_box = document.querySelector(".edit-box");

			if (edit_box.classList.contains("hidden") == true) {
				update_order_to_btn.dataset.i18n = "o_cancel";

				// 기존 배송지 정보 기입
				edit_box.querySelector(".tmp_to_place").value = document.querySelector("#to_place").value;
				edit_box.querySelector(".tmp_to_name").value = document.querySelector("#to_name").value;
				edit_box.querySelector(".tmp_to_mobile").value = document.querySelector("#to_mobile").value;

				if (country == "KR") {
					edit_box.querySelector(".tmp_to_zipcode").value = document.querySelector("#to_zipcode").value;
					edit_box.querySelector(".edit-box .keyword").value = document.querySelector("#to_road_addr").value;
					edit_box.querySelector(".tmp_to_road_addr").value = document.querySelector("#to_road_addr").value;
					edit_box.querySelector(".tmp_to_lot_addr").value = document.querySelector("#to_lot_addr").value;
					edit_box.querySelector(".tmp_to_detail_addr").value = document.querySelector("#to_detail_addr").value;

				} else if (country == "EN" || country == "CN") {

					let country_code = document.querySelector("#to_country_code").value;
					let province_idx = document.querySelector("#to_province_idx").value;

					edit_box.querySelector(".tmp_zipcode").value = document.querySelector("#to_zipcode").value;
					getCountryInfo(country, country_code, province_idx);


					edit_box.querySelector(".tmp_city").value = document.querySelector("#to_city").value;
					edit_box.querySelector(".tmp_address").value = document.querySelector("#to_address").value;
				}

			} else {
				update_order_to_btn.dataset.i18n = "p_edit";
			}
		}

		initOrderTo();
		changeLanguageR();
	});
}

function initOrderTo() {
	let put_addr_wrap = document.querySelector(".edit-box");
	let get_addr_wrap = document.querySelector(".save-box");
	let stepBtnWrap = document.querySelector(".step-btn-wrap");
	let list_addr_wrap = document.querySelector(".list-box");

	if (put_addr_wrap.classList.contains("hidden") == true) {
		put_addr_wrap.classList.remove("hidden");
		get_addr_wrap.classList.add("hidden");
		list_addr_wrap.classList.add("hidden");
		stepBtnWrap.classList.add("hidden");

	} else {
		put_addr_wrap.classList.add("hidden");
		get_addr_wrap.classList.remove("hidden");
		list_addr_wrap.classList.add("hidden");
		stepBtnWrap.classList.remove("hidden");
	}
}

function initOrderInput() {
	let put_addr_wrap = $(".edit-box");
	let country = getLanguage();

	put_addr_wrap.find('input').val('');

	if (country == 'EN' || country == 'CN') {
		getCountryInfo(country);
	}
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
		},
		onSuccess: function () {
			document.querySelector(".post-change-result").style.display = "block";
			$("#postcodify div.postcode_search_status.too_many").hide();
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
function getOrderToInfoBtnEventHandler() {
	order_to_list_btn.addEventListener('click', function () {
		getOrderToInfoList();
	});
}
function getOrderToInfoList() {
	$.ajax({
		type: "post",
		url: api_location + "order/pg/to/get",
		dataType: "json",
		error: function () {
			// notiModal("배송지 목록 조회처리에 실패했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0048', null);
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				let data = d.data;

				let order_to_body = document.querySelector(".addrList-body");
				order_to_body.innerHTML = "";

				let order_to_list_wrap = document.querySelector(".list-box");

				if (data != null) {
					data.forEach(function (row) {
						let order_to_list_content = document.createElement("div");
						order_to_list_content.className = "addrList-content";

						let toAddrStr = '';
						let toDetailStr = '';
						if (country == 'KR') {
							toAddrStr = row.to_road_addr;
							toDetailStr = row.to_detail_addr;
						} else if (country == 'EN' || country == 'CN') {
							toDetailStr = row.to_address;
							toAddrStr = `${row.to_city}, ${row.to_province_name}, ${row.to_country_name}`;
						}

						let order_to_list_html = "";
						order_to_list_html = `
							<div class="to-place" data-order_to_idx="${row.order_to_idx}">
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
											${toAddrStr}
										</span>
										<span class="to-detail">
											${toDetailStr}
										</span>
									</div>
								</div>
								<div class="delete-addr" data-order_to_idx="${row.order_to_idx}">
									<u data-i18n="p_do_delete">삭제하기</u>
								</div>
							</div>
						`;

						order_to_list_content.innerHTML = order_to_list_html;
						order_to_body.appendChild(order_to_list_content)
					});

					order_to_list_wrap.classList.remove("hidden");

					clickSetOrderTo();
					clickDeleteOrderTo();
					changeLanguageR();
				} else {
					order_to_list_wrap.classList.add('hidden');
					// notiModal("등록된 배송지가 없습니다.");
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0045', null);
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function closeOrderTo() {
	let close_order_to = document.querySelector('.close_order_to');

	close_order_to.addEventListener("click", function () {
		document.querySelector(".list-box").classList.add("hidden");
	});
}

function clickSetOrderTo(order_to_idx) {
	let set_order_to = document.querySelectorAll('.set_order_to');
	let to_place = document.querySelectorAll('.to-place');

	let clickHandler = function (e) {
		let el = e.currentTarget;
		let order_to_idx = el.dataset.order_to_idx;
		if (order_to_idx != null) {
			$.ajax({
				type: "post",
				url: api_location + "order/pg/to/get",
				data: {
					'order_to_idx': order_to_idx
				},
				dataType: "json",
				error: function () {
					// notiModal("배송지 조회처리에 실패했습니다.");
					makeMsgNoti(getLanguage(), 'MSG_F_ERR_0040', null);
				},
				success: function (d) {
					let code = d.code;
					if (code == 200) {
						let data = d.data;

						if (data != null) {
							let get_addr_wrap = document.querySelector(".save-box");
							let put_addr_wrap = document.querySelector(".edit-box");
							let step_btn_wrap = document.querySelector(".step-btn-wrap");
							let putBtn = document.querySelector(".header-btn.edit-btn");
							let order_to_list_wrap = document.querySelector(".list-box");

							data.forEach(function (row) {
								let to_addr = null;
								let to_detail_addr = null;
								if (country == 'KR') {
									if (row.to_road_addr == "" || row.to_road_addr == "" == null) {
										to_addr = row.to_lot_addr;
									} else {
										to_addr = row.to_road_addr;
									}
									to_detail_addr = row.to_detail_addr;
								}
								else if (country == 'EN' || country == "CN") {
									to_detail_addr = row.to_address;
									to_addr = `${row.to_city}, ${row.to_province_name}, ${row.to_country_name}`;
								}
								get_addr_wrap.querySelector(".to_place").innerHTML = row.to_place;
								get_addr_wrap.querySelector(".to_name").innerHTML = row.to_name;
								get_addr_wrap.querySelector(".to_mobile").innerHTML = row.to_mobile;
								get_addr_wrap.querySelector(".to_zipcode").innerHTML = row.to_zipcode;
								get_addr_wrap.querySelector(".to_addr").innerHTML = to_addr;
								get_addr_wrap.querySelector(".to_detail_addr").innerHTML = to_detail_addr;

								$('#to_place').val(row.to_place);
								$('#to_name').val(row.to_name);
								$('#to_mobile').val(row.to_mobile);
								$('#to_zipcode').val(row.to_zipcode);
								$('#to_road_addr').val(row.to_road_addr);
								$('#to_lot_addr').val(row.to_lot_addr);
								$('#to_detail_addr').val(row.to_detail_addr);

								$('#to_country_code').val(row.to_country_code);
								$('#to_province_idx').val(row.to_province_idx);
								$('#to_city').val(row.to_city);
								$('#to_address').val(row.to_address);

								get_addr_wrap.classList.remove("hidden");
								step_btn_wrap.classList.remove("hidden");
								putBtn.dataset.addr_filled = "true";
								putBtn.dataset.i18n = "p_edit";
								changeLanguageR();
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
	}

	set_order_to.forEach(order_to => {
		order_to.addEventListener('click', clickHandler);
	})
	to_place.forEach(place => {
		place.addEventListener('click', clickHandler);
	});
}

function clickDeleteOrderTo() {
	let delete_order_to = document.querySelectorAll('.delete-addr');
	delete_order_to.forEach(order_to => {
		order_to.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let order_to_idx = el.dataset.order_to_idx;

			if (order_to_idx != null) {
				$.ajax({
					type: "post",
					data: {
						"order_to_idx": order_to_idx
					},
					dataType: "json",
					url: api_location + "order/pg/to/delete",
					error: function () {
						// notiModal("배송지 정보 삭제 처리에 실패했습니다.");
						makeMsgNoti(getLanguage(), 'MSG_F_ERR_0042', null);
					},
					success: function (d) {
						let code = d.code;
						if (code == 200) {
							getOrderToInfoList();
							// notiModal("배송지 정보 삭제 처리에 성공했습니다.");
							makeMsgNoti(getLanguage(), 'MSG_F_INF_0007', null);
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
		let stepBtnWrap = document.querySelector(".step-btn-wrap");
		let editBtn = document.querySelector(".header-btn.edit-btn");

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

		if (country == 'KR') {
			let addrSearch = document.querySelector(".postcodify_search_controls .keyword");
			if (addrSearch.value.length == 0) {
				if (document.querySelector(".tmp_to_zipcode").value === document.querySelector(".postcodify_search_controls .keyword").value) {
					// notiModal("배송지를 선택해주세요.");
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0041', null);
				}
				return false;
			}
		} else if (country == 'EN' || country == "CN") {
			let city = document.querySelector(".tmp_city");
			let zipcode = document.querySelector(".tmp_zipcode");
			let address = document.querySelector(".tmp_address");
			if (city.value.length == 0) {
				// notiModal("Input City Please");
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0046', null);
			}
			if (zipcode.value.length == 0) {
				// notiModal("Input Zipcode Please");
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0019', null);
			}

			if (address.value.length == 0) {
				// notiModal("Input Address Please");
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0009', null);
			}
		}


		addOrderToInfo();

		document.querySelector("#to_place").value = to_place.value;
		document.querySelector("#to_name").value = to_name.value;
		document.querySelector("#to_mobile").value = to_mobile.value;
		if (country == 'KR') {
			document.querySelector("#to_zipcode").value = put_addr_wrap.querySelector(".tmp_to_zipcode").value;
			document.querySelector("#to_road_addr").value = put_addr_wrap.querySelector(".tmp_to_road_addr").value;
			document.querySelector("#to_lot_addr").value = put_addr_wrap.querySelector(".tmp_to_lot_addr").value;
			document.querySelector("#to_detail_addr").value = put_addr_wrap.querySelector(".tmp_to_detail_addr").value;
		}
		else if (country == 'EN' || country == 'CN') {
			document.querySelector("#to_country_code").value = put_addr_wrap.querySelector(".tmp_country_code").value;
			document.querySelector("#to_province_idx").value = put_addr_wrap.querySelector(".tmp_province_idx").value;
			document.querySelector("#to_city").value = put_addr_wrap.querySelector(".tmp_city").value;
			document.querySelector("#to_zipcode").value = put_addr_wrap.querySelector(".tmp_zipcode").value;
			document.querySelector("#to_address").value = put_addr_wrap.querySelector(".tmp_address").value;
		}

		let resetInput = document.querySelectorAll(".edit-box .input-row input");
		resetInput.forEach((el) => {
			el.value = "";
		});
		$('.add_flg').prop('checked', false);

		get_addr_wrap.classList.remove("hidden");
		put_addr_wrap.classList.add("hidden");
		list_addr_wrap.classList.add("hidden");
		stepBtnWrap.classList.remove("hidden");
		editBtn.dataset.addr_filled = "true";
		editBtn.dataset.i18n = "p_edit";
		changeLanguageR();
	});
}

function addOrderToInfo() {
	let put_addr_wrap = document.querySelector(".edit-box");
	let to_place = put_addr_wrap.querySelector(".tmp_to_place").value;
	let to_name = put_addr_wrap.querySelector(".tmp_to_name").value;
	let to_mobile = put_addr_wrap.querySelector(".tmp_to_mobile").value;

	let to_zipcode = null;
	let to_road_addr = null;
	let to_lot_addr = null;
	let to_detail_addr = null;

	let to_country_name = null;
	let to_province_name = null;
	let to_country_code = null;
	let to_province_idx = null;
	let to_city = null;
	let to_address = null;

	if (country == 'KR') {
		to_zipcode = put_addr_wrap.querySelector(".tmp_to_zipcode").value;
		to_road_addr = put_addr_wrap.querySelector(".tmp_to_road_addr").value;
		to_lot_addr = put_addr_wrap.querySelector(".tmp_to_lot_addr").value;
		to_detail_addr = put_addr_wrap.querySelector(".tmp_to_detail_addr").value;
	}
	else if (country == 'EN' || country == 'CN') {
		to_country_name = put_addr_wrap.querySelector('.country-select-box .tui-select-box-placeholder').textContent;
		to_province_name = put_addr_wrap.querySelector('.province-select-box .tui-select-box-placeholder').textContent;
		to_zipcode = put_addr_wrap.querySelector(".tmp_zipcode").value;
		to_country_code = put_addr_wrap.querySelector(".tmp_country_code").value;
		to_province_idx = put_addr_wrap.querySelector(".tmp_province_idx").value;
		to_city = put_addr_wrap.querySelector(".tmp_city").value;
		to_address = put_addr_wrap.querySelector(".tmp_address").value;
	}

	//변경된 주소 박스 
	let to_place_text = document.querySelector(".save-box .to_place");
	let to_name_text = document.querySelector(".save-box .to_name");
	let to_mobile_text = document.querySelector(".save-box .to_mobile");
	let to_zipcode_text = document.querySelector(".save-box .to_zipcode");
	let to_road_addr_text = document.querySelector(".save-box .to_addr");
	let to_detail_addr_text = document.querySelector(".save-box .to_detail_addr");

	if (country == 'KR') {
		to_road_addr_text.innerHTML = to_road_addr;
		to_detail_addr_text.innerHTML = to_detail_addr;
	}
	else if (country == 'EN' || country == 'CN') {
		to_detail_addr_text.innerHTML = to_address;
		to_road_addr_text.innerHTML = `${to_city}, ${to_province_name}, ${to_country_name}`;
	}

	to_place_text.innerHTML = to_place;
	to_name_text.innerHTML = to_name;
	to_mobile_text.innerHTML = to_mobile;
	to_zipcode_text.innerHTML = to_zipcode;

	if ($('.add_flg').prop('checked') == true) {
		$.ajax({
			type: "POST",
			url: api_location + "order/pg/to/add",
			data: {
				'to_place': to_place,
				'to_name': to_name,
				'to_mobile': to_mobile,
				'to_zipcode': to_zipcode,
				'to_road_addr': to_road_addr,
				'to_lot_addr': to_lot_addr,
				'to_detail_addr': to_detail_addr,
				'to_country_code': to_country_code,
				'to_province_idx': to_province_idx,
				'to_city': to_city,
				'to_address': to_address
			},
			dataType: "json",
			error: function () {
				// notiModal("배송지 저장중 오류가 발생했습니다.");
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0044', null);
			},
			success: function (d) {
				// notiModal("배송지 저장에 성공했습니다.");
				makeMsgNoti(getLanguage(), 'MSG_F_INF_0008', null);
			}
		});
	}
}

// 바우처 정보 셀렉트 박스 설정
function setVoucherInfoList(cnt_voucher_total,cnt_voucher_usable,data) {
	let country = getLanguage();

	switch (country) {
		case 'KR':
			no_select = `선택안함`;
			break;
		case 'EN':
			no_select = `Do not select.`;
			break;
		case 'CN':
			no_select = `不选择`;
			break;
	}

	let tui_select_data = [
		{
			'label': no_select,
			'value': false
		}
	];

	if (cnt_voucher_total > 0 && data.length > 0 && data != null) {
		data.forEach(function (voucher) {
			let voucher_data = {
				'voucher_idx'		:voucher.voucher_idx,
				'sale_type'			:voucher.sale_type,
				'sale_price'		:voucher.sale_price,
				'mileage_flg'		:voucher.mileage_flg,
				'usable_start_date'	:voucher.usable_start_date,
				'usable_end_date'	:voucher.usable_end_date,

				'voucher_status'	:voucher.voucher_status
			};

			let unit = "";

			if (voucher.sale_type == "PER") {
				unit = "%";
			} else {
				if (country == "KR") {
					unit = "원";
				} else {
					unit = "USD";
				}
			}
			
			let voucher_label = `${voucher.voucher_name} (${voucher.usable_start_date} - ${voucher.usable_end_date} / ${voucher.sale_price}${unit})`;
			
			let tui_voucher_data = {
				'label': voucher_label,
				'value': JSON.stringify(voucher_data)
			};

			tui_select_data.push(tui_voucher_data);
		});
	}

	let voucherDefaultTuiText = '';
	switch (country) {
		case 'KR':
			voucherDefaultTuiText = `사용가능 쿠폰 {cnt_voucher_usable}장 / 보유 {cnt_voucher_total}장`;
			break;
		case 'EN':
			voucherDefaultTuiText = `Available voucher {cnt_voucher_usable} / usable {cnt_voucher_total}`;
			break;
		case 'CN':
			voucherDefaultTuiText = `可用优惠券 {cnt_voucher_usable}张 / 拥有 {cnt_voucher_total}张`;
			break;
	}
	
	voucherDefaultTuiText = voucherDefaultTuiText.replace(/{cnt_voucher_total}/gi, cnt_voucher_total);
	voucherDefaultTuiText = voucherDefaultTuiText.replace(/{cnt_voucher_usable}/gi, cnt_voucher_usable);

	tui_voucher_info = new tui.SelectBox('.voucher-select-box',
		{
			placeholder: voucherDefaultTuiText,
			data: tui_select_data
		}
	);

	let tui_voucher_list = document.querySelector('.voucher-select-box');

	let tui_voucher = tui_voucher_list.querySelectorAll('.tui-select-box-item');
	tui_voucher.forEach(voucher => {
		let data = voucher.dataset.value;
		if (data != "false") {
			let json_data = JSON.parse(data);
			let voucher_status = json_data["voucher_status"];
			
			if (voucher_status == true) {
				voucher.dataset.voucher_status = "true";
			} else {
				voucher.dataset.voucher_status = "false";
			}
		}
	});

	tui_voucher_info.on('change', ev => {
		let select_value = ev.curr.getValue();

		let body_wrap_mileage = document.querySelector(".body-wrap.mileage");
		let body_wrap_dismileage = document.querySelector(".body-wrap.disable-mileage");
		let price_discount = document.querySelector(".calculation-wrap .price_discount");

		let use_mileage = $('#use_mileage');
		let mileage_point_btn = $('.mileage_point_btn');

		if (select_value != "false") {
			//바우처 선택
			let json_data = JSON.parse(select_value);

			$('#voucher_idx').val(json_data['voucher_idx']);

			let sale_type = json_data['sale_type'];
			let sale_price = json_data['sale_price'];

			let tmp_discount = 0;
			if (sale_type == "PRC") {
				tmp_discount = sale_price;
			} else if (sale_type == "PER") {
				let price_product = document.querySelector(".calculation-wrap .price_product_wrap .price_product").dataset.price_product;
				tmp_discount = (price_product * (parseInt(sale_price) / 100));
			}

			price_discount.innerHTML = tmp_discount.toLocaleString('ko-KR');
			price_discount.dataset.price_discount = tmp_discount;

			let mileage_flg = json_data['mileage_flg'];
			
			body_wrap_mileage.style.display = 'none';
			body_wrap_dismileage.style.display = 'block';
			mileage_point_btn.text("바우처는 적립 포인트와 중복 사용이 불가합니다.");

			initMileagePrice();
			
			/*
			if (mileage_flg == false) {
				body_wrap_dismileage.style.display = 'none';
				body_wrap_mileage.style.display = 'block';
				$('#use_mileage').attr('disabled', false);
				$('.mileage_point_btn').addClass('disabled');
				//$('.mileage_point_btn').attr('onClick', 'setTotalMileagePrice(true);');
			} else {
				body_wrap_mileage.style.display = 'none';
				body_wrap_dismileage.style.display = 'block';
				mileage_point_btn.text("바우처는 적립 포인트와 중복 사용이 불가합니다.");

				initMileagePrice();
			}*/
		} else {
			//바우처 미선택 (선택안함)
			body_wrap_dismileage.style.display = 'none';
			body_wrap_mileage.style.display = 'block';
			$('#voucher_idx').val(0);

			$('#use_mileage').attr('disabled', false);
			//$('.mileage_point_btn').attr('onClick', 'setTotalMileagePrice(true);');

			if (use_mileage.value !== 0) {
				mileage_point_btn.attr('data-i18n', 's_set_use');
			} else {
				mileage_point_btn.attr('data-i18n', 's_cancel_use');
			}
			price_discount.dataset.price_discount = 0;
			price_discount.innerHTML = 0;
		}
		changeLanguageR();
		calcPriceTotal();
	});
}

function setOrderMemoInfoList(data) {
	let tui_memo_placeholder = "";
	let tui_memo_data = new Array();

	if (data != null && data.length > 0) {
		data.forEach(function (memo) {
			if (memo.placeholder_flg == true) {
				tui_memo_placeholder = memo.memo_txt;
			} else {
				tui_memo_data.push({
					'label': memo.memo_txt,
					'value': memo.memo_idx
				});
			}
		});
	}

	tui_order_memo = new tui.SelectBox('.addr-message-select-box',
		{
			placeholder: tui_memo_placeholder,
			data: tui_memo_data
		}
	);

	tui_order_memo.on('change', ev => {
		let tmp_order_memo = document.querySelector("#tmp_order_memo");
		let message_value = document.querySelector(".save-message-box .save-message-value");

		let select_label = ev.curr.getLabel();
		let select_value = ev.curr.getValue();

		if (select_value === "7" || select_value === "14" || select_value === "21") {
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

function checkMileagePrice() {
	let mileage_point_btn = document.querySelector('.mileage_point_btn');
	let mileage_point_msg = document.querySelector('.mileage_point_msg');

	$('#use_mileage').keyup(function () {
		let param_mileage = parseInt($('#use_mileage').val().replace(',', ''));
		if (param_mileage > 0) {
			mileage_point_btn.dataset.btn_type = "SET";
			mileage_point_btn.dataset.i18n = "s_set_use";
		} else {
			mileage_point_btn.dataset.btn_type = "ALL";
			mileage_point_btn.dataset.i18n = "s_use_all";
		}
		changeLanguageR();
	});
}

function clickMileagePointBtn() {
	let mileage_point_btn = document.querySelector('.mileage_point_btn');
	mileage_point_btn.addEventListener('click', function () {
		let btn_type = mileage_point_btn.dataset.btn_type;
		switch (btn_type) {
			case "ALL":
				mileage_point_btn.dataset.btn_type = "INIT";
				mileage_point_btn.dataset.i18n = "s_cancel_use";

				getTotalMileagePrice();
				break;

			case "SET":
				mileage_point_btn.dataset.btn_type = "INIT";
				mileage_point_btn.dataset.i18n = "s_cancel_use";

				setMileagePrice();
				break;

			case "INIT":
				initMileagePrice();
				break;
		}
		changeLanguageR();
	});
}

function setMileagePrice() {
	let basket_idx = $('#basket_idx').val().split(",");
	let param_mileage = parseInt($('#use_mileage').val().replace(',', ''));
	let to_country_code = $('#to_country_code').val();

	var tmp_mileage_price = 0;
	if (!isNaN(param_mileage)) {
		tmp_mileage_price = param_mileage;
	}

	$.ajax({
		type: "post",
		url: api_location + "mileage/check",
		data: {
			'basket_idx': basket_idx,
			'param_mileage': tmp_mileage_price,
			'to_country_code': to_country_code
		},
		dataType: "json",
		async: false,
		error: function () {
			// notiModal("적립금 정보 조회처리중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0011', null);
		},
		success: function (d) {
			let price_mileage_point = document.querySelector(".price_mileage_point");

			if (d.code == 200) {
				let mileage_point = 0;
				let txt_mileage_point = 0;

				let data = d.data;
				mileage_point = data.mileage_point;
				txt_mileage_point = data.txt_mileage_point;

				price_mileage_point.dataset.price_mileage_point = mileage_point;
				price_mileage_point.innerHTML = txt_mileage_point;

				$('#use_mileage').val(txt_mileage_point);
				$('#price_mileage_point').val(mileage_point);
			} else {
				$('#use_mileage').val(0);
				$('#price_mileage_point').val(0);

				notiModal(d.msg);
			}

			calcPriceTotal();
		}
	});
}

function getTotalMileagePrice() {
	let basket_idx = $('#basket_idx').val().split(",");
	let to_country_code = $('#to_country_code').val();

	$.ajax({
		type: "post",
		url: api_location + "mileage/get",
		data: {
			'basket_idx': basket_idx,
			'to_country_code': to_country_code
		},
		dataType: "json",
		async: false,
		error: function () {
			// notiModal("적립금 정보 조회처리중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0011', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let use_mileage = $('#use_mileage');
				let param_mileage_point = $('#price_mileage_point');
				let price_mileage_point = document.querySelector(".price_mileage_point");

				let data = d.data;
				if (data != null) {
					let total_mileage_point = data.total_mileage_point;
					let txt_mileage_point = data.txt_mileage_point;

					use_mileage.val(txt_mileage_point);
					param_mileage_point.val(total_mileage_point);

					price_mileage_point.dataset.price_mileage_point = data.total_mileage_point;
					price_mileage_point.innerHTML = data.txt_mileage_point;

					calcPriceTotal();
				}
			} else {
				use_mileage.val(0);
				param_mileage_point.val(0);
				price_mileage_point.dataset.price_mileage_point = 0;
				price_mileage_point.innerHTML = "0";

				calcPriceTotal();

				notiModal(d.msg);
			}
		}
	});
}

function initMileagePrice() {
	let use_mileage = $('#use_mileage');
	let param_mileage_point = $('#price_mileage_point');
	let price_mileage_point = document.querySelector(".price_mileage_point");

	use_mileage.val('');
	param_mileage_point.val(0);
	price_mileage_point.dataset.price_mileage_point = 0;
	price_mileage_point.innerHTML = "0";

	let mileage_point_btn = document.querySelector('.mileage_point_btn');
	mileage_point_btn.dataset.i18n = "s_use_all";
	mileage_point_btn.dataset.btn_type = "ALL";

	calcPriceTotal();
	changeLanguageR();
}

/*
function setMileagePrice() {
	$('#use_mileage').keyup(function () {
		let basket_idx = $('#basket_idx').val().split(",");
		var param_mileage = parseInt($('#use_mileage').val().replace(',', ''));
		var tmp_mileage_price = "";

		if (isNaN(param_mileage)) {
			tmp_mileage_price = 0;
		} else {
			tmp_mileage_price = param_mileage;
		}

		$.ajax({
			type: "post",
			url: api_location + "mileage/check",
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
	total_mileage.addEventListener('click', function () {
		if (!total_mileage.classList.contains('disabled')) {
			setTotalMileagePrice(true);
		}

		let tui_selected = document.querySelector('.voucher-select-box .tui-select-box-item.tui-select-box-selected');
		if (tui_selected != null) {
			let tui_value = JSON.parse(tui_selected.dataset.value);

			let mileage_flg = tui_value.mileage_flg;
			if (mileage_flg == true) {
				let price_discount = document.querySelector('.price_discount');
				price_discount.dataset.price_discount = 0;
				price_discount.innerText = 100;
			}
		}
	});
}

function setTotalMileagePrice(calc_flg) {
	let basket_idx = $('#basket_idx').val().split(",");
	let to_country_code = $('#to_country_code').val();
	let use_mileage = $('#use_mileage');
	let mileage_point_btn = $('.mileage_point_btn');

	$.ajax({
		type: "post",
		data: {
			'basket_idx': basket_idx,
			'to_country_code': to_country_code
		},
		dataType: "json",
		url: api_location + "mileage/get",
		async: false,
		error: function () {
			notiModal("적립금 정보 조회처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null) {
					let price_mileage_point = document.querySelector(".price_mileage_point");
					
					let btn_mileage_point = document.querySelector('.('.mileage_point_btn')');
					
					if (calc_flg == true) {
						$('#use_mileage').val(data.total_mileage_point);

						$('#price_mileage_point').val(data.total_mileage_point);

						price_mileage_point.dataset.price_mileage_point = data.total_mileage_point;
						price_mileage_point.innerHTML = data.txt_mileage_point;

						$('.mileage_point_msg').text('적용 취소');
						
						btn_mileage_point.addEventListener('click',function() {
							setTotalMileagePrice(false);
						});
						
						//$('.mileage_point_btn').attr('onClick', 'setTotalMileagePrice(false);');
					} else {
						$('#use_mileage').val(0);

						$('#price_mileage_point').val(0);

						price_mileage_point.dataset.price_mileage_point = 0;
						price_mileage_point.innerHTML = 0;

						if (use_mileage.val() >= 1000 && use_mileage.val() % 1000 === 0) {
							mileage_point_btn.text("적용 취소");
						} else {
							mileage_point_btn.text("모두 적용");
						}
						
						btn_mileage_point.addEventListener('click',function() {
							setTotalMileagePrice(true);
						});
						
						//$('.mileage_point_btn').attr('onClick', 'setTotalMileagePrice(true);');
					}

					calcPriceTotal();
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}
*/

function clickCheckTerms() {
	let order_section = document.querySelector(".order-section");

	let check_terms = document.querySelectorAll('.check_terms');
	check_terms.forEach(check => {
		check.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let check_type = el.dataset.check_type;

			let order_status = order_section.dataset.status;
			if (check_type == "ALL") {
				if (el.checked) {
					$('.check_terms').prop('checked', true);
					order_status = "T";
				} else {
					$('.check_terms').prop('checked', false);
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
			next_step_btn.querySelector("span").dataset.i18n = "s_next";
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
		window.scrollTo(0, 0);
		changeLanguageR();
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
			// notiModal("빈칸 없이 모두 기입해주세요.");
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0033', null);
			return false;
		}

		if (next_step_level == 2) {

			if (order_section.dataset.status == "F") {
				// notiModal("이용약관에 동의가 필요합니다.");
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0013', null);
				return false;
			} else if (order_section.dataset.status == "T") {
				addTmpOrderInfo();
			}
		} else {
			next_step_btn.dataset.step = 2;
			prev_step_btn.dataset.step = 1;

			calc_wrap.dataset.step = 2;

			order_section.dataset.status = "F";
			$('.check_terms').prop('checked', false);

			if (next_step_btn.dataset.step === "2") {
				next_step_btn.querySelector("span").innerHTML = "결제하기";
				next_step_btn.querySelector("span").dataset.i18n = "s_checkout";
			}

			$group1.forEach(el => {
				el.classList.add("next");
			});

			$group2.classList.add("next");

			$group3.classList.add("next");
			if ($group3.classList.contains("next")) {
				document.querySelector(".address-info.next .header-box-btn").classList.add("hidden");
				document.querySelector(".list-box").classList.add("hidden");
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
				// let resent_msg_label = tui_order_memo.dropdown.items[0].label;
				// document.querySelector("#recent_order_msg").value = $('#order_memo').val();
				// console.log(document.querySelector("#recent_order_msg").value);
				// tui_order_memo.dropdown.items[0].label = '(최근) ' + document.querySelector("#recent_order_msg").value;
			}

			calcPriceTotal();
		}
		window.scrollTo(0, 0);
		changeLanguageR();
	});
}

function calcPriceProduct() {
	let product_price = 0;

	let total_price = document.querySelectorAll(".product .total_price");
	total_price.forEach(price => {
		product_price += parseInt(price.dataset.total_price);
	});

	//합산
	let price_product = document.querySelector(".calculation-wrap .price_product_wrap .price_product");
	price_product.dataset.price_product = product_price;
	price_product.innerHTML = product_price.toLocaleString("ko-KR");

	let product_qty = document.querySelector(".calculation-wrap .product-qty");
	product_qty.innerHTML = total_qty;

	//배송비 처리
	let delivery_price = 0;
	let txt_delivery_price = "0";

	if (getLanguage() != "KR") {
		let delivery_data = getDeliveryPrice();
		delivery_price = delivery_data.price_delivery;
		txt_delivery_price = delivery_data.txt_price_delivery;
	} else {
		if (product_price < 80000) {
			delivery_price = 2500;
			txt_delivery_price = "2,500";
		}
	}

	let price_delivery = document.querySelector(".calculation-wrap .price_delivery");
	price_delivery.dataset.price_delivery = delivery_price;
	price_delivery.textContent = txt_delivery_price;

	calcPriceTotal();
}

function calcPriceTotal() {
	let step = calc_wrap.dataset.step;

	let price_product = parseInt(document.querySelector(".calculation-wrap .price_product_wrap .price_product").dataset.price_product);
	let price_discount = parseInt(document.querySelector(".calculation-wrap .price_discount").dataset.price_discount);
	let voucher_name = document.querySelector(".calculation-wrap .voucher_name").dataset.voucher_name;
	let price_mileage_point = parseInt(document.querySelector(".calculation-wrap .price_mileage_point").dataset.price_mileage_point);
	// let price_charge_point = parseInt(document.querySelector(".calculation-wrap .price_charge_point").dataset.price_charge_point);
	let price_delivery = parseInt(document.querySelector(".calculation-wrap .price_delivery").dataset.price_delivery);

	let calc_result = price_product - price_discount - price_mileage_point + price_delivery;

	let txt_voucher_info = "";
	let tui_selected_voucher = document.querySelector('.voucher-select-box .tui-select-box-item.tui-select-box-selected');
	if ((tui_selected_voucher != null || tui_selected_voucher != undefined) && tui_selected_voucher.dataset.value != "false") {
		txt_voucher_info = tui_selected_voucher.textContent;
	}

	let voucher_type = document.querySelector(".calculation-wrap .voucher_name");
	voucher_type.innerHTML = txt_voucher_info;

	let price_total = document.querySelector(".calculation-wrap .price_total");
	price_total.innerHTML = calc_result.toLocaleString("ko-KR");
}

function addTmpOrderInfo() {
	let frm = $("#frm-check")[0];
	let formData = new FormData(frm);

	$.ajax({
		type: "post",
		url: api_location + "order/pg/tmp",
		data: formData,
		dataType: "json",
		async: true,
		enctype: "multipart/form-data",
		processData: false,
		contentType: false,
		error: function () {
			// notiModal("주문 정보 등록처리에 실패했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0009', null);
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				let order_info = d.data;

				if (order_info.price_total > 0) {
					setTossPayment(order_info);
				} else {
					let price_product = parseFloat(order_info.price_product);
					let price_mileage_point = parseInt(order_info.price_mileage_point);
					let price_charge_point = parseFloat(order_info.price_charge_point);
					let price_discount = parseFloat(order_info.price_discount);
					let price_delivery = parseFloat(order_info.price_delivery);

					if (((price_product + price_delivery) - price_mileage_point - price_charge_point - price_discount) == 0) {
						location.href = "/order/check?order_code=" + order_info.order_code;
					}
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function setTossPayment(order_info) {
	let country = order_info.country;

	if (country == "KR") {
		tossPayments.requestPayment('카드', {
			amount: order_info.price_total,
			orderId: order_info.order_code,
			orderName: order_info.order_title,
			customerName: order_info.member_name,
			successUrl: domain_url + 'order/check',
			failUrl: domain_url + 'order/basket/list',
		});
	} else {
		tossPayments.requestPayment('해외간편결제', {
			amount: order_info.price_total,
			orderId: order_info.order_code,
			orderName: order_info.order_title,
			customerName: order_info.member_name,
			successUrl: domain_url + '/order/check',
			failUrl: domain_url + '/order/basket/list',
			useInternationalCardOnly: true,
			provider: "PAYPAL",
			currency: "USD",
			country: "US",
		});
	}
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

function getDeliveryPrice() {
	let delivery_data = new Array();

	$.ajax({
		type: "post",
		url: api_location + "order/deliver/get",
		dataType: "json",
		async: false,
		error: function () {
			// notiModal("쇼핑백 상품 리스트 조회처리중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0028', null);
		},
		success: function (d) {
			if (d.code == 200) {
				delivery_data = d.data;
			} else {
				notiModal(d.msg);
			}
		}
	});

	return delivery_data;
}

function initTotalMileage() {
	$.ajax({
		type: "post",
		data: {
			'basket_idx': "ALL"
		},
		dataType: "json",
		url: api_location + "mileage/get",
		async: false,
		error: function () {
			// notiModal("적립금 정보 조회처리중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0011', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				$('#txt_total_mileage').remove();
				$('.get-point.reserves').append('<span id="txt_total_mileage">' + data.txt_mileage_point + '</span>');
			}
		}
	});
}