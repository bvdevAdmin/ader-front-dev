$(document).ready(function() {
	let type = get_query_string('type');
	if (type != null) {
		if (type == "id") {
			$(".tab-container ul > li").eq(0).click();
		} else if (type == "pw") {
			$(".tab-container ul > li").eq(1).click();
		}
	}

	if (config.language == "KR") {
		if (sessionStorage.getItem('result_find')) {
			let find_type = sessionStorage.getItem('find_type');
			let result = JSON.parse(sessionStorage.getItem('result_find'));
			
			if (find_type != null && result != null) {
				if (find_type == "id") {
					clickBTN_id_success(result.member_id,result.tel_mobile);
				} else if (find_type == "pw") {
					if (result.tel_mobile != null) {
						if (sessionStorage.getItem('find_id')) {
							let find_id = sessionStorage.getItem('find_id');
	
							$(".tab-container ul > li").eq(1).click();
							
							$('#frm-find-pw').addClass('hidden');
	
							$('#find-pw-change input[name="member_id"]').val(find_id);
	
							$('#find-pw-change').removeClass('hidden');
	
							clickBTN_pw_success(find_id);
						} else {
							let member_id = $('#frm-find-pw input[name="member_id"]').val();
							if (member_id != null && member_id.length > 0) {
								checkMember(member_id,result.tel_mobile);
							}
						}
					}
				}
			}
	
			sessionStorage.removeItem('find_type');
			sessionStorage.removeItem('result_find');
		}

		clickBTN_mok_id();

		clickBTN_mok_pw();
	} else if (config.language == "EN") {
		$('#frm-find input[name="tel"]').on('keyup',function(e) {
			let value = $(this).val().replace(/\D/g, "");
	
			let result = value.replace(/^(\d{3})(\d{4})(\d{4})$/,"$1-$2-$3");
			$(this).val(result);
		});
		
		clickBTN_find_id();

		clickBTN_find_init();
	}
});

function clickBTN_mok_id() {
	let btn_verify = $('#btn_verify_id');
	if (btn_verify != null) {
		btn_verify.unbind();
		btn_verify.click(function() {
			sessionStorage.setItem('find_type','id');

			if (!window.is_mobile) {
				MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-find", "WB", "mok_result_id");
			} else {
				MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-find-mobile", "MB", "");
			}
		});
	}
}

function mok_result_id(result) {
	try {
		result = JSON.parse(result);
		
		if (result.member_id != null && result.tel_mobile != null) {
			clickBTN_id_success(result.member_id,result.tel_mobile);
		}
	} catch (error) {
		$('#find-id').addClass('hidden');

		$('find-id-fail').removeClass('hidden');
	}
}

function clickBTN_id_success(member_id,tel_mobile) {
	$('#find-id').addClass('hidden');

	$('#find-id-success').removeClass('hidden');

	$('#find-id-success #find-id-result-info').text(member_id);
	
	$('#find-id-success .btn_login').unbind();
	$('#find-id-success .btn_login').click(function() {
		location.href = `${config.base_url}/login`;
	});

	$('#find-id-success .btn_next').unbind();
	$('#find-id-success .btn_next').click(function() {
		$(".tab-container ul > li").eq(1).click();

		$('#frm-find-id').addClass('hidden');

		$('#frm-find-pw').removeClass('hidden');

		$('#frm-find-pw input[name="member_id"]').val(member_id);
	});

	clickBTN_mok_pw();
}

function clickBTN_mok_pw() {
	let btn_verify = $('#btn_verify_pw');
	if (btn_verify != null) {
		btn_verify.unbind();
		btn_verify.click(function() {
			sessionStorage.setItem('find_type','pw');

			let member_id = $('#frm-find-pw input[name="member_id"]').val();
			if (member_id == null || member_id == "") {
				let msg_alert = {
					KR : "이메일주소를 입력해주세요.",
					EN : "Please enter your E-mail"
				}
				alert(msg_alert[config.language]);
				return false;
			}

			if (!window.is_mobile) {
				MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-find", "WB", "mok_result_pw");
			} else {
				sessionStorage.setItem('find_id',member_id);
				MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-find-mobile", "MB", "");
			}
		});
	}
}

function mok_result_pw(result) {
	try {
		result = JSON.parse(result);

		console.log(' [ mok result pw] ',result);

		console.log(result.tel_mobile);
		if (result.tel_mobile != null) {
			let member_id = $('#frm-find-pw input[name="member_id"]').val();
			if (member_id != null && member_id.length > 0) {
				checkMember(member_id,result.tel_mobile);
			}
		}
	} catch (error) {
		$('#find-pw').addClass('hidden');

		$('find-id-fail').removeClass('hidden');
	}
}

