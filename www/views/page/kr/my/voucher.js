$(document).ready(function() {
	if (sessionStorage.MEMBER) {
		$("#btn-voucher-submit-close").click(function() {
			$("main.my > section.voucher").removeClass("submit");
		});
		
		$("#frm-voucher").submit(function() {
			let voucher_issue_code = $('#voucher_issue_code').val()
			$.ajax({
				url: config.api + "voucher/put",
				headers : {
					country : config.language
				},
				data: {
					voucher_issue_code
				},
				error: function () {
					makeMsgNoti("MSG_F_ERR_0104", null);
				},
				success: function(d) {
					if(d.code == 200) {
						$("main.my > section.voucher").addClass("submit");
					}
					else {
						alert(d.msg);
					}
				}
			});
	
	
			return false;
		});
	
		$("#btn-voucher-submit-close").click(function () {
			window.location.href = `${config.base_url}/my/voucher/detail`;
		});
	} else {
		let msg_alert = {
			KR : "로그인 후 다시 시도해주세요.",
			EN : "Please Log in and try again."
		}

		alert(
			msg_alert[config.language],
			function() {
				location.href = `${config.base_url}/login`
			}
		);
	}
});