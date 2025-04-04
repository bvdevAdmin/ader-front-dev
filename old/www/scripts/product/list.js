window.addEventListener('DOMContentLoaded', function () {
	/* 상품 진열 페이지 리스트 조회 */
	getProductList();
	
	toggleSortBtn();
	clickImgTypeBtn();
	$("#quickview").removeClass("hidden");
	checkFilterProductCnt();
	clickBtnProductSort();
	
	getFilterInfo();
});

window.addEventListener("scroll", function () {
	const scroll_height = window.scrollY;
	const window_height = window.innerHeight;
	const doc_total_height = document.body.offsetHeight;

	const is_bottom = window_height + scroll_height >= (doc_total_height - 2);

	let product_list_wrap = document.querySelector(".product__list__wrap");

	if (more_flg == false) {
		if (scroll_height > (window_height - 450) && is_bottom == true) {
			let calc_last_idx = parseInt(product_list_wrap.dataset.last_idx);
			if (calc_last_idx > 0) {
				calc_last_idx += 12;
			} else {
				calc_last_idx = 12;
			}

			if (calc_last_idx / 60 > 0 && calc_last_idx % 60 == 0) {
				more_flg = true;
			} else {
				more_flg = false;
			}

			product_list_wrap.dataset.last_idx = calc_last_idx;

			let filter_param = checkFilterParam();
			
			/* 상품 진열 페이지 리스트 조회 (스크롤) */
			getProductListByScroll(filter_param);
		}
	}
});

let product_list_swiper;
let window_width = window.innerWidth;
let order_param = null;
let product_interval = 0;
let more_flg = false;

/* 모바일 & 웹 상품 카테고리 스와이프 */
const productCategorySwiper = () => {
	const swiper = new Swiper(".prd__menu__swiper", {
		navigation: {
			nextEl: ".prd__menu__swiper .swiper-button-next",
			prevEl: ".prd__menu__swiper .swiper-button-prev",
		},
		pagination: {
			el: ".swiper-pagination",
			clickable: true,
		},
		scrollbar: {
			el: '.prd__menu__swiper .swiper-scrollbar',
			draggable: true,
			dragSize: 45
		},
		mousewheel: {
			scrollAmount: 0.1,
		},
		grabCursor: true,
		spaceBetween: 10,
		breakpoints: {
			320: {},
			500: {},
			1024: {},
			1280: {},
			1440: {},
			1920: {}
		}
	});
	let slide = document.querySelectorAll(".prd__menu__box");
	let idx = 0;

	for (let i = 0; i < slide.length; i++) {
		if (slide[i].classList.contains("select") == true) {
			idx = i;
		}
	}

	swiper.params.slidesPerView = 'auto';
	swiper.update();
	swiper.slideTo(idx);
};

const productSml = () => {
	let productListSwiper = new Swiper(".prd__menu__category", {
		grabCursor: true,
		slidesPerView: "auto",
		pagination: {
			el: ".prd__menu__category .swiper-pagination",
			clickable: true,
		},
		breakpoints: {
			320: {
				spaceBetween: 10,
			},
			1024: {
				spaceBetween: 20,
			},
		},
	});
};

const imgSwiper = (move) => {
	let productImg = document.querySelectorAll('.product-img');
	if (typeof (product_list_swiper) == 'object') [...product_list_swiper].map(el => el.destroy());

	return product_list_swiper = new Swiper('.product-img', {
		// autoHeight: true,
		grabCursor: true,
		slidesPerView: 1,
		observer: true,
		observeParents: true,
		allowTouchMove: move,
		navigation: {
			nextEl: ".product-img .swiper-button-next",
			prevEl: ".product-img .swiper-button-prev",
		},
	});
}

//상품 불러오는 api
const getProductList = () => {
	let { menu_type, menu_idx, page_idx, last_idx } = document.querySelector(".product__list__wrap").dataset;

	$.ajax({
		type: "post",
		url: api_location + "product/list/get",
		data: {
			"country"		:getLanguage(),
			"menu_idx"		:menu_idx,
			"menu_type"		:menu_type,
			"page_idx"		:page_idx,
			"last_idx"		:last_idx,
			"order_param"	:order_param,
			"filter_param"	:checkFilterParam()
		},
		dataType: "json",
		error: function () {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0087", null);
			// alert("상품 진열페이지 조회처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				if (data != null) {
					let menu_info = data.menu_info;
					if (menu_info != null && menu_info.length > 0) {
						writeMenuHtml(menu_info);
					}
					
					let filter_info = data.filter_info;
					if (filter_info != null && filter_info.length > 0) {
						writeBTN_filter(filter_info);
					}
					
					const domFrag = document.createDocumentFragment();
					let product_list_body = document.querySelector(".product__list__body");
					product_list_body.innerHTML = "";

					let product_list_html = "";
					
					let grid_info = data.grid_info;
					if (grid_info != null && grid_info.length > 0) {
						product_list_html = writeProductListHtml(grid_info);
					} else {
						product_list_html = `<div class="none_product_list">조회 결과가 없습니다.</div>`
					}

					let product_list_box = document.createElement("div");
					product_list_box.classList.add("product-wrap");
					product_list_box.dataset.grid = "4";
					product_list_box.dataset.webpre = "4";
					product_list_box.dataset.mobilepre = "3";

					product_list_box.innerHTML = product_list_html;
					domFrag.appendChild(product_list_box);
					product_list_body.appendChild(domFrag);

					setProductListFadeIn();

					productListSelectGrid();
					productCategorySwiper();
					productSml();
					swiperStateCheck();
				}
			} else {
				notiModal(d.msg);
				//location.href = "/main";
			}
			
			clickBtnUpdateWish();
		}
	});
}

