window.addEventListener('DOMContentLoaded', function () {
	quickClickHandler();
	clickCloseContents();
	window.addEventListener('mousemove', function (ev) {
		mouseoverTargetObserver(ev);
	});
	fileUploadClickEventHandler();
	clickAddQnAInq();
	mobileSwipeClickTargetObserver();
	createFooterObserver();
	createMobileWishObserver();
});

window.addEventListener('resize', function () {
	let delay = 500;
	let timer = null;
	clearTimeout(timer);
	timer = setTimeout(function () {
		let breakpoint = window.matchMedia('screen and (min-width:1025px)');
		let el = ".swiper-quick-container";
		if (quickNowData != null) {
			writeWishListSwiperHtml(quickNowData);
		}
	}, delay);
});

var prevQuickviewFlg = false;
let last_timeout_id = 0;
let quickNowData;
let content_wrap = document.querySelector(".quickview__content__wrap");
let quickViewWarp = document.querySelector(".quickview__box");


// ------------- Quickview Observer --------------------------//
function mouseoverTargetObserver(ev) {
	//touch 이벤트는 touchstart->touchend->mousemove->mousedown->mouseup->click 순으로 진행되므로,
	//터치만 하는 경우라도 해당 이벤트가 실행된다
	//위시리스트 하트를 터치했을경우 이 경우를 예외처리로 막아줘야함
	let wishBtnFlg = ev.target.classList.contains('wish_img') == false ? false : true;

	let currentQuickviewFlg = ev.target.closest('#quickview') == null ? false : true;
	let faqSelFlg = document.querySelector('.btn__box.param_btn.faq__btn').classList.contains('select');

	if (!faqSelFlg) {
		if (currentQuickviewFlg != prevQuickviewFlg) {
			if (currentQuickviewFlg == false && wishBtnFlg == false) {
				//Timeout 
				targetOverEventHandler();
			}
			else {
				//초기화
				initSettimeout();
			}
		}
		prevQuickviewFlg = currentQuickviewFlg;
	}
}
function mobileSwipeClickTargetObserver() {
	let quickviewContentWarp = document.querySelector(".quickview__content__wrap");

	quickviewContentWarp.addEventListener('touchstart', function () {
		initSettimeout();
	})
	quickviewContentWarp.addEventListener('touchend', function () {
		let faqSelFlg = document.querySelector('.btn__box.param_btn.faq__btn').classList.contains('select');
		if (!faqSelFlg) {
			targetOverEventHandler();
		}
		else {
			initSettimeout();
		}
	})
}
function targetOverEventHandler() {
	let $btn_box = document.querySelectorAll(".btn__box");
	const set_timeout_id = setTimeout(() => {
		let $content_wrap = document.querySelector(".quickview__content__wrap");
		$btn_box.forEach((el) => {
			el.classList.remove("select");
		});
		$content_wrap.classList.remove("open");
	}, 3000);
	last_timeout_id = set_timeout_id;
}
// ------------- Quickview Common Swiper Slide  ------------- //
let quick_break_point = window.matchMedia('screen and (min-width:1025px)'); //미디어 쿼리 
let side_quick_swiper; //스와이퍼 변수

const quick_swiper_option_wb = {
	spaceBetween: 5,
	slidesPerView: 4.3,
	autoHeight: false,
	navigation: {
		nextEl: ".swiper-quick-container .swiper-button-next",
		prevEl: ".swiper-quick-container .swiper-button-prev",
	},
	breakpointsBase: "container",
}

const quick_swiper_option_mo = {
	navigation: {
		nextEl: ".quickview-wish-swiper .swiper-button-next",
		prevEl: ".quickview-wish-swiper .swiper-button-prev",
	},
	autoHeight: true,
	spaceBetween: 5,
	slidesPerView: 6.2
}

function initQuickSwiper(el, option) {
	side_quick_swiper = new Swiper(el, option);
	return side_quick_swiper;
}

function responsiveQuickSwiper(swiper_type) {
	let swiper_option = null;

	let show_all_wb = document.querySelector('.show_all_wb');
	let show_all_mo = document.querySelector('.show_all_mo');

	if (quick_break_point.matches == true) {
		swiper_option = quick_swiper_option_wb;

		if (swiper_type == "wish") {
			show_all_wb.classList.remove('hidden');
			show_all_mo.classList.add('hidden');
		}
	} else if (quick_break_point.matches == false) {
		swiper_option = quick_swiper_option_mo;

		if (swiper_type == "wish") {
			show_all_wb.classList.add('hidden');
			show_all_mo.classList.remove('hidden');
		}
	}

	return initQuickSwiper(".quickview-wish-swiper", swiper_option);
};

