const data = {
    last_index: 0,
    project_loading: true,
    detail_click: true,
}

let swiper_gallery = null, swiper_recommend = null;

let is_phased = true;

// 상세보기 이벤트 설정
function setupDetailViewEvents() {
    $("#list > li").not('.complete').off('click').on('click', function () {
        const productNo = $(this).data("no");
        swiper_gallery.slideTo($(this).index());
        data.detail_click = false;
        
        openDetailView(productNo); // 상세보기 열기
    });
    $("#list > li").not('.complete').addClass("complete");
}

// 상세보기 열기
function openDetailView(productNo) {
    // 상세보기 화면 열기
    $("main").addClass("detail");
    getDetail(productNo); // 상세보기 데이터 가져오기

    // 히스토리 스택에 상태 추가
    history.pushState({ isDetailView: true }, null, location.href);

    // 뒤로가기 이벤트 처리
    window.onpopstate = function (event) {
            closeDetailView(); // 상세보기 닫기
    };
}

// 상세보기 닫기
function closeDetailView() {
    // 상세보기 화면 닫기
    $("main").removeClass("detail");

    // popstate 이벤트 해제
    window.onpopstate = null;
}

// 최초 로드 시 상세보기 이벤트 설정
$(document).ready(function () {
    $.ajax({
        url: config.api + "collection/project",
        headers: {
            country: config.language
        },
        beforeSend: function (xhr) {
            xhr.setRequestHeader("country", config.language);
        },
        error: function () {
            // 에러 처리
        },
        success: function (d) {
            if (d.code != 200) {
                alert(d.msg, function () {
                    location.href = config.base_url;
                });
            } else {
                data.project_loading = true;
                d.data.forEach((row, idx) => {
                    $("#collection-groups").prepend(`
						<li 
							data-no="${row.project_idx}" 
							data-title="${row.project_title}" 
							data-name="${row.project_name}" 
							data-desc="${row.project_desc}" 
							data-thumb="${row.thumb_location}">${(idx + 1).toString().padStart(2, '0')}
                        </li>
					`);
                });

                // 프로젝트 선택
                $("#collection-groups > li").click(function () {
                    $(this).addClass("on").siblings().removeClass("on");
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
                        headers : {
                            country : config.language
                        },
                        data: {
                            project_idx: $(this).data("no"),
                            last_idx: 0,
                        },
                        error: function () {
                            // 에러 처리
                        },
                        success: function (d2) {
                            $("#collection-gallery-slide .swiper-wrapper").empty();
                            data.last_index = d2.last_index;
                            d2.data.forEach(row => {
                                $("#list").append(`
									<li data-no="${row.c_product_idx}">
										<img src="${config.cdn}${row.img_location}" class="lazy">
									</li>
								`);
                                $("#collection-gallery-slide .swiper-wrapper").append(`
									<div class="swiper-slide" style="background-image:url('${config.cdn}${row.img_location}')" data-no="${row.c_product_idx}">
										<div class="image">
											<img src="${config.cdn}${row.img_location}" class="lazy">
										</div>
										<div class="zoom hide-scroll">
											<img src="${config.cdn}${row.img_location}" class="lazy">
										</div>
									</div>
								`);
                            });

                            $('.lazy').lazy({
                                effect: "fadeIn",
                                effectTime: 400,
                                threshold: 0
                            });

                            if (swiper_gallery == null) {
                                swiper_gallery = new Swiper($("#collection-gallery-slide").get(0), {
                                    navigation: {
                                        nextEl: $("#collection-gallery-slide .swiper-button-next").get(0),
                                        prevEl: $("#collection-gallery-slide .swiper-button-prev").get(0)
                                    },
                                    on: {
                                        slideChange: function () {
                                            const activeSlide = $(this.slides[this.activeIndex]);
                                            const productNo = activeSlide.data("no");

                                            if (productNo !== undefined) {
                                                getRelevant(productNo); // 관련 상품 불러오기
                                            }
                                        }
                                    }
                                });
                            } else {
                                swiper_gallery.update();
                            }

                            setupDetailViewEvents(); // 상세보기 이벤트 설정
                        }
                    });
                }).eq(0).click();
                data.project_loading = false;
            }
        }
    });
    // 확대 기능
    $(document).on("mousemove", "#collection-gallery .image", function (e) {
        $("#collection-gallery .zoom")
            .scrollTop((e.pageY - $(window).scrollTop()) * (80 / 21))
            .scrollLeft(e.pageX * (50 / 35));
    });
    
    // 스크롤 이벤트
    $(window).scroll(function () {
        if (data.project_loading || !data.detail_click || !is_phased) return;
        if ($(this).scrollTop() + $(this).height() < $("main").height() - $("body > header").height() - ($("body > footer").height() * 2)) return;
        is_phased = false;

        $.ajax({
            url: config.api + "collection/product",
            headers: {
                country: config.language
            },
            data: {
                project_idx: $('#collection-groups > li.on').data("no"),
                last_idx: data.last_index,
            },
            success: function (d2) {
                data.last_index = d2.last_index;
                d2.data.forEach(row => {
                    $("#list").append(`
						<li data-no="${row.c_product_idx}">
							<img src="${config.cdn}${row.img_location}" class="lazy">
						</li>
					`);
                    $("#collection-gallery-slide .swiper-wrapper").append(`
						<div class="swiper-slide" style="background-image:url('${config.cdn}${row.img_location}')" data-no="${row.c_product_idx}">
							<div class="image">
								<img src="${config.cdn}${row.img_location}" class="lazy">
							</div>
							<div class="zoom hide-scroll">
								<img src="${config.cdn}${row.img_location}" class="lazy">
							</div>
						</div>
					`);
                });
                $('.lazy').lazy({
                    effect: "fadeIn",
                    effectTime: 400,
                    threshold: 0
                });

                setupDetailViewEvents(); // 스크롤 후 상세보기 이벤트 설정

                setTimeout(() => {
                    is_phased = true;
                }, 100);
            }
        });
    });
});

