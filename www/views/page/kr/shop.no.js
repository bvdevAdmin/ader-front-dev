let product_color = null;

$(document).on("click","#details button.close",function() {
	$("section.on-detail").removeClass("on-detail");
	$("#details > dt").removeClass("on");
});

$(document).ready(function() {
	let goods_no = location.pathname.split("/")[3]; // 상품 인덱스키

	$.ajax({
		url : config.api + "goods/detail/get",
		headers : {
			country : config.language
		},
		data : {
			product_idx: goods_no
		},
		success : function(d) {
			if (d.code == 200) {
				/* ========== 상품 이미지 ========== */
				d.data.img_main.forEach(row => {
					let img = config.cdn + row.img_location.replace('_org_org','_org');
					
					/* 상품 이미지 (웹) */
					$("#images").append(`<li data-display="${row.display_num}"><img src="${img}"></li>`);
					
					/* 상품 이미지 (모바일) */
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

                    if ($(this).hasClass("zoom")) {
                        // 확대 상태에서 히스토리 상태 추가
                        history.pushState({ isZoomed: true }, null, location.href);
                    } else {
                        // 확대 상태가 아닌 경우 히스토리 상태 복원
                        history.back();
                    }
                });

                // 뒤로가기 이벤트 처리
                window.onpopstate = function(event) {
                    $("#images > li.zoom").removeClass("zoom");
                };
				
				// 모바일 스와이프
				new Swiper($("#images-swiper").get(0), {
					loop : true,
					lazy : true,
					scrollbar: {
						el: $("#images-swiper .swiper-pagination").get(0),
						hide: false,
					},
					on : {
						slideChange : (e) => {
							$("#images-paging").html(`${e.realIndex+1}/${e.slides.length}`);
						}
					},
				});
				
				$("#images-paging").html(`1/${d.data.img_main.length}`);
				
				/* 상품명 */
				$("#frm-goods h1").html(d.data.product_name);
				
				/* 상품 가격 */
				$("#frm-goods .price").html(d.data.price);

				if(d.data.discount > 0) {
					$("#frm-goods .price").addClass("discount")
					$("#frm-goods .price").attr("data-discount", d.data.discount)
					$("#frm-goods .price").attr("data-saleprice", d.data.sales_price)
				}
				
				/* 상품 아이디 */
				$("#frm-goods > button.favorite").attr("data-goods_no", d.data.product_idx);
				
				if (d.data.whish_flg) {
					$("#frm-goods button.favorite").addClass('on')
				} else {
					$("#frm-goods button.favorite").removeClass('on')
				}
				
				/* 품절 상태일 경우 버튼 처리 */
				if (d.data.stock_status == 'STSO') {
					$("#buy-buttons button[type='submit']").addClass("soldout");
					$("#buy-buttons button[type='submit']").addClass(config.language);
				}
				
				$("#buy-buttons button[type='submit']").addClass("select-option");
				$("#buy-buttons button[type='submit']").addClass(config.language);

				let t_btn = {
					KR : "쇼핑백에 담기",
					EN : "Add to shopping bag",
				}

				/* 세트 상품 화면 처리 */
				if(d.data.product_type == 'S') { // 세트상품
					d.data.product_size.forEach((row,idx) => {
						let t_column = {
							KR : {
								't_01' : "제품",
								't_02' : "옵션을 선택해주세요"
							},
							EN : {
								't_01' : "Product",
								't_02' : "Please select the option"
							}
						}
						$("#option-set").append(`
							${t_column[config.language]['t_01']} ${idx+1}
							<dl class="setgoods">
								<dt>${t_column[config.language]['t_02']}</dt>
								<dd>
									<ul></ul>
								</dd>
							</dl>
						`);
						
						row.set_option.forEach(option => {
							$("#option-set > dl.setgoods").last().find("ul").append(`
								<li class="${(option.stock_status == 'STSO')?'soldout':''}" data-goods_no="${option.product_idx}" data-option_no="${option.option_idx}">
									<span class="image" style="background-image:url('${config.cdn}${option.img_location}')"></span>
									<span class="name">${option.product_name}</span>
									<span class="color">
										${option.color}
										<span class="colorchip" style="background-color:${option.color_rgb}"></span>
									</span>
									<span class="size">
										${option.option_name}
									</span>
								</li>
							`);
						});
						
						if(row.set_option.length > 6) {
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
						if ($("#option-set > dl.setgoods > dt > ul").length == d.data.product_size.length) {
							if (!$("#buy-buttons button[type='submit']").hasClass('soldout') && !$("#buy-buttons button[type='submit']").hasClass('reorder')) {
								$("#buy-buttons button[type='submit']").removeClass("select-option");
								$("#buy-buttons button[type='submit']").addClass("black");
							}
						} else {
							$("#buy-buttons button[type='submit']").removeClass("black");
						}
					});
				} else {
					/* 일반 상품 화면 처리 */
					if (d.data.product_size) {
						$("#option-size").removeClass("hidden");
						
						d.data.product_size.forEach(row => {
							let stock_status = "";
							if (row.stock_status == "STSO") {
								stock_status = "soldout";
							} else if (row.stock_status == "STSC") {
								stock_status = "reorder";
								$("#buy-buttons button[type='submit']").html("");
							} else {
								$("#buy-buttons button[type='submit']").html(`${t_btn[config.language]}`);
							}
							
							$("#option-size dl").append(`
								<dd class="${stock_status}">
									<label>
										<input type="radio" name="size" value="${row.option_idx}" data-stock_status="${stock_status}">
										<span>${row.option_name}</span>
									</label>
								</dd>
							`);
						});
						
						$("#option-size dd.soldout input").click(function(e) {
							e.preventDefault();
						});

						$("#option-size dd.reorder input").click(function(e) {
							$("#buy-buttons button[type='submit']").removeClass("select-option");
							$("#buy-buttons button[type='submit']").removeClass("black");
							$("#buy-buttons button[type='submit']").addClass("reorder");
							$("#buy-buttons button[type='submit']").addClass(config.language);

							$("#buy-buttons button[type='submit']").html('');
						});
						
						$("#option-size dd:not(.soldout):not(.reorder) input").click(function() {
							$("#buy-buttons button[type='submit']").removeClass("select-option");
							$("#buy-buttons button[type='submit']").addClass("black");
							$("#buy-buttons button[type='submit']").removeClass("reorder");

							$("#buy-buttons button[type='submit']").html(`${t_btn[config.language]}`);
						});
					}

					// 색상
					if (d.data.product_color) {
						$("#option-color").removeClass("hidden");
						
						d.data.product_color.forEach(row => {
							let link_color = `${config.base_url}/shop/${row.product_idx}`;
							let onclick = "";
							let checkedTag = "";

							if (row.product_idx == goods_no) {
								link_color = "#";
								onclick = `onClick="return false"`;
								checkedTag = "checked";
							}

							$("#option-color ul").append(`
								<li class="${(row.stock_status == 'STSO')?'soldout':''} ${('/shop/' + row.product_idx == location.pathname)?'now':''}">
									<label>
										<input type="radio" name="color" value="${row.color}" ${checkedTag}>
										<span class="name">${row.color}</span>
										<a href="${link_color}" class="colorchip" ${onclick}>
											<span class="${row.color.toLowerCase()}" style="background-color:${row.color_rgb}"></span>
										</a>
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
					<figure id="sizeguide-svg">
						${d.sizeguide[0].svg_web}
					</figure>

					<div class="cont">
						<ul class="sizes"></ul>
						<div class="unit"></div>
						<ul class="describe"></ul>
					</div>
				`);
				
				/* 사이즈가이드 SVG 설정 추가 */
				let dimensions = d.sizeguide[0].dimensions;
				for (let size in dimensions) {
					let size_data = dimensions[size];

					$("#sizeguide-cont > article > .cont > ul.sizes").append(`<li data-size="${size}">${size}</li>`);
					$("#sizeguide-cont > article > .cont > ul.sizes > li").last().click(function() {
						$(this).addClass("on").siblings().removeClass("on");

						let key = $(this).data('size');
						console.log(key);

						let dcts = dimensions[key];
						
						for (let i=0; i<dcts.length; i++) {
							let option_size = document.querySelectorAll('.option_size_' + (i+1));
							if (option_size != null) {
								option_size.forEach(size => {
									size.textContent = dcts[i].value;
								});
							}
						}

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

				let keys = Object.keys(dimensions);
				let dcts = dimensions[keys[0]];
				
				for (let i=0; i<dcts.length; i++) {
					let option_size = document.querySelectorAll('.option_size_' + (i+1));
					if (option_size != null) {
						option_size.forEach(size => {
							size.textContent = dcts[i].value;
						});
					}
				}

				$("#btn-sizeguide").click(function() {
					$("#sizeguide-cont").addClass("on");
					window.scrollTo({
						top: 0,
						behavior: "smooth"
					});

					$("#sizeguide-cont > article > .cont > ul.sizes > li").eq(0).click();
				});

				$("#sizeguide-cont > header > button.close").click(function() {
					$("#sizeguide-cont").removeClass("on");
				});
				
				// 소재
				$("#details > dt.material + dd .body").html(d.data.material);

				// 상세 정보
				$("#details > dt.info + dd .body").html(d.data.detail);

				// 취급 유의사항
				$("#details > dt.warning + dd .body").html(d.data.care);

				// 어울리는 상품
				if(d.data.relevant_idx) {
					$.ajax({
						url : config.api + "goods/relevant",
						headers : {
							country : config.language
						},
						data : {
							relevant_idx: d.data.relevant_idx
						},
						success : function(d2) {
							if(d2.code == 200 && d2.data && d2.data.length > 0) {
								d2.data.forEach(row => {
									let size = '';
									if (row.product_type == "B") {
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
									} else {
										size = "<li>Set</li>";
									}
									
									$("#view-relation .swiper-wrapper").append(`
										<div class="swiper-slide goods-list">
											<a href="${config.base_url}/shop/${row.product_idx}" class="image" style="background-image:url('${config.cdn}${row.product_img}')"></a>
											<div class="info">
												<span class="title">${row.product_name}</span>
												<div class="color">${row.color}<span class="colorchip" style="background-color:${row.color_rgb}"></span></div>
												<ul class="size">${size}</ul>
											</div>
											<button type="button" class="favorite ${row.whish_flg?'on':''}" data-goods_no="${row.product_idx}"></button>
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
				
				/*
				$.ajax({
					url : config.api + "goods/recommend",
					headers : {
						country : config.language
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
										<a href="${config.base_url}/shop/${row.product_idx}" class="image" style="background-image:url('${config.cdn + row.product_img}')"></a>
										<div class="info">
											<span class="title">${row.product_name}</span>
											<div class="color">${row.color}<span class="colorchip" style="background-color:${row.color_rgb}"></span></div>
											<ul class="size">${size}</ul>
										</div>
										<button type="button" class="favorite ${row.whish_flg?'on':''}" data-goods_no="${row.product_idx}"></button>
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
				*/
							
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
					if (!window.is_mobile) {
						// 마지막 이미지의 하단 위치를 계산하고 고정
						$(window).on("scroll", function () {
							let lastImage = $("#images > li:last-child"); // 마지막 이미지 선택
							let lastImageTop = lastImage.offset().top + 80; // 마지막 이미지의 상단 위치 +80
							let lastImageHeight = lastImage.height(); // 마지막 이미지의 높이
							let lastImageBottom = lastImageTop + lastImageHeight; // 마지막 이미지의 하단 위치
							let scrollTop = $(window).scrollTop(); // 현재 스크롤 위치
							let windowHeight = $(window).height(); // 창의 높이
					
							// 스크롤이 마지막 이미지의 하단을 넘었을 경우
							if (scrollTop + windowHeight > lastImageBottom) {
								$("#images-paging").addClass("fixed").css({
									top: `${lastImageTop}px`, // 마지막 이미지 상단에 고정
								});
							} else {
								// 스크롤이 마지막 이미지의 하단 이전일 경우
								$("#images-paging").removeClass("fixed").css({
									top: "",
								});
							}
						});
					
						// 갤러리 페이징 처리
						$("#images > li").each(function () {
							$(this).find("img").on("load", function () {
								$(this).parent().addClass("complete");
							});
					
							if ($(this).hasClass("complete") == false) return;
					
							let el_top = $(this).offset().top;
					
							if (
								$(window).scrollTop() + $(window).height() / 2 > el_top &&
								$(window).scrollTop() + $(window).height() * 1.5 > el_top + $(this).height()
							) {
								$("#images-paging").html(`${$(this).index() + 1}/${$("#images > li").length}`);
							}
						});
					}					
					
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

				

				/** 쇼핑백에 담기 **/
				$("#frm-goods").submit(function(e) {
					e.preventDefault();
					
					if ($("#buy-buttons button[type='submit']").hasClass("reorder")) { // 재입고 알림 신청
						if (d.data.reorder_flg == true) {
							let option_idx = null;
							let option_info = [];
							
							switch (d.data.product_type) {
								case "B":
									if ($("#frm-goods input[name='size']:checked").length > 0) {
										option_idx = $("#frm-goods input[name='size']:checked").val();
									}
									
									break;
								
								case "S": // 세트 상품
									let set_goods = document.querySelectorAll('.setgoods dt li');
									if (set_goods != null && set_goods.length > 0) {
										set_goods.forEach(set => {
											option_info.push({
												product_idx	: set.dataset.goods_no,
												option_idx	: set.dataset.option_no
											});
										})
									}
									
									option_idx = 0;
									
									break;
							}
							
							if ((d.data.product_type == "B" && option_idx > 0) || (d.data.product_type == "S" && option_info.length > 0)) {
								$.ajax({
									url: config.api + "reorder/add",
									headers : {
										country : config.language
									},
									data: {
										'product_type'	:d.data.product_type,
										'product_idx'	:d.data.product_idx,
										'option_idx'	:option_idx,
										'option_info'	:option_info
									},
									async:false,
									error: function () {
										alert_noti("MSG_F_ERR_0141", null);
									},
									success: function (d) {
										if (d.code == 200) {
											alert_noti("MSG_F_INF_0002", null);

											$("#buy-buttons button[type='submit']").removeAttr('onClick');
										} else {								
											switch(d.code) {
												case 401:
													$("#tnb a[data-side='my']").click(); // 로그인 창 표시
												break;
											}
											
											if(d.msg) {
												alert(d.msg);

												$("#buy-buttons button[type='submit']").removeAttr('onClick');
											}
										}
									}
								});
							}
						}
					} else if ($("#buy-buttons button[type='submit']").hasClass("soldout")) {
						
					} else if ($("#buy-buttons button[type='submit']").hasClass("select-option")) {
						
					} else {
						let option_idx = 0;
						let option_info = [];

						switch (d.data.product_type) {
							case "B":
								if ($("#frm-goods input[name='size']:checked").length > 0) {
									option_idx = $("#frm-goods input[name='size']:checked").val();
								}
								
								break;
							
							case "S": // 세트 상품
								let set_goods = document.querySelectorAll("#frm-goods .option dt li");
								if (set_goods != null && set_goods.length == d.data.product_size.length) {
									set_goods.forEach(set => {
										option_info.push({
											'product_idx'	:set.dataset.goods_no,
											'option_idx'	:set.dataset.option_no
										});
									});
								}
								
								break;
						}
						
						if (
							(d.data.product_type == "B" && option_idx > 0) || 
							(d.data.product_type == "S" && option_info.length > 0)
						) {
							$.ajax({
								url: config.api + "cart/put",
								headers : {
									country : config.language
								},
								data: {
									'product_type'	:d.data.product_type,
									'product_idx'	:d.data.product_idx,
									'option_idx'	:option_idx,
									'option_info'	:option_info
								},
								async:false,
								error: function () {
									alert_noti("MSG_F_ERR_0023", null);
								},
								success: function (d) {
									if (d.code == 200) {
										get_cart(true, true); // 장바구니 갱신

										$("#buy-buttons button[type='submit']").removeAttr('onClick');

										$("#frm-side-cart input[name='all_check']").click().trigger('change');
									}
									else {								
										switch(d.code) {
											case 401:
												$("#tnb a[data-side='my']").click(); // 로그인 창 표시
											break;
										}

										if(d.msg) {
											alert(d.msg);

											$("#buy-buttons button[type='submit']").removeAttr('onClick');
										}
									}
								}
							});
						}
					}
				});
			} else {
				alert(
					d.msg,
					function() {
						history.back();
					}
				);
			}
		}
	});
});