function resizeWidth(obj_cnt) {
	let arrow_width = 30;
	let width = 420;

	if (obj_cnt >= 6) {
		width = (420 - arrow_width);
	}
	if (obj_cnt < 5) {
		width = (375 - arrow_width);
	}
	if (obj_cnt < 4) {
		width = (290 - arrow_width);
	}
	if (obj_cnt < 3) {
		width = (200 - arrow_width);
	}

	document.querySelector(".swiper-quick-container .quickview-swiper-wrapper").style.width = width + "px";
}

function clickCloseContents() {
	let close_contents = document.querySelector('.close_contents');
	close_contents.addEventListener('click', function () {
		closeQuickviewContents();
	});
}

function setShowAll(param_type, param_link) {
	let show_all = document.querySelectorAll('.all-btn');
	show_all.forEach(show => {
		show.dataset.param_type = param_type;
		show.dataset.param_link = param_link;
	});
}

function clickShowAll() {
	let show_all = document.querySelectorAll('.all-btn');

	show_all.forEach(show => {
		show.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let param_type = el.dataset.param_type;
			let param_link = el.dataset.param_link;

			if (param_type != null) {
				switch (param_type) {
					case "top":
						location.href = param_link;
						break;

					case "wish":
						location.href = "/order/whish";
						break;
				}
			}
		});
	});
}

function quickClickHandler() {
	let $btn_box = document.querySelectorAll(".btn__box");
	let $btn_box_img = document.querySelector(".btn__box img");
	let $btn_box_p = document.querySelector(".btn__box p");

	let $titleBox = document.querySelector(".title__box");
	let $title_box_span = document.querySelector(".title__box span");
	let $title_box_img = document.querySelector(".title__box img");

	let swiper_container = document.querySelector(".swiper-quick-container");

	let common_contents = document.querySelector('.common-contents-container');
	let contents_footer = document.querySelector('.contents-footer');
	let $content_wrap = document.querySelector(".quickview__content__wrap");

	const common_swiper = document.querySelector(".quickview-wish-swiper");

	let show_all_wb = document.querySelector(".show_all_wb");
	let show_all_mo = document.querySelector(".show_all_mo");

	$btn_box.forEach((el) => {
		el.addEventListener("touchend", function (ev) {
			getQuickviewContent(ev, 'touch');
			ev.preventDefault();
		});
		el.addEventListener("click", function (ev) {
			getQuickviewContent(ev, 'click');
		});
	});
	function getQuickviewContent(ev, eventType) {
		let param_btn = ev.currentTarget;
		let quick_param = param_btn.dataset.quick;
		swiper_container.style.display = "flex";
		swiper_container.classList.remove("close-swiper");

		if (param_btn.classList.contains("select")) {
			param_btn.classList.remove("select");
			$content_wrap.classList.remove("open");
		} else {
			initQuickSelect();
			common_swiper.innerHTML = "";
			param_btn.classList.add("select");

			common_contents.classList.add('hidden');
			contents_footer.style.display = 'none';
			$content_wrap.classList.add("open");
			$content_wrap.classList.remove('faq');

			show_all_wb.classList.add('hidden');
			show_all_mo.classList.add('hidden');

			switch (quick_param) {
				case "recent":
					$title_box_span.innerText = "최근 본 제품";
					$title_box_span.dataset.i18n = 'q_recently_seen_product';
					$title_box_img.src = "/images/svg/wish-recent-bk.svg";
					let recent_view = null;
					if (localStorage.getItem('recentlyViewed') != null) {
						try {
							recent_view = JSON.parse(localStorage.getItem('recentlyViewed'));
						} catch (e) {
							return false;
						}
					}

					if (recent_view != null) {
						const recent_obj = recent_view.filter(item => typeof (JSON.parse(item)) === 'object');
						writeRecentProductHtml(recent_obj);
					} else {
						writeRecentProductHtml([]);
					}
					setQuickviewBtnCondition(eventType);
					break;

				case "top":
					$title_box_span.innerText = "실시간 인기 제품";
					$title_box_span.dataset.i18n = 'ss_real_time';
					$title_box_img.src = "/images/svg/wish-real-bk.svg";

					getPopularProductList();
					setQuickviewBtnCondition(eventType);
					break;

				case "wish":
					$title_box_span.innerText = "위시리스트";
					$title_box_span.dataset.i18n = 'w_wishlist';
					$title_box_img.src = "/images/svg/wish-list-bk.svg";

					let login_status = getLoginStatus();
					if (login_status == 'true') {
						getWhishlistProductList()
					} else {
						writeWishlistLoginHtml();
					}
					setQuickviewBtnCondition(eventType);
					break;

				case "qna":
					$title_box_span.innerText = "문의하기";
					$title_box_span.dataset.i18n = 'lm_inquiry';
					$title_box_img.src = "/images/svg/wish-faq-bk.svg";

					swiper_container.classList.add("close-swiper");
					swiper_container.style.display = "none";

					common_contents.classList.remove('hidden');
					$content_wrap.classList.add('faq');

					getQnAParentCategory();
					initSettimeout();
					break;
			}
			changeLanguageR();
		}
	}
	function initQuickSelect() {
		$btn_box.forEach((el) => {
			el.classList.remove("select");
		});
	}
};
function setQuickviewBtnCondition(eventType) {
	if (eventType == 'touch') {
		initSettimeout();
		targetOverEventHandler();
		prevQuickviewFlg = false;
	}
	else if (eventType == 'click') {
		initSettimeout();
		prevQuickviewFlg = true;
	}
}
function closeQuickviewContents() {
	let param_btn = document.querySelectorAll('.param_btn');

	param_btn.forEach(param => {
		if (param.classList.contains('select')) {
			param.classList.remove('select');
		}
	});

	let $content_wrap = document.querySelector("#quickview .quickview__content__wrap");
	$content_wrap.classList.remove("open");

	let $list_btn = document.querySelector("#quickview .btn__box.list__btn");
	$list_btn.classList.remove("select");
}

