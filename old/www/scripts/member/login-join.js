$(function () {
	if(getLoginStatus() == 'true'){
		location.href="/main";
	}
	$('.warn__msg.tel_confirm').css('display', 'none');
	const country = getLanguage();

	if(country == 'KR'){
		$('.addr-EN').remove();
		$('.addr-CN').remove();
		if ($('#postcodify').find('postcodify_search_controls').length == 0) {
			$("#postcodify").postcodify({
				insertPostcode5: "#zipcode",
				insertAddress: "#road_addr",
				insertDetails: "#detail_ddr",
				insertJibeonAddress: "#lot_addr",
				hideOldAddresses: false,
				results: ".post-change-result",
				hideSummary: true,
				useFullJibeon: true,
				onReady: function () {
					document.querySelector(".post-change-result").style.display = "none";
					$(".postcodify_search_controls .keyword").attr("placeholder", "예) 성동구 연무장길 53, 성수동2가 315-57");
					$('.postcodify_search_controls .keyword').attr('chk-flg', 'false');
					// $(".post-change-result").hide();
				},
				onSuccess: function () {
					document.querySelector(".post-change-result").style.display = "block";
					$("#postcodify div.postcode_search_status.too_many").hide();
					// $(".post-change-result").hide();
	
					$('.input-row').css('position', 'relative');
					$('.post-change-result').css('position', 'absolute');
					$('.post-change-result').css('top', '0px');
				},
				afterSelect: function (selectedEntry) {
	
					$("#postcodify div.postcode_search_result").remove();
					$("#postcodify div.postcode_search_status.too_many").hide();
					$("#postcodify div.postcode_search_status.summary").hide();
					document.querySelector(".post-change-result").style.display = "none";
					$("#entry_box").show();
					$("#entry_details").focus();
					$(".postcodify_search_controls .keyword").val($("#road_addr").val());
	
					$('.input-row').css('position', 'relative');
					$('.post-change-result').css('position', 'absolute');
					$('.post-change-result').css('top', '0px');
				}
			});
	
			$('.postcodify_search_controls .keyword').keyup(function () {
				$('.postcodify_search_controls .keyword').attr('chk-flg', 'false');
			});
	
			$('.post-change-result.postcodify_search_form.postcode_search_form').on('click', function () {
				$('.postcodify_search_controls .keyword').attr('chk-flg', 'true');
			});
		}
	}
	else if(country == 'EN'){
		$('.addr-KR').remove();
		$('.addr-CN').remove();

		getCountryInfo('EN');
	}
	else if(country == 'CN'){
		$('.addr-KR').remove();
		$('.addr-EN').remove();

		getCountryInfo('CN');
	}
	
	// $('input[name=tel_mobile]').keyup(function () {
	// 	let only_num_regex = /[^0-9]/g;
	// 	let result = $('input[name=tel_mobile]').val().replace(only_num_regex, "");
	// 	$('input[name=tel_mobile]').val(result);
	// });
	$('.black__btn.join_button').on('click', joinAction);
	
	/* 한국몰 모바일 인증처리 */
	if (getLanguage() != "KR") {
		let container_mobile = document.querySelector('.tel_mobile');
		if (container_mobile != null) {
			container_mobile.classList.remove('grid__two');
			
			let str_div = `
				<input type="hidden" id="mobile_authenfication_flg" value="true">
				<input class="join_tel_mobile" type="text" name="tel_mobile" value="" data-i18n-placeholder="j_num_only">
			`;
			
			container_mobile.innerHTML = str_div;
			
			changeLanguage();
			
			$('.birth_y').removeAttr('readonly');
			$('.birth_m').removeAttr('readonly');
			$('.birth_d').removeAttr('readonly');
		}
	} else {
		$('.black__small__btn.mobile_auth_btn').on('click',mobileAuthenfication);
	}
	
	$('.contnet__row .user_id_input').on('keyup', checkEmailValid);
	$('.contnet__row .user_pw_confirm_input').on('keyup', checkPwConfirm);
	
	selectAllClick();
	
	$('.component').click(function () {
		var sel_cnt = $('.component:checked').length;
		if (sel_cnt == 3) {
			$('.select__all').prop('checked', true);
		} else {
			$('.select__all').prop('checked', false);
		}
	});
	$('.font__underline.to_terms_of_use').on('click', function () {
		location.href = '/notice/privacy?notice_type=terms_of_use';
	});
	$('.font__underline.to_privacy_policy').on('click', function () {
		location.href = '/notice/privacy?notice_type=privacy_policy';
	});
	
	$('.user_id_input').focus();

	addMobileHyprenEvent();
});
let countrySelectBox = null;
let provinceSelectBox = null;