function writeMenuHtml(data) {
	const menu_list = document.querySelector(".prd__menu__grid");

	let menu_list_html = `
						<div class="prd__menu__swiper">
							<div class="swiper-wrapper">
	`;

	data.forEach(el => {
		let selected = "";
		if (el.selected == true) {
			document.querySelector('.sort-title').textContent = el.menu_location;
			selected = "select";
		}

		menu_list_html += `
								<div class="swiper-slide" onClick="location.href='${el.menu_link}'">
									<div class="prd__menu__box ${selected}">
										<div class="prd__img__wrap">
											<img class="prd__img" src="${cdn_img}${el.img_location}" alt="">
										</div>
										<p class="prd__title">${el.menu_title}</p>
									</div>
								</div>
						`;
	});

	menu_list_html += `
							</div>
						</div>
					`;

	menu_list.innerHTML = menu_list_html;
}

function writeWishBtnHtml(wish_flg, product_idx) {
	let wish_btn_html = "";

	let whish_img = "";
	
	let txt_dataset = `data-location="list" data-wish_flg="${wish_flg}" data-product_idx="${product_idx}"`;

	let login_status = getLoginStatus();
	if (login_status == "true") {
		if (wish_flg == true) {
			whish_img = `<img class="wish_img" data-status=${wish_flg} src="/images/svg/wishlist-bk.svg" alt="">`;
		} else if (wish_flg == false) {
			whish_img = `<img class="wish_img" data-status=${wish_flg} src="/images/svg/wishlist.svg" alt="">`;
		}
	} else {
		whish_img = `<img class="wish_img" data-status=${wish_flg} src="/images/svg/wishlist.svg" alt="">`;
	}

	wish_btn_html = `
		<div class="wish__btn btn_update_wish" product_idx="${product_idx}" ${txt_dataset}>
			${whish_img}
		</div>
	`;

	return wish_btn_html;
}
function writeProductImgSlide(img_param, slide_type, product_idx, data, seo_author, seo_alt_text) {
	let product_img_slide_html = "";
	let slide_hidden = "";
	if (img_param == slide_type) {
		slide_hidden = 'style="display:none;"';
	}

	let img_type = "";
	if (slide_type == "P") {
		img_type = "item";
	} else if (slide_type == "O") {
		img_type = "outfit";
	}

	data.forEach(img => {
		product_img_slide_html += `
			<div class="swiper-slide product_img_swiper" data-imgtype="${img_type}" ${slide_hidden}>
				<a href="/product/detail?product_idx=${product_idx}">
					<p>${seo_author}</p>
					<img class="prd-img" cnt="${product_idx}" src="${cdn_img}${img.img_location}" alt="${seo_alt_text}">
				</a>
			</div>
		`;
	});

	return product_img_slide_html;
}

function writeProductColorHtml(data) {
	let product_color_html = "";

	data.forEach((color, idx) => {
		let max_cnt = 5;
		let color_rgb = color.color_rgb;

		if (color_rgb != null) {
			let multi = color_rgb.split(";");
			if (idx < max_cnt) {
				if (multi.length == 2) {
					if (multi[1].length > 0) {
						style = `background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%)`;
					} else {
						style = `background-color:${multi[0]}`;
					}
				} else if (multi.length == 1) {
					style = `background-color:${multi[0]}`;
				}

				product_color_html += `
					<div class="color" data-color="${color.color_rgb}" data-soldout="${color.stock_status}" style="${style}"></div>
				`;
			}
		}
	});

	return product_color_html;
}

function writeProductSizeHtml(product_type, data) {
	let product_size_html = "";

	if (product_type != "S") {
		data.map(size => {
			product_size_html += `
				<li class="size" data-soldout="${size.stock_status}">
					${size.option_name}
				</li>
			`;
		});
	} else {
		product_size_html += "Set";
	}

	return product_size_html;
}