function writeRecentProductHtml(data) {
	if (data != null) {
		let recent_cnt = data.length;

		const wish_dom_frag = document.createDocumentFragment();
		const swiper_wrapper = document.createElement("div");
		swiper_wrapper.className = "swiper-wrapper quickview-swiper-wrapper";

		const common_swiper = document.querySelector("#quickview .quickview-wish-swiper");

		const next_btn = document.createElement("div");
		next_btn.className = "swiper-button-next";

		if (data.length === 0) {
			let show_all_wb = document.querySelector('.show_all_wb');
			let show_all_mo = document.querySelector('.show_all_mo');
			let div_msg = `
				<div class="wish-msg">최근 본 상품이 비어있습니다.</div>
			`;

			common_swiper.innerHTML = div_msg;


			show_all_wb.classList.add('hidden');
			show_all_mo.classList.add('hidden');
		} else {
			data = Array.from(data).reverse();
			let write_recent_product_html = "";
			data.forEach((json, idx) => {
				let product = JSON.parse(json);
				let stcl_flg = product.stock_status == 'STCL' ? true : false;
				dotDiv = ``;
				if (stcl_flg) {
					dotDiv = `<div class="red-dot"></div>`;
				}
				write_recent_product_html += `
					<div class="swiper-slide">
						<a href="/product/detail?product_idx=${product.product_idx}">
							<div class="swiper-box product-title">
								<img src="${cdn_img}${product.img_main}" alt="">
								${dotDiv}
								<span class="product-name">${product.product_name}</span>
							</div>
						</a>
					</div>
				`;
			});

			swiper_wrapper.innerHTML = write_recent_product_html;
			wish_dom_frag.appendChild(swiper_wrapper);

			common_swiper.innerHTML = "";
			common_swiper.appendChild(wish_dom_frag);
			common_swiper.appendChild(next_btn);

			resizeWidth(recent_cnt);
			responsiveQuickSwiper('recent');
		}
	}
}

//인기상품 API
function getPopularProductList() {
	$.ajax({
		url: api_location + "quickview/popular/get",
		type: "post",
		headers: {
			"country": getLanguage()
		},
		dataType: "json",
		error: function () {
			// notiModal("실시간 인기제품 조회처리중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0018', null);
		},
		success: function (d) {
			if (d.code == 200) {
				if (d.data != null && d.data.length > 0) {
					/*
					let popular_link = d.popular_link;
					if (popular_link != null) {
						setShowAll('top',popular_link);
						clickShowAll();
					}
					*/
					let data = d.data;
					if (data != null && data.length > 0) {
						writePopularProductHtml(data);
					}
				}
			}
			else {
				notiModal(d.msg);
			}
		}
	});
}
/*
product_idx
product_name
img_location
stock_status
*/

