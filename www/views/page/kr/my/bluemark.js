$(document).ready(function() {
	$.ajax({
		url : config.api + "bluemark/list/get",
		data : {
			rows : 10,
			page : 1
		},
		success : function(d) {
			d.data.forEach(row => {
				$("#list").append(`
					<li data-no="${row.bluemark_idx}">
						<div class="info">
							<div class="image" style="background-image:url('${config.cdn + row.img_location}')"></div>
							<div class="goods">
								<big>${row.product_name}</big>
								<div class="price">${number_format(row.sales_price)}</div>
								<div class="color">${row.color}<span class="colorchip" style="background-color:${row.color_rgb}"></span></div>
								<div class="size">${row.option_name}</div>
							</div>
							<div class="bluemark">
								<dl>
									<dt>구매처</dt>
									<dd>${row.purchase_mall}</dd>
									<dt>Bluemark 시리얼코드</dt>
									<dd>${row.serial_code}</dd>
									<dt>Bluemark 인증 날짜</dt>
									<dd>${row.reg_date}</dd>
								</dl>
							</div>
						</div>
						<div class="buttons grid-2">
							<button type="button" class="transfer">제품 양도하기</button>
							<button type="button" class="cancel">인증 취소</button>
						</div>
					</li>			
				`);
			});
			
			/** 제품 양도 **/
			$("#list button.transfer").click(function() {
				modal('transfer',{ no : $(this).parent().parent().data("no") });
			});
			
			/** 인증 취소 **/
			$("#list button.cancel").click(function() {
				let no = $(this).parent().parent().data("no");
				confirm({
					title : "블루마크 인증 취소",
					body : `
						<p>취소 후 분실 등으로 인증번호를 잊으신 경우에는 재발급이 불가합니다.</p>
						<p>블루마크 인증을 취소하시겠습니까?</p>
						`,
					ok : no => {
						$.ajax({
							url : config.api + "bluemark/cancel",
							data : { no : no },
							success : function(d2) {
								if(d2.code == 200) {
									
								}
								else {
									alert(d2.msg);
								}
							}
						});
					},
				});
			});
		}
	});
});