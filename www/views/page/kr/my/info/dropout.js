$(document).ready(function() {
	// 적립금
	$("#my-point").text(number_format(config.member.mileage));

	// 바우처
	$("#my-voucher").text(number_format(config.member.voucher));
	
	// 위시리스트
	$("#my-wishlist").text(number_format(config.member.cnt_wish));

	$("#frm").submit(function(e) {
		e.preventDefault();

		let msg_alert = {
			KR : {
				't_01' : "탈퇴 동의에 체크해주세요.",
				't_02' : "적립금 잔여금액, 바우처 자동 소멸에 동의해주세요.",
				't_03' : "회원 탈퇴가 완료되었습니다."
			},
			EN : {
				't_01' : "Please check the agree to dropout.",
				't_02' : "Please check the agree to vanish the mileage and voucher.",
				't_03' : "Dropout has completed."
			}
		}
		
		if($(this).find("input[name='agree_1']:checked").length == 0) {
			alert(msg_alert[config.language]['t_01']);
		}
		else if($(this).find("input[name='agree_2']:checked").length == 0) {
			alert(msg_alert[config.language]['t_02']);
		}
		else {
			$.ajax({
				url : config.api + 'member/drop',
				headers : {
					country : config.language
				},
				data : $(this).serialize(),
				success : function(d) {
					if(d.code == 200) {
						alert(
							msg_alert[config.language]['t_03'],
							function() {
								location.href = `${config.base_url}`;
							}
						);
					}
					else {
						alert(d.msg);
					}
				}
			});
		}

		return false;
	});

	$('.info.drop .buttons [type="button"]').click(function() {
		location.href = `${config.base_url}/my/info`;
	});
});