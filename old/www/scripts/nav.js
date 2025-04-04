var headerSwiperArr = new Array();

let clickPosition = 0;
let prevPosition = 0;
let lrgInterval = 0;

var midClassPosition = 0;
var topClassPosition = 0;
var ulInterval = 0;

const getMenuListApi = () => {
	let country = getLanguage();
	let menu_type = getUrlParamValue('menu_type');
	let menu_idx = getUrlParamValue('menu_idx');

	$.ajax({
		type: "post",
		url: api_location + "menu/get",
		data: {
			"country": country,
			"menu_type": menu_type,
			"menu_idx": menu_idx,
		},
		dataType: "json",
		async: false,
		error: function (e) {
			alert("메뉴 리스트를 불러오는데 실패 하였습니다.");
		},
		success: function (d) {
			webWriteNavHtml(d);
			mobileWriteNavHtml(d);
			disableUrlBtn();

			const sidebar = new Sidebar();
		}
	});
}

const webWriteNavHtml = (d) => {
	let menu_info = d.data.menu_info;
	let posting_story = d.data.posting_story;

	let member_info = d.member_info

	let whishCnt = member_info?.whish_cnt;
	let basketCnt = member_info?.basket_cnt;

	let menuList = document.createElement("ul")
	menuList.classList.add("header__grid");

	let menuHtml = "";
	menuHtml = '<li class="first__space"></li>';

	let domfrag = document.createDocumentFragment(menuList);
	domfrag.appendChild(menuList)

	let colaboImg = ["/sample/colabo1.png", "/sample/colabo2.png", "/sample/colabo3.png", "/sample/colabo4.png", "/sample/colabo5.png"];

	let userName = member_info != null ? member_info.member_name : "MY";

	menuHtml += `
		<li class="header__logo" onClick="location.href='/'">
			<img class="logo"src="/images/landing/logo.png" alt="">
		</li>
		<li class="header__menu">
			<ul class="hover_bg_act menu__wrap left">
	`;

	let menu_num = 1;
	menu_info.forEach((el, idx) => {
		let parent_selected = "";
		if (el.parent_flg == true) {
			parent_selected = "select";
		}

		let segment_link = el.menu_link;

		let segment_click = "";
		if (segment_link.length > 0) {
			segment_click = `onClick="location.href='${segment_link}'"`;
		}

		let menu_hl1 = el.menu_hl1;

		if (menu_num <= 5) {
			menuHtml += `
				<li class="drop web" data-lrg="${idx}">
					<div class="menu-ul lrg ${parent_selected}" ${segment_click}>
						${el.menu_title}
					</div>
					<div class="drop__menu">
						<ul class="cont pr__menu">
			`;

			let menu_slide = el.menu_slide;
			if (menu_slide != null && menu_slide.length > 0) {
				menuHtml += `
							<li class="swiper-li">
								<div class="swiper swiper__box" data-id="${idx}" id="menuSwiper${idx}">
									<div class="swiper-wrapper">
				`;

				menu_slide.forEach((slide_el, idx) => {
					let slide_link = slide_el.slide_link;

					let slide_click = "";
					if (slide_link.length > 0) {
						slide_click = `onClick="location.href='${slide_link}'"`;
					}

					menuHtml += `
										<div class="swiper-slide" data-title="${slide_el.slide_title}" ${slide_click}>
											<div>
												<img src="${cdn_img}${slide_el.img_location}" alt="" style="margin-left: auto;margin-right: auto;">
											</div>
										</div>
					`;
				});

				menuHtml += `
									</div>
									
									<div class="swiper__title"></div>
									<div class="swiper-pagination swiper-pagination-${idx}"></div>
								</div>  
							</li>
				`;
			}

			if (menu_hl1 != null && menu_hl1.length > 0) {
				menu_hl1.forEach((hl1_el, idx) => {
					let hl1_link = hl1_el.menu_link;

					let hl1_click = "";
					if (hl1_link.length > 0) {
						hl1_click = `onClick="location.href='${hl1_link}'"`;
					}

					menuHtml += `
							<li data-mdl="${idx}">
								<a class="mid-a menu-ul" ${hl1_click}>${hl1_el.menu_title}</a>
								<ul class="sma__wrap">
					`;

					let menu_hl2 = hl1_el.menu_hl2;
					if (menu_hl2 != null && menu_hl2.length > 0) {
						menu_hl2.forEach((hl2_el, idx) => {
							let hl2_link = hl2_el.menu_link;

							let hl2_click = "";
							if (hl2_link.length > 0) {
								hl2_click = `onClick="location.href='${hl2_link}'"`;
							}

							menuHtml += `
									<li><a class="menu-ul sml" ${hl2_click}>${hl2_el.menu_title}</a></li>
							`;
						});
					}
					menuHtml += `
								</ul>
							</li>
					`;
				});
			}
			menuHtml += `
						</ul>
					</div>
				</li>
			`;
		} else {
			let segment_link = el.menu_link;

			let segment_click = "";
			if (segment_link.length > 0) {
				segment_click = `onClick="location.href='${segment_link}'"`;
			}

			menuHtml += `
				<li class="drop web" data-lrg="${idx}">
					<div class="menu-ul lrg" ${segment_click}>${el.menu_title}</div>
					<div class="drop__menu">
						<ul class="cont po__menu">
							<li></li>
							<li>
								<ul class="po__cont">
									${menu_hl1.map((el, idx) => {
				return `<li class="pobox"  data-mdl="${idx}">
											<div class="colaboBox">
												<a href="${el.menu_link}">
													<div class="mid-a menu-ul" href="${el.menu_link}">${el.menu_title}</div>
													<img src='${cdn_img}/${el.img_location}'>
												</a>
											</div>
										</li>`
			}).join("")
				}
									<li class="pobox all-view" onclick="location.href="/posting/collaboration">
										<div class="menu-ul-container">
											<a href="/posting/collaboration" class="menu-ul-col t1" data-i18n="lm_view_collaborations_01"></a>
											<a href="/posting/collaboration" class="menu-ul-col t2" data-i18n="lm_view_collaborations_02"></a>
										</div>
									</li>
								</ul>
							</li>
							<li></li>
						</ul>
					</div>
				</li>
			`;
		}

		menu_num++;
	});

	menuHtml += `
			</ul>
			<ul class="menu__wrap right">`

	let storyHtml = webPostingStoryHtml(posting_story);
	menuHtml += storyHtml;
	let ln = localStorage.getItem('lang') || getLanguage();
	menuHtml += `
				<li class="drop web search_shop" >
					<a class="menu-ul lrg" href="/search/shop" data-i18n="m_stockist">매장찾기</a>
				</li>
				<li class="web bluemark__btn side-bar" data-type="M">
					<div class="bluemark__icon lrg">
						<div class="bluebox"></div>
						<div class="text">Bluemark</div>
					</div>
				</li>
				<li class="web alg__c side-bar" data-type="E">
					<div class="language_icon">
						<div class="language-bk"></div>
						<div class="language-text">${ln}</div>
					</div>
				</li>
				<li class="web search__li side-bar" data-type="S">					
					<img class="search-svg" style="height: 14px; width: 14px;" src="/images/svg/search.svg" alt="">
				</li>
				<li class="flex wishlist__btn" data-cnt="${whishCnt === undefined ? "" : whishCnt}"  data-type="W"><img class="wishlist-svg" style="height:14px; width: 16px;" src="/images/svg/wishlist.svg" alt=""><span class="wish count"></span></li>
				<li class="flex basket__btn side-bar" data-cnt="${basketCnt === undefined ? "" : basketCnt}" data-type="B"><img class="basket-svg" style="width: 10px; height: 14px;" src="/images/svg/basket.svg" alt=""><span class="basket count"></span></li>
				${member_info != null ?
			`<li class="web alg__r login__wrap mypage__icon side-bar" data-type="L">
						<img class="user-svg" style="height:14px" src="/images/svg/user-bk.svg" alt="">
						<span>` + userName + `</span>
					</li>` :
			`<li class="web alg__r login__wrap mypage__icon side-bar" data-type="L">
						<img class="user-svg" style="height:14px" src="/images/svg/user-bk.svg" alt="">
						<span>MY</span>
					</li>`}
				
				<li class="flex pr-3 lg:hidden mobileMenu">
					<div class="hamburger" id="hamburger">
						<div class="line"></div>
						<div class="line"></div>
						<div class="line"></div>
						<div class="line"></div>
					</div>
				</li>
	`;

	menuHtml += `
			</ul>
		</li>
	`;

	menuList.innerHTML = menuHtml;
	document.querySelector(".header__wrap").appendChild(domfrag);

	if (!checkMobileDevice()) {
		let hoverBgActElements = document.querySelectorAll(".hover_bg_act");
		let dropMenuElements = document.querySelectorAll(".drop__menu");

		hoverBgActElements.forEach(function (hoverBgActElement) {
			hoverBgActElement.addEventListener('mouseover', function () {
				headerHover(true);
				if (document.body.classList.contains('sidebar_open')) {
					document.querySelector('#sidebar .sidebar-close-btn').click();
				}
			});
		});

		document.addEventListener('mouseover', function (event) {
			if (!event.target.closest('.hover_bg_act')) {
				headerHover(false);
			}
			dropMenuElements.forEach(function (element) {
				if (window.getComputedStyle(element).display === 'block') {
					headerHover(true);
				}
			});
		});
	}

	mobileMenu();

	$(".drop.web").hover(function () {
		var showRate = 200;

		let idx = $(this).attr("data-lrg");

		$(".drop__menu").each(function (index, item) {
			if ($(item).css("display") == "block") {
				$(item).fadeOut(0);
				showRate = 0;
			}
		});
		$(this).find(".drop__menu").fadeIn(showRate);

		$(this).find(".drop__menu").data("wasVisible", true);

		$(".drop__menu").each(function (index, item) {
			if ($(item).css("display") == "block") {
				headerHover(true);
				return false;
			}
		});

		new Swiper(".swiper__box", {
			observer: true,
			observeParents: true,
			pagination: {
				el: ".swiper-pagination",
				dynamicBullets: true
			},
			autoplay: {
				delay: 5000,
				disableOnInteraction: false,
			}
		});

	}, function () {
		var that = $(this);

		$(".header__wrap").mouseleave(function () {
			that.find(".drop__menu").fadeOut(100, function () {
				if (that.find(".drop__menu").data("wasVisible") && $(this).css("display") == "none") {
					$("#dimmer").removeClass("show");
				}
				that.find(".drop__menu").data("wasVisible", false);

				var anyMenuVisible = false;
				$(".drop__menu").each(function (index, item) {
					if ($(item).css("display") == "block") {
						anyMenuVisible = true;
						return false;
					}
				});
				if (!anyMenuVisible) {
					headerHover(false);
				}
			});
		});
	});

	/*
	$$webMenu.forEach(el => {
		if(el.dataset.type=="PR" || el.dataset.type=="PO" || el.dataset.type=="FM"){
			el.addEventListener("mouseover", function(e) {
				headerHover(true);
			});
		}
	});
	$$webMenu.forEach(el => {
		el.addEventListener("mouseout", function(e) {
			if(this.dataset.type=="PR" || this.dataset.type=="PO" || el.dataset.type=="FM" ){
				headerHover(false);
			}
		});
	});
	*/

	// let lrgLang = ["m_trending", "m_men", "m_women", "m_life_style", "m_collaboration", "m_story", "m_stockist"];
	// let menu_lrg = document.querySelectorAll(".drop.web .menu-ul.lrg");
	// for(let i = 0; i < menu_lrg.length; i++) {
	// 	menu_lrg[i].dataset.i18n = lrgLang[i];
	// 	menu_lrg[i].textContent = i18next.t(lrgLang[i]);
	// }
	// changeLanguageR();
}

