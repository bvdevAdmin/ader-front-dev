$(function () {
	$('.black__small__btn.pw_find_btn').on('click', password_find_check);
	
	initLoginHandler();
});

function password_find_check() {
	let country_param = '&country=' + getLanguage();
	let mail_regex = new RegExp('^[a-zA-Z0-9+-\_.]+@[a-zA-Z0-9-]+\.([a-zA-Z0-9-]|([a-zA-Z0-9-]+\.[a-zA-Z0-9-]))+$');
	let member_id = $('#member_id').val();
	mail_regex.test(member_id);

	$('.font__underline.font__red').css('display', 'none');

	if (member_id == '') {
		$('.member_id_msg_write_email').css('display', 'block');

		let target = document.querySelector('.member_id_msg_write_email');
        let targetRectTop = target.getBoundingClientRect().top;

        if(targetRectTop < 100){
            window.scroll({top:140, behavior:'smooth'});
        }
		return false;
	} else {
		if (!mail_regex.test(member_id)) {
			$('.member_id_msg_correct_email').css('display', 'block');

			let target = document.querySelector('.member_id_msg_correct_email');
			let targetRectTop = target.getBoundingClientRect().top;

			if(targetRectTop < 100){
				window.scroll({top:140, behavior:'smooth'});
			}
			return false;
		}
	}
	$('.black__small__btn.pw_find_btn').unbind('click');
	$.ajax(
		{
			url: api_location + "account/check",
			type: 'POST',
			data: $("#frm-find").serialize() + country_param,
			dataType: 'json',
			error: function (data) {
				$('.member_id_msg').css('visibility', 'hidden');
				$('.result_msg').css('visibility', 'visible');
				$('.result_msg').text("비밀번호 체크처리중 오류가 발생했습니다.");
				$('.black__small__btn.pw_find_btn').on('click', password_find_check);
			},
			success: function (data) {
				if (data.code == "200") { // 이메일검사 성공
					$('.font__underline.font__red').css('display', 'none');
					
					let msg_body = "";
					switch (getLanguage()) {
						case "KR" :
							msg_body= "입력하신 이메일로 <br> 비밀번호 변경창 링크를 전송했습니다.";
							break;
						
						case "EN" :
							msg_body= "Sent the password change link<br/>to the email you entered";
							break;
						
						case "CN" :
							msg_body= "用您输入的邮箱<br/>我发送了更改密码的链接。";
							break;
					}
					notiModal(msg_body);
					
					$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
				} else {	// 이메일검사 실패
					$('.member_id_msg_correct_email').css('display', 'block');
					$('.black__small__btn.pw_find_btn').on('click', password_find_check);
				}
			}
		}
	);
}