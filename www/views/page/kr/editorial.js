$(document).ready(function() {
	let size_type = 'W'
		,swiper_cont = null;
	$.ajax({
		url: config.api + "editorial/get",
		data: {
			size_type: size_type
		},
		error: function() {
			makeMsgNoti("MSG_F_ERR_0101", null);
		},
		success: function(d) {
			// 프로젝트 목록
			//d.data.reverse();
			d.data.forEach(row => {
				let list_img;
				
				if(row.contents_location.indexOf(".mp4") > 0) {
					list_img = `<video muted autoplay loop src="${config.cdn + row.contents_location}"></video>`;
				}
				else {
					list_img = `<img src="${config.cdn + row.contents_location}">`;
				}
				
                let item = `
                    <div class="item" data-no="${row.page_idx}">                        
                        <div class="cont">${list_img}</div>
                        <div class="title">${row.page_title}</div>
                    </div>
                `;
				$("#list-mobile").append(`<li>${item}</li>`);
                if($("#list > li").length > 0 && $("#list > li").last().find(".item").length == 1) {
                    $("#list > li").last().append(item);
                }
                else {
                    $("#list").append(`
                        <li class="swiper-slide">${item}</li>
                    `);
                }
			});
			$("#list").append($("#list > li").clone());
			new Swiper($("#list").parent().get(0),{
				slidesPerView : 'auto',
				centeredSlides : true,
				mousewheel : true,
				loop : true
			});
			
			// 프로젝트 선택
			$(document).on('click',"#list .item,#list-mobile .item",function() {
				$("#swiper-editorial > .swiper-wrapper").empty();
				$.ajax({
					url: config.api + "editorial/get",
					data: { 
						page_idx : $(this).data("no"),
						size_type : size_type
					},
					error: function () {
						// alert('컬렉션 상품 개별 조회처리중 오류가 발생했습니다.');
						makeMsgNoti(country, "MSG_F_ERR_0102", null);
					},
					success: function (d2) {
						$("main").addClass("detail");
						
						// 컨텐츠
						d2.data.editorial_info.forEach(row => {
							let cont;
							if(row.contents_info[0]) {
								switch(row.contents_info[0].contents_type) {
									case "IMG": // 이미지
										cont = `<div class="image" style="background-image : url('${config.cdn + row.contents_info[0].contents_url}')"><img src="${config.cdn + row.contents_info[0].contents_url}"></div>`;
									break;
									
									case "VID": // 영상
										cont = `
											<video>
												<source src="${config.cdn + row.contents_info[0].contents_url}">
											</video>
										`;
									break;
								}
							}
							$("#swiper-editorial > .swiper-wrapper").append(`
								<div class="swiper-slide">
									<div class="cont">${cont}</div>
								</div>
							`);
						});
						if(swiper_cont == null) {
							swiper_cont = new Swiper($("#swiper-editorial").get(0),{
								loop : true,
								scrollbar: {
									el: $("#swiper-editorial .swiper-scrollbar").get(0),
									hide: false,
								},
								pagination: {
									el: $("#swiper-editorial .swiper-pagination").get(0),
								},
								navigation: {
									nextEl: $("#swiper-editorial .swiper-button-next").get(0),
									prevEl: $("#swiper-editorial .swiper-button-prev").get(0)
								},
							});
						}
						else {
							swiper_cont.update();
						}
					}
				});
			});
		}
	});
});