const webPostingStoryHtml = (d) => {
	let archive_img = d.archive_img;
	let column_NEW = d.column_NEW;
	let column_COLC = d.column_COLC;
	let column_RNWY = d.column_RNWY;
	let column_EDTL = d.column_EDTL;

	let isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

	let size_type = "W";
	if (isMobile == true) {
		size_type = "M";
	}

	let storyHtml = "";
	storyHtml += `
				<li class="hover_bg_act drop web story" data-type="ST" data-large="6">
					<div class="menu-ul lrg" href="#" data-i18n="m_story">스토리</div>
					<div class="drop__menu">
						<ul class="cont st__menu">
							<li></li>
							<li>
								<ul class="st__cont">
									<li>
										<a href="#" class="menu-ul" data-i18n="lm_latest_news"></a>
										<ul class="list__grid">
	`;

	column_NEW.forEach(function (row_NEW) {
		storyHtml += `
											<li class="st__box" onClick="location.href='${row_NEW.page_url}'">
												<div class="newsBox">
													<img src ='${cdn_img + row_NEW.img_location}'>
													<div class="news-title kr" href="">${xssDecode(row_NEW.story_title)}</div>
													<div class="news-m-title en" href="">${xssDecode(row_NEW.story_sub_title)}</div>
												</div>
											</li>
		`;
	});

	storyHtml += `
										</ul>
									</li>
									<li>
										<a href="/story/main" class="menu-ul" data-i18n="lm_archive"></a>
										<ul class="list__grid list__archive">
	`;
	
	archive_img.forEach(function (row_img){
		let archive_type = row_img.archive_type;
		
		let archive_name = null;
		let archive_location = null;
		
		if (archive_type == "COLC") {
			archive_name = "lm_collection";
			archive_location = "/posting/collection";
		} else if (archive_type == "EDTL") {
			archive_name = "lm_editorial";
			archive_location = "/posting/editorial";
		}
		
		storyHtml += `
											<li class="st__box">
												<div class="mid-a archiveTitle">
													<a href="${archive_location}" class="menu-ul" data-i18n="${archive_name}"></a>
												</div>
												<div class="archiveBox">
													<div>
														<div class="img_archive" style="background-image:url(${cdn_img}${row_img.img_location});"></div>
													</div>
												</div>
											</li>
		`;
	});
	
	storyHtml += `
										</ul>
									</li>
								</ul>
							</li>
							<li></li>
						</ul>
					</div>
				</li>
	`;

	/* 20231109 윤재은 - 스토리 수정 */

	/* column_COLC.forEach(function (row_COLC) {
		storyHtml += `
														<li class="archiveList link" onClick="location.href='${row_COLC.page_url}'">${row_COLC.story_title}</li>
		`;
	});

	storyHtml += `
													</ul>
													<ul>
														<li class="archiveList dot"></li>
														<li class="archiveList allBtn"><a href="/posting/collection" class="menu-ul"><span>+</span><span data-i18n="lm_view_all"></span></a></li>
													</ul>
												</div>
											</li>
											<li class="st__box" data-mdl="">
												<div class="mid-a archiveTitle"><a href="/posting/editorial" class="menu-ul" data-i18n="lm_editorial"></a></div>
												<div class="archiveBox">
													<ul>
	`;
	*/

	/*
											<li class="st__box"  data-mdl="">
												<div class="mid-a archiveTitle"><a href="/posting/runway" class="menu-ul">런웨이</a></div>
												<div class="archiveBox">
													<ul>
	`;

	column_RNWY.forEach(function (row_RNWY) {
		storyHtml += `
														<li class="archiveList link" onClick="location.href='${row_RNWY.page_url}&size_type=${size_type}'">${row_RNWY.story_title}</li>
		`;
	});

	storyHtml += `
													</ul>
													<ul>
														<li class="archiveList dot"></li>
														<li class="archiveList allBtn"><a href="/posting/runway" class="menu-ul" data-i18n="lm_view_all">+  전체보기</a></li>
													</ul>
												</div>
											</li>

											<li class="st__box"  data-mdl="">
												<div class="mid-a archiveTitle"><a href="/posting/editorial" class="menu-ul" data-i18n="lm_editorial">에디토리얼</a></div>
												<div class="archiveBox">
													<ul>
	
	`;
	*/

	/* 20231109 윤재은 - 스토리 수정 */
	/* column_EDTL.forEach(function (row_EDTL) {
		storyHtml += `
														<li class="archiveList link" onClick="location.href='${row_EDTL.page_url}&size_type=${size_type}'">${row_EDTL.story_title}</li>
		`;
	});


	storyHtml += `
														</ul>
														<ul>
															<li class="archiveList dot"></li>
															<li class="archiveList allBtn"><a href="/posting/editorial" class="menu-ul"><span>+</span><span data-i18n="lm_view_all"></span></a></li>
														</ul>
													</div>
												</li>
											</ul>
										</li>
									</ul>
								</li>
								<li></li>
							</ul>
						</div>
					</li>
		`;
	*/

	return storyHtml;
}

