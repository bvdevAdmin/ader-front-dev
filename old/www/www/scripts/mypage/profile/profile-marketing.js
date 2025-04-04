document.addEventListener("DOMContentLoaded", function() {
	getMarketingCheck();
	addMarketingBtnEvent();
});

function addMarketingBtnEvent() {
	$(".profile__marketing__wrap .all_check").on("click", marketingCheckAll);
	$(".profile__marketing__wrap .mkt_check").on("click", marketingCheckOne);
	
	$(".profile__marketing__wrap .underline").on("click", function() {
		mypageTabBtnClick('service', 3);
	});
	
	$(".profile__marketing__wrap .save_marketing_check_btn").on("click", putMarketingCheck);
}

function marketingCheckAll() {
	let all_check_flg = $('.all_check').is(':checked');
	if (all_check_flg == true) {
		$('.mkt_check').prop('checked', true);
	} else {
		$('.mkt_check').prop('checked', false);
	}
}

function marketingCheckOne() {
	let checkCnt = $('.mkt_check:checked').length;
	if (checkCnt == 3) {
		$('.all_check').prop('checked', true);
	} else {
		$('.all_check').prop('checked', false);
	}
}

function getMarketingCheck() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/member/marketing/get",
		dataType: "json",
		error: function () {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0064", null);
			// notiModal("계정", "마케팅 정보 조회에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			
			if (code == 200) {
				let data = d.data;
				if (data != null) {
					let mktData = data[0];
					let emailFlg = mktData.receive_email_flg == 1 ? true : false;
					let smsFlg = mktData.receive_sms_flg == 1 ? true : false;
					let telFlg = mktData.receive_tel_flg == 1 ? true : false;
					
					$('.email_check').prop('checked', emailFlg);
					$('.sms_check').prop('checked', smsFlg);
					$('.tel_check').prop('checked', telFlg);
					
					marketingCheckOne();
				}
			} else {
				makeMsgNoti(getLanguage(), "MSG_F_ERR_0064", null);
				
				// let err_str = "마케팅 정보 조회에 실패했습니다.";
				// if (d.msg != null) {
				//		 err_str = d.msg;
				// }
				// notiModal("계정", err_str);
				
				if (d.code = 401) {
					$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
				}
			}
		}
	})
}

function putMarketingCheck() {
	let receive_email_flg = $('.email_check').is(':checked');
	let receive_sms_flg = $('.sms_check').is(':checked');
	let receive_tel_flg = $('.tel_check').is(':checked');

	$.ajax({
		type: "post",
		url: api_location + "mypage/member/marketing/put",
		data: {
			"receive_email_flg": receive_email_flg,
			"receive_sms_flg": receive_sms_flg,
			"receive_tel_flg": receive_tel_flg
		},
		dataType: "json",
		error: function () {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0065", null);
			// notiModal("계정", "마케팅 정보 변경에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
				
			if (code == 200) {
				let notiMsg = "";
				switch(getLanguage()) {
					case "KR":
						notiMsg = "마케팅 정보 변경에 성공했습니다."; 
						break;
					case "EN":
						notiMsg = "Successfully changed marketing information."; 
						break;
					case "CN":
						notiMsg = "成功更改了营销信息。"; 
						break;
				}

				notiModal(notiMsg);
			} else {
				makeMsgNoti(getLanguage(), "MSG_F_WRN_0002", null);
				
				// let err_str = "회원정보가 올바르지 않습니다.";
				// if (d.msg != null) {
				//		 err_str = d.msg;
				// }
				// notiModal("계정", err_str);
				
				if (d.code = 401) {
					$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
				}
			}
		}
	});
}