function clickBTN_pw_success(member_id) {
	$('#find-pw').addClass('hidden');

	$('#find-pw-change').removeClass('hidden');

	$('#frm-find-pw').next().hide();

	$('#find-pw-change input[name="member_id"]').val(member_id);
	
	clickBTN_change();
}

function checkMember(member_id,tel_mobile) {
	$.ajax({
		url : config.api + "member/put",
		headers : {
			country : config.language
		},
		data : {
			'action_type'		:"CHECK",
			'member_id'			: member_id,
			'tel_mobile'		: tel_mobile
		},
		dataType : 'json',
		success : function(d) {
			if (d.code == 200) {
				$('#find-pw').addClass('hidden');

				$('#find-pw-change').removeClass('hidden');
				
				clickBTN_pw_success(d.data);
			} else {
				$('#frm-find-pw input[name="member_id"]').val('');

				alert(d.msg);
			}
		}
	});
}

function clickBTN_change() {
	let btn_change = $('.btn_change');
	if (btn_change != null) {
		btn_change.unbind();
		btn_change.click(function() {
			let regex = new RegExp(/^(?=.*[a-zA-Z])(?=.*[!@#$%^*+=-])(?=.*[0-9]).{8,16}$/)

			let msg_alert = {
				KR : {
					'msg_01' : "비밀번호를 입력해주세요.",
					'msg_02' : "비밀번호를 정확하게 기입해주세요.",
					'msg_03' : "비밀번호 확인을 입력해주세요.",
					'msg_04' : "비밀번호가 일치하지 않습니다."
				},
				EN : {
					'msg_01' : "Please enter the password.",
					'msg_02' : "Please enter the password correctly.",
					'msg_03' : "Please check the password.",
					'msg_04' : "Password is not correct."
				},
			}

			let member_pw = $("input[name='pw']").val();
			if (member_pw == null && member_pw.length > 0) {
				alert(msg_alert[config.language]['msg_01']);
				return false;
			}

			if (!regex.test(member_pw)) {
				alert(msg_alert[config.language]['msg_02']);
				return false;
			}

			let pw_confirm = $("input[name='pw_confirm']").val();
			if (pw_confirm == null && pw_confirm.length > 0) {
				alert(msg_alert[config.language]['msg_03']);
				return false;
			}

			if (member_pw != pw_confirm) {
				alert(msg_alert[config.language]['msg_04']);
				return false;
			}

			$.ajax({
				url : config.api + "member/put",
				headers : {
					country : config.language
				},
				data : {
					'action_type'	:"CHANGE",
					'member_id'		: $('#find-pw-change input[name="member_id"]').val(),
					'member_pw'		: member_pw
				},
				dataType: "json",
				success : function(d) {
					if (d.code == 200) {
						$('#frm-find-pw-change').addClass('hidden');
						$('#find-pw-change').addClass('hidden');
						
						$('#find-pw-success').removeClass('hidden');

						find-pw-change
					} else {
						let msg_change = {
							KR : "비밀번호 변경처리중 오류가 발생했습니다. 다시 시도해주세요.",
							EN : "An error occured in password change, Please try again."
						}
						alert(msg_change[config.language]);

						$('#frm-find-pw').removeClass('hidden');

						$('#frm-find-pw input[name="member_id"]').val('');
						
						$('#find-pw-fail').removeClass('hidden');

						alert(d.msg);
					}
				}
			});
		});
	}
}

function clickBTN_find_id() {
	let btn_find_id = document.querySelector('#frm-find button[type="button"]');
	if (btn_find_id != null) {
		btn_find_id.addEventListener('click',function(e) {
			let tel_mobile = document.querySelector('#frm-find input[name="tel"]').value;
			if (tel_mobile != null && tel_mobile.length > 0) {
				$.ajax({
					url : config.api + "member/put",
					headers : {
						country : config.language
					},
					data : {
						'action_type'		:"CHECK",
						'tel_mobile'		: tel_mobile
					},
					dataType : 'json',
					success : function(d) {
						if (d.code == 200) {
							alert('Your request has been processed successfully')
						} else {
							alert(d.msg);
						}
					}
				});
			} else {
				alert('Please enter your mobile number.');
			}
		});
	}
}

function clickBTN_find_init() {
	let btn_find_pw = document.querySelector('#frm-find-pw button[type="button"]');
	if (btn_find_pw != null) {
		btn_find_pw.addEventListener('click',function(e) {
			let member_id = document.querySelector('#frm-find-pw input[name="member_id"]').value;
			if (member_id != null && member_id.length > 0) {
				$.ajax({
					url : config.api + "member/find",
					headers : {
						country : config.language
					},
					data : {
						'action_type'		:"INIT",
						'member_id'			: member_id
					},
					dataType : 'json',
					success : function(d) {
						if (d.code == 200) {
							alert('Your request has been processed successfully')
						} else {
							alert(d.msg);
						}
					}
				});
			}
		})
	}
}