const mobileWriteNavHtml = (d) => {
	let menu_info = d.data.menu_info;
	let posting_story = d.data.posting_story;

	let member_info = d.member_info
	let userName = member_info != null ? member_info.member_name : "로그인";

	let mobileMenu = document.createElement("div");
	mobileMenu.classList.add("mobile__menu");
	let domfrag = document.createDocumentFragment(mobileMenu);
	domfrag.appendChild(mobileMenu);
	let colaboImg = ["/sample/colabo1.png", "/sample/colabo2.png", "/sample/colabo3.png", "/sample/colabo4.png", "/sample/colabo5.png"];
	let menuHtml =
		`<ul class="top">`;

	let menu_num = 1;
	menu_info.forEach((el, idx) => {
		let menu_hl1 = el.menu_hl1;
		let menu_slide = el.menu_slide;

		if (menu_num <= 5) {
			menuHtml += `
				<li class="lrg" data-lrg="${idx}">
					<div class="lrg__back__btn"></div>
					<div class="lrg__title">${el.menu_title}</div>
					<div class="mdlBox">
						<ul class="mdl">
							${menu_hl1.map((el, idx) => {
				return `<a class="mdl__title"  href="${el.menu_link}">${el.menu_title}</a>`
			}).join("")
				}
							<li class="swiper-li">
								<div class="swiper m__swiper__box" data-id="${idx}" id="mobileMenuSwiper${idx}">
										<div class="swiper-wrapper">
											${menu_slide.map((el, idx) => {
					let slide_link = el.slide_link;

					let slide_click = "";
					if (slide_link.length > 0) {
						slide_click = `onClick="location.href='${slide_link}'"`;
					}

					return `<div class="swiper-slide" data-title="${el.slide_title}" ${slide_click}>
																		<div>
																			<img src="${cdn_img}${el.img_location}" alt="" style="max-height:110px;max-width:110px;">
																		</div>
																	</div>`
				}).join("")
				}
										</div>
										<div class="swiper-pagination swiper-pagination-${idx}"></div>
									</div>  
								<div class="swiper__title"></div>
							</li>
						</ul>
					</div>
				</li>
			`;
		} else {
			menuHtml += `
				<li class="lrg" data-lrg="${idx}">
					<div class="lrg__back__btn"></div>
					<div class="lrg__title">${el.menu_title}</div>
					<div class="mdlBox">
						<div class="mdl collaboration">
							${menu_hl1.map((el, idx) => {
				return `<a class="mdl__title po__wrap" href="${el.menu_link}">
														<img src='${cdn_img}/${el.img_location}' class="po__image">
														<div class="po__title">${el.menu_title}</div>
													</a>`
			}).join("")
				}
							<a class="mdl__title po__wrap view__total" href="/posting/collaboration">
								<div style="width:50px">
									<img src="/images/svg/plus-bk.svg" style="width:12px;margin:4px auto;" alt="">
								</div>
								<div class="po__title__all" data-i18n="lm_view_collaborations"></div>
							</a>
						</div>
					</div>
				</li>
			`;
		}

		menu_num++;
	});
	menuHtml += `</ul>`;

	let storyHtml = mobilePostingStoryHtml(posting_story);
	menuHtml += storyHtml;
	let logoutDiv = '';
	let ln = localStorage.getItem('lang') || getLanguage();

	if (getLoginStatus() == 'true') {
		logoutDiv = `<li class="flex logout"><span data-i18n="m_logout"></span></li>`;
	}
	menuHtml += `
			<ul class="bottom">
			<li class="flex mobile__mypage__btn">
					${member_info != null ? `<img src="/images/svg/user-bk.svg" style="width:14px" alt=""><span>${userName}</span>` : `<img src="/images/svg/user-bk.svg" style="width:14px" alt=""><span data-i18n="m_login"></span>`}
				</li>
				<li class="mobile-search-wrap">
					<div class="mobile__search__btn lrg__title non_underline"><img src="/images/svg/search-bk.svg" style="width:14px" alt=""><span data-i18n="ss_search"></span></div>
				</li>
				<li class="flex customer">
					<div class="mobile__customer__btn lrg__title non_underline"><img src="/images/svg/customer-bk.svg" style="width:14px" alt=""><span data-i18n="lm_customer_care_service"></span></div>
				</li>
				<li class="flex bluemark">
					<div class="mobile__bluemark__btn"><div class="bluemark-icon"></div><span>Bluemark</span></div>
				</li>
				<li class="flex language">
					<div class="mobile__language__btn"><div>${ln}</div><span>Language</span></div>
				</li>
				${logoutDiv}
			</ul>
			<div class="mobile__mypage">
                <div class="mypage__back__btn"></div>
                <div class="mypage__cont"></div>
            </div>
			<div class="mobile__search">
				<div class="search__back__btn"></div>
				<div class="search__cont"></div>
			</div>
			<div class="mobile__bluemark">
				<div class="bluemark__back__btn"></div>
				<div class="mobile__bluemrk__wrap">
					<div class="mobile__bluemark__title">
						<div class="bluemark-icon"></div><span>Bluemark</span>
					</div>
					<div class="mobile__bluemark__description">
						<p data-i18n="my_b_bluemark_info_01"></p>
						<p data-i18n="my_b_bluemark_info_02"></p>
					</div>
					<div class="mobile__bluemark__btn__wrap">
						<div class="bluemark__btn__certify" data-i18n="lm_verify_blue_mark" onclick="location.href = '/login?r_url=/mypage?mypage_type=bluemark_verify'"></div>
						<div class="bluemark__btn__list" data-i18n="lm_verification_history" onclick="location.href = '/login?r_url=/mypage?mypage_type=bluemark_list'"></div>
					</div>
				</div>
			</div>
			<div class="mobile__language">
				<div class="language__back__btn"></div>
				<div class="mobile__language__wrap">
					<div class="mobile__language__title" data-i18n="lm_choose_language"></div>
					<div class="mobile__language__description">
						<p data-i18n="lm_menu_lang_msg_01"></p>
						<p data-i18n="lm_menu_lang_msg_02"></p>
					</div>
					<div class="mobile__language__btn__wrap">
						<div class="language__btn__kr" data-ln='KR'>한국어</div>
						<div class="language__btn__en" data-ln='EN'>English</div>
						<div class="language__btn__cn" data-ln='CN'>中文</div>
					</div>
				</div>
			</div>
			<div class="mobile__customer">
				<div class="customer__back__btn"></div>
				<div class="mobile__customer__wrap">
					<div class="mobile__customer__title" data-i18n="lm_customer_care_service"></div>
					<div class="mobile__customer__btn__wrap">
						<div class="customer__btn__service" data-i18n="lm_notice" onclick="location.href='/login/service'"></div>
						<div class="customer__btn__faq" data-i18n="lm_faq" onclick="location.href='/login/faq'"></div>
						<div class="customer__btn__inquiry" data-i18n="lm_inquiry" onclick="location.href = '/login?r_url=/mypage?mypage_type=inquiry'"></div>
					</div>
				</div>
			</div>
	`;
	mobileMenu.innerHTML = menuHtml;
	document.querySelector(".side__menu").appendChild(domfrag);


	midClassPos = $('.mid').eq(0).position();
	topClassPos = $('.top').eq(0).position();
	clickPos = $('.top .lrg').eq(1).position();
	prevPos = $('.top .lrg').eq(0).position();

	midClassPosTop = 0;
	topClassPosTop = 0;
	clickPosTop = 0;
	prevPosTop = 0;

	if (typeof (midClassPos) == 'object') {
		midClassPosTop = midClassPos.top;
	}
	if (typeof (topClassPos) == 'object') {
		topClassPosTop = topClassPos.top;
	}
	if (typeof (clickPos) == 'object') {
		clickPosTop = clickPos.top;
	}
	if (typeof (prevPos) == 'object') {
		prevPosTop = prevPos.top;
	}

	ulInterval = midClassPosTop - topClassPosTop;
	lrgInterval = clickPosTop - prevPosTop;

	// let lrgLang = ["m_trending", "m_men", "m_women", "m_life_style", "m_collaboration", "m_story"];
	// let menu_lrg = document.querySelectorAll(".lrg .lrg__title");
	// for(let i = 0; i < menu_lrg.length; i++) {
	// 	menu_lrg[i].dataset.i18n = lrgLang[i];
	// 	menu_lrg[i].textContent = i18next.t(lrgLang[i]);
	// }
	// changeLanguageR();

	menuLrgClick();
	logoutClick();
}

