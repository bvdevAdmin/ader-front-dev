let is_phased = true
	,swiper = [];
//localStorage.setItem("page",get_query_string("page_idx"));
localStorage.setItem("page",0);

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
			
			// swipe 초기화
			swiper.forEach(row => {
				row.enable();
			});			
		}
		else {
			$("ul#list").parent().removeClass("outfit");
			
			// swipe 비활성화
			if(typeof swiper == 'object' && swiper.length > 0) {
				swiper.forEach(row => {
					row.disabled();
				});
			}
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

let last_idx = 0
	,page = localStorage.getItem("page")
	,data = {
		menu_idx: get_query_string("menu_idx"),
		menu_type: get_query_string("menu_type"),
		page_idx: get_query_string("page_idx"),
	};
$(window).scroll(function() {
	if(is_phased == false) return;
	if($(this).scrollTop() + $(this).height() < $("main").height() - $("body > header").height() - ($("body > footer").height() * 2)) return;
	is_phased = false; // 중복 호출 방지

	if(last_idx > 0) {
		data.last_idx = last_idx;
	}
	$.ajax({
		url: config.api + 'goods/get',
		data: data,
		success: function(d) {
			if(d.code == 200) {

				// 상품 분류
				if($("#goods-category > .swiper-wrapper > .swiper-slide").length == 0) {
					d.data.menu_info.forEach(row => {
						$("#goods-category > .swiper-wrapper").append(`
							<li class="swiper-slide">
								<a href="${link_anchor(row.menu_link)}" class="${(row.selected)?'on':''}">
									<span class="image" style="background-image:url('${config.cdn + row.img_location}');"></span>
									<span class="title">${row.menu_title}</span>
								</a>
							</li>
						`);
						
						if(row.selected && row.menu_location) { // 네비 경로에 표시
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
				if(d.data.grid_info && d.data.grid_info.length > 0) {
					data.page_idx++;
				}

				d.data.grid_info.forEach(row => {
                    // 이미지 슬라이드
                    let swiper_container_o = '',swiper_slides_o = []
                        , swiper_container_p = '',swiper_slides_p = []
						, outfit_image = ''
                    if(row.product_img) {

						if(row.product_img.product_o_img.length > 0) {
							row.product_img.product_o_img.forEach(img => {
								swiper_slides_o.push(`<div class="swiper-slide"><div class="image-cont"><img src="${config.cdn + img.img_location}" loading="lazy"></div></div>`);
							});
							swiper_container_o = `
								<div class="swiper-container outfit">
									<div class="swiper-wrapper">
									${swiper_slides_o.join("")}
									</div>
								</div>
							`;
						}
						
						if(row.product_img.product_p_img.length > 0) {
							row.product_img.product_p_img.forEach(img => {
								swiper_slides_p.push(`<div class="swiper-slide"><div class="image-cont"><img src="${config.cdn + img.img_location}" loading="lazy"></div></div>`);
							});
							swiper_container_p = `
								<div class="swiper-container product">
									<div class="swiper-wrapper">
									${swiper_slides_p.join("")}
									</div>
								</div>
							`;
						}
						// 아이템 <-> 착용샷
						outfit_image = `style="--outfit-src: url('${config.cdn + row.product_img.product_p_img[0].img_location}'); "`;
						if(row.product_img.product_o_img.length > 0) { // 착용샷이 있을 경우 대표 이미지 가져옴
							outfit_image = `style="--outfit-src: url('${config.cdn + row.product_img.product_o_img[0].img_location}'); "`;
						}

					}
					
					
					// 사이즈
					let size = '';
					if(row.product_size) {
						row.product_size.forEach(row2 => {
							//if(row2.stock_status=='STSO' || 'option_name' in row2 == false) return;
							if('option_name' in row2 == false) return;
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
					}
					
					// 색상
					let color = '';
					if(row.product_color) {
						row.product_color.forEach(row2 => {
							//if(row2.stock_status=='STSO') return;
							if(row2.color == null) return;
							color += `
								<li data-no="${row2.product_idx}" class="${(row2.stock_status=='STSO')?'soldout':''}">
									<span class="name">${row2.color}</span>
									<span class="colorchip ${(row2.color_rgb=='#ffffff')?'white':''}" style="background-color:${row2.color_rgb}"></span>
								</li>
							`;
						});
					}
					
					last_idx = row.product_idx;
					$("#list").append(`
						<li class="${(row.stock_status == 'STSO')?'soldout':''}" style="${(row.background_color!='')?'background-color:' + row.background_color:''}">
							<a href="${config.base_url}/shop/${row.product_idx}">
								<span 
									class="image" 
									data-src="${(row.product_img) ? config.cdn + row.product_img.product_p_img[0].img_location : ''}"
									${outfit_image}
								>${swiper_container_o}${swiper_container_p}</span>
							</a>
							<div class="info">
								<strong>${row.product_name}</strong>
								<span class="price"><span class="cont">${number_format(row.price)}</span></span>
								<span class="color"><ul>${color}</ul></span>
								<span class="size"><ul>${size}</ul></span>
							</div>
							<button type="button" class="favorite ${(row.whish_flg)?'on':''}" data-goods_no="${row.product_idx}"></button>
						</li>
					`);

                    // inview swipe
					let swiper_option = {
						speed: 400,
						spaceBetween: 0,
						loop : true,
					};
					swiper.push(new Swiper($("#list > li").last().find(".swiper-container.product").get(0), swiper_option));
					swiper.push(new Swiper($("#list > li").last().find(".swiper-container.outfit").get(0), swiper_option));

					if($("ul#list").parent().hasClass("col-2")) {
                        let el_top = $("#list > li").last().offset().top,
                            el_bottom = $("#list > li").last().height+el_top;
                        if($(window).scrollTop > el_top || $(window).scrollTop < el_bottom) {
                            // swiper 초기화
                        }
                    }
				});
				$('.image').lazy({
					effect: "fadeIn",
					effectTime: 400,
					threshold: 0
				});
				$(window).scroll(); // 퀵메뉴 재정렬 
					
				// 필터
				if(d.data.filter_info && $("#shoplist-tools-filter .grid > ul > li").length == 1) {
					let filter_grid_eq = {
						filter_cl : 1, // 색상
						filter_ft : 2, // 핏
						filter_gp : 3, // 그래픽
						filter_ln : 4, // 라인
						filter_sz : 5, // 사이즈
					}, filter_title = {
						filter_cl : '색상', // 색상
						filter_ft : '핏', // 핏
						filter_gp : '그래픽', // 그래픽
						filter_ln : '라인', // 라인
						filter_sz : '사이즈', // 사이즈
						filter_sz_ac : 'ACC',
						filter_sz_ht : '모자',
						filter_sz_jw : '주얼리',
						filter_sz_lw : '하의',
						filter_sz_sh : '신발',
						filter_sz_ta : '테크 악세서리',
						filter_sz_up : '상의',
					}, filter = ''
					, filter2 = ''
					, filter_sz_eq = 0;
					for(let key in d.data.filter_info) {
						if(d.data.filter_info[key].length == 0) continue;
						filter = '';
						filter2 = '';
						
						switch(key) {
							case 'filter_cl': // 색상
								filter = '<ul data-filter="color">';
								d.data.filter_info[key].forEach(row => {
									filter += `<li data-no="${row.filter_idx}">${row.filter_name}<span class="colorchip" style="background-color:${row.rgb_color}"></span></li>`;
								});
								filter += '</ul>';
							break;
							case 'filter_ft': // 핏
								filter = '<ul data-filter="fit">';
								d.data.filter_info[key].forEach(row => {
									filter += `<li>${row.fit}</li>`;
								});
								filter += '</ul>';
							break;
							case 'filter_gp': // 그래픽
								filter = '<ul data-filter="graphic">';
								d.data.filter_info[key].forEach(row => {
									filter += `<li>${row.graphic}</li>`;
								});
								filter += '</ul>';
							break;
							case 'filter_ln': // 라인
								filter = '<ul data-filter="line">';
								d.data.filter_info[key].forEach(row => {
									filter += `<li data-no="${row.line_idx}">${row.line_name}</li>`;
								});
								filter += '</ul>';
							break;
							case 'filter_sz': // 사이즈
								for(let key2 in d.data.filter_info[key][0]) {
									if(d.data.filter_info[key][0][key2].length == 0) continue;
									
									filter2 += `
										<div class="item">
											<h3>${filter_title[key2]}</h3>
											<ul data-filter="${key2}">
									`;
									d.data.filter_info[key][0][key2].forEach(row => {
										filter2 += `<li data-no="${row.filter_idx}" data-sort="${row.size_sort}">${row.filter_name}</li>`;
									});
									filter2 += `
											</ul>
										</div>
									`;
									filter_sz_eq++;
									if(filter_sz_eq < 3) {
										filter += filter2;
										filter2 = '';
									}
								}
							break;
						}
						$("#shoplist-tools-filter .grid > ul").append(`
							<li>
								<h3>${filter_title[key]}</h3>
								${filter}
							</li>
						`);

						/*
						$("#shoplist-tools-filter .grid > ul > li").eq(filter_grid_eq[key]).append(`
							<div class="item">
								<h3>${filter_title[key]}</h3>
								${filter}
							</div>
						`);
						
						$("#shoplist-tools-filter .grid > ul > li").eq(6).html(`
							<div class="item"><h3></h3>${filter2}</div>
						`);
						*/
					}
					$("#shoplist-tools-filter .grid > ul > li ul > li").click(function() {
						$(this).toggleClass("on");
					});
					$("#shoplist-tools-filter button.reset").off().click(function() {
						$("#shoplist-tools-filter .grid > ul > li ul > li").removeClass("on");
						$("#shoplist-tools-filter .grid > ul > li .sort input").attr("checked",false);
					});
				}
			}
			else {
				//alert(d.msg);
			}
			setTimeout(() => { is_phased = true; },100);
		}
	});
}).scroll();