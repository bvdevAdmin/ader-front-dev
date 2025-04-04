let f = $("#frm-join");

$(document).ready(function() {
	if (config.language == "EN") {
		$('input[name="tel_mobile"]').on('keyup',function(e) {
			let value = $(this).val().replace(/\D/g, "");
	
			let result = value.replace(/^(\d{3})(\d{4})(\d{4})$/,"$1-$2-$3");
			$(this).val(result);
		});
	}

	if (sessionStorage.getItem("mok-join") != null) {
		let mok = JSON.parse(sessionStorage.getItem("mok-join"));

		$('input[name="member_id"]').val(sessionStorage.getItem('mok-id'));
		$('input[name="member_pw"]').val(sessionStorage.getItem('mok-pw'));
		$('input[name="member_pw2"]').val(sessionStorage.getItem('mok-pw'));
		$('input[name="member_name"]').val(mok.user_name);
		$('input[name="tel_mobile"]').val(`${mok.user_phone.substring(0,3)}-${mok.user_phone.substring(3,7)}-${mok.user_phone.substring(7)}`);
		$('input[name="member_birth"]').val(`${mok.user_birth.substring(0,4)}-${mok.user_birth.substring(4,6)}-${mok.user_birth.substring(6,9)}`);
		$('input[name="member_gender"]').val(mok.user_gender);

		$("#personal-certify-ok").removeClass("hidden");
		$("#btn-personal-certify").parent().remove();

		$('input[name="member_name"]').attr('readonly',true);
		$('input[name="tel_mobile"]').attr('readonly',true);
		$('input[name="member_birth"]').attr('readonly',true);
		$('input[name="member_gender"]').attr('readonly',true);

		$(f).prepend(`<input type="hidden" name="private_confirm" value="y">`);
	}

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
		} else {
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
	
	// 휴대전화 본인인증
	$("#btn-personal-certify").click(function() {
		if($(this).hasClass("on") == false) return false;
		
		// 본인인증 모듈 호출
		if (config.language == "KR") {
			if (!window.is_mobile) {
				MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-request", "WB", "mok_result");
			} else {
				sessionStorage.setItem('mok-id',$('input[name="member_id"]').val());
				sessionStorage.setItem('mok-pw',$('input[name="member_pw"]').val());

				MOBILEOK.process("https://stg.adererror.com/_api/mok/mok-join", "MB", "");
			}
		} else {
			$(f).prepend(`<input type="hidden" name="private_confirm" value="y">`);
			$("#personal-certify-ok").removeClass("hidden");
			$(this).parent().remove();
		}
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
		sessionStorage.removeItem('mok-id');
		sessionStorage.removeItem('mok-pw');
		sessionStorage.removeItem('mok-join');
		
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

function mok_result(result) {
	try {
		result = JSON.parse(result);
		
		$("#personal-certify-ok").removeClass("hidden");
		$("#btn-personal-certify").parent().remove();
		
		$('input[name="member_name"]').val(result.user_name);
		$('input[name="tel_mobile"]').val(`${result.user_phone.substring(0,3)}-${result.user_phone.substring(3,7)}-${result.user_phone.substring(7)}`);
		$('input[name="member_birth"]').val(`${result.user_birth.substring(0,4)}-${result.user_birth.substring(4,6)}-${result.user_birth.substring(6,9)}`);
		$('input[name="member_gender"]').val(result.user_gender);

		$('input[name="member_name"]').attr('readonly',true);
		$('input[name="tel_mobile"]').attr('readonly',true);
		$('input[name="member_birth"]').attr('readonly',true);
		$('input[name="member_gender"]').attr('readonly',true);

		$(f).prepend(`<input type="hidden" name="private_confirm" value="y">`);
	} catch (error) {
		alert(
			'휴대폰 본인인증을 다시 진횅해주세요.'
		);
	}
}