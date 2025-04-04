$("#frm-mypage-leave").submit(function() {
	confirm('회원 탈퇴 시 30일간 동일 아이디, 전화번호로 가입이 불가합니다.<br><br>회원 탈퇴 하시겠습니까?',function() {
		$.ajax({
			url: config.api + "member/leave",
			data: { reason : $("#frm-mypage-leave input[name='reason']").val() },
			success: function(d) {
				if(d.code == 200) {
					alert("회원 탈퇴가 완료되었습니다.<br><br>그동안 서비스를 이용해주셔서 감사합니다.",function() {
						location.href = "/";
					});
				}
				else {
					alert(d.msg);
				}
			}
		});
	});

	return false;
});