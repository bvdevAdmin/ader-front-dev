$(document).ready(function() {
	// 적립금
	$("#my-point").text(number_format(config.member.mileage));

	// 바우처
	$("#my-voucher").text(number_format(config.member.voucher));
	
	// 위시리스트
	$("#my-wishlist").text(number_format(1000));

	$("#frm").submit(function() {
		if($(this).find("input[name='agree_1']:checked").length == 0) {
			alert("탈퇴 동의에 체크해주세요.");
		}
		else if($(this).find("input[name='agree_2']:checked").length == 0) {
			alert("적립금 잔여금액, 바우처 자동 소멸에 동의해주세요.");
		}
		else {
			$.ajax({
				url : config.api + 'member/drop',
				data : $(this).serialize(),
				success : function(d) {
					if(d.code == 200) {
						alert("회원 탈퇴가 완료되었습니다.");
						location.href = "/";
					}
					else {
						alert(d.msg);
					}
				}
			});
		}

		return false;
	});
});