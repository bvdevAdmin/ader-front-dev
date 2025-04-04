let cdn_img = "https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user";
let cdn_vid = "https://media-ader.fastedge.net/adervod/_definst_";

let domain_url = "http://121.138.215.112:211/";
let api_location = domain_url + "_api/";

var popupWidth = 0;
var popupHeight = 0;

window.addEventListener("DOMContentLoaded", function () {
	createFooterObserver();
	checkPopup();
	window.addEventListener("resize", function () {
		changeMainPaddingBottom();
		//popupResize(popupWidth,popupHeight);
	});
});

window.addEventListener("load", function () {
	changeLanguage();
	changeMainPaddingBottom();
});

function changeMainPaddingBottom(){
	let element = document.querySelector('footer');
	if (element != null) {
		let footerHeight = element.offsetHeight;
		$('main').css('padding-bottom', `${footerHeight}px`);
	}
}
/**
 * @author SIMJAE
 * @param {String} elem 해당레이아웃 클래스나 , id
 */
function layoutOutSideClick(elem) {
	elem.addEventListener("click", (e) => {
		if (e.target !== elem) {
			elem.classList.remove("open")
		}
	})
}

function checkPopup() {
	let urlParts = location.href.split('/');
	let url = '/' + urlParts.slice(3).join('/');
	$.ajax({
		type: "post",
		url: api_location + "common/popup",
		headers: {
			"country":getLanguage()
		},
		data: {
			'url': url
		},
		dataType: "json",
		success: function (d) {
			if (d.code == 200) {
				if (d.data != null) {
					popupWidth = d.data.width;
					popupHeight = d.data.height;
					printPopupHtml(d.data);
				}
			}
		}
	});
}
/**
 * @author SIMJAE
 * @param {String} add_type product, whish 택 1
 * @param {String} idx add_type에 따라서 넘기는 값이 다름( product: product_idx ,whish:whish_idx)
 * @param {Array} optionIdx 상품 옵션 idx 리스트
 * @description 스크롤시 footer위로 올려야하는 엘리먼트
 */
function addBasketApi(add_type, idx, optionIdx) {
	const main = document.querySelector("main");
	let country = main.dataset.country;
	let dataResult = {};
	if (add_type == "product") {
		dataResult = {
			"add_type": add_type,
			"product_idx": idx,
			"option_idx": optionIdx,
			"country": getLanguage()
		}
	} else if (add_type == "whish") {
		dataResult = {
			"add_type": add_type,
			"whish_idx": idx,
			"option_idx": optionIdx,
			"country": getLanguage()
		}
	}
	$.ajax({
		type: "post",
		data: dataResult,
		dataType: "json",
		url: api_location + "order/basket/add",
		headers: {
			"country": getLanguage()
		},
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0023', null);
			// alert("쇼핑백 추가처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				// location.href = domain_url + 'order/basket/list';
			}
		}
	});
}

/**
 * @author SIMJAE
 * @param {String} elem 해당 클래스,Id 
 * @description 스크롤시 footer위로 올려야하는 엘리먼트
 */

const elemScrollFooterUpEvent = (elem) => {
	window.addEventListener("scroll", function () {
		const scrollHeight = window.scrollY;
		const windowHeight = window.innerHeight;
		const docTotalHeight = document.body.offsetHeight;
		const isBottom = windowHeight + scrollHeight === docTotalHeight;
		const $elem = document.querySelector(elem);
		const footer = document.querySelector("footer").offsetHeight;
		if (isBottom) {
			$elem.style.bottom = `${footer}px`;
		} else {
			$elem.style.bottom = "0px";
		}
	});
};


/**
 * @author SIMJAE
 * @description 로그인 세션값 
 * @returns true,false
 */
function getLoginStatus() {
	return sessionStorage.getItem("login_session");
}


/**
 * @author SIMJAE
 * @param {String} page 예외처리 모달 페이지
 * @param {String} message 예외처리 모달 메시지
 * @description 에러띄울 모달
 */
function exceptionHandling(page, message) {
	if (document.querySelector('#exception-modal') !== null) {
		document.querySelector('#exception-modal').remove();
	}
	const body = document.body;
	const exceptionContainner = document.createElement("div");
	exceptionContainner.id = "exception-modal";
	exceptionContainner.className = "exception-containner";
	exceptionContainner.innerHTML = `
	<div class="exception__background">
		<div class="exception__wrap">
			<div class="exception__box">
				<div class="close-btn">[X]</div>
				<h1 class="title">-${page}-</h1>
				<p>${message}</p>
			</div>
		</div>
	</div>
	`
	body.appendChild(exceptionContainner)

	this.openModal = (() => {
		exceptionContainner.classList.add("open");
		modalClose();
	})();

	function modalClose() {
		let closeBtn = document.querySelector(`#exception-modal .close-btn`);
		closeBtn.addEventListener("click", () => { exceptionContainner.classList.remove("open"); document.querySelector('#exception-modal').remove(); });
	}

}