function writeProductListHtml(data) {
	let product_list_html = "";
	
	data.forEach(el => {
		if (el.grid_type == "PRD") {
			let seo_author = el.seo_author;
			let seo_alt_text = el.seo_alt_text;

			let product_idx = el.product_idx;

			wish_btn_html = writeWishBtnHtml(el.whish_flg, el.product_idx)
			let product_p_slide_html = "";
			let product_o_slide_html = "";
			let product_price_html = "";

			let img_param = $('#img_param').val();
			let p_img = el.product_img.product_p_img;
			let o_img = el.product_img.product_o_img;
			let product_color = el.product_color;
			let product_size = el.product_size;

			if (p_img != null && p_img.length > 0) {
				product_p_slide_html = writeProductImgSlide(img_param, "P", product_idx, p_img, seo_author, seo_alt_text);
			}
			if (o_img != null && o_img.length > 0) {
				product_o_slide_html = writeProductImgSlide(img_param, "O", product_idx, o_img, seo_author, seo_alt_text);
			} 
			else{
				product_o_slide_html = writeProductImgSlide(img_param, "O", product_idx, p_img, seo_author, seo_alt_text);
			}
			/*
			else {
				product_o_slide_html = writeProductImgSlide(img_param, "O", product_idx, p_img);
			}
			*/

			let discount_flg = "false";
			if (el.price != null) {
				let discount = el.discount;
				if (discount > 0 && el.stock_status != "STSO") {
					discount_flg = "true";
					product_price_html = `
						<span>${el.txt_price}</span>
					`;
				} else {
					product_price_html = el.txt_price;
				}
			}

			let color_cnt = 0;
			let max_color = "";

			let product_color_html = "";
			if (product_color != null && product_color.length > 0) {
				color_cnt = product_color.length;
				if (color_cnt > 5) {
					max_color = "over";
					color_cnt -= 5;
				}

				product_color_html = writeProductColorHtml(product_color);
			}

			let product_size_html = "";
			if (product_size != null && product_size.length > 0) {
				product_size_html = writeProductSizeHtml(el.product_type, product_size);
			}

			product_list_html += `
				<div class="product prd op_0">
					${wish_btn_html}
					<a href="/product/detail?product_idx=${product_idx}">
						<div class="product-img swiper">
							<div class="swiper-wrapper">						
								${product_p_slide_html}
								${product_o_slide_html}
							</div>
							<div class="swiper-button-prev"></div>
							<div class="swiper-button-next"></div>
						</div>
					</a>
					
					<div class="product-info">
						<div class="info-row">
							<div class="name" data-soldout="${el.stock_status != null && el.stock_status == 'STCL' ? 'STCL' : ''}">
								<span>${el.product_name}</span>
							</div>
								
							<div class="price" data-soldout="${el.stock_status}" data-saleprice="${el.txt_sales_price}" data-discount="${el.discount}" data-dis="${discount_flg}">
								<div>${product_price_html}</div>
							</div>
						</div>
							
						<div class="color-title">
							<span>${el.color}</span>
						</div>
							
						<div class="info-row">
							<div class="color__box" data-maxcount="${max_color}" data-colorcount="${color_cnt}">${product_color_html}</div>
							<div class="size__box">${product_size_html}</div>
						</div>
					</div>
				</div>
				`;
		} else if (el.grid_type == "IMG") {
			let g2 = el.clip_info[0];
			let g4 = el.clip_info[1];

			product_list_html += `
				<div class="product product-inside-banner" data-g2x="${g2.location_start}" data-g2y="${g2.location_end}" data-g4x="${g4.location_start}" data-g4y="${g4.location_end}" style="width:50%;background-image:url('${cdn_img}${el.banner_location}')">
				</div>`;
		} else if (el.grid_type == 'VID') {
			product_list_html += `
				<div class="product product-inside-banner" style="width:50%;">
				<video autoplay muted loop>
						<source src="${cdn_img}${el.banner_location}" type="video/mp4">
				</video>
				</div>
			`;
		}
	});

	return product_list_html;
}

function setProductListFadeIn() {
	let product_list_body = document.querySelector('.product__list__body');
	product_list_body.style.minHeight = 0;

	let product = product_list_body.querySelectorAll('.product.prd.op_0');
	product.forEach((el, idx) => {
		let setTimeoutId = setTimeout(function () {
			productFadeIn(el);
			el.classList.remove('op_0');
		}, 100 * (idx));
	});
}

function productFadeIn(el) {
	el.style.opacity = 0;
	var tick = function () {
		el.style.opacity = +el.style.opacity + 0.01;
		if (+el.style.opacity < 1) {
			(window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 16)
		}
	};
	tick();
}

