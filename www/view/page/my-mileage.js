$(document).ready(function() {
	/** 마일리지 현황 **/
	$.ajax({
		url : config.api + "member/mileage/get",
		success : function(d) {
			$("#mileage-useful").text(number_format(d.mileage_balance));	// 사용 가능
			$("#mileage-stack").text(number_format(d.refund_scheduled + d.mileage_balance));	// 총 적립
			$("#mileage-used").text(number_format(d.refund_scheduled));	// 사용 완료
			$("#mileage-scheduled").text(number_format(d.used_mileage));	// 적립 예정
		}
	});
	
	/** 마일리지 목록 **/
	$.ajax({
		url : config.api + "member/mileage/history",
		data : {
			rows : 20
		},
		success : function(d) {
			if(d.code == 200 && d.data) {
				d.data.forEach(row => {
					$("#list").append(`
					
					`);
				});
			}
		}
	});
});