$(document).ready(function() {
	$("#btn-voucher-submit-close").click(function() {
		$("main.my > section.voucher").removeClass("submit");
	});
	
	$("#frm-voucher").submit(function() {
		$("main.my > section.voucher").addClass("submit");
		return false;
	});
	
	$.ajax({
		url: config.api + "voucher/get",
		error: function () {
			alert('바우처정보 조회처리중 오류가 발생했습니다.');
			// makeMsgNoti(country, "MSG_F_ERR_0104", null);
		},
		success: function(d) {
			if(d.code == 200) {
				if(d.data) {
					d.data.forEach(row => {
						$("#list").html(`
							<li>
								<p>${row.voucher_name}</p>
								<div class="sale">${row.sale_price_type}</div>
								<div class="date">${row.usable_start_date} - ${row.usable_end_date}</div>
								<div class="expire">${date_interval}일 남음</div>
							</li>
						`);
					});
				}
			}
			else {
				alert(d.msg);
			}
		}
	});
});