function makeMsgNoti(country, msg_code, mapping_arr) {
	$.ajax({
		type: "post",
		data: {
			"country": country,
			"msg_code": msg_code
		},
		dataType: "json",
		url: api_location + "common/msg/get",
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0090', null);
			// alert("메세지정보를 얻는데 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			let data = d.data;
			if (code == "200") {
				let msg_text = data.msg_text;
				if(mapping_arr != null){
					msg_text = editMsgByMapping(msg_text,mapping_arr);
				}
				notiModal('', msg_text);

				if(msg_code == 'MSG_F_INF_0018'){
					let closeBtn = document.querySelector(`#notimodal-modal .close-btn`);
					closeBtn.addEventListener('click', () => { location.href = '/logout' });
				}
				else if(msg_code == 'MSG_F_ERR_0074' || msg_code == 'MSG_F_ERR_0059' || msg_code == 'MSG_F_INF_0001'){
					let closeBtn = document.querySelector(`#notimodal-modal .close-btn`);
					closeBtn.addEventListener('click', () => { location.href = '/login' });
				}
				else if(msg_code == 'MSG_F_ERR_0112' || msg_code == 'MSG_F_ERR_0113') {
					let closeBtn = document.querySelector(`#notimodal-modal .close-btn`);
					closeBtn.addEventListener('click', () => { location.href = '/main' });
				}
			}
		}
	});
}
function editMsgByMapping(mapping_arr, text){
	let editText = '';
	mapping_arr.forEach(function(e){
		let regex = new RegExp(`${e.key}`,'gi');
		editText = text.replace(regex, e.value);
	})
	return editText;
}
/*
function makeMsgMappingJson 사용법

메세지 안에 변동 변수값이 포함되있을 경우 ex) <type> 페이지에 접근할 수 없습니다.
변수값이 들어갈 키워드(key : '<type>')와 변수값(value : 'STANDBY')를 파라미터값으로 주어 함수를 호출한다.
함수의 리턴값들을 차례대로 배열에 넣은 후, makeMsgNoti의 세번째 param에 넣어준다.

let mapping_arr = [];
mapping_arr.push(makeMsgMappingJson('<type>', 'STANDBY'));
...
makeMsgNoti(country, msg_code, mapping_arr);

*/
function makeMsgMappingJson(key, value){
	let mapping_json = new Object();
	mapping_json.key = key;
	mapping_json.value = value;

	return mapping_json;
}
function notiModal(main, sub) {
	if (document.querySelector('#notimodal-modal') !== null) {
		document.querySelector('#notimodal-modal').remove();
	}
	const body = document.body;
	const notimodalContainner = document.createElement("div");
	notimodalContainner.id = "notimodal-modal";
	notimodalContainner.className = "notimodal-containner";
	notimodalContainner.innerHTML = `
	<div class="notimodal__background">
		<div class="notimodal__wrap">
			<div class="notimodal__box">
				<div class="close-btn">
					<svg xmlns="http://www.w3.org/2000/svg" width="12.707" height="12.707" viewBox="0 0 12.707 12.707">
						<path data-name="선 1772" transform="rotate(135 6.103 2.736)" style="fill:none;stroke:#343434" d="M16.969 0 0 .001"></path>
						<path data-name="선 1787" transform="rotate(45 -.25 .606)" style="fill:none;stroke:#343434" d="M16.969.001 0 0"></path>
					</svg>
				</div>
				<h1 class="title">${main === undefined ? "" : main}</h1>
				<p>${sub === undefined ? "" : sub}</p>
			</div>
		</div>
	</div>
	`
	body.appendChild(notimodalContainner)

	this.openModal = (() => {
		notimodalContainner.classList.add("open");
		modalClose();
	})();

	function modalClose() {
		let closeBtn = document.querySelector(`#notimodal-modal .close-btn`);
		closeBtn.addEventListener('click', function () {
			notimodalContainner.classList.remove("open");
			document.querySelector('#notimodal-modal').remove();
		});
	}
}

let mobileProductDetailWhishSwiperOption = {
	// observer: true,
	// observeParents: true,
	navigation: {
		nextEl: ".mobile-wishlist-wrap .quickview-wish-swiper .swiper-button-next",
		prevEl: ".mobile-wishlist-wrap .quickview-wish-swiper .swiper-button-prev",
	},
	autoHeight: true,
	// slidesPerView: 'auto',
	spaceBetween: 5,
	slidesPerView: 5.6
}




