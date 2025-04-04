document.addEventListener("DOMContentLoaded", function () {
	/* 마이페이지 회원정보 버튼 이벤트 설정 */
	setBTN_event();
	
	addAccountMobileHyprenEvent();
});

/* 마이페이지 회원정보 버튼 이벤트 설정 */
function setBTN_event() {
	/* 1. 비밀번호 수정버튼 (모달) 클릭처리 */
	let btn_pw_modal = $(".update_btn.pw_update");
	btn_pw_modal.on("click", function () {
		$(".profile__tab").hide();
		$('.pw_update_error p').html('&nbsp');
		$(".profile__pw__update__wrap").show();
	});
	
	/* 1-1. 비밀번호 변경버튼 클릭처리 */
	$(".check_member_pw_btn").on("click",checkMemberPw);
	
	/* 1-2. 비밀번호 모달 닫기버튼 클릭처리 */
	$(".close_pw_update").on("click", function () {
		$('.current_pw').val('');
		$('.tmp_update_pw').val('');
		$('.tmp_update_pw_check').val('');
		$('.profile__pw__update__wrap').hide();
		$('.profile__set__wrap').show();
	});
	
	/* 2-1. 전화번호 수정버튼 (모달) 클릭처리 */
	let btn_tel_modal = $(".update_btn.tel_update");
	btn_tel_modal.on("click", function () {
		$(".profile__tab").hide();
		$('.tel_update_error p').html('&nbsp');
		$('.profile__tel__update__wrap').show();
		$('#ck_sendcode_phone').prop('checked', false);
		$('.alertms_del').html('');
		
		$('.mobile_number_input').val('');
		$('.auth_no').val('');
		
		if (getLanguage() == "KR") {
			$('.profile__tel__update__wrap .tel_update_description').show();
			$('.profile__tel__update__wrap .input__form__rows label').show();
		} else {
			$('.profile__tel__update__wrap .tel_update_description').hide();
			$('.profile__tel__update__wrap .input__form__rows label').hide();
		}
	});
	
	/* 2-1-1. 인증번호 발송버튼 클릭처리 */
	let btn_send = $(".send_code");
	btn_send.on("click", function () {
		if (getLanguage() == "KR") {
			if ($('input[name="tel_certificate"]').val() == '') {
				$('.tel_update_error p').text('전화번호를 입력해주세요');
				return false;
			}

			if ($('#ck_sendcode_phone').is(':checked') == false) {
				$('.tel_update_error p').text('약관동의를 체크해주세요');
				return false;
			}
			
			let send_result = false;
			
			/* 한국몰 인증번호 SMS 발송 */
			send_result = sendAUTH_sms($('input[name="tel_certificate"]').val());
		} else {
			$('.td_user_tel').text($('input[name="tel_certificate"]').val());
			$('.user_update_tel').val($('input[name="tel_certificate"]').val());
			
			$(".profile__tab").hide();
			$('.profile__set__wrap').show();
		}
	});
	
	/* 2-1-2 전화번호 모달 닫기버튼 클릭처리 */
	$(".close_tel_update").on("click", function () {
		$(".mobile_number_input").val("");
		$(".to_update_mobile_number").text("");
		$('.profile__tel__update__wrap').hide();
		$('.profile__set__wrap').show();
	});
	
	/* 2-2-1. 인증번호 재전송 버튼 클릭처리 */
	let btn_re_send = $('.btn_re_send');
	btn_re_send.on("click",function() {
		if (getLanguage() == "KR") {
			/* 한국몰 인증번호 SMS 발송 */
			send_result = sendAUTH_sms($('input[name="tel_certificate"]').val());
		}
	});
	
	/* 2-2-2. 인증 완료버튼 클릭처리 */
	$(".fin_check").on("click", function () {
		let auth_no = $('.auth_no').val();
		if (auth_no == null && auth_no.length == 0) {
			notiModal("","인증번호를 입력해주세요.");
		}
		
		checkAUTH_sms(auth_no);
	});
	
	/* 2-2-3. 인증 완료 모달 닫기버튼 클릭처리 */
	$(".close_tel_update_confirm").on("click", function () {
		$(".mobile_number_input").val("");
		$(".to_update_mobile_number").text("");
		$('.profile__tel__update__confirm__wrap').hide();
		$('.profile__set__wrap').show();
	});
	
	/* 3. 저장버튼 클릭처리 */
	$(".profile_save_btn").on("click", putMemberPwAndTel);
	
	/* 4. 계정 삭제버튼 (모달) 클릭처리 */
	let btn_drop_modal = $(".move_account_del");
	btn_drop_modal.on("click", function () {
		$(".profile__tab").hide();
		$('.profile__account__delete__wrap').show();
	});
	
	/* 4-1. 계정삭제 취소버튼 클릭처리 */
	$(".del_cancel").on("click", function () {
		$(".profile__tab").hide();
		$('.profile__set__wrap').show();
	});
	
	/* 4-2. 계정삭제 버튼 클릭처리 */
	$(".account_del").on("click", function () {
		let drop_err_str = '';

		drop_err_str = '계정 삭제 동의란을 선택해주세요';

		if ($('#ck_account_delete').is(':checked') == true) {
			accountDel();
			$('#ck_account_delete').prop('checked', false);
		} else {
			$('.profile__account__delete__wrap').show();
			$('.alertms_del').text(drop_err_str);
		}
	});
	
	/* 4-3. 계정삭제 모달 닫기버튼 클릭처리 */
	$(".close_account_delete").on("click", function () {
		$('.profile__account__delete__wrap').hide();
		$('.profile__set__wrap').show();
	});
	
	$(".service_policy_link").on("click", function () {
		mypageTabBtnClick('service',3);
	});
	
	$(".service_terms_link").on("click", function () {
		mypageTabBtnClick('service',2);
	});
}