//상품 스와이프
function swiperStateCheck() {
    const isWideScreen = window.matchMedia('screen and (min-width:1025px)').matches;
    const { grid, webpre, mobilepre } = document.querySelector(".product-wrap").dataset;

    if (isWideScreen) {
        if (webpre === "2") {
            imgSwiper(true);
        } else if (webpre === "4" && product_list_swiper !== undefined) {
            product_list_swiper.forEach(el => el.disable());
        }
    } else if (mobilepre === "1") {
        imgSwiper(true);
    } else if (mobilepre === "2" || mobilepre === "3") {
        if (product_list_swiper !== undefined) {
            product_list_swiper.forEach(el => el.disable());
        }
    }
}


//그리드 설정
const productListSelectGrid = () => {
	let $body = document.querySelector("body");
	let $prdListBox = document.querySelector(".product-wrap");
	let mql = window.matchMedia("screen and (max-width: 1024px)");

	let $webSortGrid = document.querySelector(".rW.sort__grid");
	let $websortSpan = document.querySelector(".rW.sort__grid").querySelector('span');
	let $websortImg = document.querySelector(".rW.sort__grid").querySelector('img');

	let $mobileSortGrid = document.querySelector(".rM.sort__grid");
	let $mobileSortSpan = document.querySelector(".rM.sort__grid").querySelector('span');
	let $mobileSortImg = document.querySelector(".rM.sort__grid").querySelector('img');
	let resizeTimer = null;

	let sort = document.querySelector(".sort-container");
	let btnMotion = document.querySelector(".oder-btn-motion");
	let filter = document.querySelector(".filter-container");

	//그리드 초기화 
	if (mql.matches) {
		mobileGridEvent();
	} else {
		webGridEvent();
	}
	
	//웹 sort 버튼 클릭
	$webSortGrid.addEventListener("click", () => {
		webGridEvent();
		
		swiperStateCheck();
		
		//처리가 resize이벤트와 동일하기 때문에 트리거 실행
		$(window).trigger("resize");
		
		sort.classList.remove("open");
		filter.classList.remove("open");
		
		//btnMotion.classList.remove("rotate");
		//btnMotion.classList.add("rotate_back");
		
		document.querySelector(".filter-container").classList.remove("open");
		document.querySelector(".filter-body").classList.remove("open");
		document.querySelector(".filter-motion-btn").classList.remove("open");
	});

	//모바일 sort 버튼 클릭
	$mobileSortGrid.addEventListener("click", () => {
		mobileGridEvent();
		
		swiperStateCheck();
		
		bannerHeightBySiblingElements();
		sort.classList.remove("open");
		filter.classList.remove("open");
		
		//btnMotion.classList.remove("rotate");
		//btnMotion.classList.add("rotate_back");
		
		document.querySelector(".filter-container").classList.remove("open");
		document.querySelector(".filter-body").classList.remove("open");
		document.querySelector(".filter-motion-btn").classList.remove("open");
	});

	window.addEventListener('resize', function () {
		if (window_width != $(window).width()) {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(gridResize);
			swiperStateCheck();

			window_width = $(window).width();
		}
	}, false);

	function webGridEvent() {
		const $productWrapEl = document.querySelector(".product-wrap");

		let currentWebGrid = document.querySelector(".rW.sort__grid").dataset.grid;
		switch (currentWebGrid) {
			case "2":
				$prdListBox.dataset.grid = 2;
				$prdListBox.dataset.webpre = 2;

				//그리드 박스 변경
				$webSortGrid.dataset.grid = 4;
				$websortSpan.dataset.i18n = 'pl_change_layout_04';
				$websortSpan.textContent = i18next.t('pl_change_layout_04');
				$websortImg.src = '/images/svg/grid-cols-4.svg';
				break;

			case "4":
				//그리드 버튼 변경
				$prdListBox.dataset.grid = 4;
				$prdListBox.dataset.webpre = 4;

				$webSortGrid.dataset.grid = 2;
				$websortSpan.dataset.i18n = 'pl_change_layout_02';
				$websortSpan.textContent = i18next.t('pl_change_layout_02');
				$websortImg.src = '/images/svg/grid-cols-2.svg';
				break;
		}
	}

	function mobileGridEvent() {
		currentGrid = document.querySelector(".rM.sort__grid").dataset.grid;
		switch (currentGrid) {
			case "1":
				$prdListBox.dataset.mobilepre = 1;
				$prdListBox.style.gridTemplateColumns = "repeat(8, 1fr)";
				$prdListBox.dataset.grid = 1;

				$mobileSortGrid.dataset.grid = 2;
				$mobileSortImg.src = '/images/svg/grid-cols-2.svg';
				$mobileSortSpan.dataset.i18n = 'pl_change_layout_02_m';
				$mobileSortSpan.textContent = i18next.t('pl_change_layout_02_m');
				break;

			case "2":
				$prdListBox.dataset.mobilepre = 2;
				$prdListBox.style.gridTemplateColumns = "repeat(8, 1fr)";
				$prdListBox.dataset.grid = 2;

				$mobileSortGrid.dataset.grid = 3;
				$mobileSortImg.src = '/images/svg/grid-cols-3.svg';
				$mobileSortSpan.dataset.i18n = 'pl_change_layout_03_m';
				$mobileSortSpan.textContent = i18next.t('pl_change_layout_03_m');
				break;

			case "3":
				$prdListBox.dataset.mobilepre = 3;
				$prdListBox.style.gridTemplateColumns = "repeat(9, 1fr)"
				$prdListBox.dataset.grid = 3;

				$mobileSortGrid.dataset.grid = 1;
				$mobileSortImg.src = '/images/svg/grid-cols-1.svg';
				$mobileSortSpan.dataset.i18n = 'pl_change_layout_01_m';
				$mobileSortSpan.textContent = i18next.t('pl_change_layout_01_m');
				break;
		}

		return currentGrid;
	}

	//사이즈 변경시 그리드 대응
	function gridResize() {
		let webBeforeGrid = $prdListBox.dataset.webpre;
		let mobileBeforeGrid = $prdListBox.dataset.mobilepre;
		let screenWidth = document.querySelector("body").offsetWidth;
		if (1024 <= screenWidth) {
			$prdListBox.style.gridTemplateColumns = "repeat(16, 1fr)"
			$prdListBox.dataset.grid = webBeforeGrid;
		} else {
			if (mobileBeforeGrid === 1) {
				$mobileSortImg.src = `/images/svg/grid-cols-2.svg`;
			} else if (mobileBeforeGrid === 2) {
				$mobileSortImg.src = `/images/svg/grid-cols-2.svg`;
			} else {
				$mobileSortImg.src = `/images/svg/grid-cols-2.svg`;
			}
			$prdListBox.style.gridTemplateColumns = "repeat(9, 1fr)";
			$prdListBox.dataset.grid = mobileBeforeGrid;
		}
	}
}

