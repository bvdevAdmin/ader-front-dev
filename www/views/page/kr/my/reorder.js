$(document).ready(function() {
	[['list-1','apply'],['list-2','alarm']].forEach(list_type => {
		$.ajax({
			url : config.api + "reorder/get",
			data : {
				country : config.language,
				list_type : list_type[1],
				rows : 100,
				page : 1
			},
			success : function(d) {
				if(d.code == 200) {
					if(d.total > 0) {
						d.data.forEach(row => {
							$(`#${list_type[0]}`).append(`
								<li>
									<div class="thumbnail" style="background-image:url('${config.cdn + row.img_location}')"></div>
									<div class="name">${row.product_name}</div>
									<div class="price">${number_format(row.sales_price_kr)}</div>
									<div class="color">${row.color}<span class="colorchip" style="background-color:${row.color_rgb}"></span></div>
									<div class="size">${row.option_name}</div>
									<ul class="info">
										<li class="status">신청 완료</li>
										<li class="date">${row.update_date}</li>
										<li class="buttons"><button type="button" class="cancel">신청 취소</button></li>
									</ul>
								</li>
							`);
						});
					}
				}
			}
		});
	});
});