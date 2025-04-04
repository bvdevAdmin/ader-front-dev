$(document).ready(function() {
	getCustom();
});

function getCustom() {
	$.ajax({
		url: config.api + "member/purchase/get",
		headers : {
			country : config.language
		},
		error: function () {
			makeMsgNoti('MSG_F_ERR_0046',null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null) {
					$('.gender').html(`<ul class="dot"><li>${data.member_gender}</li></ul>`)
					$('.height').html(`${data.height} cm`);
					$('.weight').html(`${data.weight} kg`);
					$('#upper_size').html(`${data.upper_size}`);
					$('#lower_size').html(`${data.lower_size}`);
					$('#shoes_size').html(`${data.shoes_size}`);
				} else {
					location.href = `${config.base_url}/my/info/purchase/custom`;
				}
			}
		}
	});
}