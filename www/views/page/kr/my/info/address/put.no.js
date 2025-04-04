let country_foreign = [];

$(document).ready(function() {
	$('input[name="to_mobile"]').on('keyup',function(e) {
		let value = $(this).val().replace(/\D/g, "");

		let result = value.replace(/^(\d{3})(\d{4})(\d{4})$/,"$1-$2-$3");
		$(this).val(result);
	});
	
	let address_idx = location.pathname.split("/")[6];
	$('#no').val(address_idx);

	if (config.language == "EN") {
		getAddress_foreign();
	}
	
	/* 마이페이지 배송지 - 회원 배송지 개별 조회 */
	getAddress();
	
	/** 우편번호 검색 **/
	clickBTN_post();
	
	/** 수정하기 버튼 **/
	$("#frm").submit(function() {
		let msg_alert = {
			KR : {
				't_01' : "배송지명을 작성해주세요.",
				't_02' : "이름을 작성해주세요.",
				't_03' : "전화번호를 작성해주세요.",
				't_04' : "주소를 입력해주세요."
			},
			EN : {
				't_01' : "Please enter the place.",
				't_02' : "Please enter the receipt.",
				't_03' : "Please enter the mobile number.",
				't_04' : "Please select the country.",
				't_05' : "Please select the province.",
				't_06' : "Please enter the city.",
				't_07' : "Please enter the address."
			}
		}

		if ($('input[name="to_place"]').val() == null || $('input[name="to_place"]').val() == "") {
			alert(msg_alert[config.language]['t_01']);
			return false;
		}

		if ($('input[name="to_name"]').val() == null || $('input[name="to_name"]').val() == "") {
			alert(msg_alert[config.language]['t_02']);
			return false;
		}

		if ($('input[name="to_mobile"]').val() == null || $('input[name="to_mobile"]').val() == "") {
			alert(msg_alert[config.language]['t_03']);
			return false;
		}

		if (config.language == "KR") {
			if ($('input[name="to_road_addr"]').val() == null || $('input[name="to_road_addr"]').val() == "") {
				alert(msg_alert[config.language]['t_04']);
				return false;
			}
		} else if (config.language == "EN") {
			if ($('select[name="to_country_code"]').val() == null || $('input[name="to_country_code"]').val() == "") {
				alert(msg_alert[config.language]['t_04']);
				return false;
			}

			if ($('select[name="to_province_idx"]').val() == null || $('input[name="to_province_idx"]').val() == "") {
				alert(msg_alert[config.language]['t_05']);
				return false;
			}

			if ($('input[name="to_city"]').val() == null || $('input[name="to_city"]').val() == "") {
				alert(msg_alert[config.language]['t_06']);
				return false;
			}

			if ($('input[name="to_address"]').val() == null || $('input[name="to_address"]').val() == "") {
				alert(msg_alert[config.language]['t_07']);
				return false;
			}
		}
		
		$.ajax({
			url: config.api + "member/address/put",
			headers : {
				country : config.language
			},
			data: $(this).serialize(),
			error: function () {
				makeMsgNoti('MSG_F_ERR_0039', null);
			},
			success: function (d) {
				if(d.code == 200) {
					location.href = `${config.base_url}/my/info/address`;
				} else {
					if (d.code == 401) {
						alert(
							d.msg,
							function() {
								sessionStorage.setItem('r_url',location.href);
								location.href = `${config.base_url}/login`;
							}
						);
					}
				}
			}
		});
		
		return false;
	});
});