function writePopularProductHtml(data) {
	let data_cnt = data.length;

	const dom_frag = document.createDocumentFragment();
	const common_swiper = document.querySelector("#quickview .quickview-wish-swiper");

	const swiper_wrapper = document.createElement("div");
	swiper_wrapper.className = "swiper-wrapper quickview-swiper-wrapper";

	const next_btn = document.createElement("div");
	next_btn.className = "swiper-button-next";

	let write_popular_product_html = "";
	data.forEach(product => {
		// TOP 20 적용일 시
		/*
		let stcl_flg = false;

		let product_type = product.product_type;
		if(product_type == 'B'){
			if(typeof(product.product_size) == 'object'){
				product.product_size.forEach(function(option){
					if(option.stock_status == 'STCL'){
						stcl_flg = true;
					}
				})
			}
			else{
				notiModal("상품 필수정보가 누락되었습니다.");
			}
		}
		else if(product_type == 'S'){
			if(typeof(product.product_size) == 'object'){
				product.product_size.forEach(function(set_prod){
					if(typeof(set_prod.set_option_info) == 'object')
					set_prod.set_option_info.forEach(function(set_prod_option){
						if(set_prod_option.stock_status == "STCL"){
							stcl_flg = true;
						}
					})
					else{
						notiModal("세트상품 필수정보가 누락되었습니다.");
					}
				})
			}
			else{
				notiModal("상품 필수정보가 누락되었습니다.");
			}
		}
		*/

		dotDiv = ``;
		if (product.stock_status == 'STCL') {
			dotDiv = `<div class="red-dot"></div>`;
		}
		write_popular_product_html += `
			<div class="swiper-slide">
				<a href="/product/detail?product_idx=${product.product_idx}">
					<div class="swiper-box product-title">
						<img src="${cdn_img}${product.img_location}" alt="">
						${dotDiv}
						<span class="product-name">${product.product_name}</span>
					</div>
				</a>
			</div>
		`;
	});

	swiper_wrapper.innerHTML = write_popular_product_html;

	dom_frag.appendChild(swiper_wrapper);

	common_swiper.innerHTML = "";
	common_swiper.appendChild(dom_frag);
	common_swiper.appendChild(next_btn);

	resizeWidth(data_cnt);
	responsiveQuickSwiper('top');
}

function getWhishlistProductList() {
	$.ajax({
		type: "post",
		headers: {
			"country": getLanguage()
		},
		dataType: "json",
		url: api_location + "order/whish/list/get",
		error: function () {
			// notiModal("위시리스트 등록상품 조회처리중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0016', null);
		},
		success: function (d) {
			if (d.code == 200) {
				if (d.data != null && d.data.length > 0) {
					let data = d.data;
					setShowAll('wish', 0);
					clickShowAll();

					writeWishListSwiperHtml(data);
					quickNowData = data;
				}
				else {
					let show_all_wb = document.querySelector('.show_all_wb');
					let show_all_mo = document.querySelector('.show_all_mo');
					let common_swiper = document.querySelector(".quickview-wish-swiper");
					let exception_msg = "";

					switch (getLanguage()) {
						case "KR":
							exception_msg = "위시리스트가 비어있습니다.";
							break;

						case "EN":
							exception_msg = "Your wishlist is empty.";
							break;

						case "CN":
							exception_msg = "您的夢想清單是空。";
							break;
					}

					let div_msg = `<div class="wish-msg">${exception_msg}</div>`;

					common_swiper.innerHTML = div_msg;

					const wish_dom_frag = document.createDocumentFragment();

					const swiper_wrapper = document.createElement("div");
					swiper_wrapper.className = "swiper-wrapper quickview-swiper-wrapper";

					wish_dom_frag.appendChild(swiper_wrapper);

					const next_btn = document.createElement("div");
					next_btn.className = "swiper-button-next";

					common_swiper.appendChild(wish_dom_frag);
					common_swiper.appendChild(next_btn);

					$('.quickview-wish-swiper .swiper-button-next').hide();

					show_all_wb.classList.add('hidden');
					show_all_mo.classList.add('hidden');
				}
			}
			else {
				notiModal(d.msg);
			}
		}
	});
}