function getDetail(no) {
    $.ajax({
        url: config.api + "collection/product",
        headers: {
            country: config.language
        },
        data: { no },
        error: function () {
            // alert('컬렉션 상품 개별 조회처리중 오류가 발생했습니다.');
            //makeMsgNoti(getLanguage(), "MSG_F_ERR_0102", null);
        },
        success: function (d3) {
            $("main").addClass("detail");
            // 관련 상품 표시
            //$("#collection-recommend").removeClass("on");
            getRelevant(no)
        }
    });

}

function getRelevant(product_idx) {
    $.ajax({
        url: config.api + "collection/relevant",
        headers: {
            country: config.language
        },
        data: {
            c_product_idx: product_idx
        },
        error: function () {
            // alert('컬렉션 관련상품 조회처리중 오류가 발생했습니다.');
            //makeMsgNoti(country, "MSG_F_ERR_0103", null);
        },
        success: function (d) {
            $("#swiper-recommend > .swiper-wrapper").empty();
            
            if (d.data) {
                $("#collection-recommend").addClass("on");
                d.data.forEach(row => {
                    // 사이즈
                    let size = '';
                    if (row.product_type == "B") {
                        if (row.product_size) {
                            row.product_size.forEach(row2 => {
                                //if(row2.stock_status=='STSO' || 'option_name' in row2 == false) return;
                                if ('option_name' in row2 == false) return;
                                size += `
                                    <li 
                                        data-no="${row2.product_idx}" 
                                        data-option_no="${row2.option_idx}" 
                                        data-type="${row2.size_type}" 
                                        class="${(row2.stock_status == 'STSO') ? 'soldout' : ''}">
                                        <span class="name">${row2.option_name}</span>
                                    </li>
                                `;
                            });
                        }
                    } else {
                        size = "<li>Set</li>";
                    }

                    // 색상
                    let color = '';
                    if (row.product_color) {
                        row.product_color.forEach(row2 => {
                            //if(row2.stock_status=='STSO') return;
                            if (row2.color == null) return;
                            color += `
                                <li data-no="${row2.product_idx}" class="${(row2.stock_status == 'STSO') ? 'soldout' : ''}">
                                    <span class="name">${row2.color}</span>
                                    <span class="colorchip ${(row2.color_rgb == '#ffffff') ? 'white' : ''}" style="background-color:${row2.color_rgb}"></span>
                                </li>
                            `;
                        });
                    }

                    $("#swiper-recommend > .swiper-wrapper").append(`
						<div class="swiper-slide">
							<a href="/${config.language.toLowerCase()}/shop/${row.product_idx}">
								<span 
									class="image" 
									style="background-image:url('${config.cdn + row.img_location}')"
								></span>
							</a>
							<div class="info">
								<strong>${row.product_name}</strong>
								<span class="price ${row.discount > 0 ? ' discount' : ''}" data-discount="${row.discount}" data-saleprice="${row.txt_sales_price}">
									<span class="cont">${row.txt_price}</span>
								</span>
								<span class="color"><ul>${color}</ul></span>
								<span class="size"><ul>${size}</ul></span>
							</div>
							<button type="button" class="favorite ${row.whish_flg ? 'on' : ''}" data-goods_no="${row.product_idx}"></button>
						</div>
					`);
                });
            }

            if (swiper_recommend == null) {
                swiper_recommend = new Swiper($("#swiper-recommend").get(0), {
                    slidesPerView: 'auto'
                });
            } else {
                swiper_recommend.update();
            }
        }
    });
}