function clickImgTypeBtn() {
	let img_param = $('#img_param');
	let img_type_text = "";
	let key = "";
	let typeButton = document.querySelector(".type-btn");

	typeButton.addEventListener("click", function () {
		let items = document.querySelectorAll(".product-img .swiper-slide[data-imgtype='item']");
		let outfits = document.querySelectorAll(".product-img .swiper-slide[data-imgtype='outfit']");
		let sort = document.querySelector(".sort-container");
		let btnMotion = document.querySelector(".oder-btn-motion");
		let filter = document.querySelector(".filter-container");

		if (img_param.val() == "O") {
			img_param.val('P');
			img_type_text = "아이템";

			$(".type-btn img").attr("src", "/images/svg/item.svg").css("height", "12px");
			$('#img_type_text').attr("data-i18n", "pl_view_product");

			key = "pl_view_product";

			items.forEach(el => el.style.display = "none");
			outfits.forEach(el => el.style.display = "block");
		} else if (img_param.val() == "P") {
			img_param.val('O');
			img_type_text = "착용컷";

			$(".type-btn img").attr("src", "/images/svg/cloth.svg").css("height", "17px");
			$('#img_type_text').attr("data-i18n", "pl_model_cut");

			key = "pl_model_cut";

			items.forEach(el => el.style.display = "block");
			outfits.forEach(el => el.style.display = "none");
		}

		sort.classList.remove("open");
		filter.classList.remove("open");
		//btnMotion.classList.remove("rotate");
		//btnMotion.classList.add("rotate_back");
		document.querySelector(".filter-container").classList.remove("open");
		document.querySelector(".filter-body").classList.remove("open");
		document.querySelector(".filter-motion-btn").classList.remove("open");

		$('#img_type_text').text(i18next.t(key));
	});
}

function productLoading(gif) {
	product_list_wrap = $('.product__list__wrap');
	if (product_list_wrap.find('.loading_bar').length == 0) {
		let loadingBar = `
		<div class="loading_bar">
			<img class="loading_img" src="${gif}">
		</div>
		`;

		product_list_wrap.append(loadingBar);
	}
}

function closeProductLoading() {
	$(".loading_bar").remove();
}