const mobilePostingStoryHtml = (d) => {
	let column_NEW = d.column_NEW;
	let column_COLC = d.column_COLC;
	let column_RNWY = d.column_RNWY;
	let column_EDTL = d.column_EDTL;

	let storyHtml = `
			<ul class="mid">`;

	storyHtml += `
				<li class="lrg" data-lrg="6" data-type="ST">
					<div class="lrg__back__btn"></div>
					<div class="lrg__title non_underline"><span data-i18n="m_story">스토리</span></div>
					<div class="mdlBox">
						<ul class="mdl">
							<li>
								<div class="sub__title" data-i18n="lm_latest_news"></div>
								<ul class="list__grid">
	`;

	column_NEW.forEach(function (row_NEW) {
		storyHtml += `
									<li class="st__box" onClick="location.href='${row_NEW.page_url}'">
										<div class="newsBox">
											<img src ='${cdn_img + row_NEW.img_location}'>
											<div class="news-title-wrap">
												<div class="news-title" href="">${xssDecode(row_NEW.story_title)}</div>
												<div class="news-m-title" href="">${xssDecode(row_NEW.story_sub_title)}</div>
											</div>
										</div>
									</li>
		`;
	});

	storyHtml += `
								</ul>
							</li>
							<li class="div__line">
							</li>
							<li>
								<div class="sub__title title__archive"><a href = "/story/main" data-i18n="lm_archive"></a></div>
								<ul class="list__grid list__archive">
									<li class="st__box">
										<div class="archiveBox">
											<div>
												<div style="background-color: #C3C7CD; width: 100px; height: 125px;"></div>
											</div>
										</div>
										<div class="mid-a archiveTitle" data-i18n="lm_collection" onclick="location.href='/posting/collection'"></div>
									</li>
									<li class="st__box">
										<div class="archiveBox">
											<div>
												<div style="background-color: #C3C7CD; width: 100px; height: 125px;"></div>
											</div>
										</div>
										<div class="mid-a archiveTitle" data-i18n="lm_editorial" onclick="location.href='/posting/editorial'"></div>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</li>
				<li class="mobile-store-search-wrap"><span data-i18n="m_stockist" onclick="location.href='/search/shop'">매장찾기</span></li>
			</ul>
	`;
	/* 20231109 윤재은 - 스토리 수정 */
	/*
	column_COLC.forEach(function (row_COLC) {
		storyHtml += `
												<li class="archiveList" onclick="location.href='${row_COLC.page_url}'">${row_COLC.story_title}</li>
		`;
	});
	

	storyHtml += `
											</ul>
										</div>
									</li>
									<li class="div__line">
									</li>
									<li class="st__box">
										<div class="mid-a archiveTitle" data-i18n="lm_editorial" onclick="location.href='/posting/editorial'"></div>
										<div class="archiveBox">
											<ul>
	`;
	*/

	/*
									<li class="st__box">
										<div class="mid-a archiveTitle" onclick="location.href='/posting/runway'">런웨이</div>
										<div class="archiveBox">
											<ul>
	`;

	column_RNWY.forEach(function (row_RNWY) {
		storyHtml += `
												<li class="archiveList" onClick="location.href='${row_RNWY.page_url}'">${row_RNWY.story_title}</li>
		`;
	});

	storyHtml += `
											</ul>
										</div>
									</li>
									<li class="div__line">
									</li>
									<li class="st__box">
										<div class="mid-a archiveTitle" data-i18n="lm_editorial" onclick="location.href='/posting/editorial'" data-i18n="lm_editorial">에디토리얼</div>
										<div class="archiveBox">
											<ul>
	`;
	*/

	/* 20231109 윤재은 - 스토리 수정 */
	/*
	column_EDTL.forEach(function (row_EDTL) {
		storyHtml += `
												<li class="archiveList" onclick="location.href='${row_EDTL.page_url}&size_type=M'">${row_EDTL.story_title}</li>
		`;
	});
	

	storyHtml += `
											</ul>
										</div>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</li>
				<li class="mobile-store-search-wrap"><span data-i18n="m_stockist" onclick="location.href='/search/shop'">매장찾기</span></li>
			</ul>
	`;
	*/

	return storyHtml;
}

