$(document).on("click","#details button.close",function() {
	$("section.on-detail").removeClass("on-detail");
	$("#details > dt").removeClass("on");
});
$(document).ready(function() {
	let goods_no = location.pathname.split("/")[3]; // 상품 인덱스키

	$.ajax({
		url : config.api + "goods/detail/get",
		data : {
			product_idx: goods_no
		},
		success : function(d) {
			if(d.code == 200) {
				// 갤러리
				d.data.img_main.forEach(row => {
					let img = config.cdn + row.img_location.replace('_org_org','_org');
					// PC
					$("#images").append(`<li data-display="${row.display_num}"><img src="${img}"></li>`);
					// 모바일
					$("#images-swiper > .swiper-wrapper").append(`
						<div class="swiper-slide"><img src="${img}" loading="lazy" /><div class="swiper-lazy-preloader"></div></div>
					`);
				});
				$('#images img').lazy({
					effect: "fadeIn",
					effectTime: 400,
					threshold: 0
				});
				// PC 확대/축소
				$("#images > li").click(function() {
					$(this).toggleClass("zoom");
				});
				// 모바일 스와이프
				new Swiper($("#images-swiper").get(0), {
					loop : true,
					lazy : true,
					scrollbar: {
						el: $("#images-swiper .swiper-pagination").get(0),
						hide: false,
					},
					/*
					pagination: {
						el: $("#images-swiper .swiper-pagination").get(0),
						type: 'progressbar',
					},
					*/
					on : {
						slideChange : (e) => {
							$("#images-paging").html(`${e.realIndex+1}/${e.slides.length}`);
						}
					},
				});
				$("#images-paging").html(`1/${d.data.img_main.length}`);
		
				$("#frm-goods h1").html(d.data.product_name); // 상품명
				$("#frm-goods .price").html(d.data.price); // 판매가
				
				if(d.data.stock_status == 'STSO') { // 품절상품
					$("#buy-buttons button[type='submit']").addClass("soldout");
				}
			
				if(d.data.product_type == 'S') { // 세트상품
					d.data.product_size.forEach((row,idx) => {
						$("#option-set").append(`
							제품 ${idx+1}
							<dl class="setgoods">
								<dt>옵션을 선택해주세요</dt>
								<dd>
									<ul></ul>
								</dd>
							</dl>
						`);
						row.set_option_info.forEach(option => {
							$("#option-set > dl.setgoods").last().find("ul").append(`
								<li class="${(option.stock_status == 'STSO')?'soldout':''}" data-goods_no="${option.product_idx}" data-option_no="${option.option_idx}">
									<span class="image" style="background-image:url('')"></span>
									<span class="name">${row.product_name}</span>
									<span class="color">${option.color}<span class="colorchip" style="background-color:${option.color_rgb}"></span></span>
								</li>
							`);
						});
						if(row.set_option_info.length > 6) {
							$("#option-set > dl.setgoods").last().children("dd").addClass("over500");
						}
					});
					$("#option-set > dl.setgoods").click(function() {
						$(this).siblings().removeClass("on");
						$(this).toggleClass("on");
					});
					$("#option-set > dl.setgoods > dd > ul > li").click(function() {
						$(this).parent().parent().parent().children("dt").html(`<ul></ul>`);
						$(this).parent().parent().parent().find("dt > ul").append($(this).clone());
						
						// 세트 전부 선택했는지 여부
						if($("#option-set > dl.setgoods > dt > ul").length == d.data.product_size.length) {
							$("#buy-buttons button[type='submit']").addClass("black");
						}
						else {
							$("#buy-buttons button[type='submit']").removeClass("black");
						}
					});
				}
				else {
					// 상품 사이즈
					if(d.data.product_size) {
						$("#option-size").removeClass("hidden");
						d.data.product_size.forEach(row => {
							$("#option-size dl").append(`
								<dd class="${(row.stock_status == 'STSO')?'soldout':''}">
									<label>
										<input type="radio" name="size" value="${row.option_idx}">
										<span>${row.option_name}</span>
									</label>
								</dd>
							`);
						});

						$("#buy-buttons button[type='submit']").addClass("select-option");
						$("#option-size dd.soldout input").click(function(e) {
							if($(this).parent().parent().hasClass("soldout")) {
								e.preventDefault();
							}
							else {
								$("#buy-buttons button[type='submit']").removeClass("select-option");
								$("#buy-buttons button[type='submit']").addClass("reorder");
							}
						});
						$("#option-size dd:not(.soldout) input").click(function() {
							$("#buy-buttons button[type='submit']").removeClass("select-option");
						});

					}

					// 색상
					if(d.data.product_color) {				
						$("#option-color").removeClass("hidden");
						d.data.product_color.forEach(row => {
							$("#option-color ul").append(`
								<li class="${(row.stock_status == 'STSO')?'soldout':''} ${('/shop/' + row.product_idx == location.pathname)?'now':''}">
									<label>
										<input type="radio" name="color" value="${row.color}">
										<span class="name">${row.color}</span>
										<a href="${config.base_url}/shop/${row.product_idx}" class="colorchip"><span class="${row.color.toLowerCase()}" style="background-color:${row.color_rgb}"></span></a>
									</label>
								</li>
							`);
						});
						$("#option-color ul > li.soldout input").prop("disabled",true);
						$(`#option-color ul > li:not(.soldout) a[href='${location.pathname}']`).parent().find("input").prop("checked",true);
					}
				}
				
				/** 상세 **/
				$("#details > dt > button").click(function() {
					$(this).parent().siblings("dd").slideUp("fast");
                    $(this).parent().siblings().removeClass("on");
					$(this).parent().toggleClass("on");
					if($(this).parent().hasClass("on")) {
						$(this).parent().next().slideDown("fast");
					}
					else {
						$(this).parent().next().slideUp("fast");
					}
				});

				// 사이즈가이드
				$("#sizeguide-cont > article").html(`
					<!--<object id="sizeguide-svg" width="100%" height="100%" type="image/svg+xml" data="/images/sizeguide/coat.svg"></object>-->
					<figure id="sizeguide-svg">${d.sizeguide[0].svg_web}</figure>
					<div class="cont">
						<ul class="sizes"></ul>
						<div class="unit"></div>
						<ul class="describe"></ul>
					</div>
				`);

				for(let size in d.sizeguide[0].dimensions) {
					let size_data = d.sizeguide[0].dimensions[size];
					$("#sizeguide-cont > article > .cont > ul.sizes").append(`<li>${size}</li>`);
					$("#sizeguide-cont > article > .cont > ul.sizes > li").last().click(function() {
						$(this).addClass("on").siblings().removeClass("on");

						//let svg_obj = $("#sizeguide-svg").get(0).contentDocument;
						let svg_obj = $("#sizeguide-svg");
						$("#sizeguide-cont > article > .cont > ul.describe").empty();

						for(let i=0;i<size_data.length;i++) {
							// 문자열 너비 구하기
							$("body").append(`<span>${size_data[i].value}</span>`);
							let size_value_width = -((($("body > span").last().width() / 2) / $("#sizeguide-svg > *").width() ) * 100);
							$("body > span").last().remove();
							// 사이즈 표시
							$(svg_obj).find(`#size-${String.fromCharCode(97+i)}`).text(size_data[i].value).attr("x",size_value_width + "%");

							// 사이즈 설명
							$("#sizeguide-cont > article > .cont > ul.describe").append(`
								<li>
									<dl>
										<dt>${String.fromCharCode(65+i)}</dt>
										<dd>
											<big>${size_data[i].title}</big>
											<p>${size_data[i].desc}</p>
											<div class="value">${size_data[i].value}</div>
										</dd>
									</dl>
								</li>
							`);
						}
					});
				}

				$("#btn-sizeguide").click(function() {
					$("#sizeguide-cont").addClass("on");
					$("#sizeguide-cont > article > .cont > ul.sizes > li").eq(0).click();
				});
				$("#sizeguide-cont > header > button.close").click(function() {
					$("#sizeguide-cont").removeClass("on");
				});
				
				// 소재
				$("#details > dt.material + dd .body").html(decodeHTMLEntities(d.data.material));

				// 상세 정보
				$("#details > dt.info + dd .body").html(decodeHTMLEntities(d.data.detail));

				// 취급 유의사항
				$("#details > dt.warning + dd .body").html(decodeHTMLEntities(d.data.care));

				// 어울리는 상품
				if(d.data.relevant_idx) {
					$.ajax({
						url : config.api + "goods/relevant",
						data : {
							relevant_idx: d.data.relevant_idx
						},
						success : function(d2) {
							if(d2.code == 200 && d2.data && d2.data.length > 0) {
								d2.data.forEach(row => {
									let size = '';
									if(row.product_size) {
										row.product_size.forEach(row2 => {
											size += `
												<li
													data-no="${row2.option_idx}" 
													data-goods_no="${row2.product_idx}" 
													data-color="${row2.color}" 
													data-size_type="${row2.size_type}" 
													data-stock_status="${row2.stock_status}" 
												>${row2.option_name}</li>`;
										});
									}
									$("#view-relation .swiper-wrapper").append(`
										<div class="swiper-slide goods-list">
											<a href="" class="image" style="background-image:url('${config.cdn + row.product_img}')"></a>
											<div class="info">
												<span class="title">${row.product_name}</span>
												<span class="color">${row.color}</span>
												<ul class="size">${size}</ul>
											</div>
											<button type="button" class="favorite" data-goods_no="${row.product_idx}"></button>
										</div>
									`);
								});
								$("#view-relation").removeClass("hidden");
								let swiper_relation = new Swiper($("#view-relation .swiper-container").get(0),{
									slidesPerView : 'auto',
									spaceBetween : 0,
									loop: false,
									loopFillGroupWithBlank: true,
									effect: "slide",
									navigation: {
										nextEl: $("#view-relation .swiper-button-next").get(0),
										prevEl: $("#view-relation .swiper-button-prev").get(0)
									}
								});
							}
						}
					});
				}
				
				// 추천
				if(d.data.relevant_idx) {
					$.ajax({
						url : config.api + "goods/relevant",
						data : {
							relevant_idx: d.data.relevant_idx
						},
						success : function(d2) {
							if(d2.code == 200 && d2.data && d2.data.length > 0) {
								d2.data.forEach(row => {
									let size = '';
									if(row.product_size) {
										row.product_size.forEach(row2 => {
											size += `
												<li
													data-no="${row2.option_idx}" 
													data-goods_no="${row2.product_idx}" 
													data-color="${row2.color}" 
													data-size_type="${row2.size_type}" 
													data-stock_status="${row2.stock_status}" 
												>${row2.option_name}</li>`;
										});
									}
									$("#view-recommend .swiper-wrapper").append(`
										<div class="swiper-slide goods-list">
											<a href="" class="image" style="background-image:url('${config.cdn + row.product_img}')"></a>
											<div class="info">
												<span class="title">${row.product_name}</span>
												<span class="color">${row.color}</span>
												<ul class="size">${size}</ul>
											</div>
											<button type="button" class="favorite" data-goods_no="${row.product_idx}"></button>
										</div>
									`);
								});
								$("#view-recommend").removeClass("hidden");
								let swiper_relation = new Swiper($("#view-recommend .swiper-container").get(0),{
									slidesPerView : 'auto',
									spaceBetween : 0,
									loop: false,
									loopFillGroupWithBlank: true,
									effect: "slide",
									navigation: {
										nextEl: $("#view-recommend .swiper-button-next").get(0),
										prevEl: $("#view-recommend .swiper-button-prev").get(0)
									}
								});
							}
						}
					});
				}

							
				/** 스크롤에 따라 주문 버튼 고정 **/
				$(window).scroll(function() {
					if($("aside#quick").height() == 40) { // 모바일일 경우에만
						// 퀵메뉴 고정
						let quick_top_offset = $("body > footer").offset().top;
						if($(this).scrollTop() + $(this).height() > quick_top_offset) {
							if($("aside#quick").height() > 40) {
								quick_top_offset -= 200;
							}
							else quick_top_offset -= 40;
							$("#buy-buttons").addClass("bottom").css({top:quick_top_offset});
						}
						else {
							$("#buy-buttons").removeClass("bottom").removeAttr("style");
						}
					}

					// 갤러리 페이징
					$("#images > li").each(function() {
						$(this).find("img").load(function() {
							$(this).parent().addClass("complete");
							//$(window).scroll();
						});
						if($(this).hasClass("complete") == false) return;
						let el_top = $(this).offset().top;
						if($(window).scrollTop() + ($(window).height() / 2) > el_top && $(window).scrollTop() + ( $(window).height() * 1.5) > el_top + $(this).height()) {
							$("#images-paging").html(`${$(this).index() + 1}/${d.data.img_main.length}`);

							// 마지막 이미지일 경우 페이징 마지막 이미지 상단에 고정
							if($(this).index() + 1 == d.data.img_main.length) {
								$("#images-paging").addClass("fixed");
							}
							else {
								$("#images-paging").removeClass("fixed");
							}
						}    
					});
					
					// 우측 상품 정보 플로팅
					if($("main.goods > section.cont").height() > $(window).height()) {
						if($("main.goods > section.cont").height() - $(window).height() >= $(window).scrollTop()) {
							$("main.goods > section.cont > section.information").removeClass("bottom");
						}
						else {
							$("main.goods > section.cont > section.information").addClass("bottom");
						}
					}
				}).scroll();
				/*
				$(window).resize(function() {
					$(this).scroll();
				});
				*/

				/** 쇼핑백에 담기 **/
				$("#frm-goods").submit(function() {
					if($("#buy-buttons button[type='submit']").hasClass("reorder")) { // 재입고 알림 신청
						
						
						return false;
					}
					else if($("#buy-buttons button[type='submit']").hasClass("soldout")) {
						return false;
					}
					else if($("#buy-buttons button[type='submit']").hasClass("select-option")) {
						return false;
					}
					
					let option_info = [];

					switch(d.data.product_type) {
						case "B":
							if($("#frm-goods input[name='size']").length > 0) {
								if($("#frm-goods input[name='size']:checked").length > 0) {
									option_info.push($("#frm-goods input[name='size']:checked").val()); // 사이즈 옵션 인덱스키 담음
								}
								else { // 선택한 사이즈가 없을 경우
									
								}
							}
							else {
							}
						break;
						
						case "S": // 세트 상품
							let selected = $("#frm-goods select[name='set_goods'] > option:selected");
							option_info.push({
								product_idx : selected.val(),
								option_idx : selected.data("option_no")
							});
						break;
					}

					$.ajax({
						url: config.api + "cart/put",
						data: {
							add_type : 'product',
							product_type : d.data.product_type,
							product_idx : d.data.product_idx,
							option_info : option_info
						},
						error: function () {
							alert_noti("MSG_F_ERR_0023", null);
						},
						success: function (d) {
							if (d.code == 200) {
								get_cart(true, true); // 장바구니 갱신
							} 
							else {								
								switch(d.code) {
									case 401:
										$("#tnb a[data-side='my']").click(); // 로그인 창 표시
									break;
								}
								if(d.msg) {
									alert(d.msg);
								}
								/*
								if(d.msg != null && d.msg.trim() != ''){
									alert_noti(d.msg, null, () => {
										switch(d.code) {
											case 401:
												$("#tnb a[data-side='my']").click(); // 로그인 창 표시
											break;
										}
									});
								}
								else{
									switch(d.code) {
										case 401:
											$("#tnb a[data-side='my']").click(); // 로그인 창 표시
										break;
									}
								}
									*/
							}
						}
					});
					
					return false;
				});
				
				
			}
			else {
				alert(d.msg);
			}
		}
	});
});