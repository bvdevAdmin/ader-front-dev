let t_column = {
    KR : {
		t_01 : "일자",
		t_02 : "주문번호",
		t_03 : "내용",
		t_04 : "구매금액",
		t_05 : "적립",
		t_06 : "사용"
    },
    EN : {
		t_01 : "Date",
		t_02 : "Order number",
		t_03 : "Type",
		t_04 : "Price",
		t_05 : "Increased",
		t_06 : "Decreased"
    }
}

let txt_none = {
	KR : {
		"#list-1" : "조회 가능한 적립금 지급 내역이 존재하지 않습니다.",
		"#list-2" : "조회 가능한 적립금 사용 내역이 존재하지 않습니다.",
	},
	EN : {
		"#list-1" : "There is no increased mileage history.",
		"#list-2" : "There is no decreased mileage history.",
	},
}

let data = {
	rows: 10,
	page: 1
}

$(document).ready(function() {
	/** 마일리지 현황 **/
	$.ajax({
		url : config.api + "member/mileage/get",
		headers : {
			country : config.language
		},
		success : function(d) {
			if (d.code == 200) {
				if (d.data != null) {
					$("#mileage-useful").text(d.data.mileage_balance);		//사용 가능
					$("#mileage-stack").text(d.data.mileage_inc);			//총 적립
					$("#mileage-used").text(d.data.mileage_dec);			//사용 완료
					$("#mileage-scheduled").text(d.data.mileage_unusable);	//적립 예정
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
				)
			}
		}
	});

	/** 마일리지 목록(적립) **/
	getMileage_list( 'INC' );

	/** 마일리지 목록(사용) **/
	getMileage_list( 'DEC' );
});

function getMileage_list( list_type ) {
	$.ajax({
		url: config.api + "member/mileage/history",
		headers : {
			country : config.language
		},
		data: {
			...data,
			list_type
		},
		success: function (d) {
			let listId = list_type == 'INC' ? '#list-1' : '#list-2'
			if (d.code == 200) {
				$(listId).empty();

				if (d.data != null && d.data.length > 0) {
					$(listId).append(`
						<li>
							<div class="t_01">${t_column[config.language]['t_01']}</div>
							<div class="h_column t_02">${t_column[config.language]['t_02']}</div>
							<div class="h_column t_03">${t_column[config.language]['t_03']}</div>
							<div class="h_column t_04">${t_column[config.language]['t_04']}</div>
							<div class="t_05">${t_column[config.language]['t_05']}</div>
							<div class="t_06">${t_column[config.language]['t_06']}</div>
						</li>
					`);

					d.data.forEach(row => {
						$(listId).append(`
							<li>
								<div>${row.update_date}</div>
								<div class="h_column">${row.order_code}</div>
								<div class="h_column">${row.mileage_type}</div>
								<div class="h_column">${row.price_total}</div>
								<div>${row.mileage_inc}</div>
								<div>${row.mileage_dec}</div>
							</li>
						`);
					});

					if (window.is_mobile) {
						$('.h_column').addClass('hidden');

						$(listId).attr('style', 'display: grid; grid-template-columns: repeat(3, 1fr) !important;');
					} else {
						$('.h_column').removeClass('hidden');
					}
				} else {
					$(listId).append(`
						<div class="list__none">
							${txt_none[config.language][listId]}
						</div>
					`);

					$(listId).css({
						display: 'grid',
						'grid-template-columns': '1fr'
					});
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
				)
			}

			if('page' in d) {
				paging({
					total : d.total,
					el : $(`${listId}-paging`),
					page : d.page,
					rows : data.rows,
					show_paging : 10,
					fn : function(page) {
						data.page = page
						getMileage_list(list_type);
					}
				});
			}
		}
	});
}