/*------------------------------------------- 위시리스트 ------------------------------------------- */
/**
 * @author SIMJAE
 * @description 모든 상황별 위시리스트 반영 
 */
async function updateWishlist(clickEl, data) {
	let loginStatus = getLoginStatus();
	let clickTarget = clickEl;
	let targetLocation = data.location;
	let targetStatus = data.wishStatus;
	let targetProductIdx = data.productIdx;
	let targetUrl = data.url;
	
	if (loginStatus === 'true') {
		changeStatusWishBtn();
	} else {
		makeMsgNoti(getLanguage(), 'MSG_B_ERR_0018', null);

		let beforUrl = location.href;
		sessionStorage.setItem('before_url', beforUrl)
		setTimeout(() => {
			location.href = '/login';
		}, 1500)
	}

	let urlParts = targetUrl.split('?')[0].split('/');
	let path = '/' + urlParts.slice(3).join('/');
	if (path == '/product/list' || path == '/product/detail') {
		$('#quickview').removeClass('hidden');
	}

	function updateCurrentPageUi() {
		let urlParts = targetUrl.split('?')[0].split('/');
		let path = '/' + urlParts.slice(3).join('/');
		if (path === '/product/list' || path === '/product/best' || path === '/product/best/auto') {
			getWhishlistProductList();

			if (!document.querySelector('.btn__box.list__btn').classList.contains('select')) {
				document.querySelector('.btn__box.list__btn').click();
			}
			initSettimeout();
			targetOverEventHandler();
		} else if (path === '/product/detail') {
			//changeModuleWishBtn(targetProductIdx)
			getWhishlistProductList();
			if (!document.querySelector('.btn__box.list__btn').classList.contains('select')) {
				document.querySelector('.btn__box.list__btn').click();
			}
			
			if (window.innerWidth < 1024) {
				mobileWishEventHandler();
			}
			
			initSettimeout();
			targetOverEventHandler();
		} else if (path === '/mypage') {
			wish.update();
		} else if (targetUrl.includes('/order/whish')) {
			getWishListInfo();
		}
	}

	function changeModuleWishBtn(param) {
		let $$wishBtn = document.querySelectorAll('.wish__btn');
		$$wishBtn.forEach((el) => {
			if (el.getAttribute('product_idx') == param) {
				if (clickTarget.querySelector('img').dataset.status == 'true') {
					el.querySelector('img').dataset.status = true;
					el.querySelector('img').setAttribute('src','/images/svg/wishlist-bk.svg');
				} else {
					el.querySelector('img').dataset.status = false;
					el.querySelector('img').setAttribute('src','/images/svg/wishlist.svg');
				}
			}
		});
		
		getWhishlistProductList();
	}

	//선택한 하트의 색상, 상태 반영 
	function changeStatusWishBtn() {
		if (clickTarget.querySelector('img').dataset.status == 'true') {
			clickTarget.querySelector('img').dataset.status = false;
			clickTarget.querySelector('img').setAttribute('src', '/images/svg/wishlist.svg');
			heartDeleteWishApi(targetProductIdx);

		} else {
			clickTarget.querySelector('img').dataset.status = true;
			clickTarget.querySelector('img').setAttribute('src', '/images/svg/wishlist-bk.svg');
			addWishApi(targetProductIdx, clickTarget);
		}

		let add_box = document.querySelectorAll(".body-wrap .add-box");
		if (add_box != null || add_box != undefined) {
			add_box.forEach(el => {
				let whish_idx = el.dataset.wish_idx;
				el.remove();

				resetSizeBox(whish_idx);

				wish_quickSwiper.removeAllSlides();
				wish_quickSwiper.update();

				showAddWrapBtns();
			});
		}

		let product_select_btn = document.querySelectorAll(".product-select-btn.select");
		if (product_select_btn != null || product_select_btn != undefined) {
			product_select_btn.forEach(el => {
				el.classList.remove("select");
				el.querySelector("span").dataset.i18n = "w_select";
				el.querySelector("span").textContent = i18next.t("w_select");
			});
		}
	}

	function addWishApi(productIdx, target) {
		if (productIdx != null) {
			$.ajax({
				type: "post",
				url: api_location + "order/whish/add",
				data: {
					"product_idx": productIdx
				},
				dataType: "json",
				async:false,
				error: function () {
					makeMsgNoti(getLanguage(), 'MSG_F_ERR_0091', null);
					// alert("위시리스트 등록/해제 처리에 실패했습니다.");
				},
				success: function (d) {
					let code = d.code;
					let msg = d.msg;
					let data = d.data;
					if (code == "200") {
						document.querySelector('.header__wrap .wishlist__btn').dataset.cnt = data;
						updateCurrentPageUi();
					}
				}
			});
		}
	}

	function heartDeleteWishApi(productIdx) {
		if (productIdx != null) {
			$.ajax({
				type: "post",
				url: api_location + "order/whish/delete",
				headers: {
					"country": getLanguage()
				},
				data: {
					"product_idx": productIdx
				},
				dataType: "json",
				error: function () {
					makeMsgNoti(getLanguage(), 'MSG_F_ERR_0091', null);
					// alert("위시리스트 등록/해제 처리에 실패했습니다.");
				},
				success: function (d) {
					let code = d.code;
					let msg = d.msg;
					let data = d.data;
					if (code == "200") {
						document.querySelector('.header__wrap .wishlist__btn').dataset.cnt = data;
						updateCurrentPageUi();

					}
				}
			});
		}
	}
}

