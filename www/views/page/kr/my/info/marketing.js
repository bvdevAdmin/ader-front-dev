$(document).ready(function() {
	if (config.member != null) {
		if (config.member.r_mail_flg) {
			$('input[name="email"]').prop('checked',true);
		}
		
		if (config.member.r_sms_flg) {
			$('input[name="sms"]').prop('checked',true);
		}
		
		if (config.member.r_tel_flg) {
			$('input[name="tel"]').prop('checked',true);
		}
	}
	
    $("#frm").submit(function(e) {
		e.preventDefault();
		
		$.ajax({
			url: config.api + "member/put",
			headers : {
				country : config.language
			},
			data: $(this).serialize(),
			async:false,
			error: function () {
				makeMsgNoti(config.language,'MSG_F_ERR_0065', null);
			},
			success: function (d) {
				if (d.code == 200) {
					location.href = `${config.base_url}/my/info`;
				}
			}
		});
	});
});

function getMarketing() {
	
}
