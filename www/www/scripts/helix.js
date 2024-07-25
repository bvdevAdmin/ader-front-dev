const LANGUAGE_CODE = {
	ko : 'KR',
	cn : 'CN',
	en : 'EN'
};
const config = {
	api : '/_api/',
	cdn : 'https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user',
	script : `/_script`,
	modal : '/_modal/',
	language : LANGUAGE_CODE[$("html").attr("lang")],
    base_url : `/${LANGUAGE_CODE[$("html").attr("lang")].toLowerCase()}`,
	cookie_expire : 90, // 90일동안 쿠키 유지
};

/** ajax 기본 설정 **/
$.ajaxSetup({ 
	type : "post",
	dataType: "json",
	headers : {
		country : config.language
	},
	beforeSend : function() {
	},
	error : function() {
		alert(`
500 서버요청 실패
[ ${this.url} ]
`,function() {
			$(".loading").removeClass();
		});
	},
	complete : function() {
	}
});

$._ajax = $.ajax;
$.ajax = (args) => { // xhr.abort 함수 사용을 위해 ajax 동작 단계 추가
	if(typeof window.xhr == 'undefined') {
		window.xhr = [];
	}
	window.xhr.push($._ajax(args)); // 배열에 ajax 호출 다 담음
};

$(window).resize(function() {
	if($(window).width() <= 1024) {
		window.is_mobile = true;
	}
	else {
		window.is_mobile = false;
	}
}).resize();

/** 위시리스트 버튼, 모든 위시리스트 동작 정의 **/
$(document).on("click","button.favorite",function() {
	// 위시리스트 지정 및 해제
	let obj = $(this);
	
	$.ajax({ 
		url: config.api + 'wishlist/' + (($(this).hasClass("on"))?"delete":"put"),
		data: {
			product_idx : $(this).data("goods_no")
		},
		success: function(d) {
			if(d.code == 200) {
				obj.toggleClass("on");
			}
			else {
				alert(d.msg);
			}
		}
	});
});

/** 탭 동작 정의 **/
$(document).on("click",".tab > .tab-container > ul > li",function() {
	$(this).addClass("on").siblings().removeClass("on");
	
	let index = $(this).index();
	$(this).parent().parent().parent().find("section.on").removeClass("on");
	$(this).parent().parent().parent().find("section").eq(index).addClass("on");
	/*
	// 위시리스트 지정 및 해제
	let obj = $(this);
	$.ajax({ 
		url: config.api + 'favorite/put',
		data: {
			goods_no : $(this).data("goods_no")
		},
		success: function(d) {
			if(d.code != 200) {
				alert(d.msg);
				obj.toggleClass("on");
			}
		}
	});
	*/
});


/** 버튼 동작 **/
/*
$(".buttons button:not(.black):not(.no-over):not(.gray),.btn:not(.black):not(.no-over):not(.gray)").each(function() {
	if($(this).find(".hover").length == 0) {
		let txt = $(this).text();
		$(this).html(`<span class="text">${txt}</span><span class="hover"><span class="text">${txt}</span></span>`);
	}
});
$(document).on("mouseover",".buttons button:not(.black):not(.no-over),.btn:not(.black):not(.no-over)",function() {
	if($(this).find(".hover").length == 0) {
		let txt = $(this).text();
		$(this).html(`<span class="text">${txt}</span><span class="hover"><span class="text">${txt}</span></span>`);
	}
});
*/
/** 버튼 동작 : 비밀번호 보기 **/
$(document).on("click",".form-inline button.pw-view-toggle",function() {
	let inp = $(this).parent().find("input");

	if(inp.attr("type") == 'password') {
		$(this).addClass("view-alphanet");
		inp.attr("type","text");
	}
	else {
		$(this).removeClass("view-alphanet");
		inp.attr("type","password");
	}
});
/** 버튼 동작 : 위로 가기 **/
$(document).on("click","button.to-top",function() {
	$("html,body").stop().animate({scrollTop : 0},"fast");
});
/** 버튼 동작 : 뒤로 가기 **/
$(document).on("click","#btn-mobile-history-back",function() {
	history.back();
});

