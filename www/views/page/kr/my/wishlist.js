$(document).ready(function() {
	let swiper = [];
	
	$.ajax({
		url : config.api + "wishlist/get",
		success : function(d) {
			if(d.code == 200) {
				if(d.data) {
					d.data.forEach(row => {
						
						// 색상
						let color = [];
						row.product_color.forEach(row2 => {
							color.push(`<span class="colorchip" style="background-color:${row2.color_rgb}"></span>`);
						});
						
						// 사이즈
						let size = [];
						row.product_size.forEach(row2 => {
							size.push(`<li>${row2.option_name}</li>`);
						});
						
						$(`#swiper-wishlist > .swiper-wrapper`).append(`
							<div class="swiper-slide">
								<a href="/shop/${row.product_idx}">
									<span 
										class="image" 
										style="background-image:url('${config.cdn + row.product_img}')"
									></span>
								</a>
								<div class="info">
									<big>${row.product_name}</big>
									<div class="price">${number_format(row.price)}</div>
									<div class="color">${row.product_color[0].color}${color.join("")}</div>
									<ul class="size">${size.join("")}</ul>
								</div>
								<button type="button" class="favorite ${(row.whish_flg)?"on":""}" data-goods_no="${row.product_idx}"></button>
							</div>
						`);
					});
					swiper.push(new Swiper("#swiper-wishlist", {
						slidesPerView: 'auto',
						spaceBetween: 0,
						loop: false,
						navigation: {
							nextEl: "#swiper-wishlist .swiper-button-next",
							prevEl: "#swiper-wishlist .swiper-button-prev",
						},
					}));
				}
			}
			else {
				alert(d.msg);
			}
		}
	});
});