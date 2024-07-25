$(document).ready(function() {
	$.ajax({
		url: config.api + "member/standby/get",
		error: function () {
			alert('바우처정보 조회처리중 오류가 발생했습니다.');
			// makeMsgNoti(country, "MSG_F_ERR_0104", null);
		},
		success: function(d) {
			if(d.code == 200) {
				if(d.data) {
					d.data.forEach(row => {
						$("#list").append(`
							<li data-status="${row.entry_status}">
								<a href="/my/standby/${row.standby_idx}">
									<span class="image" style="background-image:url('${config.cdn + row.thumbnail_location}')"><span class="label">${row.entry_status}</span></span>
									<div class="name">${row.title}</div>
									<small class="date">${row.entry_start_date} - ${row.entry_end_date}</small>
								</a>
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


	$.ajax({
		url: config.api + "member/standby/entry",
		error: function () {
			alert('바우처정보 조회처리중 오류가 발생했습니다.');
			// makeMsgNoti(country, "MSG_F_ERR_0104", null);
		},
		success: function(d) {
			if(d.code == 200) {
				if(d.data) {
					d.data.forEach(row => {
						$("#list2").append(`
							<li data-status="${row.entry_status}">
								<a href="/my/standby/${row.standby_idx}">
									<span class="image" style="background-image:url('${config.cdn + row.thumbnail_location}')"><span class="label">${row.entry_status}</span></span>
									<div class="name">${row.title}</div>
									<small class="date">${row.entry_start_date} - ${row.entry_end_date}</small>
								</a>
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