/** 버튼 동작 : 그리드 갤러리 더보기 **/
$(document).on("click","button.more",function() {
	if($(this).parent().parent().find("ul.gallery-grid").length > 0) {
		if($(this).parent().parent().find("ul.gallery-grid").hasClass("hide-after-8")) {
			$(this).addClass("fold");
			$(this).parent().parent().find("ul.gallery-grid").removeClass("hide-after-8");
		}
		else {
			$(this).removeClass("fold");
			$(this).parent().parent().find("ul.gallery-grid").addClass("hide-after-8");
		}
	}
});

/** 폼 동작 **/
$(document).on("focus",".form-inline input",function() {
	$(this).parent().find(".remark").slideDown("fast");
});
$(document).on("focusout",".form-inline input",function() {
	$(this).parent().find(".remark").slideUp("fast");
});
$(document).on("change keyup focusout",".form-inline .textarea,.form-inline textarea,.form-inline select",function() {
	if($(this).val() != "" || ($(this).hasClass("textarea") && $(this).text() != "")) $(this).addClass("has-value");
	else $(this).removeClass("has-value");
}).change();

/** 목록 동작 **/
$(document).on("click touch","dl.board-list > dt",function() {
	if($(this).hasClass("on") == false) {
		$(this).parent().find("dt.on + dd").slideUp("fast");
		$(this).parent().find("dt.on").removeClass("on");
	}
	
	$(this).toggleClass("on");
	if($(this).hasClass("on")) {
		$(this).next().slideDown("fast");
	}
	else {
		$(this).next().slideUp("fast");
	}
});

/** 컨텐츠 접기 동작 **/
$(document).on("click touch","dl.fold > dt",function() {
	if(is_mobile) { // 모바일에서만
		if($(this).hasClass("close")) { // 이미 접힌 상태
		}
		else {
		}

		$(this).toggleClass("close");
		$(this).nextUntil("dt").slideToggle("fast");
	}
});

/** 달력 **/
$(document).on("click touch",".date-search input[type='date']",function() {
	let _this = $(this);

	$(this).parent().siblings().removeClass("on");
	$(this).parent().toggleClass("on");
	if($(this).parent().hasClass("on")) {
		set_calendar($(this).parent().find(".calendar"),$(this).val(),function(obj, date, type) {
			if(type == 'month') {
			}
			else if(type == 'date') {
				_this.val(date);
				_this.parent().find(".select").text(date);
				_this.parent().removeClass("on");
			}
		});
	}
});