function mobileMdlSwipe(obj) {
	const $$swiperBox = document.querySelectorAll(".m__swiper__box");
	$$swiperBox.forEach((el, idx) => {
		let mobileMenuSwiper = new Swiper(`#mobileMenuSwiper${idx}`, {
			observer: true,
			observeParents: true,
			slidesPerView: 1,
			pagination: {
				el: ".swiper-li .swiper-pagination-" + idx,
				dynamicBullets: true
			},
			autoplay: {
				delay: 5000,
				disableOnInteraction: true
			}
		});
		var swiper__box = $(obj).next().find(".m__swiper__box");
		var swiper_title_obj = $(swiper__box).parent().find(".swiper__title");
		var titleArr = new Array();
		$(swiper__box).find(".swiper-slide").each(function (idx, el) {
			titleArr.push($(el).attr("data-title"));
		});
		if (titleArr.length > 0) {
			$(swiper_title_obj).html(titleArr[0]);
		}
		if (titleArr.length == 1) {
			$(".swiper-pagination-" + idx).hide();
		}
		mobileMenuSwiper.on('slideChange', function () {
			$(swiper_title_obj).html(titleArr[mobileMenuSwiper.activeIndex]);
		})
	});
}
/*모바일 관련*/
const menuLrgClick = () => {
	$(".mobile__menu .lrg__title").click(function () {
		let mdlBox_obj = $(this).siblings(".mdlBox");
		let lrg__back__btn_obj = $(this).siblings(".lrg__back__btn");

		if ($(mdlBox_obj).css("display") != "block") {
			$(this).closest(".side__menu").addClass("lrg__on");
			let lrg_idx = $(this).parent().attr("data-lrg") - 1;
			let lrg_type = $(this).parent().attr("data-type");
			/*
			$(".mobile__menu .lrg").each(function(idx,el){
				if($(el).attr("data-lrg") < lrg_idx){
					$(el).hide();
				}
				else{
					$(el).show();
				}
			});
			*/
			$(".mobile__menu .lrg__title").removeClass("open");
			$(".mobile__menu .lrg__back__btn").removeClass("open");
			if (lrg_type == "ST") {
				$(".mobile-store-search-wrap").hide();
				$(".bottom").hide();

				$(".top").hide();
			}
			$(this).addClass("open");
			$(lrg__back__btn_obj).addClass("open");
			//상세화면 모두 닫고, 해당하는 상세화면만 열기
			$(".mdlBox").slideUp(750);
			$(mdlBox_obj).slideDown(750);
			mobileMdlSwipe(this);

			if (lrg_type == "ST") {
				$("#mobile .side__menu").animate({ scrollTop: ulInterval }, 0);
			}
			else {
				let clickIdx = $(this).parent().index();

				if (clickIdx <= 1) {
					$("#mobile .side__menu").animate({ scrollTop: 0 }, 400);
				}
				else {
					$("#mobile .side__menu").animate({ scrollTop: lrgInterval * (clickIdx - 1) }, 400);
				}
			}

		}
	});
	$(".mobile__menu .lrg__back__btn").click(function () {
		$(".mdlBox").slideUp(500);
		$(".mobile__menu .lrg__title").removeClass("open");
		$(".mobile__menu .lrg__back__btn").removeClass("open");
		$(this).closest(".side__menu").removeClass("lrg__on");
		$(".mobile-store-search-wrap").show();
		$(".top").show();
		$(".bottom").show();
		$(".mobile__menu .lrg").slideDown(500);
		$("#mobile .side__menu").animate({ scrollTop: 0 }, 500);
	});
	$('.mobile__mypage__btn').click(function () {
		let urlParts = location.href.split('?')[0].split('/');
		let path = '/' + urlParts.slice(3).join('/');
		if (path != '/mypage') {
			let user = new User();
			user.mobileUserLoad();
			$(".top, .mid, .bottom").slideUp(500);
			setTimeout(function () {
				$(".mobile__mypage").show()
			}, 400);
			$(this).closest(".side__menu").addClass("lrg__on");
			$(".mobile__mypage .mypage__back__btn").addClass("open");
		}
	})
	$(".mobile__mypage .mypage__back__btn").click(function () {
		setTimeout(function () {
			$(".mobile__mypage").hide()
		}, 100);
		$(".top, .mid, .bottom").slideDown(500);
		$(this).closest(".side__menu").removeClass("lrg__on");
		$(".mobile__mypage .mypage__back__btn").removeClass("open");
	})
	$(".mobile__search__btn").click(function () {
		$(".top, .mid, .bottom").slideUp(500);
		$(".mobile__search").slideDown(500);
		$(this).closest(".side__menu").addClass("lrg__on");
		$(".mobile__search .search__back__btn").addClass("open");
	});
	$(".mobile__search .search__back__btn").click(function () {
		$(".mobile__search").slideUp(500);
		$(".top, .mid, .bottom").slideDown(500);
		$(this).closest(".side__menu").removeClass("lrg__on");
		$(".mobile__search .search__back__btn").removeClass("open");
	});

	$(".mobile__bluemark__btn").click(function () {
		$(".top, .mid, .bottom").slideUp(500);
		$(".mobile__bluemark").slideDown(500);
		$(this).closest(".side__menu").addClass("lrg__on");
		$(".mobile__bluemark .bluemark__back__btn").addClass("open");
	})
	$(".mobile__bluemark .bluemark__back__btn").click(function () {
		$(".mobile__bluemark").slideUp(500);
		$(".top, .mid, .bottom").slideDown(500);
		$(this).closest(".side__menu").removeClass("lrg__on");
		$(".mobile__bluemark .bluemark__back__btn").removeClass("open");
	});

	$(".mobile__language__btn").click(function () {
		$(".top, .mid, .bottom").slideUp(500);
		$(".mobile__language").slideDown(500);
		$(this).closest(".side__menu").addClass("lrg__on");
		$(".mobile__language .language__back__btn").addClass("open");
		changeLangEvent('.mobile__language__btn__wrap');
	})
	$(".mobile__language .language__back__btn").click(function () {
		$(".mobile__language").slideUp(500);
		$(".top, .mid, .bottom").slideDown(500);
		$(this).closest(".side__menu").removeClass("lrg__on");
		$(".mobile__language .language__back__btn").removeClass("open");
	});

	$(".mobile__customer__btn").click(function () {
		$(".top, .mid, .bottom").slideUp(500);
		$(".mobile__customer").slideDown(500);
		$(this).closest(".side__menu").addClass("lrg__on");
		$(".mobile__customer .customer__back__btn").addClass("open");
	})
	$(".mobile__customer .customer__back__btn").click(function () {
		$(".mobile__customer").slideUp(500);
		$(".top, .mid, .bottom").slideDown(500);
		$(this).closest(".side__menu").removeClass("lrg__on");
		$(".mobile__customer .customer__back__btn").removeClass("open");
	});
}
const logoutClick = () => {
	$('.flex.logout').on('click', function () {
		$('#hamburger').click();
		location.href = "/logout";
	})
}
const mobileSearch = () => {
	let mobile = document.querySelector("#mobile");
	let mobileSearchBtn = document.querySelector(".mobile__search__btn");
	let mobileMenuWrap = document.querySelector(".mobile__menu");
	let mobileSearchWrap = document.querySelector(".mobile__search");
	mobileSearchBtn.addEventListener("click", () => {
		mobileMenuWrap.style.display = "none";
		mobileSearchWrap.style.display = "block";
		mobile.classList.add('search');
	});
}

