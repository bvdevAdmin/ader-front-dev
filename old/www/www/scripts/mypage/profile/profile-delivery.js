document.addEventListener("DOMContentLoaded", function () {
	getOrderToList();
	addDeliveryBtnEvent();
	const country = getLanguage();

	if (country == 'KR') {
		$('.profile_addr_EN').remove();
		$('.profile_addr_CN').remove();
		
		$('#postcodify').postcodify({
			insertPostcode5: ".order_to_zipcode",
			insertAddress: ".order_to_road_addr",
			insertDetails: ".order_to_detail_addr",
			insertJibeonAddress: ".order_to_lot_addr",
			hideOldAddresses: false,
			results: ".post_change_result",
			hideSummary: true,
			useFullJibeon: true,
			onReady: function () {
				$('.post_change_result').hide();
				$(".postcodify_search_controls .keyword").attr("placeholder", "예) 성동구 연무장길 53, 성수동2가 315-57");
			},
			onSuccess: function () {
				$('.post_change_result').show();
				$("#postcodify div.postcode_search_status.too_many").hide();
			},
			afterSelect: function (selectedEntry) {
				$("#postcodify div.postcode_search_result").remove();
				$("#postcodify div.postcode_search_status.too_many").hide();
				$("#postcodify div.postcode_search_status.summary").hide();
				$('.post_change_result').hide();
				$("#entry_box").show();
				$("#entry_details").focus();
				$(".postcodify_search_controls .keyword").val($('.order_to_road_addr').val());
			}
		});
	} else if (country == 'EN') {
		$('.profile_addr_KR').remove();
		$('.profile_addr_CN').remove();
	} else if (country == 'CN') {
		$('.profile_addr_EN').remove();
		$('.profile_addr_KR').remove();
	}
	
	getCountryInfo();

	addDeliveryMobileHyprenEvent();
});

function addDeliveryBtnEvent() {
	$(".add_order_to").on("click", function () {
		$('.profile__tab').hide();
		$('.order__to__update__wrap').show();
	});

	$(".close_order_to_update").on("click", function () {
		$('.hidden_order_to_idx').val(0);
		$('.order_to_place').val('');
		$('.order_to_name').val('');
		$('.order_to_mobile').val('');
		$('.keyword').val('');
		$('.order_to_zipcode').val('');
		$('.order_to_lot_addr').val('');
		$('.order_to_road_addr').val('');
		$('.order_to_detail_addr').val('');
		$('.order_to_detail_addr').val('');
		$('input[name=country_code]').val('');
		$('input[name=province_idx]').val('');
		$('input[name=zipcode]').val('');
		$('input[name=city]').val('');
		$('input[name=address]').val('');
		$('.order_to_default_flg').prop('checked', false);
		$('.post_change_result').hide();
		$('.order__to__update__wrap').hide();
		$('.profile__delivery__wrap').show();
		
		getCountryInfo();
	});
	$(".change_order_to_btn").on("click", checkOrderToAction);
}

let country_select_box = null;
let province_select_box = null;