function clickBtnUpdateWish() {
	let btn_update_wish = document.querySelectorAll('.btn_update_wish');
	btn_update_wish.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			
			let wishObj = {
				location : 'foryou',
				wishStatus: el.dataset.wish_flg,
				productIdx: el.dataset.product_idx,
				url: window.location.href
			}
			
			updateWishlist(el,wishObj);
		});
	});
}

/**
 * 
 * @param {*} obj 
 * @description 상품의 x표일시 사용되는 delete함수
 */
const deleteWish = (obj) => {
	let product_idx = $(obj).attr('product_idx');
	let basket_wrap = $(obj).parent().parent();
	let wish_list_cnt = document.querySelectorAll('.wish_list_mp').length;
	if (product_idx != null) {
		$.ajax({
			type: "post",
			url: api_location + "order/whish/delete",
			headers: {
				"country":getLanguage()
			},
			data: {
				"product_idx": product_idx
			},
			dataType: "json",
			
			error: function () {
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0091', null);
				// alert("위시리스트 등록/해제 처리에 실패했습니다.");
			},
			success: function (d) {
				let code = d.code;

				if (code == "200") {
					basket_wrap.remove();
					changeWishBtnStatus(product_idx);
					if (wish_list_cnt == 1) {
						let swiperContainer = document.querySelector(".swiper-grid");
						let swiperMsgWrap = document.createElement("div");
						swiperMsgWrap.className = "no_wishlist_msg";
						swiperMsgWrap.textContent = i18next.t('w_empty_msg');
						swiperContainer.appendChild(swiperMsgWrap);
					}
					document.querySelector('.header__wrap .wishlist__btn').dataset.cnt = d.data;
				}
				function changeWishBtnStatus(params) {
					let $$wishBtn = document.querySelectorAll('.wish__btn');
					$$wishBtn.forEach((el) => {
						if (el.getAttribute('product_idx') == product_idx) {
							el.querySelector('img').dataset.status = false;
							el.querySelector('img').setAttribute('src', '/images/svg/wishlist.svg');
						}
					})
				}
			}
		});
	}
}
/*------------------------------------------- 위시리스트 ------------------------------------------- */
function mobileWishEventHandler() {
	let quickContentWarp = document.querySelector(".quickview__content__wrap");
	let basketWrapBtn = document.querySelector(".basket__wrap--btn.nav");
	quickContentWarp.style.top = 'calc((-1)*(100vh - ' + basketWrapBtn.getBoundingClientRect().top + 'px - 30px))';
	quickContentWarp.style.transition = 'none';
	document.addEventListener('scroll', attachDetailWishBtnEvent);
}
function attachDetailWishBtnEvent() {
	let quickContentWarp = document.querySelector(".quickview__content__wrap");
	let basketWrapBtn = document.querySelector(".basket__wrap--btn.nav");
	quickContentWarp.style.top = 'calc((-1)*(100vh - ' + basketWrapBtn.getBoundingClientRect().top + 'px - 30px))';
}
function createMobileWishObserver() {
	let observer;
	let options = {
		root: null,
		rootMargin: '0px',
		threshold: 0
	}

	let target = document.querySelector(".quickview__content__wrap");
	observer = new IntersectionObserver((entries) => {
		entries.forEach(entry => {
			if (!entry.isIntersecting && !target.classList.contains('open')) {
				document.removeEventListener('scroll', attachDetailWishBtnEvent);
				target.style.transition = '';
				target.style.transition = 'all 0.5s';
				target.style.top = '0';

				let urlParts = location.href.split('?')[0].split('/');
				let path = '/' + urlParts.slice(3).join('/');
				if (path == '/product/detail' && window.innerWidth <= 1024) {
					$('#quickview').addClass('hidden');
				}
			}
		});
	}, options);
	observer.observe(target);
}
function createFooterObserver() {
	let observer;
	let options = {
		root: null,
		rootMargin: '0px',
		threshold: 0
	}

	let target = document.querySelector("footer");
	if (target != null) {
		observer = new IntersectionObserver((entries) => {
			const $body = document.querySelector("body");
			entries.forEach(entry => {
				let windowWidth = window.innerWidth;
				let urlParts = window.location.href.split('?')[0].split('/');

				let path = '/' + urlParts.slice(3).join('/');
				if (path != '/product/detail' || windowWidth >= 1024) {
					if (entry.isIntersecting && !$body.classList.contains("m_menu_open")) {
						document.addEventListener('scroll', attachDetailQuickview);
						let footerHeight = entry.boundingClientRect.height;

						document.querySelector("#quickview .quickview__box").classList.add("on");
						document.querySelector("#quickview .quickview__box").style.bottom = `${footerHeight}px`;
						attachDetailQuickview();
					} else {
						document.removeEventListener('scroll', attachDetailQuickview);
						document.querySelector("#quickview .quickview__box").classList.remove("on");
						document.querySelector("#quickview .quickview__box").style.bottom = `0px`;
					}
				}
			});
		}, options);
		observer.observe(target);
	}
}
function attachDetailQuickview() {
	let windowHeight = window.innerHeight;
	let footerTop = document.querySelector("footer").getBoundingClientRect().top;

	let boxBottom = 0;
	if (windowHeight - footerTop > 0) {
		boxBottom = windowHeight - footerTop
	}
	document.querySelector("#quickview .quickview__box").style.bottom = `${boxBottom}px`;
}
/**
 * @author 김성식
 * @description 세로 스크롤바 사이즈 반환
 */