const mobileMenu = () => {
	const $body = document.querySelector("body");
	const mobileMenuBtn = document.querySelector('.mobileMenu');
	const mobileSide = document.querySelector('#mobile');
	const hamburgerBtn = document.querySelector(".hamburger");
	let header = document.querySelector("header");
	mobileMenuBtn.addEventListener('click', (ev) => {
		hamburgerBtn.classList.toggle("is-active");
		if (hamburgerBtn.classList.contains("is-active")) {
			mobileSide.classList.add('menu__on');
			$("#dimmer").addClass("show");
			header.classList.add("hover");
			$body.classList.add("m_menu_open");
		}
		else {
			mobileSide.classList.remove('menu__on');
			$("#dimmer").removeClass("show");
			header.classList.remove("hover");
			$body.classList.remove("m_menu_open");
		}
		let mobileSearch = new Search();
		mobileSearch.mobileWriteHtml();
		mobileSearch.addSearchEvent();
		//mobileSearch.searchCloseBtnEventHandler()
	});
};
function changeLangEvent(btnParrentClass) {
	let prevCountry = getLanguage();
	const languageBtnWrap = document.querySelector(btnParrentClass);
	const languageBtns = Array.from(languageBtnWrap.querySelectorAll('div'));

	let currentLang = localStorage.getItem('lang') || 'KR';

	setSelectedLanguage(languageBtns, currentLang);
	languageBtnWrap.addEventListener('click', e => {
		const clickedBtn = e.target.closest('div');
		if (!clickedBtn || !languageBtns.includes(clickedBtn)) { return; }
		let nowLang = currentLang;
		currentLang = clickedBtn.dataset.ln;

		setSelectedLanguage(languageBtns, currentLang);

		localStorage.setItem('lang', currentLang);
		$('.header__menu .side-bar .language-text').html(currentLang);
		i18next.changeLanguage(currentLang);
		// if(btnParrentClass === '.language-btn-box'){ sidebarClose();}
		//window.location.reload();
		if (getLoginStatus() == 'false') {
			window.location.reload();
		}
		else {
			logout(prevCountry);
		}
	});

	function setSelectedLanguage(btns, lang) {
		btns.forEach(btn => {
			btn.classList.toggle('select', btn.dataset.ln === lang);
		});
	}
	function logout(lang) {
		if (btnParrentClass == '.mobile__language__btn__wrap') {
			$('#hamburger').click();
			//window.location.reload();
		}
		returnLogoutPage(lang);
	}
}
function returnLogoutPage(lang) {
	makeMsgNoti(lang, 'MSG_F_INF_0018', null);
}
function windowResponsive() {
	const $body = document.querySelector("body");
	const bodyWidth = document.querySelector("body").offsetWidth;
	if (1024 <= bodyWidth) {
		$body.dataset.view = "rW"
	} else if (1024 >= bodyWidth) {
		$body.dataset.view = "rM"
	}
}
function headerHover(bl) {
	let header = document.querySelector("header");
	let sidebar = document.querySelector("#sidebar");
	let headerMenu = document.querySelector("header__menu");

	if (bl) {
		header.classList.add("hover");
		header.querySelectorAll(".under-line").forEach(els => {
			els.classList.remove("wh");
			els.classList.add("bk");
		});
		$("#dimmer").addClass("show");
	}
	else {
		if (!sidebar.classList.contains("open")) {
			header.classList.remove("hover");
			header.querySelectorAll(".under-line").forEach(els => {
				els.classList.remove("bk");
				els.classList.add("wh");
			});
			$("#dimmer").removeClass("show");
		}
	}
}
function searchInit() {
	headerHover(true);
	$(".login__wrap").addClass("-search-on");
	$(".search__motion__wrap").addClass("search-init", 500, function () {
		$(".search__close,.search__input").fadeIn(300);
		$(".search__drop").show();
	});
	$(".search-hide,.search__text").fadeOut(1, function () {
		$(".right__nav").addClass("search__style");
	});
	$(".mid__space").addClass("--hidden");
}
function searchClose() {
	$(".search__drop").hide();
	$(".search__close,.search__input").hide()
	$(".right__nav").removeClass("search__style");
	$(".login__wrap").removeClass("-search-on");
	$(".search__motion__wrap").removeClass("search-init", 500);
	$(".search-hide").fadeIn(500, function () {
		$(".search__text").fadeIn(300);
		$(".search-hide").attr("style", "");
	});
	headerHover(false);
	$(".mid__space").removeClass("--hidden");
}

