$(function () {
	$('.black__btn.update_pw_btn').on('click', updateMemberPw);
});

function updateMemberPw() {
	//비밀번호 변경 -> 이메일로 변경페이지 링크생성될때 파라미터로 주어진 값.
	//로그인 유무와는 상관없음.
	var member_idx = $('input[name="member_idx"]').val();
	var country = localStorage.getItem('lang');

	var member_pw = $('input[name="member_pw"]').val();
	var member_pw_confirm = $('input[name="member_pw_confirm"]').val();

	$('.warn__msg').css('visibility', 'hidden');

	if (memberPwConfirm(member_pw) == false) {
		$('.font__underline.warn__msg.member_pw').css('visibility', 'visible');
		return false;
	}
	if (member_pw != member_pw_confirm) {
		$('.warn__msg.member_pw_confirm').css('visibility', 'visible');
		return false;
	}

	$.ajax({
		type: 'POST',
		url: api_location + "account/put",
		headers: {
			"country":getLanguage()
		},
		data: {
			'member_idx': member_idx,
			'member_pw': member_pw
		},
		dataType: 'json',
		error: function (data) {
		},
		success: function (data) {
			if (data.code == "200") {
				makeMsgNoti(getLanguage(), 'MSG_F_INF_0004', null);
				//notiModal(msg_title,msg_body);
				location.href = '/login';
			}
			else {
				notiModal(data.msg);
			}
		}
	});
}