function getCountryInfo() {
	let country_info = [];
	let country_disabled = false;
	let default_country = null;

	$.ajax({
		type: 'POST',
		url: api_location + "account/country/get",
		dataType: 'json',
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0002', null);
			//notiModal("해외 국가 정보얻기에 실패 했습니다.");
		},
		success: function (d) {
			if (d.code == "200") {
				if (d.data != null) {
					country_info = d.data;
					default_country = d.data[0].value;
				}
			} else {
				if (d.code == "303") {
					$('.warn__msg.essential').css('visibility', 'visible');
					$('.warn__msg.essential').text(d.msg);
				} else {
					notiModal(d.msg);
				}
			}
			
			let result_country = d.country;
			if (country_info.length > 0 && result_country != null) {
				if (result_country == 'KR') {
					$('.country-select-box').html('');
				} else {
					if (result_country == 'CN') {
						country_disabled = true;
					}
					
					let country_select_box = new tui.SelectBox('.country-select-box', {
						data: country_info,
						autofocus: false,
						disabled: country_disabled
					});
					
					$('.profile_addr_' + country + ' input[name=country_code]').val(default_country);
					
					getProvinceInfo(default_country, null);
					
					country_select_box.on("change", ev => {
						let country_value = ev.curr.getValue();
						$('.profile_addr_' + country + ' input[name=country_code]').val(country_value);
						getProvinceInfo(country_value, null);
					});
				}
			}
		}
	});
}
function getProvinceInfo(country_code, province_idx) {
	let provinceInfo = [];
	let provinceFlg = true;
	let detaultProvince = null;
	$.ajax({
		url: api_location + "account/province/get",
		type: 'POST',
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
						detaultProvince = d.data[0].value;
					}
				}
			}
			else {
				if (d.code == "303") {
					$('.warn__msg.essential').css('visibility', 'visible');
					$('.warn__msg.essential').text(d.msg);
				}
				else {
					notiModal(d.msg);
				}
			}
			$('.profile_addr_' + country + ' input[name=province_idx]').val(detaultProvince);
			$('.province-select-box').html('');
			let provinceSelectBox = new tui.SelectBox('.province-select-box', {
				data: provinceInfo,
				autofocus: false,
				disabled: provinceFlg
			});
			if (province_idx != null) {
				let profileAddrWrap = $('.profile_addr_' + country);
				let provinceWrapDiv = $('.profile_addr_' + country + ' .province-select-box');
				let province_name = $(`.province-select-box .tui-select-box-item[data-value=${province_idx}]`).text();

				profileAddrWrap.find('input[name=province_idx]').val(province_idx);
				provinceWrapDiv.find('.tui-select-box-placeholder').text(province_name);
			}
			provinceSelectBox.on("change", ev => {
				let province_value = ev.curr.getValue();
				$('.profile_addr_' + country + ' input[name=province_idx]').val(province_value);
			});
		}
	});
}
function getOrderToList() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/member/order_to/list/get",
		dataType: "json",
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0046', null);
			//notiModal("계정", "배송지 목록을 불어오는데 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;

			if (code == 200) {
				let data = d.data;
				let defaultList = $('.default__list');
				let otherList = $('.other__list');
				let defaultListStr = "";
				let otherListStr = "";

				defaultList.html('');
				otherList.html('');

				if (data != null) {

					data.forEach((row) => {
						let addr = row.to_road_addr ? row.to_road_addr : row.to_lot_addr;
						let detailAddr = row.to_detail_addr ? row.to_detail_addr : '';

						let fullAddrStr = '';
						if (country == 'KR') {
							fullAddrStr = `${addr} ${detailAddr}`;
						}
						else {
							fullAddrStr = `${row.to_address}, ${row.to_city}, ${row.to_province_name}, ${row.to_country_name}`;
						}

						if (row.default_flg == true) {
							defaultListStr +=
								`
							  <tr class="default_destination">
								  <td>${row.to_place}</td>
								  <td>${row.to_name}</td>
								  <td>${row.to_mobile}</td>
								  <td>${fullAddrStr}</td>
								  <td>${row.to_zipcode}</td>
								  <td class="order_to_btn_td">
									  <div class="order_to_btn_wrap">
											<div class="gray__mypage__btn update_order_to" idx="${row.order_to_idx}" data-i18n="p_edit">수정</div>
											<div class="white__full__width__btn delete_order_to"  data-i18n="p_delete" idx="${row.order_to_idx}">삭제</div>
									  </div>
								  </td>
							  </tr>
						  `;
						} else {
							otherListStr +=
								`
							<tr class="other_destination">
								<td>
									<div class="other_destination_header">
										<div>${row.to_place}</div>
										<div class="order_to_idx change_default_order_to" idx="${row.order_to_idx}" data-i18n="p_set_default_address">기본 배송지로 저장</div>
									</div>
								</td>
								<td>${row.to_name}</td>
								<td>${row.to_mobile}</td>
								<td>${fullAddrStr}</td>
								<td>${row.to_zipcode}</td>
								<td class="order_to_btn_td">
									<div class="order_to_btn_wrap">
										<div class="gray__mypage__btn update_order_to" idx="${row.order_to_idx}" data-i18n="p_edit">수정</div>
										<div class="white__full__width__btn delete_order_to"  data-i18n="p_delete" idx="${row.order_to_idx}">삭제</div>
									</div>
								</td>
							</tr>
						`;
						}
					});

					let default_exception_msg = "";
					let other_exception_msg = "";

					if (defaultListStr.length == 0) {

						switch (getLanguage()) {
							case "KR":
								default_exception_msg = "기본 배송지 정보가 없습니다.";
								break;

							case "EN":
								default_exception_msg = "There is no history.";
								break;

							case "CN":
								default_exception_msg = "没有查询到相关资料。​";
								break;

						}
						defaultListStr =
							`
							<tr>
								<td class="no_order_to_msg">
									<div>${default_exception_msg}</div>
								</td>
							</tr>
						`;
					}

					if (otherListStr.length == 0) {
						switch (getLanguage()) {
							case "KR":
								other_exception_msg = "다른 배송지 정보가 없습니다.";
								break;

							case "EN":
								other_exception_msg = "There is no history.";
								break;

							case "CN":
								other_exception_msg = "没有查询到相关资料。​";
								break;

						}
						otherListStr =
							`
							  <tr>
								  <td class="no_order_to_msg">
									  <div>${other_exception_msg}</div>
								  </td>
							  </tr>
						  `;
					}

					defaultList.append(defaultListStr);
					otherList.append(otherListStr);

					$(".update_order_to").on("click", function () {
						let order_to_idx = $(this).attr('idx');

						$('.profile__tab').hide();
						$('.hidden_order_to_idx').val(order_to_idx);
						getOrderTo();
						$('.order__to__update__wrap').show();
					});

					$(".delete_order_to").on("click", function () {
						deleteOrderTo(this);
					});

					$(".change_default_order_to").on("click", function () {
						changeDefaultOrderTo(this);
					});

					changeLanguageR();
				} else {
					let default_exception_msg = "";
					let other_exception_msg = "";
					switch (getLanguage()) {
						case "KR":
							default_exception_msg = "기본 배송지 정보가 없습니다.";
							other_exception_msg = "다른 배송지 정보가 없습니다.";
							break;

						case "EN":
							default_exception_msg = "There is no history.";
							other_exception_msg = "There is no history.";
							break;

						case "CN":
							default_exception_msg = "没有查询到相关资料。​";
							other_exception_msg = "没有查询到相关资料。​";
							break;

					}
					defaultListStr =
						`
						  <tr>
							  <td class="no_order_to_msg">
								  <div>${default_exception_msg}</div>
							  </td>
						  </tr>
					  `;
					otherListStr =
						`
						  <tr>
							  <td class="no_order_to_msg">
								  <div>${other_exception_msg}</div>
							  </td>
						  </tr>
					  `;

					defaultList.append(defaultListStr);
					otherList.append(otherListStr);
				}
			} else {
				if(d.msg != null){
					notiModal(d.msg);
					if (d.code = 401) {
						$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
					}
				}
				else{
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0002', null);
				}
			}
		}
	});
}

