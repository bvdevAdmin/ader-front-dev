document.addEventListener('DOMContentLoaded', function () {
	getMemberAsInfoList();
	clickOrderToBtn();
	openShippingForm();
	closeShippingForm();
	putMemberAsHousingInfo();
	addCompleteListBtnEvent();
	getDeliveryCompanySelectBox();
	addAsMobileHyprenEvent();
});

$(function () {
	let country = getLanguage();
	if (country == "KR") {
		$('.as_addr_EN').remove();
		$('.as_addr_CN').remove();
		$('.result_addr_EN').remove();
		$('.result_addr_CN').remove();

		$("#postcodify_as").postcodify({
			insertPostcode5: ".to_zipcode",
			insertAddress: ".to_road_addr",
			insertDetails: ".to_detail_addr",
			insertJibeonAddress: ".to_lot_addr",
			hideOldAddresses: false,
			results: ".post_change_result",
			hideSummary: true,
			useFullJibeon: true,
			onReady: function () {
				$(".post_change_result").hide();
				$(".postcodify_search_controls .keyword").attr("placeholder", "예) 성동구 연무장길 53, 성수동2가 315-57");
			},
			onSuccess: function () {
				$(".post_change_result").show();
				$("#postcodify div.postcode_search_status.too_many").hide();
			},
			afterSelect: function (selectedEntry) {
				$("#postcodify div.postcode_search_result").remove();
				$("#postcodify div.postcode_search_status.too_many").hide();
				$("#postcodify div.postcode_search_status.summary").hide();
				$(".post_change_result").hide();
				$("#entry_box").show();
				$("#entry_details").focus();
				$(".postcodify_search_controls .keyword").val($(".order_to_road_addr").val());
			}
		});
	} else if (country == "EN") {
		$('.as_addr_KR').remove();
		$('.as_addr_CN').remove();
		$('.result_addr_KR').remove();
		$('.result_addr_CN').remove();

		getAsCountryInfo("EN", null, null);
	} else if (country == "CN") {
		$('.as_addr_KR').remove();
		$('.as_addr_EN').remove();
		$('.result_addr_KR').remove();
		$('.result_addr_EN').remove();

		getAsCountryInfo("CN", null, null);
	}
});

let countryAsSelectBox = null;

function getAsCountryInfo(country, selCountry, selProvince) {
	let countryInfo = [];
	let countryDisabled = false;
	let defaultCountry = null;

	$.ajax({
		type: 'POST',
		url: api_location + "account/country/get",
		headers: {
			"country": country
		},
		dataType: 'json',
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0002', null);
			//notiModal("해외 국가 정보얻기에 실패 했습니다.");
		},
		success: function (d) {
			if (d.code == "200") {
				if (d.data != null) {
					countryInfo = d.data;
					if (selCountry == null) {
						defaultCountry = d.data[0].value;
					} else {
						defaultCountry = selCountry;
					}
				}
			} else {
				notiModal(d.msg);
			}

			if (country == 'CN') {
				countryDisabled = true;
			}

			$('.country-select-box').html('');

			countryAsSelectBox = new tui.SelectBox('.country-select-box', {
				data: countryInfo,
				autofocus: false,
				disabled: countryDisabled
			});

			countryAsSelectBox.select(defaultCountry);

			$('.as_addr_' + country + ' input[name=country_code]').val(defaultCountry);

			getAsProvinceInfo(defaultCountry, selProvince);

			countryAsSelectBox.on("change", ev => {
				let country_value = ev.curr.getValue();
				$('.as_addr_' + country + ' input[name=country_code]').val(country_value);
				getAsProvinceInfo(country_value, null);
			});
		}
	});
}

let provinceAsSelectBox = null;

function getAsProvinceInfo(country_code, province_idx) {
	let provinceInfo = [];
	let provinceFlg = true;
	let defaultProvince = null;
	$.ajax({
		type: 'POST',
		url: api_location + "account/province/get",
		data: {
			'country_code': country_code
		},
		dataType: 'json',
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0001', null);
			//notiModal("해외 시/도 정보얻기에 실패 했습니다.");
		},
		success: function (d) {
			if (d.code == "200") {
				provinceFlg = !d.province_flg;

				if (d.province_flg == true) {
					if (d.data != null) {
						provinceInfo = d.data;
						if (province_idx == null) {
							defaultProvince = d.data[0].value;
						} else {
							defaultProvince = province_idx;
						}
					}
				}
			} else {
				notiModal(d.msg);
			}

			$('.as_addr_' + country + ' input[name=province_idx]').val(defaultProvince);
			$('.province-select-box').html('');

			provinceAsSelectBox = new tui.SelectBox('.province-select-box', {
				data: provinceInfo,
				autofocus: false,
				disabled: provinceFlg
			});

			// if(province_idx != null){
			//     let profileAddrWrap = $('.as_addr_' + country); 
			//     let provinceWrapDiv = $('.as_addr_' + country + ' .province-select-box');
			//     let province_name = $(`.province-select-box .tui-select-box-item[data-value=${province_idx}]`).text();

			//     profileAddrWrap.find('input[name=province_idx]').val(province_idx);
			//     provinceWrapDiv.find('.tui-select-box-placeholder').text(province_name);
			// }
			provinceAsSelectBox.select(province_idx);

			provinceAsSelectBox.on("change", ev => {
				let province_value = ev.curr.getValue();
				$('.as_addr_' + country + ' input[name=province_idx]').val(province_value);
			});
		}
	});
}

function openShippingForm() {
	let open_shipping = document.querySelector('.open_shipping');
	open_shipping.addEventListener('click', function () {
		let as_shipping_form = document.querySelector(".as_shipping_form");
		as_shipping_form.classList.remove("hidden");

		let housing_num = document.querySelector(".housing_num");
		housing_num.addEventListener('input', function (e) {
			e.target.value = e.target.value.replace(/[^0-9]/g, "");
		});
	});
}

function closeShippingForm() {
	let close_shipping = document.querySelector('.close_shipping');
	close_shipping.addEventListener('click', function () {
		let as_shipping_form = document.querySelector(".as_shipping_form");
		as_shipping_form.classList.add("hidden");
		deliveryCompany.deselect();
		$('.as_tab_current').find('.housing_num').val("");
	});
}