/* 상품 진열 페이지 리스트 조회 (스크롤) */
function getProductListByScroll() {
	if (more_flg == false) {
		let product_list_wrap = document.querySelector('.product__list__wrap');
		let { menu_idx, menu_type, page_idx, last_idx } = product_list_wrap.dataset;

		$.ajax({
			type: "post",
			url: api_location + "product/list/get",
			data: {
				"country"		:getLanguage(),
				"menu_type"		:menu_type,
				"menu_idx"		:menu_idx,
				"page_idx"		:page_idx,
				"last_idx"		:last_idx,
				"order_param"	:order_param,
				"filter_param"	:checkFilterParam()
			},
			dataType: "json",
			beforeSend: function () {
				productLoading('/images/product/loading_img.gif');
			},
			error: function () {
				closeProductLoading();
				
				makeMsgNoti(getLanguage(), "MSG_F_ERR_0087", null);
			},
			success: function (d) {
				closeProductLoading();
				
				if (d.code == 200) {
					let data = d.data;
					if (data != null) {
						let grid_info = data.grid_info;
						if (grid_info != null && grid_info.length > 0) {
							let product_list_html = writeProductListHtml(grid_info);
							$(".product-wrap").append(product_list_html);
						}
						
						setProductListFadeIn();
						
						bannerHeightBySiblingElements();
					} else {
						$('.show_more_btn').remove();
					}
				}
			}
		});
	} else {
		let show_more_btn_html = `
			<div class="show_more_btn">
				<span class="add-btn" data-i18n="pl_view_more">
					더보기 +
				</span>
			</div>
		`;

		$('.product__list__wrap').append(show_more_btn_html);

		let show_more_btn = document.querySelector('.show_more_btn');
		show_more_btn.addEventListener('click', function (e) {
			e.currentTarget.remove();
			more_flg = false;
			
			getProductListByScroll();
		});

		show_more_btn.querySelector('.add-btn').textContent = i18next.t('pl_view_more');
		
		clickBtnUpdateWish();
	}
}

/* 페이지 상품 별 필터 정보 취득 */
function getFilterInfo() {
	let { menu_idx, menu_type, page_idx } = document.querySelector(".product__list__wrap").dataset;

	$.ajax({
		type: "POST",
		url: api_location + "product/list/get",
		data: {
			"country": getLanguage(),
			"menu_idx": menu_idx,
			"menu_type": menu_type,
			"page_idx": page_idx
		},
		async:false,
		dataType: "json",
		error: function () {
			// alert('페이지별 필터정보 취득중 오류가 발생했습니다.');
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0097", null);
		},
		success: function (d) {
			let filter_info = d.data.filter_info;
			writeBTN_filter(filter_info);
		}
	});
}