function getCountryInfo(country) {
	let countryInfo = [];
	let countryDisabled = false;
	let detaultCountry = null;

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
					detaultCountry = d.data[0].value;
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
			if(country == 'CN'){
				countryDisabled = true;
			}
			countrySelectBox = new tui.SelectBox('.country-select-box', {
				data: countryInfo,
				autofocus: false,
				disabled: countryDisabled
			});
			$('.addr-' + country + ' input[name=country_code]').val(detaultCountry);
			getProvinceInfo(detaultCountry);
			countrySelectBox.on("change", ev => {
				$('.province-select-box').html('');
				let country_value = ev.curr.getValue();
				$('.addr-' + country + ' input[name=country_code]').val(country_value);
				getProvinceInfo(country_value);

				$('.tui-select-box-input').removeClass('tui-select-box-open');
				$('.tui-select-box-dropdown').addClass('tui-select-box-hidden');
			});
			countrySelectBox.on("open", function(){
				provinceSelectBox.close();
			})
		}
	});
}
function getProvinceInfo(country_code) {
	let provinceInfo = [];
	let provinceFlg = true;
	let detaultProvince = null;
	$.ajax(
		{
			url: api_location + "account/province/get",
			type: 'POST',
			data: { 'country_code': country_code },
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
							provinceInfo =  d.data;
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
				$('.addr-' + country + ' input[name=province_idx]').val(detaultProvince);
				provinceSelectBox = new tui.SelectBox('.province-select-box', {
					data: provinceInfo,
					autofocus: false,
					disabled: provinceFlg
				});
				provinceSelectBox.on("change", ev => {
					let province_value = ev.curr.getValue();
					$('.addr-' + country + ' input[name=province_idx]').val(province_value);
					$('.tui-select-box-input').removeClass('tui-select-box-open');
					$('.tui-select-box-dropdown').addClass('tui-select-box-hidden');
				});
				provinceSelectBox.on("open", function(){
					countrySelectBox.close();
				})
			}
		}
	);
}
function joinAction() {
	country = getLanguage();

	// var birth_year_regex = new RegExp('^[1-9]{1}[1-9]{1}[1-9]{1}[1-9]{1}$');
	let birth_year_regex = new RegExp('^19[0-9]{2}$|^20{1}[0-9]{2}$');
	let birth_month_regex = new RegExp('^[1-9]{1}$|^0{1}[1-9]{1}$|^1{1}[0-2]{1}$');
	let birth_day_regex = new RegExp('^[1-9]{1}$|^0{1}[1-9]{1}$|^[1-2]{1}[0-9]{1}$|^3{1}[0-1]{1}$');
	let mail_regex = new RegExp('^[a-zA-Z0-9+-\_.]+@[a-zA-Z0-9-]+\.([a-zA-Z0-9-]|([a-zA-Z0-9-]+\.[a-zA-Z0-9-]))+$');
	let member_id = $('input[name="member_id"]').val();
	let member_pw = $('input[name="member_pw"]').val();
	let member_pw_confirm = $('input[name="member_pw_confirm"]').val();
	let member_name = $('input[name="member_name"]').val();
	let birth_year = $('input[name="birth_year"]').val();
	let birth_month = $('input[name="birth_month"]').val();
	let birth_day = $('input[name="birth_day"]').val();
	let addr_chk_flg = $('.postcodify_search_controls .keyword').attr('chk-flg');
	let terms_of_service_flg = $('#terms_of_service_flg').is(':checked');
	let mobile_authenfication_flg = $('#mobile_authenfication_flg').val();
	let detail_addr = $('#addr_detail').val();

	mail_regex.test(member_id);

	$('.warn__msg').css('visibility', 'hidden');
	if (memberPwConfirm(member_pw) == false) {
		$('.font__underline.warn__msg.member_pw').css('visibility', 'visible');
		showPwDescription();

		let target = document.querySelector('.font__underline.warn__msg.member_pw');
        let targetRectTop = target.getBoundingClientRect().top;

        if(targetRectTop < 106){
            window.scroll({top:90, behavior:'smooth'});
        }

		return false;
	}
	if (member_id == '' || !mail_regex.test(member_id)) {
		$('.warn__msg.member_id').css('visibility', 'visible');

		let target = document.querySelector('.warn__msg.member_id');
        let targetRectTop = target.getBoundingClientRect().top;

        if(targetRectTop < 90){
            window.scroll({top:30, behavior:'smooth'});
        }

		return false;
	}
	if (member_pw != member_pw_confirm) {
		$('.warn__msg.member_pw_confirm').css('visibility', 'visible');

		let target = document.querySelector('.warn__msg.member_pw_confirm');
        let targetRectTop = target.getBoundingClientRect().top;

        if(targetRectTop < 110){
            window.scroll({top:165, behavior:'smooth'});
        }

		return false;
	}
	if (member_name == '') {
		$('.warn__msg.member_name').css('visibility', 'visible');

		let target = document.querySelector('.warn__msg.member_name');
        let targetRectTop = target.getBoundingClientRect().top;

        if(targetRectTop < 108){
            window.scroll({top:240, behavior:'smooth'});
        }

		return false;
	}
	if(country == 'KR'){
		if (addr_chk_flg == 'false') {
			$('.warn__msg.essential').css('visibility', 'visible');
			$('.warn__msg.essential').text('도로명/지번 주소를 검색 후 선택해주세요');
			return false;
		}
		if (detail_addr == '') {
			$('.warn__msg.essential').css('visibility', 'visible');
			$('.warn__msg.essential').text('상세주소를 입력해주세요');
			return false;
		}
	}
	else{
		let addrObj = $('.addr-' + country);
		let country_code = addrObj.find('input[name=country_code]').val();
		let province_idx = addrObj.find('input[name=province_idx]').val();
		let city = addrObj.find('input[name=city]').val();
		let zipcode = addrObj.find('input[name=zipcode]').val();
		let address = addrObj.find('input[name=address]').val();

		if(province_idx == ''){
			addrObj.find('input[name=province_idx]').val("0")
		}
		if(city == ''){
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0046', null);
			//notiModal('Input city please');
			return false;
		}
		if(zipcode == ''){
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0019', null);
			//notiModal('Input zipcode please');
			return false;
		}
		if(address == ''){
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0009', null);
			//notiModal('Input address please');
			return false;
		}
	}
	
	if (terms_of_service_flg == false) {
		$('.warn__msg.essential').css('visibility', 'visible');
		$('.warn__msg.essential').text('필수항목을 선택해주세요');
		return false;
	}
	if (mobile_authenfication_flg == 'false') {
		$('.warn__msg.tel_confirm').css('display', 'none');
		$('.warn__msg.tel_confirm.authenfication').css('display', 'block');
		$('.warn__msg.tel_confirm.authenfication').css('visibility', 'visible');

		let target = document.querySelector('.warn__msg.member_id');
        let targetRectTop = target.getBoundingClientRect().top;

        if(targetRectTop < 80){
            window.scroll({top:490, behavior:'smooth'});
        }

		return false;
	}

	if (birth_year_regex.test(birth_year) === false || birth_month_regex.test(birth_month) === false || birth_day_regex.test(birth_day) === false) {
		$('.warn__msg.birth').css('visibility', 'visible');
		return false;
	}
	var country = getLanguage();
	
	$('#frm-regist').find('input[name=country]').val(country);
	$('.black__btn.join_button').unbind('click');
	$.ajax(
		{
			url: api_location + "account/add",
			type: 'POST',
			data: $("#frm-regist").serialize(),
			dataType: 'json',
			error: function () {
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0002', null);
				$('.black__btn.join_button').on('click', joinAction);
			},
			success: function (data) {
				let code = data.code;
				
				if (code == 200) {
					//location.reload();
					
					makeMsgNoti(getLanguage(), 'MSG_F_INF_0001', null);
					//notiModal(msg_title,msg_body);
					
				} else if (code == 303) {
					$('.warn__msg.essential').css('visibility', 'visible');
					$('.warn__msg.essential').text(data.msg);
					$('.black__btn.join_button').on('click', joinAction);
					
				} else {
					notiModal(data.msg);
					$('.black__btn.join_button').on('click', joinAction);
				}
				
				console.log(code);

			}
		}
	);
}