let deliveryCompany = null;
function getDeliveryCompanySelectBox() {
	let country = getLanguage();

	switch (country) {
		case "KR":
			deliveryCompany = new tui.SelectBox('.housing-company-list', {
				placeholder: '배송업체를 선택해주세요.',
				data: [
					{
						label: 'CJ 대한통운',
						value: '1'
					},
					{
						label: '한진 택배',
						value: '2'
					},
					{
						label: '우체국 택배',
						value: '3'
					},
					{
						label: '기타',
						value: '4'
					}
				],
				autofocus: false
			});
			break;

		case "EN":
		case "CN":
			let placeholder_txt = "";

			if (country == "EN") {
				placeholder_txt = "Please select a delivery company.";
			} else {
				placeholder_txt = "请选择配送公司。";
			}
			deliveryCompany = new tui.SelectBox('.housing-company-list', {
				placeholder: placeholder_txt,
				data: [
					{
						label: 'DHL',
						value: '1'
					}
				],
				autofocus: false
			});
			break;
	}
}

function openAsStatusTab(as_idx, tab_status) {
	let currentList = document.querySelector(".as_current_list");
	let currentStatus = document.querySelector(".as_current_status");

	currentStatus.classList.remove("hidden");
	currentList.classList.add("hidden");

	getMemberAsInfo(as_idx, tab_status);
	showAsCurrentList();
	window.scrollTo(0, 0);
}

function showAsCurrentList() {
	let showListBtn = document.querySelector(".show_as_current_list_btn");
	let currentList = document.querySelector(".as_current_list");
	let currentStatus = document.querySelector(".as_current_status");
	let asItemStatus = document.querySelector(".as_current_status .as_item_status");

	showListBtn.addEventListener("click", function () {
		asItemStatus.innerHTML = "";

		initAsStep();
		initOrderTo();
		clickAsStatusCurrentBtnEvent();
		currentList.classList.remove("hidden");
		currentStatus.classList.add("hidden");
	});
}

function getMemberAsInfoList() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/as/list/get",
		data: {
			"as_status": "CRT"
		},
		dataType: "json",
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0081', null);
			//notiModal("A/S 현황 조회처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			let data = d.data;

			let asCurrentList = document.querySelector(".as_current_list");
			asCurrentList.innerHTML = "";

			if (data != null) {
				let div_as_product = writeAsProductListHtml(data, "current");

				asCurrentList.innerHTML = `
					<table class="as__contents__table">
							<colsgroup>
									<col style="width: 15%;">
									<col style="width: 55%;">
									<col style="width: 30%;">
							</colsgroup>
							<tbody>
									${div_as_product}
							</tbody>
					</table>
				`;
			} else {
				let exception_msg = "";

				switch (getLanguage()) {
					case "KR":
						exception_msg = "A/S 진행 내역이 없습니다.";
						break;

					case "EN":
						exception_msg = "There is no history.";
						break;

					case "CN":
						exception_msg = "没有查询到相关资料。​";
						break;

				}
				asCurrentList.innerHTML = `
					<div class="no_as_product_msg">${exception_msg}</div>
				`;
			}
			clickAsStatusCurrentBtnEvent();
		}
	});
}

let asOrderToMsg = null;