function getScrollBarWidth() {
	var inner = document.createElement('p');
	inner.style.width = "100%";
	inner.style.height = "200px";

	var outer = document.createElement('div');
	outer.style.position = "absolute";
	outer.style.top = "0px";
	outer.style.left = "0px";
	outer.style.visibility = "hidden";
	outer.style.width = "200px";
	outer.style.height = "150px";
	outer.style.overflow = "hidden";
	outer.appendChild(inner);

	document.body.appendChild(outer);
	var w1 = inner.offsetWidth;
	outer.style.overflow = 'scroll';
	var w2 = inner.offsetWidth;
	if (w1 == w2) w2 = outer.clientWidth;

	document.body.removeChild(outer);

	return (w1 - w2);
};



/**
 * @author 김성식
 * @description 스크롤바 유무 판단
 */
$.fn.hasScrollBar = function () {
	return (this.prop("scrollHeight") == 0 && this.prop("clientHeight") == 0) || (this.prop("scrollHeight") > this.prop("clientHeight"));
};

/**
 * @author 김성식
 * @description 디바이스 체크
 */
function checkMobileDevice() {
	var mobileKeyWords = new Array('Android', 'iPhone', 'iPod', 'BlackBerry', 'Windows CE', 'SAMSUNG', 'LG', 'MOT', 'SonyEricsson');
	for (var info in mobileKeyWords) {
		if (navigator.userAgent.match(mobileKeyWords[info]) != null) {
			//mobile
			return true;
		}
	}
	//web
	return false;
}
/**
 * @author SIMJAE
 * @param {String} key 
 * @description location.href 주소값의 파라미터 키값
 */
function getUrlParamValue(key) {
	const urlParams = new URL(location.href).searchParams;
	const param_value = urlParams.get(`${key}`);
	return param_value;
}

/**
 * @author SIMJAE
 * @param {Object} product
 * @description 최근 본 상품 기록 10개로 갯수제한 
 */