function mobileAuthenfication() {
	MOBILEOK.process("https://dev.adererror.com/_api/mok/mok-request", "WB", "mobile_result");
	/*
	var tel_regex = new RegExp('^01([0|1|6|7|8|9])([0-9]{3,4})([0-9]{4})$');

	var tel = $('.content__wrap__tel').find('input[name=tel_mobile]').val();
	tel = tel.replace(/-/g,"");
	
	if (tel_regex.test(tel) === false) {
		$('.warn__msg.tel_confirm').css('display', 'none');
		$('.warn__msg.tel_confirm.format').css('display', 'block');
		$('.warn__msg.tel_confirm.format').css('visibility', 'visible');
		$('#mobile_authenfication_flg').val('false');
	} else {
		$('.warn__msg.tel_confirm').css('display', 'none');
		$('#mobile_authenfication_flg').val('true');
	}
	*/
}

function mobile_result(result) {
	try {
		result = JSON.parse(result);
		
		let tel_mobile = result.user_phone.replace(/[^0-9]/g, '').replace(/^(\d{0,3})(\d{0,4})(\d{0,4})$/g, "$1-$2-$3").replace(/(\-{1,2})$/g, "");
		document.querySelector('.join_tel_mobile').value = tel_mobile;
		
		let user_birth = result.user_birth;
		console.log(user_birth);
		
		document.querySelector('.birth_y').value = user_birth.substring(0,4);
		document.querySelector('.birth_m').value = user_birth.substring(4,6);
		document.querySelector('.birth_d').value = user_birth.substring(6,9);
		
		//document.querySelector("#result").value = JSON.stringify(result, null, 4);
		
		$('#mobile_authenfication_flg').val('true');
		
		$('.warn__msg.tel_confirm').css('display', 'none');
	} catch (error) {
		$('#mobile_authenfication_flg').val('false');
		
		$('.warn__msg.tel_confirm').css('display', 'none');
		$('.warn__msg.tel_confirm.format').css('display', 'block');
		$('.warn__msg.tel_confirm.format').css('visibility', 'visible');
		
		//console.log(result);
		//document.querySelector("#result").value = result;
	}
}