function writeWishListSwiperHtml(data) {
	let data_cnt = data.length;

	const common_swiper = document.querySelector("#quickview .quickview-wish-swiper");
	const wish_dom_frag = document.createDocumentFragment();

	const swiper_wrapper_wb = document.createElement("div");
	swiper_wrapper_wb.className = "swiper-wrapper quickview-swiper-wrapper";

	const next_btn = document.createElement("div");
	next_btn.className = "swiper-button-next";

	let write_wish_list_html = "";

	data.forEach(product => {
		let stcl_flg = false;
		let product_type = product.product_type;
		if (product_type == 'B') {
			if (typeof (product.product_size) == 'object') {
				product.product_size.forEach(function (option) {
					if (option.stock_status == 'STCL') {
						stcl_flg = true;
					}
				})
			}
			else {
				// notiModal("상품 필수정보가 누락되었습니다.");
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0030', null);
			}
		}
		else if (product_type == 'S') {
			if (typeof (product.product_size) == 'object') {
				product.product_size.forEach(function (set_prod) {
					if (typeof (set_prod.set_option_info) == 'object')
						set_prod.set_option_info.forEach(function (set_prod_option) {
							if (set_prod_option.stock_status == "STCL") {
								stcl_flg = true;
							}
						})
					else {
						// notiModal("세트상품 필수정보가 누락되었습니다.");
						makeMsgNoti(getLanguage(), 'MSG_F_WRN_0027', null);
					}
				})
			}
			else {
				// notiModal("상품 필수정보가 누락되었습니다.");
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0030', null);
			}
		}

		dotDiv = ``;
		if (stcl_flg) {
			dotDiv = `<div class="red-dot"></div>`;
		}
		write_wish_list_html += `
			<div class="swiper-slide">
				<a href="/product/detail?product_idx=${product.product_idx}">
					<div class="swiper-box product-title">
						<img src="${cdn_img}${product.product_img}" alt="">
						${dotDiv}
						<span class="product-name">${product.product_name}</span>
					</div>
				</a>
			</div>
		`;
	});

	swiper_wrapper_wb.innerHTML = write_wish_list_html;

	wish_dom_frag.appendChild(swiper_wrapper_wb);

	common_swiper.innerHTML = "";
	common_swiper.appendChild(wish_dom_frag);
	common_swiper.appendChild(next_btn);

	resizeWidth(data_cnt);
	responsiveQuickSwiper('wish');
}

function writeWishlistLoginHtml() {
	const common_swiper = document.querySelector(".quickview-wish-swiper");
	let show_all_wb = document.querySelector('.show_all_wb');
	let show_all_mo = document.querySelector('.show_all_mo');

	common_swiper.innerHTML = `
		<div class='quick-login-wrap'>
			<div class='quick-login-box'>
				<div class='quick-login-msg' data-i18n="q_login_move">로그인 후 이용 가능합니다.</div>
				<a href="/login"><span class='quick-login-btn' data-i18n="m_login">로그인</span></a>
			</div>
		</div>
	`;
	changeLanguageR();

	show_all_wb.classList.add('hidden');
	show_all_mo.classList.add('hidden');
}

