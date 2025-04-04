window.addEventListener('DOMContentLoaded', () => {
	getMainInfo();
	changeHeaderColor();
});

window.addEventListener('resize', () => {
	resizeMainSwiper();
});

const observer = lozad();
observer.observe();

//슬라이더별 헤더색상 변경
let header_color = [];

let main_banner_swiper = null;

function getMainBannerSwipper() {
	main_banner_swiper = new Swiper(".new__project__swiper", {
		navigation: {
			nextEl: ".new__project__swiper .swiper-button-next",
			prevEl: ".new__project__swiper .swiper-button-prev",
		},
		pagination: {
			el: ".swiper-pagination",
			clickable: true,
		},
		grabCursor: true,
		slidesPerView: 1,
		on: {
			init: function() {
				$("header").removeClass("BK");
				$("header").removeClass("WH");
				$("header").addClass(header_color[0]);
			},
			slideChange : function() {
				$("header").removeClass("BK");
				$("header").removeClass("WH");
				$("header").addClass(header_color[main_banner_swiper.activeIndex]);
			}
		}
	});
}

let main_contents_product_swiper = new Swiper(".re-swiper", {
	navigation: {
		nextEl: ".re-swiper .swiper-button-next",
		prevEl: ".re-swiper .swiper-button-prev",
	},
	pagination: {
		el: ".swiper-pagination",
		clickable: true,
	},
	grabCursor: true,
	breakpoints: {
		1024: {
			slidesPerView: 4,
		}
	}
});

let main_images_swiper = new Swiper(".styling-swiper", {
	navigation: {
		nextEl: ".styling-swiper .swiper-button-next",
		prevEl: ".styling-swiper .swiper-button-prev",
	},
	pagination: {
		el: ".swiper-pagination",
		clickable: true,
	},
	grabCursor: true,
	breakpoints: {
		320: {
			slidesPerView: 1.32,
			spaceBetween: 10
		},
		1024: {
			slidesPerView: 3.2,
			spaceBetween: 0
		}
	},
	on: {
		activeIndexChange: function () {
			if (1 <= this.realIndex) {
				main_images_swiper.passedParams.centeredSlides = true;
				this.update();
				this.updateSize();
				this.updateSlides();
			}
		}
	}
});

function getDeviceType() {
	let device_type = null;
	
	let screen_width = document.querySelector(".styling-wrap").offsetWidth;
	if (screen_width < 1024) {
		device_type = "M";
	} else {
		device_type = "W";
	}
	
	return device_type;
}

const changeHeaderColor = () => {
	let header = document.querySelector("header");

	window.addEventListener('scroll', () => {
		let height = window.scrollY;

		if (height > 50) {
			header.classList.add("scroll");
		} else {
			header.classList.remove("scroll");
		}
	});
};

const getMainInfo = () => {
	$.ajax({
		type: "post",
		url: api_location + "landing/get",
		headers: {
			"country" : getLanguage()
		},
		dataType: "json",
		async: false,
		error: function () {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0060', null);
			//notiModal("메인 랜딩 조회처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			window.scrollTo(0,0);
			if (d.code == 200) {
				let data = d.data;

				if (data != null) {
					let banner_info = data.banner_info;
					if (banner_info != null && banner_info.length > 0) {
						writeMainBanner(banner_info);
					}
					
					let contents_info = data.contents_info;
					if (contents_info != null) {
						writeMainContents(contents_info);
						$('.main_contents_wrap').hide();
					}
					
					let product_info = data.product_info;
					if (product_info != null && product_info.length > 0) {
						writeMainContentsProduct(product_info);
					}
					
					let img_info = data.img_info;
					if (img_info != null && img_info.length > 0) {
						writeMainImages(img_info);
						$('#main_image_swiper').hide();
					}
					
					// main_banner_swiper.update();
					resizeMainSwiper();
					getMainBannerSwipper();
					//비디오 포멧
					videoFomating();
				}
			}
		}
	});
	
	$('.main_contents_wrap').fadeIn(1000)

	$('#main_image_swiper').fadeIn(1000);
	
	let login_status = getLoginStatus();
	if (login_status == 'true') {
		document.querySelector('.section_recommend_product').classList.remove('hidden');
		getRecommendProductInfo();
	}
	
	window.scrollTo(0,0);
}