function getOrderTo() {
	let order_to_idx = $('.hidden_order_to_idx').val();
	if (order_to_idx != '' || order_to_idx != null) {
		$.ajax({
			type: "post",
			url: api_location + "mypage/member/order_to/get",
			data: {
				"order_to_idx": order_to_idx
			},
			dataType: "json",
			error: function () {
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0051', null);
				//notiModal("계정", "배송지 개별정보 조회에 실패했습니다");
			},
			success: function (d) {
				let code = d.code;
				if (code == 200) {
					let data = d.data;
					if (data != null) {
						let row = data[0];
						$('.order_to_place').val(row.to_place);
						$('.order_to_name').val(row.to_name);
						$('.order_to_mobile').val(row.to_mobile);
						$('.order_to_zipcode').val(row.to_zipcode);
						if (country == "KR") {
							$('.order_to_lot_addr').val(row.to_lot_addr);
							$('.order_to_road_addr').val(row.to_road_addr);
							let addr = row.to_road_addr ? row.to_road_addr : row.to_lot_addr;
							$('.keyword').val(addr);
							$('.order_to_detail_addr').val(row.to_detail_addr);
						}
						else if (country == "EN" || country == "CN") {
							let profileAddrWrap = $('.profile_addr_' + country);
							let countryWrapDiv = profileAddrWrap.find('.country-select-box');
							let country_code = row.to_country_code;
							let country_name = $(`.country-select-box .tui-select-box-item[data-value=${country_code}]`).text();

							profileAddrWrap.find('input[name=country_code]').val(country_code);
							countryWrapDiv.find('.tui-select-box-placeholder').text(country_name);

							let province_idx = row.to_province_idx;

							getProvinceInfo(country_code, province_idx);

							profileAddrWrap.find('input[name=city]').val(row.to_city);
							profileAddrWrap.find('input[name=zipcode]').val(row.to_zipcode);
							profileAddrWrap.find('input[name=address]').val(row.to_address);
						}
						let flg = row.default_flg == 1 ? true : false;
						$('.order_to_default_flg').prop('checked', flg);
					}
				}
				else {
					if(d.msg != null){
						notiModal(d.msg);
						if (d.code = 401) {
							$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
						}
					}
					else{
						makeMsgNoti(getLanguage(), 'MSG_F_WRN_0002', null);
					}
				}
			}
		})
	}
}