function accountDel() {
	$.ajax({
		type: 'POST',
		url: api_location + "mypage/member/account/delete",
		dataType: 'json',
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0072', null);
			//notiModal("계정", "계정 탈퇴를 수행하지 못했습니다.");
		},
		success: function (data) {

			if (data.code == "200") {
				makeMsgNoti(getLanguage(), 'MSG_F_INF_0015', null);
				//notiModal("계정", "계정탈퇴를 완료했습니다.");
				$('.alertms_del').html('');
				$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
			} else {
				if (d.msg != null){
					notiModal(d.msg);
				}
				else{
					makeMsgNoti(getLanguage(), 'MSG_F_ERR_0072', null);
				}
				if (d.code = 401) {
					$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
				}
			}
		}
	});
}

function checkMemberPw() {
	let current_pw = $('.current_pw').val();
	let tmp_update_pw = $('.tmp_update_pw').val();
	let tmp_update_pw_check = $('.tmp_update_pw_check').val();

	//  입력 가능 특수문자 : '!@#$%^()_-={}[]|;:<>,.?/                    
	var password_reg = /^(?=.*[\{\}\[\]\/?.,;:|\)*`!^\-_<>@\#$%\=\(])(?=.*\d)(?=.*[A-Za-z])[\da-zA-Z\{\}\[\]\/?.,;:|\)*`!^\-_<>@\#$%\=\(]{8,16}/;
	//  공백 입력 불가능
	var space_reg = /\s/g;

	// if문 정리 ajax는 현재 비밀번호와 체크하는 것만 / 나머지는 뷰 쪽에서 처리 하도록
	if (current_pw.length == 0 || tmp_update_pw.length == 0 || tmp_update_pw_check.length == 0) {
		$('.pw_update_error p').text('모든 항목을 기입해야만 비밀번호 변경이 가능합니다.');
		return false;
	}

	if (space_reg.test(tmp_update_pw) == true) {
		$('.pw_update_error p').text('변경하려는 비밀번호의 공백을 확인해주세요.');
		return false;
	}

	if (password_reg.test(tmp_update_pw) == false) {
		$('.pw_update_error p').text('변경하려는 비밀번호의 형식을 확인해주세요.');
		return false;
	}

	if (tmp_update_pw != tmp_update_pw_check) {
		$('.pw_update_error p').text('비밀번호 확인란에 동일한 비밀번호를 입력해주세요.');
		return false;
	}

	if (current_pw == tmp_update_pw || current_pw == tmp_update_pw_check) {
		$('.pw_update_error p').text('현재 비밀번호와 다르게 설정해주세요.');
		return false;
	}

	$.ajax({
		type: "post",
		url: api_location + "mypage/member/account/check",
		data: {
			"member_pw": current_pw
		},
		dataType: "json",
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0002', null);
			//notiModal("계정", "회원정보가 올바르지 않습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				$('.user_update_pw').val(tmp_update_pw);
				$('.current_pw').val('');
				$('.tmp_update_pw').val('');
				$('.tmp_update_pw_check').val('');
				$('.profile__pw__update__wrap').hide();
				$('.profile__set__wrap').show();
			} else {
				if(d.msg != null){
					notiModal(d.msg);
				}
				else{
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0002', null);
				}
				if (d.code = 401) {
					// $('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
				}
				return false;
			}
		}
	});
}

