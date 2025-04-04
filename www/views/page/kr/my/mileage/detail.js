let t_column = {
    KR : {
		t_01 : "적립일",
        t_02 : "적립유형",
        t_03 : "주문 번호",
        t_04 : "구매금액",
        t_05 : "주문 제품번호",
        t_06 : "제품 이름",
        t_07 : "수량",
        t_08 : "제품 가격",
        t_09 : "적립 예정",
        t_10 : "적립 예정일",
        t_11 : "적립",
        t_12 : "사용",
        t_13 : "잔액"
    },
    EN : {
		t_01 : "Date",
        t_02 : "type",
        t_03 : "Order number",
        t_04: "Purchase amount",
        t_05: "Product number",
        t_06 : "Product name",
        t_07 : "Qty",
        t_08: "Price",
        t_09 : "Accumulated",
        t_10 : "Scheduled",
        t_11: "Increase",
        t_12 : "Use",
        t_13 : "balance"    
    }
}

let txt_none = {
	KR : "조회 가능한 적립금 내역이 존재하지 않습니다.",
	EN : "There is no mileage history.",
}

let data = {
	rows: 10,
	page: 1
}

$(document).ready(function() {
	getMileage_log();
});

function getMileage_log() {
	$.ajax({
		url: config.api + "member/mileage/log",
		headers : {
			country : config.language
		},
		data: {
			...data
		},
		success: function (d) {
			if (d.code == 200) {
				$('#list-log').empty();

				$('#list-log').append(`
					<li>
						<div>${t_column[config.language]['t_01']}</div>
						<div class="h_column">${t_column[config.language]['t_02']}</div>
						<div>${t_column[config.language]['t_03']}</div>
						<div class="h_column">${t_column[config.language]['t_04']}</div>
						<div class="h_column">${t_column[config.language]['t_05']}</div>
						<div class="h_column">${t_column[config.language]['t_06']}</div>
						<div class="h_column">${t_column[config.language]['t_07']}</div>
						<div class="h_column">${t_column[config.language]['t_08']}</div>

						<div>${t_column[config.language]['t_09']}</div>
						<div class="h_column">${t_column[config.language]['t_10']}</div>
						<div class="h_column">${t_column[config.language]['t_11']}</div>
						<div>${t_column[config.language]['t_12']}</div>
						<div>${t_column[config.language]['t_13']}</div>
					</li>
				`);

				if (d.data != null && d.data.length > 0) {
					d.data.forEach(row => {
						$('#list-log').append(`
							<li>
								<div>${row.create_date}</div>
								<div class="h_column">${row.mileage_type}</div>
								<div class="code">${row.order_code}</div>
								<div class="price h_column">${row.price_total}</div>
								<div class="code h_column">${row.order_product_code}</div>
								<div class="h_column">${row.product_name}</div>
								<div class="qty h_column">${row.product_qty}</div>
								<div class="price h_column">${row.product_price}</div>

								<div class="price">${row.mileage_unu}</div>
								<div class="h_column">${row.usable_date}</div>
								<div class="price h_column">${row.mileage_inc}</div>
								<div class="price">${row.mileage_dec}</div>
								<div class="price">${row.mileage_bal}</div>
							</li>
						`);
					});

					if (window.is_mobile) {
						$('.h_column').addClass('hidden');

						$('#list-log').attr('style', 'display: grid; grid-template-columns: repeat(5, 1fr) !important;');
					} else {
						$('.h_column').removeClass('hidden');
					}
				} else {
					$('#list-log').append(`
						<div class="list__none">
							조회 가능한 적립금 내역이 존재하지 않습니다.
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

			if ('page' in d) {
				paging({
					total : d.total,
					el : $('#list-log-paging'),
					page : d.page,
					rows : data.rows,
					show_paging : 10,
					fn : function(page) {
						data.page = page
						getMileage_log();
					}
				});
			}
		}
	});
}