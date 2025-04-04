const LANGUAGE_CODE = {
	ko : 'KR',
	en : 'EN',
	KR : 'KR',
	EN : 'EN'
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

let title_quickview = {
    KR: {
        R: "최근 본 제품",
        P: "실시간 인기 제품",
        W: "위시리스트"
    },
    EN: {
        R: "Recently viewed",
        P: "Popular",
        W: "Wishlist"
    }
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
	} else {
		window.is_mobile = false;
	}
}).resize();

/** 위시리스트 버튼, 모든 위시리스트 동작 정의 **/
$(document).on("click","button.favorite:not(.custom)",function() {
	let msg_alert = {
		KR : "로그인 후 다시 시도해주세요.",
		EN : "Please log in and try again."
	}

	if (!sessionStorage.MEMBER) {
		if ($('.zoom.hide-scroll').length == 0) {
			$("#tnb a[data-side='my']").click();
		} else {
			alert(msg_alert[config.language]);
		}

		return false;
	}
	
	// 위시리스트 지정 및 해제
	let obj = $(this);
	let uri = (($(this).hasClass("on"))?"delete":"put")
	let product_idx =  $(this).data("goods_no")
	
	$.ajax({ 
		url: config.api + 'wishlist/' + uri,
		headers : {
			country : config.language
		},
		data: {
			product_idx : product_idx
		},
		success: function(d) {
			if(d.code == 200) {
				obj.toggleClass("on");
				if(uri === "delete") {
					$('.wishlist-container-' + product_idx).remove();
					$.each($('button.favorite'), function(index, b) {
						let button = $(b);
						if(button.data("goods_no") == product_idx) {
							button.toggleClass("off");
						}
					})
				}
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
	/** 언어 설정 **/
	let lang		= null;

	let url_path = window.location.pathname;
	if (url_path.includes('/kr')) {
		lang = 'KR';
	} else if (url_path.includes('/en')) {
		lang = 'EN';
	} else {
		fetch('https://ipapi.co/json/')
			.then(response => response.json())
			.then(data => {
				lang = data.country_code === 'KR' ? 'KR' : 'EN';
			})
			.catch(error => {
				lang = "EN";
			});
	}
	
	if (lang == null) {
		if (sessionStorage.getItem('lang') != null) {
			lang = sessionStorage.getItem('lang');
		} else {
			lang = "KR";
		}
	}

	sessionStorage.setItem('lang',lang);

	config.language = lang;
	config.base_url = `/${lang.toLowerCase()}`;
	
	if (location.pathname == "/") {
		config.language = sessionStorage.getItem('lang');
		config.base_url = `/${sessionStorage.getItem('lang').toLowerCase()}`;

		location.href = `${config.base_url}`;
	}

	$("body").attr("data-path",location.pathname);
	
	/** 첫번째 탭 선택 **/
	$(".tab > .tab-container > ul > li").first().click();
	
	/** gnb 버튼 **/
	$("button#btn-gnb").click(function() {
		$("body").toggleClass("fold-nav");
		if ($("body").hasClass("fold-nav")) {
			// GNB 열림 상태 - 히스토리에 상태 추가
			history.pushState({ isGnbOpen: true }, null, location.href);
		} else {
			// GNB 닫힘 상태 - 히스토리 상태 복원
			history.back();
		}

		if (window.is_mobile) {
			if (!$('body').hasClass('fold-nav')) {
				$("body > header").removeAttr('class');
				$("aside").removeAttr("class");

				if ($("body > header").hasClass('fadeout')) {
					$("body > header").removeClass('fadeout');
				}
			} else {
				$("body > header").addClass("on").addClass("fadeout");
			}
		} else {
			if (!$('body').hasClass('fold-nav')) {
				$("body > header").removeClass("on").removeClass("fadeout");
				$("aside").removeClass("on");
			}
		}
	});
	// 뒤로가기 이벤트 처리
	window.onpopstate = function (event) {
		// 뒤로가기 시 GNB 닫기
		$("body").removeClass("fold-nav");

		if (window.is_mobile) {
			$("body > header").removeAttr('class');
			$("aside").removeAttr("class");

			if ($("body > header").hasClass('fadeout')) {
				$("body > header").removeClass('fadeout');
			}
			// tnb 자식들 중 on 클래스 삭제
			$("#tnb").find(".on").removeClass("on");
		} else {
			$("body > header").removeClass("on").removeClass("fadeout");
			$("aside").removeClass("on");
		}
	};

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
		$("#frm-cookie-accept button[type='button']").click(function () {
			// 각 체크박스 상태 확인 및 쿠키 설정
			$("section.cookie-agree input[type='checkbox']").each(function () {
				const cookieName = "accpet-" + $(this).attr("name"); // 쿠키 이름 생성
				const isChecked = $(this).is(":checked") ? "y" : "n";
				cookie.set(cookieName, isChecked, config.cookie_expire);
			});
			// 설정 완료 후 배너 닫기
			$("section.cookie-agree").remove();
		});
	}
	else {
		$("section.cookie-agree").remove();
	}
	
	/** 퀵메뉴 **/
	$("#quick-tabs > li").click(function() {
		let title = "";
		let api_url = [
				'goods/recently',
				'quickview/popular',
				'wishlist/get'
			]
			,empty_str
			,obj;

		switch ($(this).index()) {
			// 최근 본 상품
			case 0:
				empty_str = {
					"KR": "최근 본 제품이 비어있습니다.",
					"EN": "No recently viewed products.",
				}[config.language] || "최근 본 제품이 비어있습니다.";
				obj = $("#quick-recently-list");
				title = title_quickview[config.language]['R'];
				break;
		
			// 실시간 인기 제품
			case 1:
				empty_str = {
					"KR": "실시간 인기 제품이 비어있습니다.",
					"EN": "No real-time popular products.",
				}[config.language] || "실시간 인기 제품이 비어있습니다.";
				obj = $("#quick-popular-list");
				title = title_quickview[config.language]['P'];
				break;
		
			// 위시리스트
			case 2:
				empty_str = {
					"KR": "위시리스트가 비어있습니다.",
					"EN": "Your wishlist is empty.",
				}[config.language] || "위시리스트가 비어있습니다.";
				obj = $("#quick-wishlist-list");
				title = title_quickview[config.language]['W'];
				break;
		}
			

		$('.recently-viewed.on header').text(title);
		const index = $(this).index()

		$.ajax({
			url : config.api + api_url[$(this).index()],
			headers : {
				country : config.language
			},
			success : function(d) {
				if(d.code == 200) {
					if(d.data && d.data.length > 0) {
						$(obj).empty();
						const is_wishlist = api_url[index] === 'wishlist/get'
						d.data.forEach(row => {
							const back_image = is_wishlist ? `background-image:url('${config.cdn + row.img_location}')` : `background-image:url('${config.cdn + row.img_location}')`
							$(obj).append(`
								<li class="${index === 2 ? 'wishlist-container-' + row.product_idx : '' }">
									<a href="/${config.language.toLowerCase()}/shop/${row.product_idx}">
										<span class="image" style="${back_image}"></span>
										${row.product_name}
									</a>
									<button type="button" class="favorite ${index === 2 ? 'on' : (row.whish_flg? 'on' : '')}" data-goods_no="${row.product_idx}"></button>
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
			
			$.ajax({
				url : config.api + 'quickview/inquiry/list',
				headers : {
					country : config.language
				},
				async:false,
				data : { category_idx: no },
				success : function(d) {
					let t_msg = {
						KR : {
							't_01' : "문의 카테고리를 선택해주세요.",
							't_02' : "다른 도움이 더 필요하신가요?",
							't_03' : "예",
							't_04' : "아니오"
						},
						EN : {
							't_01' : "Please select the category.",
							't_02' : "Do you need any other help?",
							't_03' : "Yes",
							't_04' : "No"
						}
					}
					if (d.data && d.data.length > 0) {
						$("#quick-qna-chat > ul").append(`
							<li class="you">
								<p>${t_msg[config.language]['t_01']}</p>
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
								<li class="me">
									<span class="cont">${title}</span>
								</li>
							`);

							$("#quick-qna-chat").animate({scrollTop:$("#quick-qna-chat > ul > li").last().offset().top},'fast');
							$.ajax({
								url : config.api + 'quickview/inquiry/get',
								headers : {
									country : config.language
								},
								async:false,
								data : { faq_idx: no },
								success : function(d) {
									if(d.data) {
										$("#quick-qna-chat > ul").append(`
											<li class="you">
												<p>${d.data.question}</p>
												<p>${decodeHTMLEntities(d.data.answer)}</p>
											</li>
											<li class="you">
												<p>${t_msg[config.language]['t_02']}</p>
												<div class="buttons">
													<button type="button" class="yes">${t_msg[config.language]['t_03']}</button>
													<button type="button" class="no">${t_msg[config.language]['t_04']}</button>
												</div>
											</li>
										`);
										const chatContainer = $("#quick-qna-chat")[0];
										chatContainer.scrollTop = chatContainer.scrollHeight;
										$("#quick-qna-chat > ul > li.you").last().find("button").off().click(function() {
											if($(this).hasClass("yes")) { // 예
												$("#quick-qna-chat > ul").append(`
													<li class="you">
														<p>${t_msg[config.language]['t_01']}</p>
														<ul class="category">${$("#quick-qna-category > ul").html()}</ul>
													</li>
												`);
												
												// 기존 데이터 가져오기
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
						});
					}
				}
			});
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
					headers : {
						country : config.language
					},
					async:false,
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
		
		$('.qna.on #quick-qna .btn').unbind();
		$('.qna.on #quick-qna .btn').click(function() {
			location.href = `${config.base_url}/my/customer/qna/write`;
		});
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
		headers : {
			country : config.language
		},
		beforeSend: function(xhr) {
			xhr.setRequestHeader("country",config.language);
		},
		success: function(d) {
			if(d.code == 200 && d.data) {
				/* TNB - 헤더 */
				let landing_header = d.data.landing_header;
				if (landing_header != null && landing_header.length > 0) {
					landing_header.forEach(h1 => {
						$("#gnb").append(`
							<dt>${h1.header_title}</dt>
							<dd id="gnb-${h1.header_idx}" data-no="${h1.header_idx}" data-title="${h1.header_title}" class="no-gallery">
								<div class="cont"><ul></ul></div>
							</dd>
						`);

						let children_h2 = h1.children;
						if (children_h2 != null && children_h2.length > 0) {
							children_h2.forEach(h2 => {
								let h2_link = h2.header_link;
								if (h2.ext_flg == true) {
									h2_link = `https://${h2_link}`;
								} else {
									h2_link = `${config.base_url}${h2_link}`;
								}

								$(`#gnb-${h1.header_idx} > .cont > ul`).append(`
									<li>
										<div class="row">
											<dl id="gnb-sub-${h2.header_idx}">
												<dt>
													<a href="${h2_link}" data-no="${h2.header_idx}">${decodeHTMLEntities(h2.header_title)}</a>
												</dt>
											</dl>
										</div>
									</li>
								`);
								
								let children_h3 = h2.children;
								if (children_h3 != null && children_h3.length > 0) {
									children_h3.forEach(h3 => {
										let h3_link = h3.header_link;
										if (h3.ext_flg == true) {
											h3_link = `https://${h3_link}`;
										} else {
											h3_link = `${config.base_url}${h3_link}`;
										}
										
										$(`#gnb-sub-${h2.header_idx}`).append(`
											<dd>
												<a href="${h3_link}" data-no="${h3.header_idx}">${h3.header_title}</a>
											</dd>
										`);
									});
								}
							});
						}
					});
				}

				/* TNB - 콜라보레이션 */
				let t_collabo = {
					KR : {
						title	: "콜라보레이션",
						desc	: "전체보기"
					},
					EN : {
						title	: "Collaboration",
						desc	: "Show all"
					},
				};

				$("#gnb").append(`
					<dt>${t_collabo[config.language]['title']}</dt>
					<dd id="gnb-collaboration" data-title="${t_collabo[config.language]['title']}" class="no-gallery">
						<div class="cont">
							<ul>
								<li>
									<a href="${config.base_url}/collaboration/converse/2023/2nd">${t_collabo[config.language]['desc']}</a>
								</li>
							</ul>
						</div>
					</dd>
				`);
				
				/* TNB - 스토리 */
				
				/* TNB - 스토리 (새로운 소식) */
				let landing_story = d.data.landing_story;
				if (landing_story != null && landing_story.length > 0) {
					$('#tnb dt').eq(0).find('a').text(d.data.story_title);

					landing_story.forEach(story => {
						let story_link = story.story_link;
						if (story.ext_flg == true) {
							story_link = `https://${story_link}`;
						} else {
							story_link = `${config.base_url}${story.story_link}`;
						}

						$("#tnb-story-new,#tnb-story-new-m").append(`
							<dd>
								<a href="${story_link}">
									<h3>
										<small>${decodeHTMLEntities(story.story_sub_title)}</small>
										${decodeHTMLEntities(story.story_title)}
									</h3>
									
									<span class="image" style="background-image:url('${config.cdn}${story.img_location}')"></span>
								</a>
							</dd>
						`);
					});
				}

				/* TNB - 스토리 (아카이브) */
				let t_archive = {
					KR : {
						colc	: "컬렉션",
						edtl	: "에디토리얼"
					},
					EN : {
						colc	: "Collection",
						edtl	: "Editorial"
					},
				};

				let landing_archive = d.data.landing_archive;
				if (landing_archive != null && landing_archive.length > 0) {
					landing_archive.forEach(archive => {
						if (archive.archive_type == "COLC") {
							$("#tnb-story-archive,#tnb-story-archive-m").append(`
								<dd>
									<a href="${config.base_url}/collection">
										<h3>${t_archive[config.language]['colc']}</h3>
										<span class="image" style="background-image:url('${config.cdn}${archive.img_location}')"></span>
									</a>
								</dd>
							`);
						} else if (archive.archive_type == "EDTL") {
							// 에디토리얼
							$("#tnb-story-archive,#tnb-story-archive-m").append(`
								<dd>
									<a href="${config.base_url}/editorial">
										<h3>${t_archive[config.language]['edtl']}</h3>
										<span class="image" style="background-image:url('${config.cdn}${archive.img_location}')"></span>
									</a>
								</dd>
							`);
						}
					});
				}
				
				let swiper = [];
				
				$("body > header > nav > dl#gnb > dt").on('click mouseover',function(e) {
					e.stopPropagation()
					if (e.type === 'mouseover' && is_mobile) return;

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
					$("body > header > aside").removeAttr("class");
					//$("body > header > aside").removeClass("on");
					$("body > header").removeClass("fadeout");
					$("#tnb .on").removeClass("on");
					setTimeout(() => { $("body > header").removeClass("on"); },350);
				});
				
				//.on('click mouseover',function() {
				$("#tnb a.side,#tnb-mobile a").click(function() {
					if ($(this).hasClass('customer')) {
						if (config.member != null) {
							location.href = `${config.base_url}/my/customer`;
						} else {
							location.href = `${config.base_url}/login`;
						}
					} else {
						$("#gnb dt.on").removeClass("on");
						$("#tnb dt.on").removeClass("on");
						$("#tnb a.side.on").removeClass("on");
						$("body > header > aside").removeAttr("class");
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
							if($(this).parent().closest(".on").length > 0) {
								$(this).parent().closest(".on").removeClass("on");
							}
							$("body > header > aside").removeAttr("class");
							$("body > header > aside").addClass(side);
							$("body > header > aside").addClass("on");
							$("body > header").addClass("on");
							$("body > header").addClass("fadeout");
						}
					}
				});

			}
		}
	});

	let msg_cart = {
		KR : "선택된 제품이 없습니다.",
		EN : "No products has selected"
	}

	/** 사이드바 > 쇼핑백 **/
	$("#btn-tnb-cart-continue").click(function() { // 닫기 버튼
		$("body > header > aside button.close").click();
		$("#frm-side-cart input[name='all_check']").click().trigger('change');
	});
	$("#frm-side-cart input[name='all_check']").click(function() { // 체크 박스
		$("#frm-side-cart input[name='cart_no[]']:not(:disabled)").prop("checked",$(this).prop("checked"));
		$("#side-cart-num").text($("#frm-side-cart input[name='cart_no[]']:checked").length);
	});
	$("#btn-cart-select-delete").click(function() { // 선택 삭제
		let cart_no = [];
		$("#frm-side-cart input[name='cart_no[]']:checked").each(function() {
			cart_no.push(parseInt($(this).val()));
		});

		if(cart_no.length == 0) {
			alert(msg_cart[config.language]);
		}
		else {
			$.ajax({
				url: config.api + "cart/delete",
				headers : {
					country : config.language
				},
				data: { basket_idx : cart_no },
				error: function(e) {
				},
				success: function(d) {
					if (d.code == 200) {
						get_cart(null,true); // 장바구니
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
			alert(msg_cart[config.language]);
		}
		else {
			sessionStorage.setItem("cart_no", JSON.stringify(cart_no));
			location.href = `${config.base_url}/pay`;
		}
		return false;
	});
	
	getMEMBER();

	if(sessionStorage.getItem("MEMBER") != null) {
		config.member = JSON.parse(sessionStorage.getItem("MEMBER"));
		
		if($("body > main.my").length > 0) {
			$("#member-info-name").text(config.member.name);
			$("#member-info-email").text(config.member.id);
			$("#member-info-mileage").text(config.member.t_mileage);
			$("#member-info-voucher").text(number_format(config.member.voucher));
			$("#member-info-membership").text(config.member.membership);
		}
	}
	
	/** 사이드바 > 로그인 **/
	$(".ready-login").click(function() {
		$("#tnb a[data-side='my']").click();
	});
	$("#frm-side-login,#frm-login").submit(function() {
		//중복alert 방지
		modal = $(".modal.alert");
		if (modal.hasClass("on")) {
			return false;
		}
		let f = $(this),
			id = $(this).find("input[name='member_id']"),
			pw = $(this).find("input[name='member_pw']"),
			saveIdCheckbox = document.querySelector("input[name='save_id']"),
			idVal = document.querySelector("input[name='member_id']").value;

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
			headers : {
				country : config.language
			},
			data: $(this).serialize(),
			error: function(e) {
				//makeMsgNoti(config.language, "MSG_F_ERR_0067", null);
				alert("로그인 처리중 오류가 발생했습니다.");
			},
			success: function(d) {
				if (d.code == 200) {
					let base_url = `/${LANGUAGE_CODE[$("html").attr("lang")].toLowerCase()}`;

					getMEMBER();
						
					if (saveIdCheckbox.checked) {
						cookie.set("savedId", idVal, 365); // 30일 동안 쿠키 저장
					} else {
						cookie.set("savedId", "", -1); // 체크 해제 시 쿠키 삭제
					}

					let r_url = sessionStorage.getItem('r_url');
					if (r_url != null) {
						location.href = r_url;
						sessionStorage.removeItem('r_url');
					} else {
						if ($.inArray(location.pathname,[`${base_url}/login`,`${base_url}/logout`,`${base_url}/join`]) >= 0) {
							sessionStorage.setItem("lang",d.country);
							location.href = `${base_url}`;
						} else {
							location.reload();
						}
					}
				} else {
					if (f.attr("id") === "frm-side-login") {
						$('#side-login-result').text(d.msg);
					}
					else{
						alert(d.msg);
					}
				}
			}
		});

		return false;
	});

	/** 아이디 저장 공통 적용 **/
	let savedId = cookie.get("savedId");
	if (savedId) {
		if (location.pathname == `${config.base_url}/join` || location.pathname == `${config.base_url}/find-account`) {
			document.querySelector(".login input[name='member_id']").value = savedId;
			document.querySelector(".login input[name='save_id']").checked = true;
		} else {
			$("input[name='member_id']").val(savedId);
			$("input[name='save_id']").prop('checked',true);
		}
	}

	/** 로그아웃 **/
	$("button.logout").click(function() {
		$.ajax({
			url: config.api + "member/logout",
			headers : {
				country : config.language
			},
			error: function(e) {
				//makeMsgNoti(config.language, "MSG_F_ERR_0067", null);
				alert("로그아웃 처리중 오류가 발생했습니다.");
			},
			success: function(d) {
				if (d.code == 200) {
					//makeMsgNoti(config.language, "MSG_F_INF_0018", null)
					location.href = `${config.base_url}/logout`
				} else {
					alert(d.msg);
				}
			}
		});		
	});


    /** 추천 검색어, 실간 인기 제품 불러오기  **/
	$.ajax({
		url: config.api + "search/list",
		headers : {
			country : config.language
		},
		success: function(d) {
			if(d.data) {
				let search_keyword = d.data.search_keyword;
				if (search_keyword != null && search_keyword.length > 0) {
					search_keyword.forEach(keyword => {
						let keyword_link = keyword.keyword_link;
						if (keyword.ext_flg !=  true) {
							keyword_link = `${config.base_url}${keyword_link}`
						}

						$("#search-recommend-keyword").append(`
							<li>
								<a href="${keyword_link}">${keyword.keyword_txt}</a>
							</li>
						`);
					});
				}

				let search_product = d.data.search_product;
				if (search_product != null && search_product.length > 0) {
					search_product.forEach(product => {
						$("#search-recommend-goods").append(`
							<li><a href="${config.base_url}/shop/${product.product_idx}">
								<span class="img" style="background-image:url('${config.cdn}${product.img_location}')"></span>
								<span class="name"><span class="cont">${product.product_name}</span></span>
							</a></li>
						`);
					});
				}
			}
        }
	});
	
	/** 사이드바 > 검색 **/
	$("#frm-side-search").submit(function() {
		let keyword = $(this).find("input[name='keyword']").val().trim(); // 검색어 앞뒤 공백 제거
	
		if (keyword === "") { // 빈 공백일 경우
			$(this).find("input[name='keyword']").val(""); // 검색창 초기화
			return false; // 검색 처리 중단
		}
	
		$.ajax({
			url: config.api + "search/get",
			headers: {
				country: config.language
			},
			data: {
				search_keyword: keyword
			},
			success: function(d) {
				if (d.code == 200) {
					$("#tnb-search > section.intro").addClass("hidden");
					$("#tnb-search > section.result").removeClass("hidden");
					
					$("#search-result").html('');
					
					if (d.data && d.data.length > 0) {
						d.data.forEach(row => {
							$("#search-result").removeClass("empty")
							$("#search-result").append(`
								<li><a href="${config.base_url}/shop/${row.product_idx}">
									<span class="img" style="background-image:url('${config.cdn + row.img_location}')"></span>
									<span class="name"><span class="cont">${row.product_name}</span></span>
								</a></li>
							`);
						});
						$("#search-result + a.btn").attr("href", `${config.base_url}/search?keyword=${$("#frm-side-search input[name='keyword']").val()}`);
					} else {
						$("#search-result")
							.addClass("empty")
							.html(`<li class="empty">검색 결과가 없습니다.</li>`);
					}
				} else {
					alert(d.msg);
				}
			}
		});
		return false; // 기본 제출 동작 방지
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

	/** 마이페이지 네비 아이콘 스크롤 이동 **/
	if(is_mobile && $("main").hasClass("my")) { // 모바일에서만
		let my_nav_index = {
				'/my/mileage' : 8
			}
			,my_nav_obj = $("main.my > section.summary > article.links");
	}
	
	$("body").removeClass("--loading");
	
	$("section.language .buttons a").click(function (e) {
		const selectedLang = $(this).data('country');

		sessionStorage.setItem("lang", selectedLang);
		
		sessionStorage.removeItem('MEMBER');
		
		location.href = `/${selectedLang.toLowerCase()}`;
	});

	initLoginHandler();

	checkPopup();

	check_dev();
});

function getMEMBER() {
	/** 회원 정보 가져오기 **/
	$.ajax({
		url: config.api + "member/get",
		headers : {
			country : config.language
		},
		async: false,
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
}

function initLoginHandler() {
	/* 카카오 로그인 버튼 클릭 */
	let btn_login_K = document.querySelectorAll('#btn-login-kakao');
	if (btn_login_K != null && btn_login_K.length > 0) {
		btn_login_K.forEach(btn => {
			btn.addEventListener('click', function () {
				let oauth_kakao		= "https://kauth.kakao.com/oauth/";
				let client_kakao	= "b43df682b08d3270e40a79b5c51506b5";
				let redirect_kakao	= `https://stg.adererror.com${config.base_url}/kakao-login`;
				
				let tmp_url = `${oauth_kakao}authorize?client_id=${client_kakao}&scope=account_email,name,phone_number,birthyear&redirect_uri=${redirect_kakao}&response_type=code&prompt=login`;
				location.href = tmp_url;
			});
		});
	}

	/* 네이버 로그인 버튼 클릭 */
	let btn_login_N = document.querySelectorAll('#btn-login-naver');
	if (btn_login_N != null && btn_login_N.length > 0) {
		btn_login_N.forEach(btn => {
			btn.addEventListener('click', function () {
				let oauth_naver		= "https://nid.naver.com/oauth2.0/";
				let client_naver	= "k4gK4Eon6TG0GwnX5zhM";
				let redirect_naver	= encodeURI(`https://stg.adererror.com${config.base_url}/naver-login`);
				
				let mt				= Date.now().toString();
				let rand			= Math.random().toString();
				let state			= CryptoJS.MD5(mt + rand).toString();
	
				let tmp_url = `${oauth_naver}authorize?response_type=code&client_id=${client_naver}&redirect_uri=${redirect_naver}&state=${state}`;
				location.href = tmp_url;
			});
		});
	}

	/* 카카오 로그인 버튼 클릭 */
	let btn_login_G = document.querySelectorAll('#btn-login-google');
	if (btn_login_G != null && btn_login_G.length > 0) {
		btn_login_G.forEach(btn => {
			btn.addEventListener('click', function () {
				let oauth_google	= "https://accounts.google.com/o/oauth2/v2/auth";
				let client_google	= "999124937022-qgkvknpulb77vgvdntunoqsj90ka2jga.apps.googleusercontent.com";
				let redirect_google	= encodeURI(`https://stg.adererror.com${config.base_url}/google-login`);
				let scope			= encodeURI("https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email");
				
				let tmp_url = `${oauth_google}?client_id=${client_google}&redirect_uri=${redirect_google}&state=OK&scope=${scope}&access_type=online&include_granted_scopes=true&response_type=code`;
				location.href = tmp_url;
			});
		});
	}
}

function loadingMASK() {
	var mask_h = $(document).height();
	var mask_w  = window.document.body.clientWidth;

	//화면에 출력할 마스크를 설정해줍니다.
	let mask = `<div id="mask_loading"></div>`;

	$('body').append(mask);

	$('#mask_loading').css({'width':mask_w,'height':mask_h,'opacity':'0.5'}); 

	$('#mask_loading').show();

	document.body.style.overflow = "hidden";
}

function closeMASK() {
    $('#mask_loading').hide();
    $('#mask_loading').remove();

	document.body.style.overflow = "auto";
}

function checkPopup() {
	let popup_type	= null;
	let param_popup	= null;

	let path = location.pathname.replace(config.base_url,"");
	if (path.includes('/shop/')) {
		popup_type	= "P";
		param_popup = location.pathname.split("/")[3];
	} else {
		popup_type	= "W";
		param_popup = path;
	}

	$.ajax({
		url : config.api + "popup",
		headers : {
			country : config.language
		},
		data : {
			'popup_type'	:popup_type,
			'param_popup'	:param_popup
		},
		success : function(d) {
			if (d.data != null) {
				setPopup(d.data);
			}
		}
	});
}

function setPopup(data) {
	let close_key	= `popup_close_${data.popup_idx}`;
	let close_time	= localStorage.getItem(close_key);

	if (close_time != null) {
		let now = new Date();
		now = now.setTime(now.getTime());
		
		if (parseInt(close_time) <= now) {
			localStorage.removeItem(`popup_close_${data.popup_idx}`);
		} else if (parseInt(close_time) > now) {
			return false;
		}
	}

	const body = document.body;

	let tmp_popup = document.querySelector('#popup-container');
	if (tmp_popup != null) {
		tmp_popup.remove();
	}

	let txt_close = {
		KR : {
			tday : "하루동안 열지않음",
			none : "다시 열지않음"
		},
		EN : {
			tday : "Do not open for one day",
			none : "Never open again"
		}
	}

	let t_close		= "";
	let id_close	= "";
	
	if (data.close_flg == 'TODAY') {
		t_close		= txt_close[config.language]['tday']
		id_close	= 'today';
	} else {
		t_close		= txt_close[config.language]['none'];
		id_close	= 'none';
	}

	let t_location = {
		VRT : {
			T : "top",
			M : "middle",
			B : "bottom",
		},
		HRZ : {
			L : "left",
			C : "center",
			R : "right"
		}
	}

	const popup = document.createElement("div");
	popup.id		= "popup-container";
	popup.className	= "popup-containner open";
	popup.innerHTML = `
		<div class="popup__background ${t_location['VRT'][data.popup_vrt]} ${t_location['HRZ'][data.popup_hrz]}">
			<div class="popup__wrap" style="width:${data.width}px;height:${data.height}px">
				<button type="button" class="popup_close"></button>
				<div class="popup__box">
					<div class="popup_header">
						<h1 class="title">
							${data.popup_title}
						</h1>
					</div>
					
					<div class="popup_body" style="">
						${data.popup_contents}
					</div>
					<div class="popup_logo">
						<img src="/images/landing/mini-logo.svg" alt="">
					</div>
				</div>
				<div class="do_not_open">
					<input type="checkbox" id="${id_close}">
						<label for="${id_close}"></label>
					<span>${t_close}</span>
				</div>
			</div>
		</div>
	`

	body.appendChild(popup);

	document.querySelectorAll('#popup-container h1, #popup-container p').forEach(function (el) {
		el.style.removeProperty('font-size');
		el.style.removeProperty('font-family');
		el.style.removeProperty('font-weight');
		el.style.removeProperty('font-stretch');
		el.style.removeProperty('line-height');
		el.style.removeProperty('letter-spacing');
		el.style.removeProperty('text-align');
		el.style.removeProperty('color');
	});

	let close_btn = document.querySelector(`#popup-container .popup_close`);
	close_btn.addEventListener('click', function () {
		document.querySelector('#popup-container').remove();
	});

	document.getElementById(id_close).addEventListener('change', function(){
		if (this.checked) {
			setPopup_close(id_close,data.popup_idx);
		} else {
			localStorage.removeItem(`popup_close_${data.popup_idx}`);
		}
	});

	let do_not_open = $('.do_not_open span');
	if (do_not_open != null) {
		do_not_open.click(function() {
			$('.do_not_open input[type="checkbox"]').click();
		});
	}

	setPopup_resize(data.width,data.height);
}

function setPopup_close(popup_type,popup_idx){
	let key = `popup_close_${popup_idx}`;
	let param_day = 0;
	if(popup_type == 'tday'){
		param_day = 1;
	}
	else{
		param_day = 9999;
	}

	var date = new Date();
	date = date.setTime(date.getTime() + param_day * 24 * 60 * 60 * 1000);
	localStorage.setItem(key, date);
}

function setPopup_resize(width,height) {
	let windowWidth		= window.innerWidth;
	let popup__wrap		= document.querySelector('.popup__wrap');
	let popup_header	= document.querySelector('.popup_header');
	let popup_body		= document.querySelector('.popup_body');
	
	popup__wrap.style.removeProperty('width');
	popup__wrap.style.removeProperty('height');
	popup_header.style.removeProperty('width');
	popup_header.style.removeProperty('height');
	popup_body.style.removeProperty('width');
	popup_body.style.removeProperty('height');
	
	let header_width = popup_header.clientWidth;
	let body_width = popup_body.clientWidth;

	let contents_width = header_width>body_width?header_width:body_width;
	let contents_height = 0;

	if(windowWidth > 1024){
		popup__wrap.style.width = `${width}px`;
		popup__wrap.style.height = `${height}px`;
	} else {
		if (contents_width > windowWidth - 100) {
			popup_header.style.width = `${windowWidth - 100}px`;
			popup_body.style.width = `${windowWidth - 100}px`;
		}
	
		contents_height = popup_header.clientHeight + popup_body.clientHeight;
		if (contents_height > 350) {
			popup_body.style.height = `${350 - popup_header.clientHeight}px`;
		}
	}
}

function check_dev() {
    function detect_dev(allow = 100) {
        let start = +new Date();
        //debugger;
        let end = +new Date();

        if (isNaN(start) || isNaN(end) || end - start > allow) {
            //console.log('개발자 도구 사용이 금지되어 있습니다.');
        }
    }

    function addEventListeners() {
        detect_dev(); // 초기 실행
        window.addEventListener('resize', () => detect_dev());
        window.addEventListener('mousemove', () => detect_dev());
        window.addEventListener('focus', () => detect_dev());
        window.addEventListener('blur', () => detect_dev());
    }

    if (document.readyState === "complete" || document.readyState === "interactive") {
        addEventListeners();
    } else {
        window.addEventListener('DOMContentLoaded', addEventListeners);
    }
}

function en_number_format(number, decimals = 1) {
    return number.toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}