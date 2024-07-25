// 
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
}).scroll();


$.ajax({
	url : config.api + "product/get",
	data : {
		product_idx: location.pathname.split("/")[2],
		country: config.language
	},
	success : function(d) {
		if(d.code == 200) {
			
			// 갤러리
			d.data.img_main.forEach(row => {
				$("#images").append(`<li data-display="${row.display_num}"><img src="${config.cdn + row.img_location.replace('_org_org','_org')}"></li>`);
			});
			$('#images img').lazy({
				effect: "fadeIn",
				effectTime: 400,
				threshold: 0
			});

			d.data.img_thumbnail.forEach(row => {
				if('img_location' in row == false) return;
				
				let title = '착용이미지';
				if($("#thumbnails > li").length > 0) title = '디테일';
				$("#thumbnails").append(`<li data-display="${row.display_num}"><img src="${config.cdn + row.img_location}">${title}</li>`);
			});				
			$("#thumbnails > li").click(function() {
				let top = $(`#images > li[data-display='${$(this).data("display")}']`).offset().top;
				top -= $("body > header").height();
				$("body,html").stop().animate({scrollTop: top + 'px'}, 750,'easeInOutQuad');
			});
			
			$("#frm-goods h1").html(d.data.product_name); // 상품명
			$("#frm-goods .price").html(d.data.price); // 판매가
			
			// 상품 사이즈
			if(d.data.product_size) {
				d.data.product_size.forEach(row => {
					$("#option-size dl").append(`
						<dd>
							<label>
								<input type="radio" name="size" value="${row.option_idx}">
								<span>${row.option_name}</span>
							</label>
						</dd>
					`);
				});
			}
			
			// 색상
			if(d.data.product_color) {
				d.data.product_color.forEach(row => {
					$("#option-color ul").append(`
						<li class="${(row.stock_status == 'STSO')?'soldout':''}">
							<label>
								<input type="radio" name="color" value="${row.color}">
								<span class="name">${row.color}</span>
								<span class="colorchip"><span style="background-color:${row.color_rgb}"></span></span>
							</label>
						</li>
					`);
				});
				$("#option-color ul > li.soldout input").prop("disabled",true);
				$("#option-color ul > li:not(.soldout) input").prop("checked",true);
			}
			
			/** 상세 **/
			$("#details > dt button").click(function() {
				$(this).parent().siblings().removeClass("on");
				if($(this).parent().hasClass("on")) {
					$(this).parent().removeClass("on");
					$(this).parent().parent().parent().parent().removeClass("on-detail");
				}
				else {
					$(this).parent().addClass("on");
					$(this).parent().parent().parent().parent().addClass("on-detail");
				}
			});
			$(document).on("click","#details button.close",function() {
				$("section.on-detail").removeClass("on-detail");
				$("#details > dt").removeClass("on");
			});

			// 사이즈가이드
			
			
			// 소재
			$("#details > dt.material + dd .body").html(d.data.material);
			
			// 상세 정보
			$("#details > dt.info + dd .body").html(d.data.detail);
			
			// 취급 유의사항
			$("#details > dt.warning + dd .body").html(d.data.care);
			
			// 어울리는 상품
			if(d.data.relevant_idx) {
				$.ajax({
					url : config.api + "common/relevant/get",
					data : {
						relevant_idx: d.data.relevant_idx,
						country: config.language
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
										<button type="button" class="favorite"></button>
									</div>
								`);
							});
							$("#view-relation").removeClass("hidden");
							let swiper_relation = new Swiper($("#view-relation .swiper-container").get(0),{
								slidesPerView : 'auto',
								spaceBetween : 0,
								loop: true,
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
			
		}
		else {
			alert(d.msg);
		}
	}
});