function writeBTN_filter(data) {
	let filter = document.querySelector(".filter-btn");
	let filter_container = document.querySelector(".filter-container");
	let filter_body = document.querySelector(".filter-body");
	let filter_motion = document.querySelector(".filter-motion-btn");

	let {filter_cl,filter_ft,filter_gp,filter_ln,filter_sz} = data;

	setFilterColorHtml();
	setFilterFitHtml();
	setFilterGraphicHtml();
	setFilterLineHtml();
	setFilterSizeHtml();
	
	mobileFilterEvent();
	selectFilterOption();

	//색상
	function setFilterColorHtml() {
		let filter_box = document.createElement("ul");
		filter_box.className = "filter-box color filter-toggle";

		filter_cl.forEach(el => {
			let {
				filter_idx, filter_name, rgb_color
			} = el;

			let filer_color = document.createElement("li");
			filer_color.className = "filter-color";
			filer_color.dataset.filter_idx = filter_idx;

			filer_color.innerHTML = `
				<span class="filter-title">${filter_name}</span>
				<div class="color__box">
					<div class="color-line" style="--background-color:${rgb_color}">
						<div class="color" data-title="${rgb_color}"></div>
					</div>
				</div>
			`;

			filter_box.appendChild(filer_color);
		});

		document.querySelector(".filter-content.color").appendChild(filter_box);
	}

	//핏
	function setFilterFitHtml() {
		let filter_box = document.createElement("ul");
		filter_box.className = "filter-box fit filter-toggle";

		filter_ft.forEach(el => {
			let { fit } = el

			let filter_fit = document.createElement("li");
			filter_fit.className = "filter-fit";
			filter_fit.dataset.fit = fit;

			filter_fit.innerHTML = `
				<span class="filter-title" id="filter-fit">${fit}</span>
			`;

			filter_box.appendChild(filter_fit);
		});

		document.querySelector(".filter-content.fit").appendChild(filter_box);
	}

	//그래픽
	function setFilterGraphicHtml() {
		let filter_box = document.createElement("ul");
		filter_box.className = "filter-box graphic filter-toggle";

		filter_gp.forEach((el, idx) => {
			let { graphic } = el;

			let filter_graphic = document.createElement("li");
			filter_graphic.className = "filter-graphic";
			filter_graphic.dataset.graphic = graphic;

			filter_graphic.innerHTML = `
				<span class="filter-title">${graphic}</span>
			`;

			filter_box.appendChild(filter_graphic);
		});

		document.querySelector(".filter-content.graphic").appendChild(filter_box);
	}

	function setFilterLineHtml() {
		let filter_box = document.createElement("ul");
		filter_box.className = "filter-box line filter-toggle";

		filter_ln.forEach(el => {
			let { line_idx, line_name } = el;
			let filter_line = document.createElement("li");

			filter_line.className = "filter-line";
			filter_line.dataset.line_idx = line_idx;

			filter_line.innerHTML = `
				<span class="filter-title">${line_name}</span>
			`

			filter_box.appendChild(filter_line);
		});

		document.querySelector(".filter-content.line").appendChild(filter_box);
	}

	function setFilterSizeHtml() {
		let filter_box = document.createElement("ul");
		filter_box.className = "filter-box size";

		filter_sz.forEach((el) => {
			let { filter_sz_ac, filter_sz_ht, filter_sz_jw, filter_sz_lw, filter_sz_sh, filter_sz_ta, filter_sz_up } = el;

			let filterMdl = document.createElement("div");
			filterMdl.className = "filter-mdl filter-toggle";

			Object.entries(el).forEach(([key, value]) => {
				if (value.length !== 0) {
					let size = sizeBox(key,value);
					filterMdl.appendChild(size);
				}
			});

			document.querySelector(".filter-content.size").appendChild(filterMdl);

			function sizeBox(key,data) {
				let filter_title = "";
				switch (key) {
					case filter_sz_ac:
						filter_title = i18next.t('pl_filter_accessory');
						break;

					case filter_sz_ht:
						filter_title = i18next.t('pl_filter_hat');
						break;

					case filter_sz_jw:
						filter_title = i18next.t('pl_filter_jewelry');
						break;

					case filter_sz_lw:
						filter_title = i18next.t('pl_filter_bottom');
						break;

					case filter_sz_sh:
						filter_title = i18next.t('pl_filter_shoes');
						break;

					case filter_sz_ta:
						filter_title = i18next.t('pl_filter_techacc');
						break;

					case filter_sz_up:
						filter_title = i18next.t('pl_filter_top');
						break;
				}

				let filter_box = document.createElement("ul");
				filter_box.className = "fiter-box";

				let li_box = document.createElement("div");
				li_box.className = "size-li-wrap";

				filter_box.innerHTML = `<summary class="filter-mdl-title">${filter_title}</summary>`;
				data.forEach(el => {
					let { filter_idx, filter_name, size_sort } = el;

					let filter_size = document.createElement("li");
					filter_size.dataset.sizetype = size_sort;
					filter_size.dataset.filter_idx = filter_idx;
					filter_size.innerHTML = `<span class="filter-title">${filter_name}</span>`;

					if (size_sort == "O") {
						li_box.insertBefore(filter_size, li_box.firstChild);
					}

					li_box.appendChild(filter_size);
				});

				filter_box.appendChild(li_box);

				return filter_box;
			}
		})
	}

	filter.addEventListener("click", function () {
		let sort_container = document.querySelector(".sort-container");
		let btn_motion = document.querySelector(".oder-btn-motion");

		filter_container.classList.toggle("open");
		filter_body.classList.toggle("open");
		filter_motion.classList.toggle("open");

		if (sort_container.classList.contains("open")) {
			sort_container.classList.remove("open");

			btn_motion.classList.remove("rotate");
			btn_motion.classList.add("rotate_back");
		}
	});

	// 필터 옵션 선택했을 때
	function selectFilterOption() {
		const filterOption = document.querySelectorAll(".filter-wrapper li");

		filterOption.forEach(el => {
			el.addEventListener("click", function () {
				if (!this.classList.contains('select')) {
					this.classList.add('select');
				} else {
					this.classList.remove('select');
				}
				checkFilterProductCnt();
			});
		});
	}

	function mobileFilterEvent() {
		let filterContent = document.querySelectorAll(".filter-content");
		let toggleTarget = document.querySelectorAll(".filter-toggle");
		let mobileFilter = document.querySelectorAll(".mobile-filter-btn");
		let filterItem = document.querySelectorAll(".filter-wrapper li");

		filterContent.forEach(el => {
			el.addEventListener("click", function () {
				if (this.children[1].classList.contains("open")) {
					this.children[1].classList.remove("open");
					this.children[0].children[1].classList.remove("open");
				} else {
					toggleTarget.forEach(el => {
						el.classList.remove("open");
					});
					mobileFilter.forEach(el => {
						el.classList.remove("open");
					});
					this.children[0].children[1].classList.add("open");
					this.children[1].classList.add("open");
				}
			})
		});

		filterItem.forEach(el => {
			el.addEventListener("click", function (event) {
				event.stopPropagation();
			});
		});
	}
}