function getMemberAsInfo(as_idx, tab_status) {
	$.ajax({
		type: "post",
		url: api_location + "mypage/as/get",
		data: {
			"as_idx": as_idx
		},
		dataType: "json",
		async: false,
		error: function (d) {
		},
		success: function (d) {
			let data = d.data;
			let asItemStatus = document.querySelector(".as_current_status .as_item_status");
			let country = getLanguage();
			asItemStatus.innerHTML = "";

			if (data != null) {

				let as_tab = $('.as_tab_' + tab_status);
				as_tab.find('.as_idx').val(data.as_idx);

				let div_as_product = writeAsProductItemHtml(data, "current");

				asItemStatus.innerHTML = `
					<table class="as__contents__table">
							<colsgroup>
								<col style="width: 15%;">
								<col style="width: 55%;">
								<col style="width: 30%;">
							</colsgroup>
							<tbody>
									${div_as_product}
							</tbody>
					</table>
				`;

				let as_status = data.as_status;
				let step_btn = document.querySelector('.step_btn_' + as_status);
				let as_contents = document.querySelector('.contents_' + as_status);

				initAsStep();
				let as_payment_info = data.payment_info;

				let product_img_box = null;
				let receipt_img_box = null;
				let product_img_wrap = null;
				let receipt_img_wrap = null;
				let product_img_html = "";
				let receipt_img_html = "";
				let img_info = data.img_info;
				let img_type_p_cnt = 0;
				let img_type_r_cnt = 0;

				if (img_info != null || img_info.length > 0) {

					img_info.forEach(info => {
						if (info.img_type == "P") {
							product_img_html += `
								<div class="img_info_content">
									<img src="${cdn_img}${info.img_location}">
								</div>
							`;
							img_type_p_cnt++;
						} else if (info.img_type == "R") {
							receipt_img_html += `
								<div class="img_info_content">
									<img src="${cdn_img}${info.img_location}">
								</div>
							`;
							img_type_r_cnt++;
						}
					});
				}

				switch (as_status) {
					case "APL":
						let as_repair_type = data.as_repair_type;

						if (as_repair_type != null) {
							as_contents = document.querySelector('.contents_APL_' + as_repair_type);
						} else {
							as_contents = document.querySelector('.contents_APL_RWT');
						}

						product_img_wrap = as_contents.querySelector(".as_product_img");
						receipt_img_wrap = as_contents.querySelector(".as_receipt_img");

						product_img_wrap.innerHTML = product_img_html;
						receipt_img_wrap.innerHTML = receipt_img_html;

						product_img_box = as_contents.querySelector(".product_img_box");
						receipt_img_box = as_contents.querySelector(".receipt_img_box");
						if (img_type_p_cnt < 1) {
							product_img_box.classList.add("hidden");
						}
						if (img_type_r_cnt < 1) {
							receipt_img_box.classList.add("hidden");
						}

						as_contents.querySelector('.create_date').innerText = data.create_date;
						as_contents.querySelector('.as_contents').innerText = xssDecode(data.as_contents);

						break;

					case "HOS":
						let housing_company = data.housing_company;
						let housing_num = data.housing_num;
						let asImgWrap = document.querySelectorAll(".contents_HOS .as_img_wrap");

						if (housing_company != null && housing_num != null) {
							as_contents = document.querySelector('.contents_' + as_status + '_DCP');

							as_contents.querySelector('.housing_start_date').innerText = data.housing_start_date;
							as_contents.querySelector('.housing_company').innerText = data.housing_company;
							as_contents.querySelector('.housing_num').innerText = data.housing_num;
						} else {
							as_contents = document.querySelector('.contents_' + as_status + '_DPG');
						}

						asImgWrap.forEach(wrap => {
							product_img_box = as_contents.querySelector(".product_img_box");
							receipt_img_box = as_contents.querySelector(".receipt_img_box");
							if (img_type_p_cnt < 1) {
								product_img_box.classList.add("hidden");
							}
							if (img_type_r_cnt < 1) {
								receipt_img_box.classList.add("hidden");
							}

							product_img_wrap = wrap.querySelector(".as_product_img");
							receipt_img_wrap = wrap.querySelector(".as_receipt_img");
							product_img_wrap.innerHTML = product_img_html;
							receipt_img_wrap.innerHTML = receipt_img_html;
						});

						break;

					case "RPR":
						as_contents.querySelector('.housing_end_date').innerText = data.housing_end_date;
						as_contents.querySelector('.as_contents').innerText = xssDecode(data.as_contents);
						as_contents.querySelector('.repair_desc').innerText = data.repair_desc;
						as_contents.querySelector('.completion_date').innerText = data.completion_date;

						product_img_box = as_contents.querySelector(".product_img_box");
						receipt_img_box = as_contents.querySelector(".receipt_img_box");
						if (img_type_p_cnt < 1) {
							product_img_box.classList.add("hidden");
						}
						if (img_type_r_cnt < 1) {
							receipt_img_box.classList.add("hidden");
						}

						product_img_wrap = as_contents.querySelector(".as_product_img");
						receipt_img_wrap = as_contents.querySelector(".as_receipt_img");
						product_img_wrap.innerHTML = product_img_html;
						receipt_img_wrap.innerHTML = receipt_img_html;
						break;

					case "APG":
						let as_order_to_msg_list = document.querySelector(".as_order_to_msg_list");
						let order_memo_list = data.order_memo_list;
						let order_memo_placeholder = "";
						let order_memo_data = [];
						let direct_memo = {};

						as_order_to_msg_list.innerHTML = "";

						order_memo_list.forEach(memo => {
							if (memo.placeholder_flg == "1") {
								order_memo_placeholder = memo.memo_txt;
							} else if (memo.direct_flg == "1") {
								direct_memo.label = memo.memo_txt;
								direct_memo.value = "direct";
							} else {
								let order_memo = {};
								order_memo.label = memo.memo_txt;
								order_memo.value = memo.memo_idx;

								order_memo_data.push(order_memo);
							}
						});

						order_memo_data = [...order_memo_data, direct_memo];

						asOrderToMsg = new tui.SelectBox('.as_order_to_msg_list', {
							placeholder: order_memo_placeholder,
							data: order_memo_data,
							autofocus: false
						});

						let add_order_to_btn = as_contents.querySelector(".add_order_to_btn");
						let as_payment_btn = as_contents.querySelector("#as_payment_btn");
						let put_order_to_btn = as_contents.querySelector(".edit_order_to_btn");
						let as_payment_complete_wrap = as_contents.querySelector(".as_payment_complete_noti_wrap");
						let as_receipt_link = as_contents.querySelector(".as_receipt_link");

						let to_place = data.to_place;
						let to_name = data.to_name;
						let to_mobile = data.to_mobile;

						if (data.as_price > 0) {
							as_payment_btn.classList.remove("hidden");
							if (data.as_price_flg == true) {
								as_payment_btn.classList.add("disabled");
							} else {
								as_payment_btn.addEventListener('click', function () {
									checkAsPrice();
								});
							}
						} else {
							as_payment_btn.classList.add('hidden');
						}

						if (to_place != null && to_name != null && to_mobile != null) {
							add_order_to_btn.classList.add('disable');
							put_order_to_btn.classList.remove("hidden");
							add_order_to_btn.disabled = true;

							let data_wrap = document.querySelector('.as_order_to_data');

							data_wrap.querySelector('.as_to_country_code').value = data.to_country_code;
							data_wrap.querySelector('.as_to_province_idx').value = data.to_province_idx;
							data_wrap.querySelector('.as_to_detail_addr').value = data.to_detail_addr;
							data_wrap.querySelector('.as_to_city').value = data.to_city;
							data_wrap.querySelector('.as_to_lot_addr').value = data.to_lot_addr;
							data_wrap.querySelector('.as_to_road_addr').value = data.to_road_addr;
							data_wrap.querySelector('.as_to_place').innerText = data.to_place;
							data_wrap.querySelector('.as_to_name').innerText = data.to_name;
							data_wrap.querySelector('.as_to_mobile').innerText = data.to_mobile;
							data_wrap.querySelector('.as_to_zipcode').innerText = data.to_zipcode;
							data_wrap.querySelector('.as_to_address').innerText = `${data.to_road_addr} ${data.to_detail_addr}`;
							data_wrap.querySelector('.as_order_memo').innerText = data.order_memo;

							data_wrap.classList.remove('hidden');

						} else {
							add_order_to_btn.classList.remove("hidden");
							add_order_to_btn.disabled = false;
							put_order_to_btn.classList.add("hidden");
						}

						let delivery_payment_txt = "배송비 포함";
						let delivery_currency_txt = "원";

						if (country != "KR") {
							as_receipt_link.classList.add("hidden");
							delivery_currency_txt = "";

							if (country == "EN") {
								delivery_payment_txt = "Includes the delivery fee"
							} else {
								delivery_payment_txt = "包括送货费";
							}

						} else if (as_payment_info.pg_receipt_url != null) {
							add_order_to_btn.classList.add("hidden");
							as_payment_btn.classList.add("hidden");
							as_payment_complete_wrap.classList.remove("hidden");
							as_receipt_link.setAttribute("href", data.payment_info.pg_receipt_url);
						}

						as_contents.querySelector('.as_price').innerText = '(' + delivery_payment_txt + ') ' + data.txt_as_price + delivery_currency_txt;
						break;

					case "DLV":
						let delivery_num_html = "";

						if (data.delivery_idx == 0 || data.delivery_idx == 1) {
							delivery_num_html = `
								<div class="DLV_data_contents delivery_number">
									<a href="https://trace.cjlogistics.com/web/detail.jsp?slipno=${data.delivery_num}" target='_blank'>${data.delivery_num}</a>
								</div>
							`;
						} else {
							delivery_num_html = `
								<div class="DLV_data_contents delivery_number">
									${data.delivery_num}
								</div>
							`;
						}
						as_contents.querySelector('.company_name').innerText = data.company_name;
						as_contents.querySelector('.delivery_num_wrap').innerHTML = delivery_num_html;
						as_contents.querySelector('.delivery_start_date').innerText = data.delivery_start_date;

						let as_payment_complete_noti_wrap_dvl = as_contents.querySelector(".as_payment_complete_noti_wrap");
						let as_receipt_link_dvl = as_contents.querySelector(".as_receipt_link");

						if (country != "KR") {
							as_receipt_link_dvl.classList.add("hidden");
						} else if (as_payment_info.pg_receipt_url != null) {
							as_receipt_link_dvl.setAttribute("href", data.payment_info.pg_receipt_url);
						} else {
							as_payment_complete_noti_wrap_dvl.classList.add("hidden");
						}
						break;

					case "ACP":
						let as_payment_complete_noti_wrap_acp = as_contents.querySelector(".as_payment_complete_noti_wrap");
						let as_receipt_link_acp = as_contents.querySelector(".as_receipt_link");

						if (country != "KR") {
							as_receipt_link_acp.classList.add("hidden");
						} else if (as_payment_info.pg_receipt_url != null) {
							as_receipt_link_acp.setAttribute("href", data.payment_info.pg_receipt_url);
						} else {
							as_payment_complete_noti_wrap_acp.classList.add("hidden");
						}
						break;
				}
				step_btn.classList.add('on');
				as_contents.classList.remove('hidden');
				showImgAsInfoDetail();
				selectDirectMsg();
			} else {
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0003', null);
				//notiModal("현재 진행중인 A/S 현황 정보가 존재하지 않습니다.");
			}
		}
	});
}