function deleteOrderTo(obj) {
	let order_to_idx = $(obj).attr('idx');
	if (order_to_idx != '' || order_to_idx != null) {
		$.ajax({
			type: "post",
			url: api_location + "mypage/member/order_to/delete",
			data: {
				"order_to_idx": order_to_idx
			},
			dataType: "json",
			error: function () {
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0045', null);
				//notiModal("계정", "배송지 삭제에 실패했습니다.");
			},
			success: function (d) {
				let code = d.code;
				if (code == 200) {
					getOrderToList();
					makeMsgNoti(getLanguage(), 'MSG_F_INF_0009', null);
					//notiModal("계정", "배송지 삭제에 성공했습니다.");
				}
				else {
					if(d.msg != null){
						notiModal(d.msg);
						if (d.code = 401) {
							$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
						}
					}
					else{
						makeMsgNoti(getLanguage(), 'MSG_F_WRN_0002', null);
					}
				}
			}
		});
	}
}

function changeDefaultOrderTo(obj) {
	let order_to_idx = $(obj).attr('idx');
	let default_flg = true;

	if (order_to_idx != '' || order_to_idx != null) {
		$.ajax({
			type: "post",
			url: api_location + "mypage/member/order_to/put",
			data: {
				"order_to_idx": order_to_idx,
				"default_flg": default_flg
			},
			dataType: "json",
			error: function () {
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0069', null);
				//notiModal("계정", "기본 배송지 변경에 실패했습니다.");
			},
			success: function (d) {
				let code = d.code;

				if (code == 200) {
					getOrderToList();
					makeMsgNoti(getLanguage(), 'MSG_F_INF_0014', null);
				} else {
					if(d.msg != null){
						notiModal(d.msg);
						if (d.code = 401) {
							$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
						}
					}
					else{
						makeMsgNoti(getLanguage(), 'MSG_F_WRN_0002', null);
					}
				}
			}
		})
	}
}

function checkOrderToAction() {
	let order_to_idx = parseInt($('.hidden_order_to_idx').val());

	if (order_to_idx > 0) {
		putOrderToInfo(order_to_idx);
	} else {
		addOrderToInfo();
	}
}

