$(document).ready(function() {
	let f = $("#frm-join");

	$(f).prepend(`<input type="hidden" name="country" value="${config.language}">`);
	
	function chk_confirm_personal_certify() {
		let chk_current_certify = 0;
		$(f).find("input[name='member_id'],input[name='member_pw'],input[name='member_pw2']").each(function() {
			if($(this).val() != "" && $(this).parent().find(".vaild").hasClass("on") == false) {
				chk_current_certify++;
			}
		});
		
		if(chk_current_certify == 3) {
			$("#btn-personal-certify").addClass("on");
		}
		else {
			$("#btn-personal-certify").removeClass("on");
		}
	}

	// 이메일 검증
	$(f).find("input[name='member_id']").keyup(function() {
		let noti = $(this).parent().find(".vaild");
		if($(this).val() != '' && is_email($(this).val()) == false) {
			noti.addClass("on");
		}
		else {
			noti.removeClass("on");
		}
		chk_confirm_personal_certify();
	});
	
	// 비밀번호 검증
	$(f).find("input[name='member_pw']").keyup(function() {
		let reg = new RegExp(/^(?=.*[a-zA-Z])(?=.*[!@#$%^*+=-])(?=.*[0-9]).{8,16}$/)
			, noti = $(this).parent().find(".vaild");
		if($(this).val() != '' && reg.test($(this).val()) == false) {
			noti.addClass("on");
		}
		else {
			noti.removeClass("on");
		}
		chk_confirm_personal_certify();
	});

	// 비밀번호 확인 검증
	$(f).find("input[name='member_pw2']").keyup(function() {
		let pw = $(f).find("input[name='member_pw']").val()
			, noti = $(this).parent().find(".vaild");
		if($(this).val() != pw) {
			noti.addClass("on");
		}
		else {
			noti.removeClass("on");
		}
		chk_confirm_personal_certify();
	});

	function mobileAuthenfication() {
		MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-request", "WB", "mobile_result");
	}
	
	// 휴대전화 본인인증
	$("#btn-personal-certify").click(function() {
		if($(this).hasClass("on") == false) return false;
		
		// 본인인증 모듈 호출
		//mobileAuthenfication()
		
		// 가입 진행
		$(f).prepend(`<input type="hidden" name="private_confirm" value="y">`);
		$("#personal-certify-ok").removeClass("hidden");
		$(this).parent().remove();

		/*
		//let reg = new RegExp(/^(01[016789]{1}|02|0[3-9]{1}[0-9]{1})-?[0-9]{3,4}-?[0-9]{4}$/)
		let reg = new RegExp(/^(01[016789]{1}|02|0[3-9]{1}[0-9]{1})?[0-9]{3,4}?[0-9]{4}$/)
			, noti = $(f).find("input[name='tel_mobile']").parent().find(".vaild");
		
		if(reg.test($(f).find("input[name='tel_mobile']").val())) {
			noti.addClass("off");
			if($(f).find("input[name='private_confirm']").length == 0) {
				$(f).prepend(`<input type="hidden" name="private_confirm" value="y">`);
			}
		}
		else {
			noti.addClass("on");
		}
		*/	
	});

	// 약관 동의
	$(f).find(".agrees input[type='checkbox']").click(function() {
		if($(this).attr("name") == "agree_all") {
			$(f).find("input[name='agree_terms']").prop("checked",$(this).prop("checked"));
			$(f).find("input[name='agree_receive_sms']").prop("checked",$(this).prop("checked"));
			$(f).find("input[name='agree_receive_email']").prop("checked",$(this).prop("checked"));
		}

		if($(f).find("input[name='agree_terms']:checked").length == 0
			|| $(f).find("input[name='agree_receive_sms']:checked").length == 0
			|| $(f).find("input[name='agree_receive_email']:checked").length == 0
		) {
			$(f).find("input[name='agree_all']").prop("checked",false); // 모두 선택 체크
		}
		else {
			$(f).find("input[name='agree_all']").prop("checked",true); // 모두 선택 체크
		}
		
	});
	
	$(f).submit(function() {		
		if($(f).find("input[name='private_confirm']").length == 0 || $(f).find("input[name='private_confirm']").val() != "y") {
			alert("휴대전화 본인인증을 진행해주세요.");
		}
		else if($(this).find("input[name='agree_terms']").prop("checked") == false) {
			alert("이용약관, 개인정보수집 및 이용에 동의해주셔야 합니다.");
		}
		else {
			$.ajax({
				url : config.api + "member/join",
				headers : {
					country : config.language
				},
				data : new FormData($(this).get(0)),
				processData:false,
				contentType:false,
				success : function(d) {
					if(d.code == 200) {
						$("body > main.my > section.account").addClass("item-center");
						$("#btn-mobile-history-back").remove();
						$("section.account > article.join").addClass("ok");						
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