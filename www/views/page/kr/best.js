let is_phased = true;
localStorage.setItem("page",get_query_string("page_idx"));


$(document).on("click","main.goods.list > header section.tools button",function() {
	if($(this).hasClass("on")) {
		$(this).removeClass("on");
	}
	else {
		$("main.goods.list > header section.tools button.on:not(.showing):not(.column)").removeClass("on");
		$(this).addClass("on");
		
	}
	if($(this).hasClass("filter")) { // 필터
		/*
		if($(this).hasClass("on")) { // 2칸 보기
			$("body > header").addClass("on").addClass("fadeout");
		}
		else {
			$("body > header").removeClass("on").removeClass("fadeout");
		}
		*/
	}	
	else if($(this).hasClass("showing")) { // 아이템/착용샷 토글
		if($(this).hasClass("on")) { // 착용샷 보기
			$("ul#list").parent().addClass("outfit");
		}
		else {
			$("ul#list").parent().removeClass("outfit");
		}
	}
	else if($(this).hasClass("column")) { // 2/4칸 보기 토글
		if($(this).hasClass("on")) { // 2칸 보기
			$("ul#list").parent().addClass("col-2");
		}
		else {
			$("ul#list").parent().removeClass("col-2");
		}
	}
});

$("#shoplist-tools-sort input").click(function() {
	$(this).toggleClass("checked");
	if($(this).hasClass("checked") == false) {
		$(this).prop("checked",false);
	}
	$(this).parent().parent().siblings().find("input.checked").removeClass("checked");
});

$(window).scroll(function() {
	if(is_phased == false) return;
	if($(this).scrollTop() + $(this).height() < $("main").height() - $("body > header").height() - ($("body > footer").height() * 2)) return;
	is_phased = false; // 중복 호출 방지

	$(window).scroll(); // 퀵메뉴 재정렬 
	let page = localStorage.getItem("page");
	$.ajax({
		url: config.api + 'goods/get',
		data: {
			menu_idx: get_query_string("menu_idx"),
			menu_type: get_query_string("menu_type"),
			page_idx: page
		},
		success: function(d) {
			if(d.code == 200) {
				localStorage.setItem("page",parseInt(page) + 1);
				// 상품 분류
				if($("#goods-category > .swiper-wrapper > .swiper-slide").length == 0) {
					d.data.menu_info.forEach(row => {
						$("#goods-category > .swiper-wrapper").append(`
							<li class="swiper-slide">
								<a href="${link_anchor((row.menu_link).replace("/product/",""))}" class="${(row.selected)?'on':''}">
									<span class="image" style="background-image:url('${config.cdn + row.img_location}');"></span>
									<span class="title">${row.menu_title}</span>
								</a>
							</li>
						`);

						if(row.selected) { // 네비 경로에 표시
							$("#goods-nav").html(row.menu_location.split(" ").join(" / "));
						}
					});
					let swiper_category = new Swiper("#goods-category",{
						slidesPerView : 'auto',
						loop: false,
						loopFillGroupWithBlank: true,
						effect: "slide",
						navigation: {
							nextEl: $("#goods-category .swiper-button-next").get(0),
							prevEl: $("#goods-category .swiper-button-prev").get(0)
						},
					});
					$("main.goods > header").addClass("on");
				}
				
				// 상품 목록
				d.data.grid_info.forEach(row => {					
					// 사이즈
					let size = '';
					row.product_size.forEach(row2 => {
						if(row2.stock_status=='STSO' || 'option_name' in row2 == false) return;
						size += `
							<li 
								data-no="${row2.product_idx}" 
								data-option_no="${row2.option_idx}" 
								data-type="${row2.size_type}" 
								class="${(row2.stock_status=='STSO')?'soldout':''}">
								<span class="name">${row2.option_name}</span>
							</li>
						`;
					});
					
					// 색상
					let color = '';
					row.product_color.forEach(row2 => {
						if(row2.stock_status=='STSO') return;
						color += `
							<li data-no="${row2.product_idx}" class="${(row2.stock_status=='STSO')?'soldout':''}">
								<span class="name">${row2.color}</span>
								<span class="colorchip ${(row2.color_rgb=='#ffffff')?'white':''}" style="background-color:${row2.color_rgb}"></span>
							</li>
						`;
					});
					
					$("#list").append(`
						<li style="${(row.background_color!='')?'background-color:' + row.background_color:''}">
							<a href="/shop/${row.product_idx}" style="background-image:url('${config.cdn + row.product_img.product_p_img[0].img_location}')"></a>
							<div class="info">
								<button type="button" class="favorite"></button>
								<strong>${row.product_name}</strong>
								<span class="price">${number_format(row.price)}</span>
								<span class="color"><ul>${color}</ul></span>
								<span class="size"><ul>${size}</ul></span>
							</div>
						</li>
					`);
				});
				$('.image').lazy({
					effect: "fadeIn",
					effectTime: 400,
					threshold: 0
				});
				$(window).scroll(); // 퀵메뉴 재정렬 
			}
			else {
				//alert(d.msg);
			}
			setTimeout(() => { is_phased = true; },100);
		}
	});
}).scroll();