function addOrderToInfo() {
	$('.delivery_regist_error p').html('&nbsp');

	let deliPlaceMsg = "";
	let deliNameMsg = "";
	let deliMobileMsg = "";

	switch (getLanguage()) {
		case "KR":
			deliPlaceMsg = "배송지명을 작성해주세요.";
			deliNameMsg = "이름을 작성해주세요.";
			deliMobileMsg = "전화번호를 작성해주세요.";
			break;
		case "EN":
			deliPlaceMsg = "Please fill out the place.";
			deliNameMsg = "Please fill out the name.";
			deliMobileMsg = "Please fill out the mobile number.";
			break;
		case "CN":
			deliPlaceMsg = "请填写地址名。";
			deliNameMsg = "请填写姓名。";
			deliMobileMsg = "请填写手机号码。";
			break;
	}
	let to_place = $('.order_to_place').val();
	if (to_place == '' || to_place == null) {
		$('.delivery_regist_error p').text(deliPlaceMsg);
		return false;
	}
	let to_name = $('.order_to_name').val();
	if (to_name == '' || to_name == null) {
		$('.delivery_regist_error p').text(deliNameMsg);
		return false;
	}
	let to_mobile = $('.order_to_mobile').val();
	if (to_mobile == '' || to_mobile == null) {
		$('.delivery_regist_error p').text(deliMobileMsg);
		return false;
	}

	let to_zipcode = '';
	let to_lot_addr = '';
	let to_road_addr = '';
	let to_detail_addr = '';
	let addrObj = null;
	let country_code = '';
	let province_idx = '';
	let city = '';
	let address = '';

	if (country == 'KR') {
		to_zipcode = $('.order_to_zipcode').val();
		if (to_zipcode == '' || to_zipcode == null) {
			$('.delivery_regist_error p').text("우편번호를 작성해주세요.");
			return false;
		}
		to_lot_addr = $('.order_to_lot_addr').val();
		if (to_lot_addr == '' || to_lot_addr == null) {
			$('.delivery_regist_error p').text("지번명주소를 작성해주세요.");
			return false;
		}

		to_road_addr = $('.order_to_road_addr').val();
		if (to_road_addr == '' || to_road_addr == null) {
			$('.delivery_regist_error p').text("도로명주소를 작성해주세요.");
			return false;
		}

		to_detail_addr = $('.order_to_detail_addr').val();
	}

	else {
		addrObj = $('.profile_addr_' + country);
		country_code = addrObj.find('input[name=country_code]').val();
		province_idx = addrObj.find('input[name=province_idx]').val();
		city = addrObj.find('input[name=city]').val();
		address = addrObj.find('input[name=address]').val();
		to_zipcode = addrObj.find('input[name=zipcode]').val();
		if (to_zipcode == '' || to_zipcode == null) {
			$('.delivery_regist_error p').text("Input zipcode please");
			return false;
		}
		if (province_idx == '') {
			addrObj.find('input[name=province_idx]').val("0")
		}
		if (city == '') {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0046', null);
			//notiModal('Input city please');
			return false;
		}
		if (address == '') {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0009', null);
			//notiModal('Input address please');
			return false;
		}
	}



	let default_flg = $('.order_to_default_flg').is(':checked');

	$.ajax({
		type: "post",
		url: api_location + "mypage/member/order_to/add",
		data: {
			"to_place": to_place,
			"to_name": to_name,
			"to_mobile": to_mobile,
			"to_zipcode": to_zipcode,
			"to_lot_addr": to_lot_addr,
			"to_road_addr": to_road_addr,
			"to_detail_addr": to_detail_addr,
			"country_code": country_code,
			"province_idx": province_idx,
			"city": city,
			"address": address,
			"default_flg": default_flg
		},
		dataType: "json",
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0039', null);
			//notiModal("배송지 추가에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				$('.order_to_place').val('');
				$('.order_to_name').val('');
				$('.order_to_mobile').val('');
				$('.keyword').val('');
				$('.order_to_zipcode').val('');
				$('.order_to_lot_addr').val('');
				$('.order_to_road_addr').val('');
				$('.order_to_detail_addr').val('');
				$('.order_to_default_flg').prop('checked', false);
				$('.order__to__update__wrap').hide();

				getOrderToList();

				$('.other_list_wrap').show();
				$('.profile__delivery__wrap').show();

				makeMsgNoti(getLanguage(), 'MSG_F_INF_0006', null);
				//notiModal(successMsg);
			} else {
				if(d.msg != null){
					notiModal(d.msg);
					if (d.code = 401) {
						$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
					}
				}
				else{
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0037', null);
				}
			}
		}
	});
}