function selectAllClick(obj) {
	let select_all = document.querySelector('.login__check__option.select__all');
	select_all.addEventListener('click',function(e) {
		let el = e.currentTarget;
		if ($(el).prop('checked') == true) {
			$(el).prop('checked', true);
			$(".login__check__option").prop('checked', true);
		} else {
			$(el).attr('checked', false);
			$(".login__check__option").prop('checked', false);
		}
	});
}

function checkEmailValid() {
	let emailRegex = new RegExp('^[a-zA-Z0-9+-\_.]+@[a-zA-Z0-9-]+\.([a-zA-Z0-9-]|([a-zA-Z0-9-]+\.[a-zA-Z0-9-]))+$');
	let memberId = $('input[name="member_id"]').val();
	if (memberId.length == 0) {
		$('.warn__msg.member_id').css('visibility', 'hidden');
	} else if (emailRegex.test(memberId) == false) {
		$('.warn__msg.member_id').css('visibility', 'visible');
	} else {
		$('.warn__msg.member_id').css('visibility', 'hidden');
	}
}

function checkPwConfirm() {
	let memberPw = $('input[name="member_pw"]').val();
	let memberPwConfirm = $('input[name="member_pw_confirm"]').val();

	if (memberPwConfirm.length == 0 || memberPw == memberPwConfirm) {
		$('.warn__msg.member_pw_confirm').css('visibility', 'hidden');
	} else {
		$('.warn__msg.member_pw_confirm').css('visibility', 'visible');
	}
}

function mobileAutoHyphen(target) {
	target.value = target.value
		.replace(/[^0-9]/g, '')
		.replace(/^(\d{0,3})(\d{0,4})(\d{0,4})$/g, "$1-$2-$3").replace(/(\-{1,2})$/g, "");
}

function addMobileHyprenEvent() {
	let mobileInput = document.querySelector(".content__wrap__tel .join_tel_mobile");

	if(getLanguage() == "KR") {
		mobileInput.addEventListener("input", function(e) {
			mobileAutoHyphen(e.target);
		});
	}
}