function writeMainBanner(data) {
	let device_type = getDeviceType();
	
	let main_banner_html = "";
	
	data.forEach(banner => {
		header_color.push(banner.background_color);
		
		let banner_location_html = "";
		let content_type = banner.content_type;
		if (content_type == "IMG") {
			banner_location_html = `
				<picture>
					<source media="(max-width: 1024px)" srcset="${cdn_img}${banner.banner_location_mob}">
					<img class="lozad" src="${cdn_img}${banner.banner_location}" alt="">
				</picture>
			`;
		} else if (content_type == "MOV") {
			banner_location_html = `
				 <video autoplay loop muted>
					<source media="(max-width: 1024px)" src=${cdn_vid}${banner.banner_location_mob}" type="video/mp4">
					<source src="${cdn_vid}${banner.banner_location}" type="video/mp4">
				</video>
			`;
		}
		
		let banner_btn1_html = "";
		if (banner.btn1_display_flg == true) {
			banner_btn1_html = `
				<a href="${banner.btn1_url}" class="read__more under-line wh">
					${banner.btn1_name}
				</a>
			`;
		}
		
		let banner_btn2_html = "";
		if (banner.btn2_display_flg == true) {
			banner_btn2_html = `
				<a href="${banner.btn2_url}" class="read__more under-line wh">
					${banner.btn2_name}
				</a>
			`;
		}
		
		main_banner_html += `
			<div class="swiper-slide">
				${banner_location_html}
				<div class="new__project__content">
					<div class="cnt-box">
						<div class="season__title">${banner.title}</div>
						<div class="title">${banner.sub_title}</div>
						<div class="btn__wrap">
							${banner_btn1_html}
							${banner_btn2_html}
						</div>
					</div>
				</div>
			</div>
		`;
	});
	
	document.querySelector('#main_banner_swiper').innerHTML = main_banner_html;
}

function writeMainContents(data) {
	let main_contents_wrap = document.querySelector('.main_contents_wrap');
	
	let contents_btn_html = "";
	if (data.btn1_display_flg == true) {
		contents_btn_html += `
			<a href="${data.btn1_url}" class="btn under-line bk" data-i18n="ss_view_detail">
				${data.btn1_name}
			</a>
		`;
	}
	
	if (data.btn2_display_flg == true) {
		contents_btn_html += `
			<a href="${data.btn2_url}" class="btn under-line bk" data-i18n="o_goto_product">
				${data.btn2_name}
			</a>
		`;
	}
	
	let main_contents_html = `
		<img id="contents_img" class="exhibtion__img" src="${cdn_img}${data.img_location}" alt="">
		<div class="exhibtion__content">
			<div class="ex__box">
				<div id="contents_title" class="title">${data.title}</div>
				<div id="contents_sub_title" class="season__title">${data.sub_title}</div>
				<div class="btn__wrap contents_btn_wrap">
					${contents_btn_html}
				</div>
			</div>
		</div>
	`;
	
	main_contents_wrap.innerHTML = main_contents_html;
}

function writeMainContentsProduct(data) {
	let product_slide_w_html = ``;
	let product_slide_m_html = ``;
	
	data.forEach(product => {
		product_slide_w_html += `
			<div class="swiper-slide" onClick="location.href='/product/detail?product_idx=${product.product_idx}'">
				<a class="slide-box">
					<div class="center-box">
						<img src="${cdn_img}${product.img_location}" alt="">
						<div class="slide__title">${product.product_name}</div>
					</div>
				</a>
			</div>
		`;
		
		product_slide_m_html += `
			<a class="slide-box" onClick="location.href='/product/detail?product_idx=${product.product_idx}'">
				<div class="center-box">
					<img src="${cdn_img}${product.img_location}" alt="">
					<div class="title">${product.product_name}</div>
				</div>
			</a>
		`;
	});

	document.querySelector('#contents_wrapper_web').innerHTML = product_slide_w_html;
	document.querySelector('#contents_wrapper_mobile').innerHTML = product_slide_m_html;
}

function writeMainImages(data) {
	let main_images_html = "";
	data.forEach(img => {
		main_images_html += `
			<div class="swiper-slide">
				<div class="styling__card">
					<div class="styling-box">
						<a href="${img.btn_url}" class="style-img" style="background:url(${cdn_img}${img.img_location});"></a>
						
						<div class="t-box">
							<p class="title">
								${img.title}
							</p>
		`;
		
		if (img.btn_display_flg == true) {
			main_images_html += `
							<div class="btn__wrap">
								<a href="" class="under-line bk styling-title">
									<p class="sub-title" style="width:fit-content" onClick="location.href='${img.btn_url}'">
										${img.btn_name}
									</p>
								</a>
							</div>
			`;
		}
		
		main_images_html += `
						</div>
					</div>
				</div>
			</div>
		`;
	});

	document.querySelector('#main_image_swiper').innerHTML = main_images_html;
}