function putOrderToInfo(order_to_idx) {
	let to_place = $('.order_to_place').val();
	if (to_place == '' || to_place == null) {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0038', null);
		//notiModal("배송지명을 작성해주세요.");
		return false;
	}

	let to_name = $('.order_to_name').val();
	if (to_name == '' || to_name == null) {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0017', null);
		//notiModal("이름을 작성해주세요.");
		return false;
	}

	let to_mobile = $('.order_to_mobile').val();
	if (to_mobile == '' || to_mobile == null) {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0012', null);
		//notiModal("전화번호를 작성해주세요.");
		return false;
	}

	let to_zipcode = '';
	let to_lot_addr = '';
	let to_road_addr = '';
	let to_detail_addr = '';
	let addrObj = null;
	let country_code = '';
	let province_idx = '';
	let city = '';
	let address = '';

	if (country == 'KR') {
		to_zipcode = $('.order_to_zipcode').val();
		if (to_zipcode == '' || to_zipcode == null) {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0019', null);
			//notiModal("우편번호를 작성해주세요.");
			return false;
		}
		to_lot_addr = $('.order_to_lot_addr').val();
		if (to_lot_addr == '' || to_lot_addr == null) {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0008', null);
			//notiModal("지번주소를 작성해주세요.");
			return false;
		}

		to_road_addr = $('.order_to_road_addr').val();
		if (to_road_addr == '' || to_road_addr == null) {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0047', null);
			//notiModal("도로명주소를 작성해주세요.");
			return false;
		}

		to_detail_addr = $('.order_to_detail_addr').val();
	}
	else {
		addrObj = $('.profile_addr_' + country);
		country_code = addrObj.find('input[name=country_code]').val();
		province_idx = addrObj.find('input[name=province_idx]').val();
		city = addrObj.find('input[name=city]').val();
		address = addrObj.find('input[name=address]').val();
		to_zipcode = addrObj.find('input[name=zipcode]').val();
		if (to_zipcode == '' || to_zipcode == null) {
			$('.delivery_regist_error p').text("Input zipcode please");
			return false;
		}
		if (province_idx == '') {
			addrObj.find('input[name=province_idx]').val("0")
		}
		if (city == '') {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0046', null);
			//notiModal('Input city please');
			return false;
		}
		if (address == '') {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0009', null);
			//notiModal('Input address please');
			return false;
		}
	}

	let default_flg = $('.order_to_default_flg').is(':checked');

	$.ajax({
		type: "post",
		url: api_location + "mypage/member/order_to/put",
		data: {
			"order_to_idx": order_to_idx,
			"to_place": to_place,
			"to_name": to_name,
			"to_mobile": to_mobile,
			"to_zipcode": to_zipcode,
			"to_lot_addr": to_lot_addr,
			"to_road_addr": to_road_addr,
			"to_detail_addr": to_detail_addr,
			"country_code": country_code,
			"province_idx": province_idx,
			"city": city,
			"address": address,
			"default_flg": default_flg
		},
		dataType: "json",
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0043', null);
			//notiModal("계정", "배송지 정보 변경에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				$('.hidden_order_to_idx').val(0);
				$('.order_to_place').val('');
				$('.order_to_name').val('');
				$('.order_to_mobile').val('');
				$('.keyword').val('');
				$('.order_to_zipcode').val('');
				$('.order_to_lot_addr').val('');
				$('.order_to_road_addr').val('');
				$('.order_to_detail_addr').val('');
				$('.order_to_default_flg').prop('checked', false);
				$('.order__to__update__wrap').hide();

				getOrderToList();

				$('.other_list_wrap').show();
				$('.profile__delivery__wrap').show();

				makeMsgNoti(getLanguage(), 'MSG_F_INF_0020', null);
			}
			else {
				if(d.msg != null){
					notiModal(d.msg);
					if (d.code = 401) {
						$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
					}
				}
				else{
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0037', null);
				}
			}
		}
	})
}

function addDeliveryMobileHyprenEvent() {
	let mobileInput = document.querySelector(".order__to__update__wrap .order_to_mobile");
	
	if(getLanguage() == "KR") {
		mobileInput.addEventListener("input", function(e) {
			mobileAutoHyphen(e.target);
		});
	}
}