function disableUrlBtn() {
	const pageUrl = new URL(document.location);
	let path = pageUrl.pathname;
	let sideBarBtn = document.querySelectorAll('.side-bar');

	//위시
	let $quickview = document.querySelector("#quickview");
	let $contentWrap = document.querySelector(".quickview__content__wrap");
	let $listBtn = document.querySelector(".btn__box.list__btn");
	let $whishlistBtn = document.querySelector(".wishlist__btn");
	let $titleBoxSpan = document.querySelector(".title__box span");
	let $titleBoxImg = document.querySelector(".title__box img");



	navWhishlistBtn();
	sideBarBtn.forEach(el => {
		el.addEventListener("click", function (ev) {
			sideBarBtn.forEach(el => el.classList.remove("open"));
			let target = this;
			// let sideBarCloseBtn = document.querySelector('.sidebar-close-btn');
			// sideBarCloseBtn.addEventListener("click",sidebarClose);
			let sideBox = document.querySelector(".side__box");
			let typeTarget = this.dataset.type;
			sideBox.innerHTML = "";
			sidebarClose();

			switch (typeTarget) {
				case 'S':
					let search = new Search();
					search.writeHtml();
					search.addSearchEvent();
					console.log('search');
					//search.searchCloseBtnEventHandler();
					break;
				case 'E':
					let language = new Language();
					language.writeHtml();
					language.addSelectEvent();
					changeLangEvent('.language-btn-box');
					break;
				case 'B':
					if (getLoginStatus() == 'false') {
						location.href = '/login?r_url=/order/basket/list';
						return
					} else {
						let hamburgerBtn = document.querySelector(".hamburger");
						let $body = document.querySelector("body");
						let mobileSide = document.querySelector('#mobile');
						let header = document.querySelector("header");

						if (hamburgerBtn.classList.contains("is-active")) {
							hamburgerBtn.classList.toggle("is-active");
							mobileSide.classList.remove('menu__on');
							$("#dimmer").removeClass("show");
							header.classList.remove("hover");
							$body.classList.remove("m_menu_open");
						}
						const basket = new Basket("basket", true);
						basket.writeHtml();
						if (path.includes("basket")) {
							sidebarClose();
							ev.stopImmediatePropagation();
							return false;
						}
					}
					break;
				case 'M':
					let bluemark = new Bluemark();
					bluemark.writeHtml();
					if (path.includes("mypage")) {
						sidebarClose();
						ev.stopImmediatePropagation();
						return false;
					}
					break;
				case 'L':
					let user = new User();
					user.userLoad();
					if (path.includes("mypage") || (path.includes("login") && !path.includes("check"))) {
						sidebarClose();
						ev.stopImmediatePropagation();
						return false;
					}
					break;
				case 'W':
					if (path.includes("whish")) {
						ev.stopImmediatePropagation();
						return false;
					}
					break;

			}
			sideBarToggleEvent(target);
		});
	})
	if (path === '/product/list') {

	}
	if (path === '/product/detail') {

		// $('.whish-btn').on("click", function(){
		// 	$('#quickview_observer').val('open');
		// 	$quickview.classList.remove("hidden");
		// 	$titleBoxSpan.innerText = "위시리스트";
		// 	$titleBoxImg.src = "/images/svg/wish-list-bk.svg";
		// 	$contentWrap.classList.add("open");
		// 	$listBtn.classList.add("select");
		// 	$whishlistBtn.classList.add("open");
		// 	setTimeout(() => {
		// 		getWhishlistProductList();
		// 	}, 100);
		// })
	}

	function sideBarToggleEvent(target) {
		const $body = document.querySelector("body");
		let sideContainer = document.querySelector("#sidebar");
		let sideBg = document.querySelector(".side__background");
		let sideWrap = document.querySelector(".side__wrap");

		if (sideContainer.classList.contains("open")) {
			sidebarClose();
		} else {
			if ($(".wishlist__btn.open").length !== 0) {
				whishlistClose();
			}
			target.classList.add("open");
			$("header").addClass("hover");
			$(".drop__menu").css('display', 'none');
			$body.classList.add("sidebar_open");
			$body.dataset.sbType = target.dataset.type;
			sideContainer.classList.add("open");
			sideContainer.classList.add(target.dataset.type);
			sideBg.classList.add("open");
			sideWrap.classList.add("open");
			headerHover(true);
		}
	}


	function sideBarRight() {
		const menu_right = document.querySelector(".menu__wrap.right > li");
		menu_right.addEventListener("click", function () {
			sideBarToggleEvent();
		})
	}


	function navWhishlistBtn() {
		let $wishlistBtn = document.querySelector(".wishlist__btn");
		if ($("body").hasScrollBar()) {
			$("#quickview .quickview__box").css("padding-right", getScrollBarWidth());
		}
		else {
			$("#quickview .quickview__box").css("padding-right", "");
		}
		$wishlistBtn.addEventListener("click", function () {
			if (this.classList.contains("open")) {
				//					whishlistClose();
				location.href = "/order/whish";
			} else {
				location.href = "/order/whish";
				// if($(".side-bar.open").length !== 0){
				// 	sidebarClose();
				// }
				// $quickview.classList.remove("hidden");
				// $titleBoxSpan.innerText = "위시리스트";
				// $titleBoxImg.src = "/images/svg/wish-list-bk.svg";
				// $contentWrap.classList.add("open");
				// $listBtn.classList.add("select");
				// getWhishlistProductList();
				// $whishlistBtn.classList.add("open");
			}
		})
	}
	function whishlistClose() {
		$quickview.classList.add("hidden");
		$contentWrap.classList.remove("open");
		$listBtn.classList.remove("select");
		$whishlistBtn.classList.remove("open");
	}
	function layoutClick() {
		let sideWrap = document.querySelector(".side__wrap");
		let sideBg = document.querySelector(".side__background");
		sideBg.addEventListener("click", (e) => {
			if (e.target == sideBg) {
				sidebarClose();
			}
		})

	}
	return {
		sidebarClose: sidebarClose
	}
}
function sidebarClose() {
	const $body = document.querySelector("body");
	let sideBarBtn = document.querySelectorAll('.side-bar');
	let sideContainer = document.querySelector("#sidebar");
	let sideBg = document.querySelector(".side__background");
	let sideWrap = document.querySelector(".side__wrap");
	sideBarBtn.forEach(el => el.classList.remove("open"));
	sideBarBtn.forEach(el => el.classList.remove("open"));
	$("header").removeClass("hover");
	$body.classList.remove("sidebar_open");
	$body.dataset.sbType = "";
	//class all remove
	sideContainer.className = "";
	sideBg.classList.remove("open");
	sideWrap.classList.remove("open");
	sideWrap.dataset.module = "login";
	document.querySelector(".side__box").innerHTML = "";
	headerHover(false);
}

window.addEventListener('DOMContentLoaded', function () {
	getMenuListApi();
	changeLanguageR();
	// sideBarRight();
	//		windowResponsive();
});
