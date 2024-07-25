$(document).ready(function() {
	/** 우편번호 검색 **/

	
	/** 등록하기 버튼 **/
	$("#frm").submit(function() {
		let msg = {
			place : {
				KR : "배송지명을 작성해주세요.",
				EN : "Please fill out the place.",
				CN : "请填写地址名。"
			},
			name : {
				KR : "이름을 작성해주세요.",
				EN : "Please fill out the name.",
				CN : "请填写姓名。"
			},
			mobile : {
				KR : "전화번호를 작성해주세요.",
				EN : "Please fill out the mobile number.",
				CN : "请填写手机号码。"
			}
		};

		$.ajax({
			url: config.api + "member/address/put",
			data: $(this).serialize(),
			error: function () {
				makeMsgNoti('MSG_F_ERR_0039', null);
			},
			success: function (d) {
				if(d.code == 200) {
					location.href = "/my/info/address";
				} 
				else if(d.code = 401) {
					$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
				}
				else {
					makeMsgNoti('MSG_F_WRN_0037', null);
				}
			}
		});

		return false;
	});
});