function initAsStep() {
	let btn_step = document.querySelectorAll('.btn_step');
	btn_step.forEach(btn => {
		btn.classList.remove('on');
		btn.disabled = true;
	});

	let as_step_contents = document.querySelectorAll('.as_step_contents');
	as_step_contents.forEach(contents => {
		contents.classList.add('hidden');
	});
}

function putMemberAsHousingInfo() {
	let put_housing = document.querySelector('.put_housing');
	put_housing.addEventListener('click', function () {
		let as_tab = $('.as_tab_current');

		let as_idx = as_tab.find('.as_idx').val();
		let housing_company = null;
		let housing_num = null;

		let tui_housing_company = document.querySelector('.housing-company-list');

		let tmp_housing_company = tui_housing_company.querySelector('.tui-select-box-selected');
		if (tmp_housing_company != null) {
			housing_company = tmp_housing_company.textContent;
		} else {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0042', null);
			//notiModal('배송 업체를 선택해주세요.');
			return false;
		}

		housing_num = as_tab.find('.housing_num').val();
		if (housing_num == null || housing_num.length == 0) {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0018', null);
			//notiModal('운송장 번호를 입력해주세요.');
			return false;
		}

		$.ajax({
			type: "post",
			url: api_location + "mypage/as/put",
			data: {
				"update_type": "HOS",
				"as_idx": as_idx,
				"housing_company": housing_company,
				"housing_num": housing_num
			},
			dataType: "json",
			error: function (d) {
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0086', null);
				//notiModal("A/S 배송정보 입력처리중 오류가 발생했습니다.");
			},
			success: function (d) {
				if (d.code == 200) {
					let data = d.data;
					if (data != null) {
						document.querySelector('.as_shipping_form').classList.add('hidden');
						document.querySelector('.contents_HOS_DPG').classList.add('hidden');

						let contents_HOS = document.querySelector('.contents_HOS_DCP');
						contents_HOS.classList.remove('hidden');

						contents_HOS.querySelector('.housing_start_date').innerText = data.housing_start_date;
						contents_HOS.querySelector('.housing_company').innerText = data.housing_company;
						contents_HOS.querySelector('.housing_num').innerText = data.housing_num;


						let product_img_box = contents_HOS.querySelector(".product_img_box");
						let receipt_img_box = contents_HOS.querySelector(".receipt_img_box");

						let img_type_p_cnt = contents_HOS.querySelectorAll(".as_product_img .img_info_content").length;
						let img_type_r_cnt = contents_HOS.querySelectorAll(".as_receipt_img .img_info_content").length;

						if (img_type_p_cnt < 1) {
							product_img_box.classList.add("hidden");
						}
						if (img_type_r_cnt < 1) {
							receipt_img_box.classList.add("hidden");
						}
						makeMsgNoti(getLanguage(), 'MSG_F_INF_0010', null);
						//notiModal('배송정보 등록이 완료되었습니다.');
					}
				} else {
					notiModal(d.msg);
				}
			}
		});
		deliveryCompany.deselect();
		as_tab.find('.housing_num').val("");

	});
}

function checkAsPrice() {
	let add_order_to_btn = document.querySelector(".add_order_to_btn");
	if (!add_order_to_btn.classList.contains('disable')) {
		document.querySelector('.as_order_to_alert').classList.remove('hidden');
		return false;
	}

	let as_tab = $('.as_tab_current');
	let as_idx = as_tab.find('.as_idx').val();

	$.ajax({
		type: "post",
		url: api_location + "mypage/as/check",
		data: {
			"as_idx": as_idx
		},
		dataType: "json",
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0083', null);
			//notiModal("A/S 요금 체크처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null) {
					if (data.as_price > 0) {
						setTossPayment(data.as_code, data.as_price);
					} else {
						putAsPrice(data.as_code);
					}
				}
			}
		}
	});
}

