$(document).ready(function() {
	$.ajax({
		url : config.api + 'landing/get',
		data : {
			country : config.language
		},
		success : function(d) {
			if(d.code == 200) {
				// 메인 배너들
				d.data.banner_info.forEach((row,index) => {
					if(index > 3) return;

					// 버튼 #1
					let btn1 = '';
					if(row.btn1_display_flg == 1) {
						btn1 = `<a href="${row.btn1_url}">${decodeHTMLEntities(row.btn1_name)}</a>`;
					}

					// 버튼 #2
					let btn2 = '';
					if(row.btn2_display_flg == 1) {
						btn2 = `<a href="${row.btn2_url}">${decodeHTMLEntities(row.btn2_name)}</a>`;
					}
					
					switch(row.content_type) {
						case 'IMG': // 이미지 형식
							$("#campaign-list > li").eq(index).css({backgroundImage:`url('${config.cdn + row.banner_location}')`});
						break;
						
						case 'MOV': // 동영상
                            if(index == 0) {
                                row.banner_location = 'https://player.vimeo.com/progressive_redirect/playback/830037812/rendition/720p/file.mp4?loc=external&signature=734247ed3604a78f476c63e8e6f06c38520b600c68864d1399c49bb8d08e6675';
                            }
                            if(index == 1) {
                                row.banner_location = 'https://player.vimeo.com/progressive_redirect/playback/815239716/rendition/720p/file.mp4?loc=external&signature=022e794e3330b5e8b576d4848fab85fb145f1991a0f3489dabbdf8cc720cd942';
                            }
							$("#campaign-list > li").eq(index).html(`
								<div class="video"><video src="${row.banner_location}" muted loop autoplay></video></div>
							`);
						break;
					}

                    if(index == 0 && row.background_color.toLowerCase() == 'wh') {
                        $("body > main").addClass("bk-header");
                    }

					$("#campaign-list > li").eq(index)
						.addClass(row.background_color.toLowerCase())
						.append(`
							<div class="title">
								<div class="box">
									<h2>${decodeHTMLEntities(row.title)} ${decodeHTMLEntities(row.sub_title)}</h2>
									${btn1}
									${btn2}
								</div>
							</div>
						`);
				});
				
				// 하단 상품 4개
				d.data.product_info.forEach((row,index) => {
					if(index > 4) return;
					$("#goods-list > li").eq(index).html(`
						<a href="/shop/${row.product_idx}" style="background-image:url('${config.cdn + row.img_location}')">
							<span class="name">${row.product_name}</span>
						</a>
					`);
				});
			}
		}
	});

});