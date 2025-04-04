let txt_none = {
    KR : "조회 가능한 A/S 신청내역이 존재하지 않습니다.",
    EN : "There is no A/S history",
}

$(document).ready(function() {
	if (!config.member.id) {
		let msg_alert = {
			KR : "로그인 후 다시 시도해주세요.",
			EN : "Please Log in and try again."
		}

		alert(
			msg_alert[config.language],
			function() {
				sessionStorage.setItem('r_url',location.href);
				location.href = `${config.base_url}/login`;
			}
		)
		
	}
	
	getAS_list();
});

const data = {
	rows: 10,
	page: 1
}

function getAS_list() {
	$.ajax({
		url : config.api + "as/list/get",
		headers : {
			country : config.language
		},
		data,
		success : function(d) {
			if (d.code == 200) {
				$("#list").html('');
				
				let data = d.data;
				if (data != null && data.length > 0) {
					let dt = {
						KR : {
							't_01' : "구매처",
							't_02' : "Bluemark 시리얼코드",
							't_03' : "Bluemark 인증날짜",

							't_04' : "A/S번호",
							't_05' : "신청날짜",
							't_06' : "자세히 보기"
						},
						EN : {
							't_01' : "Purchase mall",
							't_02' : "Bluemark serial",
							't_03' : "Bluemark date",

							't_04' : "A/S number",
							't_05' : "Apply date",
							't_06' : "Details"
						}
					}
					
					data.forEach(row => {
						$("#list").append(`
							<li>
								<header>
									<dl>
										<dt>${dt[config.language]['t_04']}</dt>
										<dd>${row.as_code}</dd>
										<dt>${dt[config.language]['t_05']}</dt>
										<dd>${row.create_date}</dd>
									</dl>
									<a href="${config.base_url}/my/as/status/${row.as_idx}" class="btn">
										<dt>${dt[config.language]['t_06']}</dt>
									</a>
								</header>

								<div class="image" style="background-image:url('${config.cdn}${row.img_location}')"></div>

								<div class="goods">
									<div class="status">${row.as_status}</div>
									<br>
									<div class="title">${row.product_name}</div>
									<div class="price">${number_format(row.price)}</div>
									<div class="color">${row.color}
										<span class="colorchip" style="background-color:${row.color_rgb.toLowerCase()}"></span>
									</div>
									<div class="size">${row.option_name}</div>
								</div>

								<dl class="buy">
									<dt>${dt[config.language]['t_01']}</dt>
									<dd>${row.purchase_mall}</dd>
									<dt>${dt[config.language]['t_02']}</dt>
									<dd>${row.serial_code}</dd>
									<dt>${dt[config.language]['t_03']}</dt>
									<dd>${row.reg_date}</dd>
								</dl>
							</li>
						`);
					});
				} else {
					$('#list').append(`
						<div class="list__none">
							${txt_none[config.language]}
						</div>
					`);
				}
			}
			
			/** 자세히 보기 **/


			/** 페이징 처리 **/
			if('page' in d) {
				paging({
					total : d.total,
					el : $(".paging"),
					page : d.page,
					rows : data.rows,
					show_paging : 10,
					fn : function(page) {
						data.page = page
						getAS_list();
					}
				});
			}
			
		}
	});
}