function saveRecentlyViewed(product) {
	let product_idx = parseInt(product.product_idx);
	const keyName = "recentlyViewed";
	let recentlyViewed = localStorage.getItem('recentlyViewed');
	recentlyViewed = recentlyViewed ? new Set(JSON.parse(recentlyViewed)) : new Set();
	
	//console.log(Array.from(recentlyViewed));
	
	let arr_recent = Array.from(recentlyViewed);
	
	let already = 0;
	for (let i=0; i<arr_recent.length; i++) {
		let tmp_recent = JSON.parse(arr_recent[i]).product_idx;
		console.log(tmp_recent);
		if (tmp_recent == product_idx) {
			already++;
		}
	}
	
	const prevValue = JSON.parse(localStorage.getItem(keyName));
	/*
	if (recentlyViewed.has(JSON.stringify(product_idx))) {
		console.log('already viewed');
		// 이미 존재하는 상품이면 삭제 후 다시 추가하여 가장 최신 상품으로 보이도록 함
		recentlyViewed.delete(JSON.stringify(product_idx));
	}
	*/
	console.log(already);
	if (already > 0) {
		console.log('already viewed');
		recentlyViewed.delete(product_idx);
	}
	
	recentlyViewed.add(JSON.stringify(product));

	if (recentlyViewed.size > 10) {
		// 최근 본 상품이 10개를 초과하면 가장 오래된 상품 삭제
		const iterator = recentlyViewed.values();
		recentlyViewed.delete(iterator.next().value);
	}

	localStorage.setItem(keyName, JSON.stringify(Array.from(recentlyViewed)));
	let recentlyresultReverse;
	if (JSON.stringify(Array.from(recentlyViewed)) !== JSON.stringify(prevValue)) {
		recentlyresultReverse = Array.from(recentlyViewed).reverse();
	}
	recentlyresultReverse = Array.from(recentlyViewed).reverse();
	return recentlyresultReverse;
}
/**
 * @author SIMJAE
 * @description 번역할 json데이터 불러오는 fetch
 */
let krdata, endata, cndata;
const lang = getLanguage();
const krLnData = (() => {
	return fetch(domain_url + 'scripts/i18n/KR.json')
		.then(response => response.json())
		.then(data => {
			krdata = data;
		})
		.catch(error => {
			console.error('Error fetching data:', error);
		});
})();
const enLnData = (() => {
	return fetch(domain_url + 'scripts/i18n/EN.json')
		.then(response => response.json())
		.then(data => {
			endata = data;
		})
		.catch(error => {
			console.error('Error fetching data:', error);
		});
})();
const cnLnData = (() => {
	return fetch(domain_url + 'scripts/i18n/CN.json')
		.then(response => response.json())
		.then(data => {
			cndata = data;
		})
		.catch(error => {
			console.error('Error fetching data:', error);
		});
})();
/**
 * @author SIMJAE
 * @description i18next라이브러리로 불러온 json기반으로 다국어 변경
 */
function changeLanguage() {
	const ln = localStorage.getItem('lang') || getLanguage();

	i18next.init({
		lng: ln,
		resources: {
			KR: {
				translation: krdata
			},
			EN: {
				translation: endata
			},
			CN: {
				translation: cndata
			},
		},
	},

		function () {
			changeText();
			changePlaceholder();
		});

	i18next.on('languageChanged', function (lng) {
		localStorage.setItem('lang', lng);
		changeText();
		changePlaceholder();
	});

	function changeText() {
		const elements = document.querySelectorAll('[data-i18n]');
		elements.forEach(el => {
			const key = el.dataset.i18n;
			el.textContent = i18next.t(key);
		});
	}

	function changePlaceholder() {
		const elements = document.querySelectorAll('[data-i18n-placeholder]');
		elements.forEach(el => {
			const key = el.dataset.i18nPlaceholder;
			el.placeholder = i18next.t(key);
		});
	}

	return ln;
}
/**
 * @author YOON
 * @description 새로고침시 재번역
 */
function changeLanguageR() {
	// const ln = localStorage.getItem('lang') || getLanguage();
	const elements = document.querySelectorAll('[data-i18n]');
	elements.forEach(el => {
		const key = el.dataset.i18n;
		el.textContent = i18next.t(key);
	});
}
/**
 * @author SIMJAE
 * @description 디바운스 구현
 */
function debounce(func, delay) {
	let timerId;
	return function (...args) {
		if (timerId) {
			clearTimeout(timerId);
		}
		timerId = setTimeout(() => {
			func(...args);
			timerId = null;
		}, delay);
	};
}
/**
 * @author SIMJAE
 * @description 스로틀링 구현
 */
function throttle(func, interval) {
	let lastTime = 0;
	return function (...args) {
		const now = new Date().getTime();
		if (now - lastTime >= interval) {
			func(...args);
			lastTime = now;
		}
	};
}