function getAddress() {
	$.ajax({
		url: config.api + "member/address/get",
		headers : {
			country : config.language
		},
		data: {
			'no'		:$('#no').val()
		},
		async: false,
		error: function () {
			makeMsgNoti('MSG_F_ERR_0046',null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data[0];
				if (data != null) {
					$('#to_place').val(data.to_place);
					$('#to_name').val(data.to_name);
					$('#to_mobile').val(data.to_mobile);
					$('#to_zipcode').val(data.to_zipcode);

					if ($('#to_road_addr') != null) {
						$('#to_road_addr').val(data.to_road_addr);
					}
					
					if ($('#to_lot_addr') != null) {
						$('#to_lot_addr').val(data.to_lot_addr);
					}
					
					if ($('select[name="to_country_code"]') != null) {
						$('select[name="to_country_code"]').val(data.to_country_code).trigger('change');
					}

					if ($('select[name="to_province_idx"]') != null) {
						$('select[name="to_province_idx"]').val(data.to_province_idx);
					}

					if ($('input[name="to_city"]') != null) {
						$('input[name="to_city"]').val(data.to_city);
					}

					if ($('input[name="to_address"]') != null) {
						$('input[name="to_address"]').val(data.to_address);
					}

					$('#to_detail_addr').val(data.to_detail_addr);

					if (data.default_flg == true) {
						$('input[name="default_flg"]').prop('checked',true);
					}
				}
			}
		}
	});	
}

function clickBTN_post() {
	if ($('#postcodify').find('postcodify_search_controls').length == 0) {
		$("#postcodify").postcodify({
			insertPostcode5: "#to_zipcode",
			insertAddress: "#to_road_addr",
			insertDetails: "#to_detail_ddr",
			insertJibeonAddress: "#to_lot_addr",
			hideOldAddresses: false,
			results: ".post-change-result",
			hideSummary: true,
			useFullJibeon: true,
			useCors: false,
			onReady: function () {
				document.querySelector(".post-change-result").style.display = "none";
				$(".postcodify_search_controls .keyword_label").text('우편번호 검색');
				$(".postcodify_search_controls .keyword").attr("placeholder", "3글자 이상 입력해주세요.");
				$('.postcodify_search_controls .keyword').attr('chk-flg', 'false');
				// $(".post-change-result").hide();
			},
			onSuccess: function () {
				document.querySelector(".post-change-result").style.display = "block";
				$("#postcodify div.postcode_search_status.too_many").hide();
				// $(".post-change-result").hide();

				$('.input-row').css('position', 'relative');
				$('.post-change-result').css('position', 'absolute');
				$('.post-change-result').css('top', '-12px');
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
				$('.post-change-result').css('top', '-12px');
			}
		});

		$(".postcodify_search_controls .keyword").css({
			"display": "grid",
			"width": "75%"
		});

		$(".postcodify_search_controls .search_button").css({
			"border": "1px solid #bfbfbf"
		});

		$('.postcodify_search_controls .keyword').keyup(function () {
			$('.postcodify_search_controls .keyword').attr('chk-flg', 'false');
		});

		$('.post-change-result.postcodify_search_form.postcode_search_form').on('click', function () {
			$('.postcodify_search_controls .keyword').attr('chk-flg', 'true');
		});
	}
}

function getAddress_foreign() {
	$.ajax({
		url: config.api + "member/address/foreign",
		headers : {
			country : config.language
		},
		async: false,
		error: function () {
			makeMsgNoti(config.base_url,'MSG_F_ERR_0039', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null && data.length > 0) {
					$('#frm .country').html('');
					$('#frm .province').html('');
					
					data.forEach(function(row) {
						country_foreign[row.country_code] = row;
						$('#frm .country').append(`<option value="${row.country_code}">${row.country_name}</option>`);
					});

					let province = data[0].province;
					if (province != null && province.length > 0) {
						province.forEach(function(row2) {
							$('#frm .province').append(`<option value="${row2.province_idx}">${row2.province_name}</option>`);
						});
					}

					changeCountry_foreign();
				}
			}
		}
	});
}

function changeCountry_foreign() {
	let country = $('#frm .country');
	if (country != null) {
		country.on('change',function() {
			let country_code = $(this).val();

			let country		= country_foreign[country_code];
			let province	= country.province;

			$('#frm .province').html('<option value="0">-</option>');
			if (province != null && province.length > 0) {
				province.forEach(function(row2) {
					$('#frm .province').html('');
					$('#frm .province').append(`<option value="${row2.province_idx}">${row2.province_name}</option>`);
				});
			}
		});
	}
}