$(document).ready(function() {
	$("body").attr("data-path",location.pathname);
	
	/** 첫번째 탭 선택 **/
	$(".tab > .tab-container > ul > li").first().click();

	/** 언어 설정 **/
	if(typeof $("html").attr("lang") != 'undefined' && $("html").attr("lang") in LANGUAGE_CODE == true) {
		config.language = LANGUAGE_CODE[$("html").attr("lang")];
	}
	
	/** gnb 버튼 **/
	$("button#btn-gnb").click(function() {
		$("body").toggleClass("fold-nav");
		if($("body").hasClass("fold-nav") == false) {
			$("body > header").removeClass("on").removeClass("fadeout");
		}
	});

	
	let cookie = new Cookie();
	if(
		cookie.get("accpet-necessary") == null
		&& cookie.get("accpet-general") == null
		&& cookie.get("accpet-stat") == null
		&& cookie.get("accpet-marketing") == null
		&& cookie.get("accpet-close") == null
	) {
		$("section.cookie-agree").addClass("on");

		/** 쿠키 설정 배너 닫기 **/
		$("section.cookie-agree > .banner button.close").click(function() {
			cookie.set("accept-close","y",1);
			$("section.cookie-agree").remove();
		});
		$("section.cookie-agree > .accept button.close").click(function() {
			$("section.cookie-agree").removeClass("config");
		});
		/** 쿠키 설정 모두 수락 **/
		$("section.cookie-agree button.accept-all").click(function() {
			cookie.set("accpet-necessary","y",config.cookie_expire);
			cookie.set("accpet-general","y",config.cookie_expire);
			cookie.set("accpet-stat","y",config.cookie_expire);
			cookie.set("accpet-marketing","y",config.cookie_expire);
			$("section.cookie-agree").remove();
		});
		/** 쿠키 설정 팝업 **/
		$("section.cookie-agree > .banner button.config").click(function() {
			$("section.cookie-agree").addClass("config");
		});

	}
	else {
		$("section.cookie-agree").remove();
	}

	
	/** 퀵메뉴 **/
	$("#quick-tabs > li").click(function() {
		let api_url = [
				'common/recommend/get',
				'quickview/popular',
				'wishlist/get'
			]
			,empty_str
			,obj;

		switch($(this).index()) {
			// 최근 본 상품
			case 0:
				empty_str = '최근 본 제품이 비어있습니다.';
				obj = $("#quick-recently-list");
			break;
			
			// 실시간 인기 제품
			case 1:
				empty_str = '실시간 인기 제품이 비어있습니다.';
				obj = $("#quick-popular-list");
			break;
			
			// 위시리스트
			case 2:
				empty_str = '위시리스트가 비어있습니다.';
				obj = $("#quick-wishlist-list");
			break;
		}
		
		$.ajax({
			url : config.api + api_url[$(this).index()],
			success : function(d) {
				if(d.code == 200) {
					if(d.data && d.data.length > 0) {
						$(obj).empty();
						d.data.forEach(row => {
							$(obj).append(`
								<li>
									<a href="/shop/${row.product_idx}">
										<span class="image" style="background-image:url('${config.cdn + row.img_location}')"></span>
										${row.product_name}
									</a>
									<button type="button" class="favorite" data-no="${row.product_idx}"></button>
								</li>
							`);
						});
					}
					else {
						$(obj).html(`<li class="empty">${empty_str}</li>`);
					}
				}
				else if(d.code == 401) {
					$("#tnb > dt a[data-side='my']").click(); // 로그인창 호출
				}
			}
		});
	});
	let swiper_quick = [];
	$("body > aside > ul > li > button.quick").click(function() {
		get_qna_category = (no,title) => {
			$("#quick-qna-chat > ul").append(`
				<li class="me"><span class="cont">${title}</span></li>
			`);
			$("#quick-qna-chat").animate({scrollTop:$("#quick-qna-chat > ul > li").last().offset().top},'fast');
			
			setTimeout(() => {
				$.ajax({
					url : config.api + 'quickview/inquiry/list',
					data : { category_idx: no },
					success : function(d) {
						if(d.data && d.data.length > 0) {
							$("#quick-qna-chat > ul").append(`
								<li class="you">
									<p>문의 유형을 선택해 주세요.</p>
									<ul></ul>
								</li>
							`);
							d.data.forEach(row => {
								$("#quick-qna-chat > ul > li").last().find("ul").append(`
									<li data-no="${row.category_idx}">${row.sub_category}</li>
								`);
							});

							$("#quick-qna-chat > ul > li.you > ul > li").off().click(function() {
								let no = $(this).data("no"), title = $(this).text();
								$("#quick-qna-chat > ul").append(`
									<li class="me"><span class="cont">${title}</span></li>
								`);
								$("#quick-qna-chat").animate({scrollTop:$("#quick-qna-chat > ul > li").last().offset().top},'fast');
								
								setTimeout(() => {
									$.ajax({
										url : config.api + 'quickview/inquiry/get',
										data : { faq_idx: no },
										success : function(d) {
											if(d.data) {
												$("#quick-qna-chat > ul").append(`
													<li class="you">
														<p>${d.data.question}</p>
														<p>${decodeHTMLEntities(d.data.answer)}</p>
													</li>
													<li class="you">
														<p>다른 도움이 더 필요하신가요?</p>
														<div class="buttons">
															<button type="button" class="yes">예</button>
															<button type="button" class="no">아니요</button>
														</div>
													</li>
												`);
												$("#quick-qna-chat > ul > li.you").last().find("button").off().click(function() {
													if($(this).hasClass("yes")) { // 예
														$("#quick-qna-chat > ul").append(`
															<li class="you">
																<p>문의 유형을 선택해 주세요.</p>
																<ul class="category">${$("#quick-qna-category > ul").html()}</ul>
															</li>
														`); // 기존 데이터 가져오기
														$("#quick-qna-chat").animate({scrollTop:$("#quick-qna-chat > ul > li.you").last().prev().offset().top},'fast');

														$("#quick-qna-chat > ul > li.you").last().find("ul.category > li").off().click(function() {
															get_qna_category($(this).data("no"),$(this).text());
														});
													}
													
													else if($(this).hasClass("no")) { // 아니요
														$("#quick-qna > button.close").click();
														$("#quick-qna-chat > ul").empty();
													}
												});
											}
										}
									});
								},1000);
							});
						}
					}
				});
			},1000);
		};
		
		$(this).parent().siblings().removeClass("on");
		$(this).parent().toggleClass("on");
		if($(this).parent().hasClass("on")) {

			let api_quickmenu = 'quickview/popular'
				, obj = $(this).next().find("section")
				, idx = $(this).index();

			// 질답
			if($(this).parent().hasClass("qna")) {
				$.ajax({
					url : config.api + 'quickview/inquiry/category',
					data : { category_type: 'FAQ' },
					success : function(d) {
						$("#quick-qna-category > ul").empty();
						$("#quick-qna-category").removeClass("hidden");
						if(d.data && d.data.length > 0) {
							d.data.forEach(row => {
								$("#quick-qna-category > ul").append(`
									<li data-no="${row.category_idx}">${row.category_title}</li>
								`);
							});
							$("#quick-qna-category > ul > li").click(function() {
								$("#quick-qna-category").addClass("hidden");
								get_qna_category($(this).data("no"),$(this).text());
							});
						}
					}
				});

			}
			else {
				$("#quick-tabs > li.on").click();
			}
		}
		else {
			$(this).parent().removeClass("on");
		}
		
	});
	$("body > aside button.close").click(function() {
		$("body > aside > ul > li.on").removeClass("on");
	});

	// 스토리 상세 닫기 버튼
	$("main > section.detail header button.close").click(function() {
		$("main").removeClass("detail");
	});

	/** 스크롤에 따라 헤더 고정 **/
	$(window).scroll(function() {
		
		// 헤더 고정
		if($(this).scrollTop() > 200) {
			$("body > header").addClass("fixed");
		}
		else {
			$("body > header").removeClass("fixed");
		}
		
		// 퀵메뉴 고정
		let quick_top_offset = $("body > footer").offset().top;
		if($(this).scrollTop() + $(this).height() > quick_top_offset) {
			if($("aside#quick").height() > 40) {
				quick_top_offset -= 200;
			}
			else quick_top_offset += 1;
			$("body > aside").addClass("bottom").css({top:quick_top_offset});
		}
		else {
			$("body > aside").removeClass("bottom").removeAttr("style");
		}
	}).scroll();
	
	/** 상단 메뉴 **/
	$.ajax({
		url: config.api + 'menu/get',
		success: function(d) {
			if(d.code == 200 && d.data) {
				/** GNB 상단 메뉴 **/
				if(d.data.menu_info) {
					d.data.menu_info.forEach(row => {
						$("#gnb").append(`
							<dt>${row.menu_title}</dt>
							<dd id="gnb-${row.menu_idx}" data-no="${row.menu_idx}" data-title="${row.menu_title}" class="no-gallery">
								<div class="cont"><ul></ul></div>
							</dd>
						`);

						// BEST 메뉴
						if(row.menu_idx == 1 && row.menu_hl1[0].menu_hl2) {
							row.menu_hl1[0].menu_hl2.forEach(row2 => {
								$(`#gnb-${row.menu_idx} > .cont > ul`).append(`
									<li>
										<a href="${(row2.menu_link).replace("/product/list","/shop")}">
											${decodeHTMLEntities(row2.menu_title)}
											<span class="image" style="background-image:url('${row2.img_location}')"></span>
										</a>
									</li>
								`);
							});
							return;
						}

						// 좌측 갤러리
						if(row.menu_slide && row.menu_slide.length > 0) {
							$(`#gnb-${row.menu_idx}`)
								.removeClass("no-gallery")
								.prepend(`
									<div class="gallery">
										<div class="swiper-container" id="gnb-gallery-${row.menu_idx}">
											<div class="swiper-wrapper"></div>
											<div class="swiper-title"></div>
											<div class="swiper-pagination"></div>
										</div>
									</div>
								`);
							row.menu_slide.forEach(row2 => {
								$(`#gnb-gallery-${row.menu_idx} > .swiper-wrapper`).append(`
									<a href="${row2.slide_link}" class="swiper-slide" style="background-image:url('${config.cdn + row2.img_location}')" data-title="${row2.slide_title}"></a>
								`);
							});
						}

						if(row.menu_hl1) { // 2차 메뉴
							row.menu_hl1.forEach(row2 => {
								$(`#gnb-${row.menu_idx} > .cont > ul`).append(`
									<li>
										<div class="row">
											<dl id="gnb-sub-${row2.menu_idx}">
												<dt><a href="${link_anchor(row2.menu_link)}" data-no="${row2.menu_idx}">${decodeHTMLEntities(row2.menu_title)}</a></dt>
											</dl>
										</div>
									</li>
								`);
								
								if(row2.img_location) {
									$(`#gnb-${row.menu_idx} > .cont > ul > li > .row`).last().append(`
										<span class="image" style="background-image:url('${config.cdn + row2.img_location}')"></span>
									`);
								}
								
								if(row2.menu_hl2) {
									row2.menu_hl2.forEach(row3 => {
										$(`#gnb-sub-${row2.menu_idx}`).append(`
											<dd><a href="${link_anchor(row3.menu_link)}" data-no="${row3.menu_idx}">${row3.menu_title}</a></dd>
										`);
									});
								}
							});

							// 콜라보레이션 전체보기 추가
							if(row.menu_title == '콜라보레이션') {
								$(`#gnb-${row.menu_idx} > .cont > ul`).append(`
									<li>
										<a href="">콜라보레이션 전체보기</a>
									</li>
								`);
							}
						}

					});

					/** 콜라보레이션 메뉴 추가 **/
					$("#gnb").append(`
						<dt>콜라보레이션</dt>
						<dd id="gnb-collaboration" data-title="콜라보레이션" class="no-gallery">
							<div class="cont">
								<ul>
									<li>
										<a href="${config.base_url}/collaboration/converse/2023/2nd">콜라보레이션 전체보기</a>
									</li>
								</ul>
							</div>
						</dd>
					`);

				}

		
				/** TNB 스토리 **/
				if(d.data.posting_story) {
					// 새로운 소식
					d.data.posting_story.column_NEW.forEach(row => {
						let href = `href="${row.page_url}"`;
						$("#tnb-story-new,#tnb-story-new-m").append(`
							<dd>
								<a ${href}>
									<h3>
										<small>${decodeHTMLEntities(row.story_sub_title)}</small>
										${decodeHTMLEntities(row.story_title)}
									</h3>
									<span class="image" style="background-image:url('${config.cdn + row.img_location}')"></span>
								</a>
							</dd>
						`);
					});
					
					// 컬렉션
					$("#tnb-story-archive,#tnb-story-archive-m").append(`
						<dd>
							<!--<a href="d.data.posting_story.column_COLC[0].page_url">-->
							<a href="${config.base_url}/collection">
								<h3>컬렉션</h3>
								<span class="image" style="background-image:url('${config.cdn + d.data.posting_story.archive_img[0].img_location}')"></span>
							</a>
						</dd>
					`);
					
					// 에디토리얼
					$("#tnb-story-archive,#tnb-story-archive-m").append(`
						<dd>
							<!--<a href="d.data.posting_story.column_EDTL[0].page_url">-->
							<a href="${config.base_url}/editorial">
								<h3>에디토리얼</h3>
								<span class="image" style="background-image:url('${config.cdn + d.data.posting_story.archive_img[1].img_location}')"></span>
							</a>
						</dd>
					`);

					// 콜라보레이션
					/*
					$("#tnb-story-archive,#tnb-story-archive-m").append(`
						<dd>
							<!--<a href="${d.data.posting_story.column_RNWY[0].page_url}">-->
							<a href="/collaboration/converse">
								<h3>콜라보레이션</h3>
								<span class="image" style="background-image:url('${config.cdn + d.data.posting_story.column_RNWY[0].img_location}')"></span>
							</a>
						</dd>
					`);
					*/

				}
				
				let swiper = [];

				$("body > header > nav > dl#gnb > dt").on('click mouseover',function() {
                    // tnb 닫기
                    $("#tnb a.side.on").removeClass("on");
                    $("body > header > aside").removeAttr("class");

					if($(this).hasClass("on") && $(window).width() <= 720) {
						$(this).removeClass("on");
					}
					else {
						$("body > header > nav > dl > dt").removeClass("on");
						$(this).addClass("on");
						$("body > header").addClass("on");
						$("body > header").addClass("fadeout");
					}
					
					if($("#gnb > dt.on").length == 0) {
						$("body > header > nav").removeClass("on");
					}
					else {
						$("body > header > nav").addClass("on");
					}
					
					let idx = (($(this).index()+2)/2)-1;
					
					// 다른 갤러리 비활성화
					/*
					swiper.forEach(row => {
						if(typeof row == 'object') row.disable();
					});
					if($(this).parent().find(".swiper-container").length > 0) {						
						if(typeof swiper[idx] == 'undefined') {
							let gallery = $(this).next().find(".swiper-container");
							swiper[idx] = new Swiper($(gallery).get(0),{
								slidesPerView : $(gallery).data("perView") || 1,
								slidesPerGroup : $(gallery).data("perGroup") || 1,
								spaceBetween : $(gallery).data("space") || 0,
								loop: true,
								loopFillGroupWithBlank: true,
								effect: $(gallery).data("effect") || "slide",
								autoplay: {
									delay: $(gallery).data("delay") || 2000,
									disableOnInteraction: false,
								},
								pagination: {
									el: $(gallery).find(".swiper-pagination").get(0),
									clickable: true,
								},
								navigation: {
									nextEl: $(gallery).find(".swiper-button-next").get(0),
									prevEl: $(gallery).find(".swiper-button-prev").get(0)
								},
							});
						}
						else {
							swiper[idx].enable();
							swiper[idx].update();
							swiper[idx].autoplay.start();
						}
					}
					*/
				});
				$("body > header > nav > dl").mouseleave(function(e) {
					if(e.offsetY > $(this).height()) {
						$(this).find("dt.on").removeClass("on");
						if(typeof $("body > header > aside").attr("class") == 'undefined') {
							$("body > header").removeClass("fadeout");
							setTimeout(() => { $("body > header").removeClass("on"); },350);
						}
					}
				});
				
				$("body > header").mouseleave(function(e) {
					if($(this).find(".on:not([data-side='story'])").length == 0) {
						$(this).find(".on").removeClass("on");
						$("body > header").removeClass("fadeout");
						setTimeout(() => { $("body > header").removeClass("on"); },350);
					}
				});

				/** TNB **/
				$(document).on("click","body > header > aside button.close",function() {
					//$("body > header > aside").removeAttr("class");
					$("body > header > aside").removeClass("on");
					$("body > header").removeClass("fadeout");
					$("#tnb .on").removeClass("on");
					setTimeout(() => { $("body > header").removeClass("on"); },350);
				});
				//.on('click mouseover',function() {
				$("#tnb a.side,#tnb-mobile a").click(function() {
                    $("#gnb dt.on").removeClass("on");
					$("#tnb a.side.on").removeClass("on");
					$(this).addClass("on");
					
					let side = $(this).data("side");
					
					if(side == 'story' && is_mobile == false) {
						if($(this).parent().hasClass("on")) {
							$(this).parent().removeClass("on");
							$("body > header").removeClass("on");
							$("body > header").removeClass("fadeout");
						}
						else {
							$(this).parent().addClass("on");
							$("body > header").addClass("on");
							$("body > header").addClass("fadeout");
						}
					}
					else {
						$("body > header > aside").removeAttr("class");
						$("body > header > aside").addClass(side);
						$("body > header > aside").addClass("on");
						$("body > header").addClass("on");
						$("body > header").addClass("fadeout");
					}
				});

			}
		}
	});

	/** 사이드바 > 쇼핑백 **/
	$("#btn-tnb-cart-continue").click(function() { // 닫기 버튼
		$("body > header > aside button.close").click();
	});
	$("#frm-side-cart input[name='all_check']").click(function() { // 체크 박스
		$("#frm-side-cart input[name='cart_no[]']").prop("checked",$(this).prop("checked"));
	});
	$("#btn-cart-select-delete").click(function() { // 선택 삭제
		let cart_no = [];
		$("#frm-side-cart input[name='cart_no[]']:checked").each(function() {
			cart_no.push(parseInt($(this).val()));
		});
		if(cart_no.length == 0) {
			alert("선택된 제품이 없습니다.");
		}
		else {
			$.ajax({
				url: config.api + "cart/delete",
				data: { basket_idx : cart_no },
				error: function(e) {
				},
				success: function(d) {
					if (d.code == 200) {
						get_cart(); // 장바구니 새로 고침
					}
					else {
						alert(d.msg);
					}
				}
			});
		}
	});
	$("#frm-side-cart").submit(function() { // 결제하기
		let cart_no = [];
		$("#frm-side-cart input[name='cart_no[]']:checked").each(function() {
			cart_no.push(parseInt($(this).val()));
		});
		if(cart_no.length == 0) {
			alert("선택된 제품이 없습니다.");
		}
		else {
			sessionStorage.setItem("cart_no", JSON.stringify(cart_no));
			location.href = config.base_url + "/pay";
		}
		return false;
	});
	
	/** 회원 정보 가져오기 **/
	$.ajax({
		url: config.api + "member/get",
		success: function(d) {
			if (d.code == 200) {
				$("body").addClass("loged");
				get_cart(null,true); // 장바구니
				sessionStorage.setItem("MEMBER",JSON.stringify(d));
			}
			else {
				sessionStorage.removeItem("MEMBER");
			}
		}
	});
	if(sessionStorage.getItem("MEMBER") != null) {
		config.member = JSON.parse(sessionStorage.getItem("MEMBER"));
		
		if($("body > main.my").length > 0) {
			$("#member-info-name").text(config.member.name);
			$("#member-info-email").text(config.member.id);
			$("#member-info-mileage").text(number_format(config.member.mileage));
			$("#member-info-boucher").text(number_format(config.member.boucher));
			$("#member-info-membership").text(number_format(config.member.membership));
		}
	}
	
	/** 사이드바 > 로그인 **/
	$(".ready-login").click(function() {
		$("#tnb a[data-side='my']").click();
	});
	$("#frm-side-login,#frm-login").submit(function() {
		let f = $(this),
			id = $(this).find("input[name='member_id']"),
			pw = $(this).find("input[name='member_pw']");

		// 기존 오류 메시지 삭제
		$(this).find(".control-label .warning").remove(); 
		$('#side-login-result').empty();

		if(id.val() == "") {
			id.next().append(`<span class="warning">${id.data("msg1")}</span>`);
		}
		else if(is_email(id.val()) == false) {
			id.next().append(`<span class="warning">${id.data("msg2")}</span>`);
		}
		else if(pw.val() == "") {
			pw.next().append(`<span class="warning">${id.data("msg")}</span>`);
		}

		$.ajax({
			url: config.api + "member/login",
			data: $(this).serialize(),
			error: function(e) {
				//makeMsgNoti(config.language, "MSG_F_ERR_0067", null);
				alert("로그인 처리중 오류가 발생했습니다.");
			},
			success: function(d) {
				if (d.code == 200) {
					if($.inArray(location.pathname,['/login','/logout']) == true) {
						location.href = "/";
					}
					else {
						location.reload();
					}
				} else {
					$('#side-login-result').text(d.msg);
				}
			}
		});
		return false;
	});	
	/** 로그아웃 **/
	$("button.logout").click(function() {
		$.ajax({
			url: config.api + "member/logout",
			error: function(e) {
				//makeMsgNoti(config.language, "MSG_F_ERR_0067", null);
				alert("로그아웃 처리중 오류가 발생했습니다.");
			},
			success: function(d) {
				if (d.code == 200) {
					location.href = "/logout";
				} else {
					alert(d.msg);
				}
			}
		});		
	});


    /** 추천 검색어, 실간 인기 제품 불러오기  **/
	$.ajax({
		url: config.api + "search/list/get",
		success: function(d) {
			if(d.data) {
				if(d.data.keyword_info) {
					d.data.keyword_info.forEach(row => {
						$("#search-recommend-keyword").append(`
							<li><a href="${row.menu_link}">${row.keyword_txt}</a></li>
						`);
					});
				}
				if(d.data.popular_info) {
					d.data.popular_info.forEach(row => {
						$("#search-recommend-goods").append(`
							<li><a href="/shop/${row.product_idx}">
								<span class="img" style="background-image:url('${config.cdn + row.img_location}')"></span>
								<span class="name"><span class="cont">${row.product_name}</span></span>
							</a></li>
						`);
					});
				}
			}
        }
	});
	/** 사이드바 > 검색 **/
	$("#frm-side-search").submit(function() {
		$.ajax({
			url: config.api + "search/get",
			data: {
				search_keyword : $(this).find("input[name='keyword']").val()
			},
			success: function(d) {
				if(d.code == 200) {
					$("#tnb-search > section.intro").addClass("hidden");
					$("#tnb-search > section.result").removeClass("hidden");
					if(d.data && d.data.length > 0) {
						d.data.forEach(row => {
							$("#search-result").append(`
								<li><a href="/shop/${row.product_idx}">
									<span class="img" style="background-image:url('${config.cdn + row.img_location}')"></span>
									<span class="name"><span class="cont">${row.product_name}</span></span>
								</a></li>
							`);
						});
						$("#search-result + a.btn").attr("href",`/search?keyword=${$("#frm-side-search input[name='keyword']").val()}`);
					}
					else {
						$("#search-result")
							.addClass("empty")
							.html(`<li class="empty">검색 결과가 없습니다.</li>`);
					}
				}
				else {
					alert(d.msg);
				}
			}
		});	
		return false;
	});
	/** 검색 취소 **/
	$("#frm-side-search button").click(function() {
		$("#tnb-search > section.intro").removeClass("hidden");
		$("#tnb-search > section.result").addClass("hidden");
	});
	$("form.search button").click(function() {
		$(this).parent().find("input[name='keyword']").val("");
	});

	
	$("body > footer > section > article > h2").click(function() {
		$(this).parent().siblings().removeClass("on");
		$(this).parent().toggleClass("on");
	});
	
	/** 해당 페이지 스크립트 불러오기 **/
	/*
	$.ajax({
		url: config.script + ((location.pathname=='/')?'/main':location.pathname),
		async: true,
		dataType: "script"
	});
	*/

	/** 마이페이지 네비 아이콘 스크롤 이동 **/
	if(is_mobile && $("main").hasClass("my")) { // 모바일에서만
		let my_nav_index = {
				'/my/mileage' : 8
			}
			,my_nav_obj = $("main.my > section.summary > article.links");
		//$(my_nav_obj).animate({scrollLeft: $(my_nav_obj).find("ul > li").eq(my_nav_index[location.pathname]).offset().left},"fast");
	}
		
	$("body").removeClass("--loading");
});