function resizeMainSwiper() {
	let screen_width = document.querySelector(".styling-wrap").offsetWidth;
	
	let next_btn = document.querySelector(".styling-swiper .navigation .swiper-button-next");
	let prev_btn = document.querySelector(".styling-swiper .navigation .swiper-button-prev");
	
	let styleTboxHeight = 0;
	let oneGridSize = 0;
	let mobileOneGridSize = 0;

	if(main_images_swiper.el.querySelector(".t-box") != null){
		styleTboxHeight = main_images_swiper.el.querySelector(".t-box").offsetHeight;
	}

	if (screen_width >= 1024) {
		oneGridSize = (screen_width / 16) / 2;
		
		next_btn.style.width = `${oneGridSize}px`;
		next_btn.style.height = `${styleTboxHeight}px`;
		
		prev_btn.style.width = `${oneGridSize}px`;
		prev_btn.style.height = `${styleTboxHeight}px`;
		
		main_contents_product_swiper.update();
	} else {
		screen_width = document.querySelector("body").offsetWidth;
		
		mobileOneGridSize = (screen_width / 8) * 2 - 20;
		next_btn.style.width = `${mobileOneGridSize}px`;
		next_btn.style.height = `${styleTboxHeight}px`;
	}
}

const getRecommendProductInfo = () => {
	$.ajax({
		type: "post",
		url: api_location + "common/recommend/get",
		dataType: "json",
		async: false,
		error: function () {
			makeMsgNoti(country, "MSG_F_ERR_0087", null);
			// notiModal("상품 진열페이지 조회처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			let data = d.data;
			if (data != null && data.length > 0) {
				const domFrag = document.createDocumentFragment();
				const recomment_product_wrapper = document.querySelector(".product_recommend_wrapper");
				
				let recommend_product_html = "";
				data.forEach(el => {
					const recommend_product_slide = document.createElement("div");
					recommend_product_slide.classList.add("swiper-slide");

					let product_link = "/product/detail?product_idx=" + `${el.product_idx}`;

					let wishBtnHtml = () => {
						let whish_flg = `${el.whish_flg}`;
						let whish_img = "";
						
						let txt_dataset = `data-location="foryou" data-wish_flg="${el.whish_flg}" data-product_idx="${el.product_idx}"`;
						
						if (whish_flg == 'true') {
							whish_img = `<img class="wish_img" data-status=${el.whish_flg} src="/images/svg/wishlist-bk.svg" alt="">`;
						} else if (whish_flg == 'false') {
							whish_img = `<img class="wish_img" data-status=${el.whish_flg} src="/images/svg/wishlist.svg" alt="">`;
						}
						
						return ` <div class="wish__btn btn_update_wish" product_idx="${el.product_idx}" ${txt_dataset}>${whish_img}</div>`
					}

					recommend_product_html = `
						<div>
							${wishBtnHtml()}
							<div onClick="location.href='${product_link}'">
								<img src="${cdn_img}${el.product_img}" alt="">
								<div class="prd-title"><p>${el.product_name}</p></div>
							</div>
						</div>
					`;

					recommend_product_slide.innerHTML = recommend_product_html;
					domFrag.appendChild(recommend_product_slide);
					
					$('.product_recommend_wrapper').hide();
				});

				recomment_product_wrapper.appendChild(domFrag);
				
				clickBtnUpdateWish();
			} else {
				$('.foryou-wrap').hide();
			}
		}
	});
	
	let main_recommend_swiper = new Swiper(".foryou-swiper", {
		navigation: {
			nextEl: ".foryou-swiper .swiper-button-next",
			prevEl: ".foryou-swiper .swiper-button-prev",
		},
		pagination: {
			el: ".swiper-pagination",
			clickable: true,
		},
		grabCursor: true,
		breakpoints: {
			320: {
				slidesPerView: 2.64
			},
			1024: {
				slidesPerView: 5.318
			}
		}
	});
	
	$('.product_recommend_wrapper').fadeIn(100);
}
