$(document).ready(function() {
	clickBTN_auth();
});

function clickBTN_auth() {
	let btn_auth = document.querySelector('.btn_auth');
	if (btn_auth != null) {
		btn_auth.addEventListener('click',function() {
			if (!is_mobile) {
				MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-request", "WB", "mok_result");
			} else {
				MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-join", "MB", "");
			}
		});
	}
}

function mok_result(result) {
	try {
		result = JSON.parse(result);
		if (result) {
			member_auth();
		}
	} catch (error) {
		alert(
			'휴대폰 본인인증을 다시 진횅해주세요.'
		);
	}
}

function member_auth() {
	$.ajax({
		url: config.api + "member/auth",
		headers : {
			country : config.language
		},
		async:false,
		error: function () {
			makeMsgNoti(config.language,'MSG_F_ERR_0046','KR',null);
		},
		success: function (d) {
			if (d.code == 200) {
				alert(
					'회원 인증이 완료되었습니다.',
					function() {
						location.href = config.base_url;
					}
				);
			} else {
				alert(
					d.msg,
					function() {
						if (d.code == 401) {
							location.href = `${config.base_url}/login`;
						}
					}
				)
			}
		}
	});
}