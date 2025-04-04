let t_column = {
	KR : {
		t_01 : "신청 취소",
		t_02 : "알림 완료",
		t_03 : ""
	},
	EN : {
		t_01 : "Cancel",
		t_02 : "Completed",
		t_03 : ""
	}
}

/* 재입고 알림 기본 메시지 */
let txt_none = {
	KR : {
		apply : "조회 가능한 재입고 알림 신청내역이 존재하지 않습니다.",
		alarm : "조회 가능한 재입고 알림 완료내역이 존재하지 않습니다.",
	},
	EN : {
		apply : "There is no requested reorder.",
		alarm : "There is no completed reorder.",
	},
}

$(document).ready(function() {
	getReorder_list();
});

function getReorder_list() {
	[['list-1','apply'],['list-2','alarm']].forEach(list_type => {
		$.ajax({
			url : config.api + "reorder/get",
			headers : {
				country : config.language
			},
			data : {
				country : config.language,
				list_type : list_type[1],
				rows : 100,
				page : 1
			},
			success : function(d) {
				if (d.code == 200) {
					$(`#${list_type[0]}`).html('');
					
					if (d.data != null && d.data.length > 0) {
						d.data.forEach(row => {
							let btn_cancel = "";
							if (list_type[1] == "apply") {
								btn_cancel += `
									<li class="buttons">
										<button type="button" class="cancel" data-reorder_no="${row.reorder_idx}">${t_column[config.language]['t_01']}</button>
									</li>
								`;
							}

							let status_reorder = "";
							if (list_type[1] == "alarm") {
								status_reorder = `
									<li class="status">${t_column[config.language]['t_02']}</li>
								`;
							}
							
							$(`#${list_type[0]}`).append(`
								<li>
									<div class="thumbnail" style="background-image:url('${config.cdn}${row.img_location}')" data-no="${row.product_idx}"></div>
									<div class="name">${row.product_name}</div>
									<div class="price">${number_format(row.sales_price_kr)}</div>
									<div class="color">
										${row.color}
										<span class="colorchip" style="background-color:${row.color_rgb}"></span>
									</div>
									<div class="size">${row.option_name}</div>
									<ul class="info">
										${status_reorder}
										<li class="date">${row.create_date}</li>
										${btn_cancel}
									</ul>
								</li>
							`);
						});

						let div_product = document.querySelectorAll(`#${list_type[0]} li .thumbnail`);
						if (div_product != null && div_product.length > 0) {
							div_product.forEach(div => {
								div.addEventListener('click',function(e) {
									let el = e.currentTarget;

									if (el.dataset.no != null) {
										location.href = `${config.base_url}/shop/${el.dataset.no}`;
									}
								});
							});
						}
					} else {
						let txt_default = "";
						if (list_type[1] == "apply") {
							txt_default = "";
						} else if (list_type[1] == "alarm") {
							txt_default = "";
						}

						$(`#${list_type[0]}`).append(`
							<div class="list__none">
								${txt_none[config.language][list_type[1]]}
							</div>
						`);
					}

					clickBTN_cancel();
				} else {
					alert(
						d.msg,
						function() {
							sessionStorage.setItem('r_url',location.href);
							location.href = `${config.base_url}/login`;
						}
					)
				}
			}
		});
	});
}

function clickBTN_cancel() {
	let btn_cancel = $('.cancel');
	if (btn_cancel != null && btn_cancel.length > 0) {
		btn_cancel.unbind();
		btn_cancel.click(function() {
			let reorder_idx = $(this).data('reorder_no');
			if (reorder_idx != null) {
				$.ajax({
					url: config.api + "reorder/put",
					headers : {
						country : config.language
					},
					data: {
						'reorder_idx'	:reorder_idx,
					},
					error: function () {
						alert_noti("MSG_F_ERR_0141", null);
					},
					success: function (d) {
						if (d.code == 200) {
							getReorder_list();
							
							alert_noti("MSG_F_INF_0025", null);
						} else {								
							switch(d.code) {
								case 401:
									$("#tnb a[data-side='my']").click(); // 로그인 창 표시
								break;
							}
							
							if(d.msg) {
								alert(d.msg);
							}
						}
					}
				});
			}
		});
	}
}