const videoFomating = () => {
	var videos = document.querySelectorAll('video');
	videos.forEach(plyr => {
		let player = new Plyr(plyr, {
			volume: 0,
			autoplay: true,
			muted: true,
			clickToPlay: false, // clickable add by Hyuk
			disableContextMenu: false, // menu buttons add by Hyuk						
			hideControls: false, // controls pannel add by Hyuk
			fullscreen: {
				enabled: false // fullscreen add by Hyuk
			},
			controls: ['play', 'progress', 'mute', 'fullscreen'],
			settings: ['quality', 'speed', 'loop'],
			progressBar: {
			}
		});

		player.on('ready', function (event) {
			var instance = event.detail.plyr;
			var hslSource = null;
			var sources = instance.media.querySelectorAll('source'), i;
			for (i = 0; i < sources.length; ++i) {
				if (sources[i].src.indexOf('.m3u8') > -1) {
					hslSource = sources[i].src;
				}
			}

			if (hslSource !== null && Hls.isSupported()) {
				var hls = new Hls();
				hls.loadSource(hslSource);
				hls.attachMedia(instance.media);
				hls.on(Hls.Events.MANIFEST_PARSED, function () {
				});
			}
		});
	});
}

function getLanguage() {
	let local_lng = localStorage.getItem('lang');
	if (!local_lng) {
		let country = navigator.language || navigator.userLanguage;
		switch (country) {
			case "ko-KR":
				local_lng = "KR";
				break;

			case "zh-CN":
				local_lng = "CN";
				break;

			default:
				local_lng = "EN";
				break
		}
	}

	return local_lng;
}

/*
elementFadeIn(el, opacityPerTick, tickTime)
	el : Fadein 작업대상 노드
	opacityPerTick : 틱마다 증가시킬 opacity 값
	tickTime : 틱간 시간 (ms)
*/
function elementFadeIn(el, opacityPerTick, tickTime) {
	el.style.opacity = 0;
	var tick = function () {
		el.style.opacity = +el.style.opacity + opacityPerTick;
		if (+el.style.opacity < 1) {
			(window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, tickTime)
		}
	};
	tick();
}

function xssDecode(data) {
	var decode_str = null;
	if (data != null) {
		decode_str = data.replace(/&amp;/g, '&');
		decode_str = decode_str.replace(/&quot;/g, '\"');
		decode_str = decode_str.replace(/&apos;/g, "'");
		decode_str = decode_str.replace(/&lt;/g, '<');
		decode_str = decode_str.replace(/&gt;/g, '>');
		decode_str = decode_str.replace(/<br>/g, '\r');
		decode_str = decode_str.replace(/<p>/g, '\n');
	}

	return decode_str;
}

/**
 * @author EUNHYUNG
 * @description 쇼핑계속하기 버튼
 */
document.querySelectorAll('.continue-shopping-btn').forEach(function (button) {
	button.addEventListener('click', continueShopping);
});

function continueShopping() {
	let prev_url = document.referrer;
	let result = prev_url.indexOf('product');

	if (result < 0) {
		location.href = '/main';
	} else {
		location.href = prev_url;
	}
}

function tossWidgetModal() {
	if (document.querySelector('#toss_widget-modal') !== null) {
		document.querySelector('#toss_widget-modal').remove();
	}
	const body = document.body;
	const toss_widget_container = document.createElement("div");
	toss_widget_container.id = "toss_widget-modal";
	toss_widget_container.className = "toss_widget-containner";

	toss_widget_container.innerHTML = `
		<div class="toss_widget__background">
			<div class="toss_widget__wrap">
				<div class="toss_widget__box">
					<div class="close-btn">
						<svg xmlns="http://www.w3.org/2000/svg" width="12.707" height="12.707" viewBox="0 0 12.707 12.707">
							<path data-name="선 1772" transform="rotate(135 6.103 2.736)" style="fill:none;stroke:#343434" d="M16.969 0 0 .001"></path>
							<path data-name="선 1787" transform="rotate(45 -.25 .606)" style="fill:none;stroke:#343434" d="M16.969.001 0 0"></path>
						</svg>
					</div>
					<h1 class="title"></h1>
					<div id="payment-method"></div>
					<div id="agreement"></div>
				</div>
			</div>
		</div>
	`;

	body.appendChild(toss_widget_container);

	this.openModal = (() => {
		toss_widget_container.classList.add("open");
		modalClose();
	})();

	function modalClose() {
		let closeBtn = document.querySelector(`#toss_widget-modal .close-btn`);
		closeBtn.addEventListener("click", function () {
			toss_widget_container.classList.remove("open");
			document.querySelector('#toss_widget-modal').remove();
		});
	}
}

function setTelMobile(data) {
	data = data.replace(/[^0-9]/g, '');

	let tel_mobile = "";

	if (data.length < 4) {
		tel_mobile = data;
	} else if (data.length < 7) {
		tel_mobile = data.substr(0, 3) + "-" + data.substr(3);
	} else if (data.length < 11) {
		tel_mobile = data.substr(0, 3) + "-" + data.substr(3, 3) + "-" + data.substr(6);
	} else {
		tel_mobile = data.substr(0, 3) + "-" + data.substr(3, 4) + "-" + data.substr(7);
	}

	return tel_mobile;
}

