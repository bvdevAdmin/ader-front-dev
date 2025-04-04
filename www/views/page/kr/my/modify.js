$(document).ready(function() {
	if (sessionStorage.getItem("mok-modify") != null) {
		let mok = JSON.parse(sessionStorage.getItem("mok-modify"));

		let member_name  = mok.user_name;
		let tel_mobile	 = `${mok.user_phone.substring(0,3)}-${mok.user_phone.substring(3,7)}-${mok.user_phone.substring(7)}`;
		let member_birth = `${mok.user_birth.substring(0,4)}-${mok.user_birth.substring(4,6)}-${mok.user_birth.substring(6,9)}`;

		putMember(member_name,tel_mobile,member_birth);
	}

	if (config.member) {
		$("#member-name").text(config.member.name);
		$("#member-email").text(config.member.id);
		$("#member-tel").text(config.member.tel);
		$("#member-birthday").text(config.member.birthday.split(" ")[0]);
		if (config.member.auth_flg == "T") {
			$('.info.modify .buttons').remove();
		}
	}
	
	$("#btn-change-pw").click(function() {
		modal('pwchange');
	});

	let btn_auth = document.querySelector('.btn_auth');
	if (btn_auth != null) {
		btn_auth.addEventListener('click',function() {
			if (!window.is_mobile) {
				MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-request", "WB", "mok_result");
			} else {
				MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-modify", "MB", "");
			}
		});
	}
});

function clickBTN_pw() {
	let btn_pw = $('.btn_pw');
	if (btn_pw != null) {
		btn_pw.unbind();
		
		btn_pw.click(function() {
			let member_pw	= document.querySelector('.pwchange input[name=member_pw]').value;
			let pw_confirm	= document.querySelector('.pwchange input[name=pw_confirm]').value;
			
			console.log('member_pw',member_pw);
			console.log('member_pw',member_pw.length);
			
			if (member_pw != null && member_pw.length > 0) {
				let check_result = checkPW_regex(member_pw);
				if (check_result == true) {
					if (pw_confirm != null && pw_confirm.length > 0) {
						if (member_pw == pw_confirm) {
							$.ajax({
								url: config.api + "member/put",
								headers : {
									country : config.language
								},
								data :{
									'action_type'	:"PASSWORD",
									'member_pw'		:member_pw
								},
								error: function () {
									makeMsgNoti(config.language,'MSG_F_ERR_0143',null);
								},
								success: function (d) {
									if (d.code == 200) {
										modal_close();
										makeMsgNoti(config.language,'MSG_F_INF_0004',null);
									} else {
										alert(d.msg);
									}
								}
							});
						} else {
							makeMsgNoti(config.language,'MSG_F_WRN_0066',null);
						}
					} else {
						makeMsgNoti(config.language,'MSG_F_WRN_0065',null);
					}
				} else {
					makeMsgNoti(config.language,'MSG_F_WRN_0064',null);
				}
			} else {
				makeMsgNoti(config.language,'MSG_F_WRN_0063',null);
			}
		});
	}
}

function checkPW_regex(param) {
	let check_result = true;
	
	let regex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[!@#$%^()_\-={}\[\]|;:<>,.?/])[a-zA-Z\d!@#$%^()_\-={}\[\]|;:<>,.?/]{8,16}$/;
    
	if (/\s/.test(param)) { // 공백 문자 체크
        check_result = false;
    }
	
    if (!regex.test(param)) {
        check_result = false;
    }
	
    return check_result;
}

function mok_result(result) {
	try {
		result = JSON.parse(result);

		let member_name	 = result.user_name;
		let tel_mobile	 = `${result.user_phone.substring(0,3)}-${result.user_phone.substring(3,7)}-${result.user_phone.substring(7)}`;
		let member_birth = `${result.user_birth.substring(0,4)}-${result.user_birth.substring(4,6)}-${result.user_birth.substring(6,9)}`;

		putMember(member_name,tel_mobile,member_birth);
	} catch (error) {
		alert(
			'휴대폰 본인인증을 다시 진횅해주세요.'
		);
	}
}

function putMember(member_name,tel_mobile,member_birth) {
	sessionStorage.removeItem('mok-modify');

	$.ajax({
		url: config.api + "member/put",
		headers : {
			country : config.language
		},
		data :{
			'action_type'		:"INFO",
			'member_name'		:member_name,
			'tel_mobile'		:tel_mobile,
			'member_birth'		:member_birth
		},
		error: function () {
			makeMsgNoti(config.language,'MSG_F_ERR_0071',null);
		},
		success: function (d) {
			if (d.code == 200) {
				alert(
					d.msg,
					function() {
						location.href = `${config.base_url}/my/info`;
					}
				);
			} else {
				alert(
					d.msg,
					function() {
						if (d.code == 401) {
							sessionStorage.setItem('r_url',location.href);
							location.href = `${config.base_url}/login`;
						}
					}
				);
			}
		}
	});
}