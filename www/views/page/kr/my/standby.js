let txt_none = {
	KR : {
		apply : "신청 가능 한 스탠바이가 존재하지 않습니다.",
		entry : "조회 가능한 스탠바이 신청 내역이 존재하지 않습니다."
	},
	EN : {
		apply : "There is no standby to apply.",
		entry : "There is no entry history about standby."
	}
}

$(document).ready(function() {
	$.ajax({
		url: config.api + "member/standby/get",
		headers : {
			country : config.language
		},
		error: function () {
			makeMsgNoti("MSG_F_ERR_0104", null);
		},
		success: function(d) {
			if(d.code == 200) {
				let mediaQuery = window.matchMedia("screen and (max-width:1025px)");
				let standby_page = d.data.standby_page;

				if (standby_page != null && standby_page.length > 0) {
					standby_page.forEach(row => {
						let blur = row.entry_status == 'E' && !(row.txt_entry == '종료' || row.txt_entry == 'Coming soon') ? 'filter:blur(5px);' : ''
						let thumb_location = mediaQuery.matches ? row.thumb_location_M : row.thumb_location_W
						$("#list").append(`
							<li data-status="${row.entry_status}">
								<a href="${config.base_url}/my/standby/${row.standby_idx}">
									<span class="image" style="background-image:url('${config.cdn}${thumb_location}');${blur}">
										<span class="label">${row.txt_entry}</span>
									</span>
									<div class="name">${row.title}</div>
									<small class="date">${row.entry_start_date} - ${row.entry_end_date}</small>
								</a>
							</li>
						`);
					});
				} else {
					$("#list").append(`
						<div class="list__none">
							${txt_none[config.language]['apply']}
						</div>
					`);
				}
				
				let standby_entry = d.data.standby_entry;
				if (standby_entry != null && standby_entry.length > 0) {
					standby_entry.forEach(row => {
						let blur = row.entry_status == 'E' && !(row.txt_entry == '종료' || row.txt_entry == 'Coming soon') ? 'filter:blur(5px);' : ''
						let thumb_location = mediaQuery.matches ? row.thumb_location_M : row.thumb_location_W
						$("#list2").append(`
							<li data-status="${row.entry_status}">
								<a href="${config.base_url}/my/standby/${row.standby_idx}">
									<span class="image" style="background-image:url('${config.cdn}${thumb_location}');${blur}">
										<span class="label">${row.txt_entry}</span>
									</span>
									<div class="name">${row.title}</div>
									<small class="date">${row.entry_start_date} - ${row.entry_end_date}</small>
								</a>
							</li>
						`);
					});
				} else {
					$("#list2").append(`
						<div class="list__none">
							${txt_none[config.language]['entry']}
						</div>
					`);
				}
			} else {
				alert(
					d.msg,
					function() {
						if (d.code == 401) {
							sessionStorage.setItem('r_url',location.href);
							location.href = `${config.base_url}/login`;
						}
					}
				);
			}
		}
	});
});