function toggleSortBtn() {
	let sort_btn = document.querySelector(".sort-btn");
	
	sort_btn.addEventListener("click", function () {
		let sort_container = document.querySelector(".sort-container");
		let sort_wrap = sort_container.querySelector('.sort-wrap');
		
		let btn_motion = document.querySelector(".oder-btn-motion");
		
		if (sort_container.classList.contains('open')) {
			sort_container.classList.remove("open");
			sort_wrap.classList.add('hidden');
		} else {
			sort_container.classList.add("open");
			sort_wrap.classList.remove('hidden');
		}

		if (btn_motion.classList.contains("rotate_back")) {
			btn_motion.classList.remove("rotate_back");
			btn_motion.classList.add("rotate");
		} else if (!btn_motion.classList.contains("rotate") && !btn_motion.classList.contains("rotate-back")) {
			btn_motion.classList.add("rotate");
		} else {
			btn_motion.classList.remove("rotate");
			btn_motion.classList.add("rotate_back");
		}

		document.querySelector(".filter-container").classList.remove("open");
		document.querySelector(".filter-body").classList.remove("open");
		document.querySelector(".filter-motion-btn").classList.remove("open");
	});
}

function sortProductList(obj) {
	more_flg = false;

	let product_list_wrap = document.querySelector(".product__list__wrap");
	product_list_wrap.dataset.last_idx = 0;

	$('.show_more_btn').remove();

	let checkInfo = $(obj).prop('checked');
	if (checkInfo == true) {
		order_param = $(obj).val();
		$('.sort__cb').not($(obj)).prop('checked', false);
	} else {
		order_param = null;
	}

	let { menu_idx, menu_type, page_idx, last_idx } = document.querySelector(".product__list__wrap").dataset

	$.ajax({
		type: "POST",
		url: api_location + "product/list/get",
		data: {
			"country": getLanguage(),
			"menu_idx": menu_idx,
			"menu_type": menu_type,
			"page_idx": page_idx,
			"order_param": order_param,
			"last_idx": last_idx
		},
		dataType: "json",
		error: function () {
			// alert('페이지별 필터정보 취득중 오류가 발생했습니다.');
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0097", null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;
				let grid_info = data.grid_info;

				let product_wrap = product_list_wrap.querySelector(".product-wrap");
				product_wrap.innerHTML = "";

				if (grid_info.length > 0) {
					let filterdInfo = grid_info.filter(el => el.grid_type != 'VID' && el.grid_type != 'IMG');
					let sortedInfo = checkInfo ? filterdInfo : grid_info;
					let product_list_html = writeProductListHtml(sortedInfo);

					product_wrap.innerHTML = product_list_html;

					setProductListFadeIn();
				}
			} else {
				notiModal(d.msg)
			}
		}
	});
}

/**
 * @author SIMJAE
 * @deprecated 배너 이미지의 형제 요소들의 높이를 측정하여, 이미지 배너의 높이를 설정해주는 기능 
 */
function bannerHeightBySiblingElements() {
	const elements = document.querySelectorAll(".product");
	const targets = document.querySelectorAll(".product-inside-banner");
	const heights = [];
	for (idx = 0; elements.length; idx++) {
		if (elements[idx].classList.contains('prd')) {
			heights.push(elements[idx].offsetHeight);
			break;
		}
	}

	const maxHeight = Math.max(...heights);
	targets.forEach((t) => {
		t.style.height = `${maxHeight}px`;
	});
}
function listCategoryStickyEvent() {
	let header = document.querySelector('header');
	let main = document.querySelector('main');
	let category = document.querySelector('.product__list__wrap .prd__menu');
	let sort = document.querySelector('.product__list__wrap .sort-container');
	let filter = document.querySelector('.product__list__wrap .filter-container');
	let prevScrollpos = window.pageYOffset;
	window.onscroll = function () {
		let currentScrollPos = window.pageYOffset;

		if (prevScrollpos > currentScrollPos + 15) {
			// 스크롤을 15만큼 올릴 때
			main.style.overflow = 'initial';
			category.classList.add('hidden');

		} else if (prevScrollpos < currentScrollPos - 15) {
			// 스크롤을 15만큼 내릴 때
			filter.style.top = `${category.offsetHeight - 2}px`;
			sort.style.top = `${category.offsetHeight - 2}px`;
			category.style.top = `${header.offsetHeight}px`;
			main.style.overflow = 'hidden';

			category.classList.remove('hidden');
		}

		prevScrollpos = currentScrollPos;
	};
}

function clickBtnProductSort() {
	let btn_product_sort = document.querySelectorAll('.btn_product_sort');
	if (btn_product_sort != null) {
		btn_product_sort.forEach(btn => {
			btn.addEventListener('click',function(e) {
				let el = e.currentTarget;
				sortProductList(el);
			});
		});
	}
}