function putMemberPwAndTel() {
	let user_update_pw = $('.user_update_pw').val();
	let user_update_tel = $('.user_update_tel').val();

	if ((user_update_pw.length < 1 || user_update_pw == null) && (user_update_tel.length < 1 || user_update_tel == null)) {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0024', null);
		//notiModal("계정", "수정된 정보가 없습니다.");
		return false;
	} else {
		$.ajax({
			type: "post",
			url: api_location + "mypage/member/account/put",
			data: {
				"member_pw": user_update_pw,
				"member_tel_mobile": user_update_tel
			},
			dataType: "json",
			error: function () {
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0071', null);
				//notiModal("계정", "계정정보 변경에 실패했습니다.");
			},
			success: function (d) {
				let code = d.code;
				if (code == 200) {
					$('.user_update_pw').val('');
					$('.user_update_tel').val('');
					makeMsgNoti(getLanguage(), 'MSG_F_INF_0016', null);
					//notiModal("계정", "계정정보 변경에 성공했습니다.");
				} else {
					if (d.msg != null) {
						notiModal(d.msg);
						if (d.code = 401) {
							$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
						}
					}
					else{
						makeMsgNoti(getLanguage(), 'MSG_F_ERR_0071', null);
					}
				}
			}
		});
	}
}

function addAccountMobileHyprenEvent() {
	let mobileInput = document.querySelector(".profile__tel__update__wrap .mobile_number_input");

	if(getLanguage() == "KR") {
		mobileInput.addEventListener("input", function(e) {
			mobileAutoHyphen(e.target);
		});
	}
}

/* 인증번호 SMS 발송 */
function sendAUTH_sms(tel_mobile) {
	$.ajax({
		type: 'POST',
		url: api_location + "send/send-sms-check",
		data: {
			'tel_mobile':tel_mobile
		},
		dataType: 'json',
		async:false,
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0072', null);
		},
		success: function (d) {
			if (d.code == 202) {
				$('.profile__tel__update__wrap').hide();
				$('.profile__tel__update__confirm__wrap').show();
				
				let mobileNumber = $(".mobile_number_input").val();
				
				$(".to_update_mobile_number").text(mobileNumber);
			}
		}
	});
}

/* 인증번호 체크 */
function checkAUTH_sms(auth_no) {
	$.ajax({
		type: 'POST',
		url: api_location + "mypage/member/account/auth",
		data: {
			'auth_no':auth_no
		},
		dataType: 'json',
		async:false,
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0072', null);
		},
		success: function (d) {
			if (d.code == 200) {
				$('.td_user_tel').text($('input[name="tel_certificate"]').val());
				$('.user_update_tel').val($('input[name="tel_certificate"]').val());
				
				$(".profile__tab").hide();
				$('.profile__set__wrap').show();
			} else {
				notiModal("인증실패","유효하지 않은 인증번호가 입력되었습니다.");
			}
		}
	});
}