function printPopupHtml(data) {
	let closeLocalstorageKey = `popup_close_${data.idx}`;
	let closeTime = localStorage.getItem(closeLocalstorageKey);

	if(closeTime != null){
		var now = new Date();
		now = now.setTime(now.getTime());
		if(parseInt(closeTime) <= now){
			initPopupCloseSetting(data.idx);
		}
		else if(parseInt(closeTime) > now){
			return false;
		}
	}

	const body = document.body;

	let tmp_popup = document.querySelector('#popup-container');
	if (tmp_popup != null) {
		tmp_popup.remove();
	}

	let close_one = 'popup_close_oneday';
	let close_never ='popup_close_never';
	let close_btn_id = '';
	if(data.close_flg == 'TODAY'){
		close_btn_id = 'today_none_open';
	}
	else{
		close_btn_id = 'none_open';
	}

	const popup = document.createElement("div");
	popup.id = "popup-container";
	popup.className = "popup-containner open";
	popup.innerHTML = `
		<div class="popup__background center middle">
			<div class="popup__wrap" style="width:${data.width}px;height:${data.height}px">
				<div class="close-btn">
					<svg xmlns="http://www.w3.org/2000/svg" width="12.707" height="12.707" viewBox="0 0 12.707 12.707">
						<path data-name="선 1772" transform="rotate(135 6.103 2.736)" style="fill:none;stroke:#343434" d="M16.969 0 0 .001"></path>
						<path data-name="선 1787" transform="rotate(45 -.25 .606)" style="fill:none;stroke:#343434" d="M16.969.001 0 0"></path>
					</svg>
				</div>
				<div class="popup__box">
					<div class="popup_header">
						<h1 class="title">${data.title}</h1>
					</div>
					
					<div class="popup_body" style="">
						${data.contents}
					</div>
					<div class="popup_logo"><img src="/images/landing/mini-logo.svg" alt=""></div>
				</div>
				<div class="do_not_open">
					<input type="checkbox" id="${close_btn_id}">
						<label for="${close_btn_id}"></label>
					<span data-i18n="${data.close_flg == 'TODAY' ? close_one : close_never}"></span>
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

	let close_btn = document.querySelector(`#popup-container .close-btn`);
	close_btn.addEventListener('click', function () {
		document.querySelector('#popup-container').remove();
	});
	document.getElementById(close_btn_id).addEventListener('change', function(){
		if(this.checked){
			if(close_btn_id == 'today_none_open'){
				setPopupClose('today', data.idx);
			}
			else if(close_btn_id == 'none_open'){
				setPopupClose('none', data.idx);
			}
		}
		else{
			initPopupCloseSetting(data.idx);
		}
	})
	popupResize(popupWidth,popupHeight);
}
function setPopupClose(popup_type, popup_idx){
	let key = `popup_close_${popup_idx}`;
	let param_day = 0;
	if(popup_type == 'today'){
		param_day = 1;
	}
	else{
		param_day = 9999;
	}

	var date = new Date();
	date = date.setTime(date.getTime() + param_day * 24 * 60 * 60 * 1000);
	localStorage.setItem(key, date);
}
function initPopupCloseSetting(popup_idx){
	localStorage.removeItem(`popup_close_${popup_idx}`);
}
function popupResize(width, height) {
	let windowWidth = window.innerWidth;
	let popup__wrap = document.querySelector('.popup__wrap');
	let popup_header = document.querySelector('.popup_header');
	let popup_body = document.querySelector('.popup_body');
	
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
		/*
		if(contents_width > 840){
			popup_header.style.width = "840px";
			popup_body.style.width = "840px";
		}
	
		contents_height = popup_header.clientHeight + popup_body.clientHeight;
		if(contents_height > 360){
			popup_body.style.height = `${360 - popup_header.clientHeight}px`;
		}
		*/
	}
	else{
		if(contents_width > windowWidth - 100){
			popup_header.style.width = `${windowWidth - 100}px`;
			popup_body.style.width = `${windowWidth - 100}px`;
		}
	
		contents_height = popup_header.clientHeight + popup_body.clientHeight;
		if(contents_height > 350){
			popup_body.style.height = `${350 - popup_header.clientHeight}px`;
		}
	}
};

/** 마일리지 천단위 변환 함수*/
function convertMileageFloor(obj) {
	let country = getLanguage();
	
	let mileage = $(obj).val(); 
	
	if (country == "KR") {
		mileage = Math.floor(mileage / 500) * 500;
	} else {
		mileage = Math.floor(mileage / 1) * 1;
	}

	$(obj).val(mileage);
}