function putAsPrice(as_code) {
	$.ajax({
		type: "post",
		url: api_location + "mypage/as/put",
		data: {
			"as_code": as_code,
			"update_type": "APG",
		},
		dataType: "json",
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0084', null);
			//notiModal("A/S 요금 결제처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				location.href = '/mypage?mypage_type=as_third';
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function clickOrderToBtn() {
	let country = getLanguage();

	let popup = document.querySelector('.order_to_popup');

	let order_to_btn = document.querySelector('.add_order_to_btn');

	order_to_btn.addEventListener('click', function (e) {
		initOrderTo();

		if (country == 'EN' || country == 'CN') {
			getAsCountryInfo(country, null, null);
		}
		popup.classList.remove('hidden');
	});

	let close_btn = document.querySelector('.close_btn');

	close_btn.addEventListener('click', function (e) {
		let as_order_to_input_wrap = document.querySelector(".as_order_to_input_wrap");
		let as_order_to_result = document.querySelector(".as_order_to_result");
		let as_order_to_complete_btn = document.querySelector(".as_order_to_complete_btn");
		let msg_direct = document.querySelector(".as_order_to_msg_direct");

		popup.querySelector('.to_place').value = "";
		popup.querySelector('.to_name').value = "";
		popup.querySelector('.to_mobile').value = "";
		popup.querySelector('.to_zipcode').value = "";
		popup.querySelector('.as_order_to_default_flg').checked = false;

		if (country == "KR") {
			popup.querySelector('#postcodify_as .keyword').value = "";
			popup.querySelector('.to_lot_addr').value = "";
			popup.querySelector('.to_road_addr').value = "";
			popup.querySelector('.to_detail_addr').value = "";
		} else {
			popup.querySelector('.to_city').value = "";
			popup.querySelector('.to_address').value = "";
			getAsCountryInfo(country, null, null);
		}

		as_order_to_result.classList.add("hidden");
		as_order_to_input_wrap.classList.remove("hidden");
		as_order_to_complete_btn.dataset.order_to_idx = 0;

		asOrderToMsg.deselect();
		msg_direct.classList.add("hidden");
		msg_direct.value = '';

		popup.classList.add('hidden');
	});

	let edit_btn = document.querySelector('.edit_order_to_btn');

	edit_btn.addEventListener('click', function (e) {
		// initOrderTo();

		getAsOrderToInfo(country);

		popup.classList.remove('hidden');
	});

	clickOrderToListBtn();
	checkMemberAsOrderTo();
}

function getAsOrderToInfo(country) {
	let popup = document.querySelector(".order_to_popup");
	let as_order_to_data_wrap = document.querySelector(".as_order_to_data_wrap");

	popup.querySelector('.to_place').value = as_order_to_data_wrap.querySelector(".as_to_place").textContent;
	popup.querySelector('.to_name').value = as_order_to_data_wrap.querySelector(".as_to_name").textContent;
	popup.querySelector('.to_mobile').value = as_order_to_data_wrap.querySelector(".as_to_mobile").textContent;
	popup.querySelector('.to_zipcode').value = as_order_to_data_wrap.querySelector(".as_to_zipcode").textContent;

	if (country == "KR") {
		popup.querySelector('.to_lot_addr').value = as_order_to_data_wrap.querySelector(".as_to_lot_addr").value;
		popup.querySelector('.to_road_addr').value = as_order_to_data_wrap.querySelector(".as_to_road_addr").value;
		popup.querySelector('.keyword').value = as_order_to_data_wrap.querySelector(".as_to_road_addr").value;
		popup.querySelector('.to_detail_addr').value = as_order_to_data_wrap.querySelector(".as_to_detail_addr").value;
	} else {
		let selCountry = document.querySelector(".as_to_country_code").value;
		let selProvince = document.querySelector(".as_to_province_idx").value;

		getAsCountryInfo(country, selCountry, selProvince);

		popup.querySelector('.to_address').value = as_order_to_data_wrap.querySelector(".as_to_detail_addr").value;
		popup.querySelector('.to_city').value = as_order_to_data_wrap.querySelector(".as_to_city").value;
	}

	let selIdx = null;
	let selText = document.querySelector(".as_order_to_data_wrap").querySelector('.as_order_memo').innerText;
	document.querySelector(".order_to_popup").querySelectorAll('.tui-select-box-item').forEach(function (el, index) {
		if (el.innerText == selText) {
			selIdx = el.dataset.index;
		}
	});

	if (selIdx != null) {
		//console.log(selIdx);
		asOrderToMsg.select(parseInt(selIdx));
	}
	else {
		asOrderToMsg.select('direct');
		let msg_direct = document.querySelector('.as_order_to_msg_direct');
		msg_direct.classList.remove('hidden');
		msg_direct.value = selText;
	}
}

function initOrderTo() {
	let country = getLanguage();
	let popup = document.querySelector('.order_to_popup');

	popup.querySelector('.to_place').value = "";
	popup.querySelector('.to_name').value = "";
	popup.querySelector('.to_mobile').value = "";
	popup.querySelector('.to_zipcode').value = "";

	if (country == "KR") {
		popup.querySelector('.keyword').value = "";
		popup.querySelector('.to_road_addr').value = "";
		popup.querySelector('.to_lot_addr').value = "";
		popup.querySelector('.to_detail_addr').value = "";
	} else {
		popup.querySelector('.to_city').value = "";
		popup.querySelector('.to_address').value = "";
	}

	asOrderToMsg.deselect();

	let input_wrap = popup.querySelector('.as_order_to_input_wrap');
	input_wrap.classList.remove('hidden');

	let order_to_result = document.querySelector('.as_order_to_result');
	order_to_result.classList.add('hidden');

	order_to_result.querySelector('.result_to_lot_addr').value = "";
	order_to_result.querySelector('.result_to_road_addr').value = "";
	order_to_result.querySelector('.result_to_country_code').value = "";
	order_to_result.querySelector('.result_to_province_idx').value = "";
	order_to_result.querySelector('.result_to_city').value = "";
	order_to_result.querySelector('.result_to_place').innerText = "";
	order_to_result.querySelector('.result_to_name').innerText = "";
	order_to_result.querySelector('.result_to_mobile').innerText = "";
	order_to_result.querySelector('.result_to_zipcode').innerText = "";
	order_to_result.querySelector('.result_to_addr').innerText = "";

	if (country == "KR") {
		order_to_result.querySelector('.result_to_detail_addr').innerText = "";
	}

	document.querySelector('.as_order_to_alert').classList.add('hidden');

	let as_order_to = document.querySelector('.as_order_to_data');
	as_order_to.classList.add('hidden');

	document.querySelector('.add_order_to_btn').classList.remove('disable');

	as_order_to.querySelector('.as_to_lot_addr').value = "";
	as_order_to.querySelector('.as_to_road_addr').value = "";
	as_order_to.querySelector('.as_to_country_code').value = "";
	as_order_to.querySelector('.as_to_province_idx').value = "";
	as_order_to.querySelector('.as_to_detail_addr').value = "";
	as_order_to.querySelector('.as_to_city').value = "";
	as_order_to.querySelector('.as_to_place').innerText = "";
	as_order_to.querySelector('.as_to_name').innerText = "";
	as_order_to.querySelector('.as_to_mobile').innerText = "";
	as_order_to.querySelector('.as_to_zipcode').innerText = "";
	as_order_to.querySelector('.as_to_address').innerText = "";
	as_order_to.querySelector('.as_order_memo').innerText = "";

	let as_payment_complete_noti_wrap = document.querySelector(".as_payment_complete_noti_wrap");

	as_payment_complete_noti_wrap.classList.add("hidden");
}

function clickOrderToListBtn() {
	let list_btn = document.querySelector('.order_to_list_btn');

	list_btn.addEventListener('click', function (e) {
		$.ajax({
			type: "post",
			url: api_location + "order/pg/to/get",
			dataType: "json",
			error: function (d) {
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0047', null);
				//notiModal("배송지 목록 조회처리중 오류가 발생했습니다.");
			},
			success: function (d) {
				if (d.code == 200) {
					let data = d.data;
					let list_body = document.querySelector('.order_to_list_body');
					let order_to_list_html = "";
					let country = getLanguage();

					if (data != null && data.length > 0) {

						data.forEach(function (row) {
							let delete_txt = "";
							let addr_txt = "";
							let province_txt = "";
							let country_data_html = "";

							switch (country) {
								case "KR":
									delete_txt = "삭제하기";
									addr_txt = row.to_road_addr + " " + row.to_detail_addr;
									country_data_html = `
										<input class="to_lot_addr" type="hidden" value="${row.to_lot_addr}">
										<input class="to_road_addr" type="hidden" value="${row.to_road_addr}">
									`;
									break;
								case "EN":
									delete_txt = "Delete";
									province_txt = row.to_province_name != null ? row.to_province_name + ", " : "";
									addr_txt = row.to_address + ", " + row.to_city + ", " + province_txt + row.to_country_name;
									country_data_html = `
										<input class="to_country_code_data" type="hidden" value="${row.to_country_code}">
										<input class="to_province_idx_data" type="hidden" value="${row.to_province_idx}">
										<input class="to_city_data" type="hidden" value="${row.to_city}">
									`;
									break;
								case "CN":
									delete_txt = "删除";
									province_txt = row.to_province_name != null ? row.to_province_name + ", " : "";
									addr_txt = row.to_address + ", " + row.to_city + ", " + province_txt + row.to_country_name;
									country_data_html = `
										<input class="to_country_code_data" type="hidden" value="${row.to_country_code}">
										<input class="to_province_idx_data" type="hidden" value="${row.to_province_idx}">
										<input class="to_city_data" type="hidden" value="${row.to_city}">
									`;
									break;
							}

							order_to_list_html += `
								<div class="order_to_container">
									<div class="order_to_data_wrap" data-order_to_idx="${row.order_to_idx}">
										${country_data_html}
										<div>${row.to_place}</div>
										<div>${row.to_name} / ${row.to_mobile}</div>
										<div>(${row.to_zipcode}) ${addr_txt}</div>
									</div>
									<div class="order_to_close_btn delete_order_to" data-order_to_idx="${row.order_to_idx}">${delete_txt}</div>
								</div>
							`;
						});
					} else {
						let exception_msg = "";

						switch (country) {
							case "KR":
								exception_msg = "등록된 배송지 정보가 없습니다.";
								break;

							case "EN":
								exception_msg = "There is no history.";
								break;

							case "CN":
								exception_msg = "没有查询到相关资料。​";
								break;

						}
						order_to_list_html += `
							<div class="order_to_container_none">
								${exception_msg}
							</div>
						`;
					}

					list_body.innerHTML = order_to_list_html;

					let list_popup = document.querySelector('.as_order_to_list');
					list_popup.classList.toggle('hidden');

					getOrderToInfo();
					deleteOrderToInfo();
				} else {
					notiModal(d.msg);
				}
			}
		});
	});

	let close_btn = document.querySelector('.order_to_list_close_btn');
	close_btn.addEventListener('click', function (e) {
		let list_popup = close_btn.offsetParent;
		list_popup.classList.toggle('hidden');
	});
}

function getOrderToInfo() {
	let order_to_data = document.querySelectorAll('.order_to_data_wrap');

	order_to_data.forEach(order_to => {
		order_to.addEventListener('click', function (e) {
			let popup = document.querySelector('.order_to_popup');
			let list_popup = document.querySelector('.as_order_to_list');

			let input_wrap = popup.querySelector('.as_order_to_input_wrap');
			let order_to_result = popup.querySelector('.as_order_to_result');

			let order_to_idx = order_to.dataset.order_to_idx;

			if (order_to_idx > 0) {
				$.ajax({
					type: "post",
					url: api_location + "order/pg/to/get",
					dataType: "json",
					data: {
						'order_to_idx': order_to_idx
					},
					error: function (d) {
						makeMsgNoti(getLanguage(), 'MSG_F_ERR_0047', null);
						//notiModal("배송지 목록 조회처리중 오류가 발생했습니다.");
					},
					success: function (d) {
						if (d.code == 200) {
							let data = d.data[0];

							if (data.order_to_idx > 0) {
								order_to_result.querySelector('.result_to_place').innerText = data.to_place;
								order_to_result.querySelector('.result_to_name').innerText = data.to_name;
								order_to_result.querySelector('.result_to_mobile').innerText = data.to_mobile;
								order_to_result.querySelector('.result_to_zipcode').innerText = data.to_zipcode;

								if (country == "KR") {
									order_to_result.querySelector('.result_to_lot_addr').innerText = data.to_lot_addr;
									order_to_result.querySelector('.result_to_road_addr').innerText = data.to_road_addr;
									order_to_result.querySelector('.result_to_addr').innerText = data.to_road_addr;
									order_to_result.querySelector('.result_to_detail_addr').innerText = data.to_detail_addr;
								} else {
									let province_txt = "";
									let addr_txt = "";

									province_txt = data.to_province_name != null ? data.to_province_name + " " : "";
									addr_txt = data.to_address + " " + data.to_city + " " + province_txt + data.to_country_name;

									order_to_result.querySelector('.result_to_country_code').value = data.to_country_code;
									order_to_result.querySelector('.result_to_province_idx').value = data.to_province_idx;
									order_to_result.querySelector('.result_to_city').value = data.to_city;
									order_to_result.querySelector('.result_to_addr').innerText = addr_txt;
								}

								order_to_result.classList.remove('hidden');

								list_popup.classList.add('hidden');
								input_wrap.classList.add('hidden');

								document.querySelector('.as_order_to_complete_btn').dataset.order_to_idx = data.order_to_idx;
							} else {
								makeMsgNoti(getLanguage(), 'MSG_F_WRN_0029', null);
								//notiModal('선택한 배송지의 정보가 존재하지 않습니다. 배송지를 다시 선택해주세요.');
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

function deleteOrderToInfo() {
	let delete_order_to = document.querySelectorAll('.delete_order_to');
	delete_order_to.forEach(order_to => {
		order_to.addEventListener('click', function (e) {
			let order_to_idx = order_to.dataset.order_to_idx;
			if (order_to_idx > 0) {
				$.ajax({
					type: "post",
					url: api_location + "order/pg/to/delete",
					dataType: "json",
					data: {
						'order_to_idx': order_to_idx
					},
					error: function (d) {
						makeMsgNoti(getLanguage(), 'MSG_F_ERR_0049', null);
						//notiModal("배송지 목록 삭제처리중 오류가 발생했습니다.");
					},
					success: function (d) {
						if (d.code == 200) {
							let container = order_to.parentNode;
							container.remove();
							makeMsgNoti(getLanguage(), 'MSG_F_INF_0003', null);
							//notiModal('선택한 배송지 정보가 삭제되었습니다.');
						} else {
							notiModal(d.msg);
						}
					}
				});
			}
		});
	})
}

function checkMemberAsOrderTo() {
	let country = getLanguage();
	let complete_btn = document.querySelector('.as_order_to_complete_btn');
	let msg_direct = document.querySelector(".as_order_to_msg_direct");

	complete_btn.addEventListener('click', function (e) {
		console.log('checkMemberAsOrderTo');
		
		let as_idx = $('.as_tab_current').find('.as_idx').val();
		let to_place = null;
		let to_name = null;
		let to_mobile = null;
		let to_zipcode = null;
		let order_memo = null;

		let to_lot_addr = null;
		let to_road_addr = null;
		let to_detail_addr = null;

		let to_country = null;
		let to_country_code = null;
		let to_province = null;
		let to_province_idx = null;
		let to_city = null;
		let to_address = null;

		let popup = document.querySelector('.order_to_popup');

		let order_to_idx = e.currentTarget.dataset.order_to_idx;


		if (order_to_idx == 0) {

			to_place = popup.querySelector('.to_place').value;
			if (to_place.length == 0) {
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0038', null);
				//notiModal('배송지명을 입력해주세요.');
				return false;
			}

			to_name = popup.querySelector('.to_name').value;
			if (to_name.length == 0) {
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0025', null);
				//notiModal('수령자를 입력해주세요.');
				return false;
			}

			to_mobile = popup.querySelector('.to_mobile').value;
			if (to_mobile.length == 0) {
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0001', null);
				//notiModal('휴대전화를 입력해주세요.');
				return false;
			}

			to_zipcode = popup.querySelector('.to_zipcode').value;

			if (country == "KR") {
				to_lot_addr = popup.querySelector('.to_lot_addr').value;
				to_road_addr = popup.querySelector('.to_road_addr').value;

				if (to_zipcode.length == 0 || (to_lot_addr.length == 0 && to_road_addr.length == 0)) {
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0009', null);
					//notiModal('주소를 입력해주세요.');
					return false;
				}

				to_detail_addr = popup.querySelector('.to_detail_addr').value;

				if (to_detail_addr.length == 0) {
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0032', null);
					//notiModal('상세주소를 입력해주세요.');
					return false;
				}
			} else {
				to_country = countryAsSelectBox.getSelectedItem().label;
				to_country_code = countryAsSelectBox.getSelectedItem().value;

				if (provinceAsSelectBox.getSelectedItem() != null) {
					to_province = provinceAsSelectBox.getSelectedItem().label;
					to_province_idx = provinceAsSelectBox.getSelectedItem().value;
				}

				to_city = popup.querySelector(".to_city").value;

				if (to_zipcode.length == 0 || (to_country.length == 0 && to_city.length == 0)) {
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0009', null);
					//notiModal('주소를 입력해주세요.');
					return false;
				}

				to_address = popup.querySelector(".to_address").value;

				if (to_address.length == 0) {
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0032', null);
					//notiModal('상세주소를 입력해주세요.');
					return false;
				}
			}

			let checked_flg = popup.querySelector('.as_order_to_default_flg').checked;

			let order_data = {
				"to_place": to_place,
				"to_name": to_name,
				"to_mobile": to_mobile,
				"to_zipcode": to_zipcode,
				"to_lot_addr": to_lot_addr,
				"to_road_addr": to_road_addr,
				"to_detail_addr": to_detail_addr,
				"to_country_code": to_country_code,
				"to_province_idx": to_province_idx,
				"to_city": to_city,
				"to_address": to_address
			};

			if (checked_flg == true) {

				let check_result = addOrderTo(order_data);

				if (check_result != true) {
					makeMsgNoti(getLanguage(), 'MSG_F_ERR_0050', null);
					//notiModal('배송정보 등록처리중 오류가 발생했습니다.');
					return false;
				}
			}
		} else if (country != "KR") {
			// order_to_idx > 0 && country != "KR"
			to_country_code = document.querySelector(".result_to_country_code").value;
			to_province_idx = document.querySelector(".result_to_province_idx").value;
			to_city = document.querySelector(".result_to_city").value;
		}

		if (asOrderToMsg.getSelectedItem() != null) {
			if (asOrderToMsg.getSelectedItem().value == "direct") {
				order_memo = document.querySelector(".as_order_to_msg_direct").value;
			} else {
				order_memo = asOrderToMsg.getSelectedItem().label;
			}

		}

		$.ajax({
			type: "post",
			url: api_location + "mypage/as/put",
			dataType: "json",
			data: {
				'as_idx': as_idx,
				'order_to_idx': order_to_idx,
				'update_type': "APG",
				'to_place': to_place,
				'to_name': to_name,
				'to_mobile': to_mobile,
				'to_zipcode': to_zipcode,
				'to_lot_addr': to_lot_addr,
				'to_road_addr': to_road_addr,
				'to_detail_addr': to_detail_addr,
				'to_country_code': to_country_code,
				'to_province_idx': to_province_idx,
				'to_city': to_city,
				'to_address': to_address,
				'order_memo': order_memo
			},
			async: false,
			error: function (d) {
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0041', null);
				//notiModal("배송지 정보 입력처리중 오류가 발생했습니다.");
			},
			success: function (d) {
				if (d.code == 200) {
					let data = d.data;
					let data_wrap = document.querySelector('.as_order_to_data');
					let data_result_wrap = document.querySelector(".as_order_to_result");
					let as_order_to_input_wrap = document.querySelector(".as_order_to_input_wrap");

					data_wrap.querySelector('.as_to_place').innerText = data.to_place;
					data_wrap.querySelector('.as_to_name').innerText = data.to_name;
					data_wrap.querySelector('.as_to_mobile').innerText = data.to_mobile;
					data_wrap.querySelector('.as_to_zipcode').innerText = data.to_zipcode;
					data_wrap.querySelector('.as_to_detail_addr').value = data.to_detail_addr;
					data_wrap.querySelector('.as_order_memo').innerText = data.order_memo;

					if (country == "KR") {
						data_wrap.querySelector('.as_to_lot_addr').value = data.to_lot_addr;
						data_wrap.querySelector('.as_to_road_addr').value = data.to_road_addr;
						data_wrap.querySelector('.as_to_address').innerText = data.to_road_addr ? data.to_road_addr + " " + data.to_detail_addr : data.to_lot_addr + " " + data.to_detail_addr;
					} else {
						data_wrap.querySelector('.as_to_country_code').value = data.to_country_code;
						data_wrap.querySelector('.as_to_province_idx').value = data.to_province_idx;
						data_wrap.querySelector('.as_to_city').value = data.to_city;
						data_wrap.querySelector('.as_to_address').innerText = data.to_road_addr ? data.to_detail_addr + " " + data.to_road_addr : data.to_detail_addr + " " + data.to_lot_addr;
					}

					let add_order_to_btn = document.querySelector('.add_order_to_btn');
					let edit_order_to_btn = document.querySelector('.edit_order_to_btn');

					add_order_to_btn.classList.add('disable');
					add_order_to_btn.disabled = true;

					data_result_wrap.classList.add("hidden");
					edit_order_to_btn.classList.remove("hidden");
					as_order_to_input_wrap.classList.remove("hidden");
					popup.classList.add('hidden');
					data_wrap.classList.remove('hidden');
					complete_btn.dataset.order_to_idx = 0;

					popup.querySelector('.to_place').value = "";
					popup.querySelector('.to_name').value = "";
					popup.querySelector('.to_mobile').value = "";
					popup.querySelector('.to_zipcode').value = "";

					if (country == "KR") {
						popup.querySelector('#postcodify_as .keyword').value = "";
						popup.querySelector('.to_lot_addr').value = "";
						popup.querySelector('.to_road_addr').value = "";
						popup.querySelector('.to_detail_addr').value = "";
					} else {
						popup.querySelector('.to_city').value = "";
						popup.querySelector('.to_address').value = "";
						countryAsSelectBox.deselect();
						provinceAsSelectBox.deselect();
					}
					popup.querySelector('.as_order_to_default_flg').checked = false;
					asOrderToMsg.deselect();
					msg_direct.classList.add("hidden");
					msg_direct.value = '';

					makeMsgNoti(getLanguage(), 'MSG_F_INF_0010', null);
					//notiModal('배송 주소가 정상적으로 등록되었습니다.');
				} else {
					notiModal(d.msg);
				}
			}
		});
	});
}

function addOrderTo(data) {
	let check_result = false;

	$.ajax({
		type: "post",
		url: api_location + "order/pg/to/add",
		dataType: "json",
		data: {
			'to_place': data.to_place,
			'to_name': data.to_name,
			'to_mobile': data.to_mobile,
			'to_zipcode': data.to_zipcode,
			'to_lot_addr': data.to_lot_addr,
			'to_road_addr': data.to_road_addr,
			'to_detail_addr': data.to_detail_addr,
			'to_country_code': data.to_country_code,
			'to_province_idx': data.to_province_idx,
			'to_city': data.to_city,
			'to_address': data.to_address
		},
		async: false,
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0050', null);
			//notiModal("배송지 등록처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				check_result = true;
			} else {
				notiModal(d.msg);
			}
		}
	});

	return check_result;
}

function showImgAsInfoDetail() {
	let imgInfo = document.querySelectorAll(".as_current_status .img_info_content");

	imgInfo.forEach(img => {
		let imgSrc = img.querySelector("img").getAttribute("src");

		img.addEventListener("click", function () {
			let popupWindow = window.open("", "_blank", "scrollbars=yes");
			popupWindow.document.write('<html><head><title>Image Preview</title></head><body></body></html>');
			popupWindow.document.body.style.background = '#fff';
			popupWindow.document.body.style.display = 'flex';
			popupWindow.document.body.style.justifyContent = 'center';
			popupWindow.document.body.style.alignItems = 'center';
			popupWindow.document.body.style.margin = '0';
			popupWindow.document.body.style.padding = '0';
			popupWindow.document.body.style.height = '100vh';
			popupWindow.document.body.style.cursor = 'pointer';
			popupWindow.document.body.innerHTML = `<img src="${imgSrc}" style="max-width: 100%; max-height: 100%; margin: auto;">`;
			popupWindow.document.body.addEventListener('click', function () {
				popupWindow.close();
			});
		});
	});
}

function addCompleteListBtnEvent() {
	let as_current_status = document.querySelector(".as_current_status");
	let as_current_list = document.querySelector(".as_current_list");

	$(".show_as_complete_list_btn").on("click", function () {
		getAsCompleteInfoList();
		getMemberAsInfoList();

		as_current_status.classList.add("hidden");
		as_current_list.classList.remove("hidden");

		$(".as_tab").removeClass("on");
		$(".as_tab[data-tab_num='four']").addClass("on");

		$(".as__tab__wrap").find(".as_tab_current").hide();
		$(".as__tab__wrap").find(".tab.four").show();
	});
}

function setTossPayment(as_code, as_price) {
	tossPayments.requestPayment('카드', {
		amount: as_price,
		orderId: as_code,
		orderName: "A/S 요금 결제",
		customerName: "<?=$_SESSION['MEMBER_NAME']?>",
		successUrl: domain_url + '/as/payment',
		failUrl: domain_url + '/as/payment',
	});
}

function addAsMobileHyprenEvent() {
	let mobileInput = document.querySelector(".order_to_popup .to_mobile");

	if (getLanguage() == "KR") {
		mobileInput.addEventListener("input", function (e) {
			mobileAutoHyphen(e.target);
		});
	}
}

function selectDirectMsg() {
	let select_item = document.querySelectorAll(".as_order_to_msg_list .tui-select-box-item")
	select_item.forEach(item => {
		item.addEventListener('click', function (ev) {
			let target = ev.target;
			let target_value = target.dataset.value;
			let msg_direct = document.querySelector(".as_order_to_msg_direct");
			if (target_value == "direct") {
				msg_direct.classList.remove("hidden");
			} else {
				msg_direct.classList.add("hidden");
				msg_direct.value = '';
			}
		})
	})
};