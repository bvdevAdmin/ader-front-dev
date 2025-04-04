let f = $("#frm-mypage-info");
$.ajax({
	url: config.api + "member/get",
	success: function(d) {
		if(d.code == 200) {
			$(f).find("input[name='id']").val(d.id);
			$(f).find("input[name='name']").val(d.name);
			$(f).find("input[name='email']").val(d.email);
			$(f).find("input[name='mobile']").val(tel_format(d.tel));
			$(f).find(".image").css({backgroundImage:`url('${d.profile_image}')`});

			/** 프로필 이미지 변경 **/
			$(f).find("input[name='profile_image']").change(function() {
				$(f).find("div.profile figure .image").css({
					backgroundImage:`url('${window.URL.createObjectURL($(this)[0].files[0])}')`
				});
			});

			/** 휴대폰 본인 인증 문자 **/
			$(f).find("input[name='mobile'] ~ button.btn").click(function() {
				if($(f).find("input[name='mobile']").parent().hasClass("auth-ok")) {
					confirm("다른 번호로 인증 시도할까요?",function() {
						$(f).find("input[name='mobile']").prop("readonly",false).val("").focus();
						$(f).find("input[name='mobile']").parent().removeClass("auth-ok");
					});
					return;
				}

				if($(this).hasClass("disabled")) {
					$("#mobile-confirm").addClass("hidden");
					$("#mobile-confirm .counter").empty();
					$(f).find("input[name='mobile']").prop("disable",false).val("");
					$(this).removeClass("disabled");
					return;
				}
				$(this).addClass("disabled");
				$("#mobile-confirm .counter").empty();
				$(f).find("input[name='mobile_confirm']").val("");
				$.ajax({
					url: config.api + "auth/mobile",
					data: {
						number : $(this).parent().find("input").val()
					},
					success: function(d) {
						if(d.code == 200) {
							$("#mobile-confirm").removeClass("hidden");
							$(f).find("input[name='mobile']").prop("disable",true);
							let check_mobile_confirm = setInterval(function() {
								if(d.count == 0) {
									clearInterval(check_mobile_confirm);
									alert("인증 시간이 초과되었습니다. 본인인증을 다시 시도하여 주세요.",function() {
										$("#mobile-confirm").addClass("hidden");
										$(f).find("input[name='mobile']").prop("disable",false);
										$(f).find("input[name='mobile'] ~ button.btn").removeClass("disabled");
										$("#mobile-confirm .counter").empty();
									});
								}
								else {
									d.count--;
									$("#mobile-confirm .counter").html(`${Math.floor(d.count/60)}:${addzero(d.count%60)}`);
								}
							},1000);

							/** 인증 번호 입력 **/
							$(f).find("input[name='mobile_confirm']").focus();
							$(f).find("input[name='mobile_confirm'] ~ button").click(function() {
								$.ajax({
									url: config.api + "auth/ok",
									data: {
										auth_number : $(this).parent().find("input").val()
									},
									success: function(d) {
										if(d.code == 200) {
											$("#mobile-confirm").addClass("hidden");
											$(f).find("input[name='mobile']").parent().addClass("auth-ok");
											$(f).find("input[name='mobile']").prop("readonly",true);
											$(f).find("input[name='mobile'] ~ button.btn").removeClass("disabled");
										}
										else {
											alert(d.msg);
										}
									}
								});
							});
						}
						else {
							alert(d.msg);
							$(f).find("input[name='mobile'] ~ button.btn").removeClass("disabled");
						}
					}
				});
			});


		}
		else {
			alert(d.msg);
		}
	}
});

$(f).submit(function() {
	$.ajax({
		url: config.api + "member/put",
		data: new FormData($(this)[0]),
		processData:false,
		contentType:false,
		success: function(d) {
			if(d.code == 200) {
				alert("회원 정보가 변경되었습니다.");
			}
			else {
				alert(d.msg);
			}
		}
	});
	return false;
});