$(document).ready(function() {
	/* 01. 인트로 서브텍스트 페이드 인 **/
	$("#bi > h2").addClass("on");
	setTimeout(() => {
		$("#bi").addClass("fadeout");

		setTimeout(() => {
			$("#bi").remove();
			// https://github.com/SDuck4/type-hangul/releases
			$("#typing-effect").get(0).addEventListener('th.endType', function () {
				$("#logo > .logo").addClass("on");
				setTimeout(() => {
					$("#logo").addClass("fadeout");
					setTimeout(() => {
						$("#logo").remove();
						$("#form").addClass("scroll");
					},2000);
				},3000);
			});
			TypeHangul.type("#typing-effect", {
			});
		},2500);
	},2000);


	/** 폼 서브밋 **/
	$("form#frm").submit(function() {
		let name = $(this).find("input[name='name']").val()
			, tel = $(this).find("input[name='tel']").val()
			, birthday = $(this).find("input[name='birthday']").val()
			, gender = $(this).find("input[name='gender']:checked").val();
		if(name == "") {
			alert("성함을 입력해주세요.");
			$(this).find("input[name='name']").focus();
		}
		else if(is_tel(tel) == false) {
			alert("연락처를 입력해주세요.");
			$(this).find("input[name='tel']").focus();
		}
		/*
		else if(isNaN(birthday) == true || birthday.length != 8) {
			alert("생년월일을 정확히 입력해주세요.");
			$(this).find("input[name='tel']").focus();
		}
		*/
		else if(typeof gender == 'undefined') {
			alert("성별을 선택해주세요.");
		}
		else if($(this).find("input[name+='receive_']:checked").length == 0) {
			alert("받고 싶은 소식 1개이상 선택해 주세요.");
		}
		else if($(this).find("input[name='agree_terms']:checked").length == 0) {
			alert("개인정보 수집 및 이용에 동의해 주세요.");
		}
		else {
			$.ajax({
				url : config.api + "blue-message/put",
				data : $(this).serialize(),
				success : function(d) {
					if(d.code == 200) {
						$("#form > article.input").remove();
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