// ------------- FQA api  ------------- //
function getQnAParentCategory() {
	initSettimeout();
	$.ajax({
		type: "post",
		url: api_location + "quickview/inquiry/category/get",
		headers:{
			"country": getLanguage()
		},
		data: {
			'category_type': 'FAQ'
		},
		dataType: "json",
		error: function () {
			// notiModal("QnA 상위 카테고리 조회처리중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0077', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (d.data != null && d.data.length > 0) {
					let contents_container = document.querySelector('.common-contents-container');
					contents_container.innerHTML = "";

					let contents_container_html = "";
					contents_container_html += `
						<div class="faq-btn-wrap admin">
							<div class="contents-header">
								<p data-i18n="inquiry_search">무엇을 도와드릴까요?</p>
							</div>
							<div class="contents-body">
					`;

					d.data.forEach(function (row) {
						contents_container_html += `
								<div class="contents-btn qna_category" data-category_idx="${row.category_idx}" data-category_title="${row.category_title}">
									${row.category_title}
								</div>
						`;
					});

					contents_container_html += `
								<div class="contents-btn inq_category">
									<span data-i18n="q_inquiry_directly">직접 문의하기</span>
								</div>
							</div>
						</div>
					`;

					contents_container.innerHTML = contents_container_html;

					clickQnACategory();
					clickQnAInqCategory();

					let scroll_height = $(".common-contents-container").prop('scrollHeight');
					$(".quickview__content__wrap.faq.open .common-contents-container").animate({
						scrollTop: scroll_height
					}, 400);
					$(".quickview__content__wrap.faq.open").animate({
						scrollTop: scroll_height
					}, 400);
					changeLanguageR();
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function getQnACategoryList(category_idx, category_title) {
	$('#sel_category_no').val(category_idx);
	$('#sel_category_title').val(category_title);

	$.ajax({
		type: "post",
		url: api_location + "quickview/inquiry/list/get",
		headers: {
			"country": getLanguage()
		},
		data: {
			'category_idx': category_idx
		},
		dataType: "json",
		error: function () {
			// notiModal("QnA 카테고리 조회처리중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0076', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null && data.length > 0) {
					let contents_container = $('.quickview__content__wrap .common-contents-container');

					let contents_container_html = "";
					contents_container_html += `
							<div class="faq-btn-wrap member">
								<div class="chat-box">
									<div class="arrow_box">
										<span>${category_title}</span>
									</div>		
								</div>
							</div>
							<div class="faq-btn-wrap admin">
								<div class="contents-header">
									<span>${category_title}</span>
									<span class="parent-move-link parent_category" data-i18n="q_viewing_parent_menu">
										< 상위 메뉴 보기
									</span>
								</div>
							</div>
							
							<div class="faq-btn-wrap admin">
								<div class="contents-body">
					`;

					d.data.forEach(function (row) {
						contents_container_html += `
									<div class="contents-btn qna_contents" data-category_idx="${row.category_idx}" data-sub_category="${row.sub_category}">
										${row.sub_category}
									</div>
						`;
					});

					contents_container_html += `
							</div>
						</div>
					`;

					contents_container.append(contents_container_html);

					clickQnAParentCategory();
					clickQnAContents();

					let scroll_height = $(".common-contents-container").prop('scrollHeight');
					$(".quickview__content__wrap.faq.open .common-contents-container").animate({
						scrollTop: scroll_height
					}, 400);
					$(".quickview__content__wrap.faq.open").animate({
						scrollTop: scroll_height
					}, 400);
					changeLanguageR();
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function getQnAContents(category_idx, sub_category) {
	let prev_category_idx = $('#sel_category_no').val();
	let prev_category_title = $('#sel_category_title').val();

	$.ajax({
		type: "post",
		url: api_location + "quickview/inquiry/get",
		headers: {
			"country": getLanguage()
		},
		data: {
			'faq_idx': category_idx
		},
		dataType: "json",
		error: function () {
			// notiModal("QnA 컨텐츠 조회처리중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0075', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null) {
					let contents_container = $('.quickview__content__wrap .common-contents-container');

					let contents_container_html = "";
					contents_container_html += `
						<div class="faq-btn-wrap member">
							<div class="chat-box">
								<div class="arrow_box">
									<span>${sub_category}</span>
								</div>		
							</div>
						</div>
						<div class="faq-btn-wrap admin">
							<div class="contents-header">
								<span>${sub_category}</span>
								<span class="parent-move-link qna_category" data-category_idx="${prev_category_idx}" data-category_title="${prev_category_title}" data-i18n="q_viewing_parent_menu">
									< 상위 메뉴 보기
								</span> 
							</div>
							<div class="contents-body">
								<div class="question">
									Q. ${data.question}
								</div>
								<div class="answer">
									<span>A. </span>${xssDecode(data.answer)}
								</div>
							</div>
						</div>
						
						<div class="faq-btn-wrap admin">
							<div class="contents-header">
								<p data-i18n="q_inquiry_other_help">다른 도움이 더 필요하신가요?</p>
							</div>
							<div class="contents-body">
								<div class="contents-btn parent_category" data-i18n="q_inquiry_yes">예</div>
								<div class="contents-btn close_content" data-i18n="q_inquiry_no">아니요</div>
							</div>
						</div>
					`;

					contents_container.append(contents_container_html);

					clickQnAParentCategory();
					clickCloseContentsBtn();
					clickQnACategory();

					let scroll_height = $(".common-contents-container").prop('scrollHeight');
					$(".quickview__content__wrap.faq.open .common-contents-container").animate({
						scrollTop: scroll_height
					}, 400);
					$(".quickview__content__wrap.faq.open").animate({
						scrollTop: scroll_height
					}, 400);
					changeLanguageR();
				} else {

				}
			} else { }
		}
	});
}

function getInqCategory() {
	$.ajax({
		type: "post",
		url: api_location + "quickview/inquiry/category/get",
		headers: {
			"country": getLanguage()
		},
		data: {
			'category_type': 'INQ'
		},
		dataType: "json",
		error: function () {
			// notiModal("QnA 문의유형 조회처리중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0078', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null && data.length > 0) {
					let contents_container = $('.quickview__content__wrap .common-contents-container');

					contents_container_html = `
						<div class="faq-btn-wrap member">
							<div class="chat-box">
								<div class="arrow_box">
									<span data-i18n="q_inquiry_directly">직접 문의하기</span>
								</div>		
							</div>
						</div>
						
						<div class="faq-btn-wrap admin">
							<div class="contents-header">
								<span data-i18n="q_inquiry_type_select">문의유형을 선택해주세요.</span>
								<span class="parent-move-link parent_category" data-i18n="q_viewing_parent_menu">
									< 상위 메뉴 보기
								</span>
							</div>
							<div class="contents-body">
					`;

					d.data.forEach(function (row) {
						i18nClass = '';
						switch (row.code_value) {
							case 'DAE':
								i18nClass = 'data-i18n="q_inquiry_subcategory_01"';
								break;
							case 'CAR':
								i18nClass = 'data-i18n="q_inquiry_subcategory_02"';
								break;
							case 'OAP':
								i18nClass = 'data-i18n="q_inquiry_subcategory_03"';
								break;
							case 'FAD':
								i18nClass = 'data-i18n="q_inquiry_subcategory_04"';
								break;
							case 'RAE':
								i18nClass = 'data-i18n="q_inquiry_subcategory_05"';
								break;
							case 'RST':
								i18nClass = 'data-i18n="q_inquiry_subcategory_06"';
								break;
							case 'PIQ':
								i18nClass = 'data-i18n="q_inquiry_subcategory_07"';
								break;
							case 'BGP':
								i18nClass = 'data-i18n="q_inquiry_subcategory_08"';
								break;
							case 'VUC':
								i18nClass = 'data-i18n="m_voucher"';
								break;
							case 'OSV':
								i18nClass = 'data-i18n="q_inquiry_subcategory_09"';
								break;
						}
						contents_container_html += `
								<div class="contents-btn show_inq" data-code_value="${row.code_value}" data-code_name="${row.code_name}" ${i18nClass}>
									${row.code_name}
								</div>
						`;
					});

					contents_container_html += `
							</div>
						</div>
					`;

					contents_container.append(contents_container_html);

					clickQnAParentCategory();
					clickShowInq();

					let scroll_height = $(".common-contents-container").prop('scrollHeight');

					$(".quickview__content__wrap.faq.open .common-contents-container").animate({
						scrollTop: scroll_height
					}, 400);
					$(".quickview__content__wrap.faq.open").animate({
						scrollTop: scroll_height
					}, 400);
					changeLanguageR();
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function showInqForm(code_value, code_name) {
	let prev_category_idx = $('#sel_category_no').val();
	let prev_category_title = $('#sel_category_title').val();

	let contents_container = $('.quickview__content__wrap .common-contents-container');
	let contents_container_html = `
		<div class="faq-btn-wrap member">
			<div class="chat-box">
				<div class="arrow_box">
					<span>${code_name}</span>
				</div>		
			</div>
		</div>
		
		<div class="faq-btn-wrap admin">
			<div class="contents-header">
				<span data-i18n="q_inquiry_enter">문의 내용을 입력해주세요.</span>
				<span class="parent-move-link parent_category" data-i18n="q_viewing_parent_menu">
					<상위메뉴보기
				</span>
			</div>
		</div>
	`;

	contents_container.append(contents_container_html);

	clickQnAParentCategory();


	$('#inquiry_type').val(code_value);
	$('#inquiry_title').val(code_name);

	let contents_footer = document.querySelector('.contents-footer');
	contents_footer.style.display = "flex";
	contents_footer.classList.remove('hidden');

	let scroll_height = $(".common-contents-container").prop('scrollHeight');
	$(".quickview__content__wrap.faq.open .common-contents-container").animate({
		scrollTop: scroll_height
	}, 400);
	$(".quickview__content__wrap.faq.open").animate({
		scrollTop: scroll_height
	}, 400);
	changeLanguageR();
}

function clickQnACategory() {
	let qna_category = document.querySelectorAll('.qna_category');
	qna_category.forEach(category => {
		category.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let category_idx = el.dataset.category_idx;
			let category_title = el.dataset.category_title;

			if (category_idx != null && category_title != null) {
				getQnACategoryList(category_idx, category_title);
			}
		});
	});
}

function clickQnAParentCategory() {
	let parent_category = document.querySelectorAll('.parent_category');
	parent_category.forEach(category => {
		category.addEventListener('click', function () {
			let contents_footer = document.querySelector('.contents-footer');
			getQnAParentCategory();
			contents_footer.classList.add('hidden');
		});
	});
}

function clickCloseContentsBtn() {
	let close_contents = document.querySelectorAll('.contents-btn.close_content');
	close_contents.forEach(function (close_content) {
		close_content.addEventListener('click', function () {
			closeQuickviewContents();
		})
	})
}

function clickQnAContents() {
	let qna_contents = document.querySelectorAll('.qna_contents');
	qna_contents.forEach(contents => {
		contents.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let category_idx = el.dataset.category_idx;
			let sub_category = el.dataset.sub_category;

			if (category_idx != null && sub_category != null) {
				getQnAContents(category_idx, sub_category);
			}
		});
	});
}

function clickQnAInqCategory() {
	let inq_category = document.querySelectorAll('.inq_category');
	inq_category.forEach(category => {
		category.addEventListener('click', function () {
			getInqCategory();
		});
	});
}

function clickShowInq() {
	let show_inq = document.querySelectorAll('.show_inq');
	show_inq.forEach(show => {
		show.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let code_value = el.dataset.code_value;
			let code_name = el.textContent;
			if (code_value != null && code_name != null) {
				showInqForm(code_value, code_name);
			}
		});
	});
}

function clickAddQnAInq() {
	let add_qna_inq = document.querySelector('.add_qna_inq');
	add_qna_inq.addEventListener('click', function () {
		let inq_textbox = $('#inquiryTextBox').val();
		if (inq_textbox != null && inq_textbox.length > 0) {
			addQnAInq();
		} else {
			// notiModal('문의 내용을 입력해주세요.');
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0061', null);
			return false;
		}
	});
}

function addQnAInq() {
	$('#country').val(country);
	let form = $('#frm-inquiry')[0];
	let formData = new FormData(form);

	$.ajax({
		type: "post",
		url: api_location + "mypage/inquiry/add",
		data: formData,
		dataType: "json",
		cache: false,
		contentType: false,
		processData: false,
		error: function (d) {
			// notiModal("문의신청 도중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0054', null);
		},
		success: function (d) {
			let contents_container = $('.quickview__content__wrap .common-contents-container');
			let contents_container_html = "";

			if (d.code == 200) {
				contents_container_html += `
					<div class="faq-btn-wrap admin">
						<div class="contents-header">
							<p data-i18n="q_go_to_my_inquiry">
								문의내역이 등록되었습니다.
							</p>   
						</div>
						<div class="contents-body">
						<p data-i18n="inquiry_cs_time">· 고객센터 운영시간 월-금 / AM 9:30 - PM 1:00, PM 2:00 - PM 5:00</p>
						<div class="inquiry_info_wrap">
							<span data-i18n="inquiry_cs_info_01">· 매월 15일 (공휴일인 경우 직전 영업일)은 당사의 CS 및 배송 시스템 점검일입니다.</span>
							<span data-i18n="inquiry_cs_info_02">보다 나은 서비스를 제공하기 위하여 위 점검일에는 CS 및 배송 업무가 중단됩니다.</span>
							<span data-i18n="inquiry_cs_info_03">고객 여러분들의 양해를 부탁드립니다. 오프라인 스토어는 정상 운영됩니다.</span>
						</div>
						<p data-i18n="inquiry_cs_answered">· 답변이 완료된 문의내역은 수정이 불가능합니다.</p>
							<div class="contents-btn" onclick="location.href='/login?r_url=/mypage?mypage_type=inquiry_list'" data-i18n="q_my_inquiry_list_move">나의 문의내역 보러가기</div>
						</div>
					</div>
				`;
			} else {
				contents_container_html += `
					<div class="faq-btn-wrap admin">
						<div class="contents-header">
							<p data-i18n="q_login_move">로그인 후 이용 가능합니다.</p>   
						</div>
						<div class="contents-body">
							<div class="contents-btn" onclick="location.href='/login?r_url=/mypage?mypage_type=inquiry_list'" data-i18n="q_go_to_login_window">로그인 창으로 이동</div>
						</div>
					</div>
				`;
			}

			contents_container.append(contents_container_html);

			initInqueryInput();

			let scroll_height = $(".common-contents-container").prop('scrollHeight');
			$(".quickview__content__wrap.faq.open .common-contents-container").animate({
				scrollTop: scroll_height
			}, 400);
			$(".quickview__content__wrap.faq.open").animate({
				scrollTop: scroll_height
			}, 400);
			changeLanguageR();
			let inquiry_title = $('#inquiry_title').val();
			
			/*
			$.ajax({
				type: "post",
				url: api_location + "mypage/inquiry/mail",
				headers: {
					"country": getLanguage()
				},
				data: {
					
					'inquiry_title': inquiry_title
				},
				dataType: "json",
				success: function (d) {
					
				}
			});
			*/
		}
	});

	function initInqueryInput() {
		let contents_footer = document.querySelector('.contents-footer');
		contents_footer.classList.add("hidden");
		document.getElementById('inquiryTextBox').value = '';
		document.getElementById('inquiry_img').value = '';
	}
}

// ------------- FQA api  ------------- //
function fileUploadClickEventHandler() {
	let fileBtn = document.querySelector('.file-upload-btn');
	fileBtn.addEventListener('click', function () {
		document.getElementById('inquiry_img').click();
		document.querySelector('.file-upload-btn').classList.add('upload');
	});
}
function initSettimeout() {
	if (last_timeout_id >= 0) {
		clearTimeout(last_timeout_id);
	}
	last_timeout_id = null;
}
function allCloseWrap() {
	let $content_wrap = document.querySelector(".quickview__content__wrap");
	$content_wrap.classList.remove("open");
	let $btn_box = document.querySelectorAll(".btn__box");
	$btn_box.forEach((el) => {
		el.classList.remove("select");
	});
}