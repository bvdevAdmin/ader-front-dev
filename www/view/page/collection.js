$(document).ready(function() {
	let swiper_gallery = null,swiper_recommend = null;
	$.ajax({
		url: config.api + "collection/project",
		error: function() {
			// alert('컬렉션 프로젝트 조회처리중 오류가 발생했습니다.');
			makeMsgNoti(country, "MSG_F_ERR_0101", null);
		},
		success: function(d) {
			// 프로젝트 목록
			d.data.forEach((row, idx) => {
				$("#collection-groups").prepend(`
					<li 
						data-no="${row.project_idx}" 
						data-title="${row.project_title}" 
						data-name="${row.project_name}" 
						data-desc="${row.project_desc}" 
						data-thumb="${row.thumb_location}">${(idx+1).toString().fillZero(2)}</li>
				`);
			});
			
			// 프로젝트 선택
			$("#collection-groups > li").click(function() {
				$(this)
					.addClass("on")
					.siblings().removeClass("on");
				$("#list").empty();
				
				// 타이틀, 설명 표시
				$("#collection-info").html(`
					<h1>${$(this).data("name")}</h1>
					<h2>${$(this).data("title")}</h2>
					<p>${$(this).data("desc")}</p>
				`);
				
				// 이미지 목록 불러오기
				$.ajax({
					url: config.api + "collection/product",
					data: {
						project_idx : $(this).data("no"),
						last_idx : 0,
					},
					error: function () {
						// alert('컬렉션 상품 이미지 조회처리중 오류가 발생했습니다.');
						makeMsgNoti(country, "MSG_F_ERR_0104", null);
					},
					success: function(d2) {
						$("#collection-gallery-slide .swiper-wrapper").empty();
						d2.data.forEach(row => {
							$("#list").append(`
								<li
									data-no="${row.c_product_idx}"><img src="${config.cdn}/${row.img_location}" class="lazy"></li>
							`);
							$("#collection-gallery-slide .swiper-wrapper").append(`
								<div class="swiper-slide" style="background-image:url('${config.cdn}/${row.img_location}')">
									<div class="image"><img src="${config.cdn}/${row.img_location}" class="lazy"></div>
									<div class="zoom hide-scroll"><img src="${config.cdn}/${row.img_location}" class="lazy"></div>
								</div>
							`);
						});
						$('.lazy').lazy({
							effect: "fadeIn",
							effectTime: 400,
							threshold: 0
						});
						if(swiper_gallery == null) {
							swiper_gallery = new Swiper($("#collection-gallery-slide").get(0),{
								navigation: {
									nextEl: $("#collection-gallery-slide .swiper-button-next").get(0),
									prevEl: $("#collection-gallery-slide .swiper-button-prev").get(0)
								},
							});
						}
						else {
							swiper_gallery.update();
						}
						
						// 상세보기
						$("#list > li").click(function() {
							let no = $(this).data("no");
							swiper_gallery.slideTo($(this).index());
							$.ajax({
								url: config.api + "collection/product",
								data: { no : $(this).data("no") },
								error: function () {
									// alert('컬렉션 상품 개별 조회처리중 오류가 발생했습니다.');
									makeMsgNoti(getLanguage(), "MSG_F_ERR_0102", null);
								},
								success: function (d3) {
									$("main").addClass("detail");

									// 관련 상품 표시
									//$("#collection-recommend").removeClass("on");
									$("#swiper-recommend > .swiper-wrapper").empty();
									$.ajax({
										url: config.api + "collection/relevant",
										data: {
											c_product_idx : no
										},
										error: function () {
											// alert('컬렉션 관련상품 조회처리중 오류가 발생했습니다.');
											makeMsgNoti(country, "MSG_F_ERR_0103", null);
										},
										success: function (d4) {
											if(d4.data) {
												$("#collection-recommend").addClass("on");
												d4.data.forEach(row => {
													$("#swiper-recommend > .swiper-wrapper").append(`
														<div class="swiper-slide">
															<a href="/shop/${row.product_idx}">
																<span 
																	class="image" 
																	style="background-image:url('${config.cdn + row.img_location}')"
																></span>
															</a>
															<div class="info">
																<strong>${row.product_name}</strong>
																<span class="price"></span>
																<span class="color"><ul></ul></span>
																<span class="size"><ul></ul></span>
															</div>
															<button type="button" class="favorite"></button>
														</div>
													`);
												});
												if(swiper_recommend == null) {
													swiper_recommend = new Swiper($("#swiper-recommend").get(0),{
														slidesPerView : 'auto'
													});
												}
												else {
													swiper_recommend.update();
												}
											}
										}
									});
								}
							});
						});
					}
				});
			}).eq(0).click();
		}
	});
	
	// 함께 스타일링된 아이템 보기
	$(document).on("click","#btn-view-goods", function(e) {
		$("#collection-recommend").addClass("on");
	});
	
	// 확대기능
	$(document).on("mousemove","#collection-gallery .image", function(e) {
		$("#collection-gallery .zoom")
			.scrollTop((e.pageY- $(window).scrollTop()) * (80/35))
			.scrollLeft(e.pageX * (50/35));
	});
});
