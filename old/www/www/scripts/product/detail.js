let delay = 300;
let timer = null;
let breakpoint = window.matchMedia('screen and (min-width:1025px)');
const urlParams = new URL(location.href).searchParams;

window.addEventListener('DOMContentLoaded', function () {
	
	changeLanguageR();

	const product_idx = urlParams.get('product_idx');

	getProductInfo(product_idx);
	stylingObserver();
	pdResponsiveSwiper();
	mobileDetailBtnHanddler();
	
	$('#quickview').removeClass("hidden");
	if(window.innerWidth <= 1024){
		setTimeout(() => {
			quickviewResize();
		}, delay);
	}

	new Swiper(".swiper-container.sizeguide-swiper", {
		grabCursor: true,
		slidesPerView: "auto",
		spaceBetween: 5,
		loop: false
	});

	new Swiper(".swiper-container.product_btn_swiper", {
		grabCursor: true,
		slidesPerView: "auto",
		spaceBetween: 10,
		loop: false
	});
});

window.addEventListener('resize', function () {
	clearTimeout(timer);
	timer = setTimeout(function () {	
		pdResponsiveSwiper();
		//quickviewResize();
	}, delay);
});

function quickviewResize() {
	let breakpoint = window.matchMedia('screen and (max-width:1025px)');
	if(breakpoint.matches == true) {
		$("#quickview").addClass("hidden");
	} else {
		$("#quickview").removeClass("hidden");
	}
}

const foryou = new ForyouRender();

const getProductInfo = (product_idx) => {
	let country = getLanguage();

	$.ajax({
		type: "post",
		url: api_location + "product/get",
		headers: {
			"country": country
		},
		data: {
			"product_idx": product_idx
		},
		dataType: "json",
		async: false,
		error: function () {
			// notiModal("상품 진열 페이지 불러오기 처리에 실패했습니다.");
			makeMsgNoti(country, "MSG_F_ERR_0108", null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;

				makeProductListFlag(data);

				let recent_img_location = null;

				let img_thumbnail = data.img_thumbnail;
				if (img_thumbnail != null && img_thumbnail.length > 0) {
					let tmp_length = img_thumbnail.length;

					let tmp_img_location = img_thumbnail[tmp_length - 1].img_location;
					if (tmp_img_location != null && tmp_img_location !== undefined) {
						recent_img_location = tmp_img_location;
					} else {
						recent_img_location = img_thumbnail[0].img_location;
					}
				}

				const recent_product = {
					product_idx: data.product_idx,
					img_main: recent_img_location,
					product_name: data.product_name,
					stock_status: data.stock_status
				};

				saveRecentlyViewed(recent_product);

				let detailInfo = {
					'material': data.material,
					'detail': data.detail,
					'care': data.care
				};

				getSizeGuideInfo(data.product_type,data.product_idx);
				getProductDetailInfo(detailInfo);
				webDetailBtnHanddler();


				addProductForGA(data, country);
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function getProductDetailInfo(info) {
	let web = document.querySelector(".detail__sidebar__wrap");

	let webMaterial = web.querySelector(".detail-content.material .content-body");
	let webProductInfo = web.querySelector(".detail-content.productinfo .content-body");
	let webPrecaution = web.querySelector(".detail-content.precaution .content-body");

	let mobile = document.querySelector(".rM-detail-containner");

	let mobileMaterial = mobile.querySelector(".detail-content.material .content-body");
	let mobileProductInfo = mobile.querySelector(".detail-content.productinfo .content-body");
	let mobilePrecaution = mobile.querySelector(".detail-content.precaution .content-body");

	if(info.material != null) {
		webMaterial.innerHTML = xssDecode(info.material);
		mobileMaterial.innerHTML = xssDecode(info.material);
	}
	if(info.detail != null) {
		webProductInfo.innerHTML = xssDecode(info.detail);
		mobileProductInfo.innerHTML = xssDecode(info.detail);
	}
	if(info.care != null) {
		webPrecaution.innerHTML = xssDecode(info.care);
		mobilePrecaution.innerHTML = xssDecode(info.care);
	}
}

function makeProductListFlag(data) {
	const domFrag = document.createDocumentFragment();

	const info_wrap = document.querySelector(".info__wrap");
	const thumbnailImgWrap = document.querySelector(".thumbnail_img_wrapper");
	const navigation_wrap = document.querySelector(".navigation__wrap");
	const main_img_wrap = document.querySelector(".main_img_wrapper");

	let infoBoxHtml = "";

	//----------

	let product_type = data.product_type;
	let sales_price = data.txt_sales_price;
	let refund_msg_flg = data.refund_msg_flg;
	let sold_out_flg = data.sold_out_flg;
	let refund = xssDecode(data.refund);
	let set_type = data.set_type;

	info_wrap.dataset.soldflg = sold_out_flg;
	info_wrap.dataset.refund_msg_flg = refund_msg_flg;

	//상품 썸네일 이미지
	let img_thumbnail = data.img_thumbnail;
	if (img_thumbnail != null && img_thumbnail.length > 0) {
		img_thumbnail.forEach((thumbnail, index) => {
			const thumbnail_img_box = document.createElement("div");

			const img_location = index === 0 || !thumbnail.img_location ? img_thumbnail[0].img_location : thumbnail.img_location;
			const thumbnail_txt = thumbnail.display_num == 1 ? "착용이미지" : "디테일";

			let img_thumb_nail_html = `
				<img src="${cdn_img}${img_location}"/><span>${thumbnail_txt}</span>
			`;

			thumbnail_img_box.classList.add("thumb__box");
			thumbnail_img_box.dataset.type = thumbnail.display_num;
			thumbnail_img_box.innerHTML = img_thumb_nail_html;

			domFrag.appendChild(thumbnail_img_box);
		});
	}

	//NAVIGATION_WRAP 상품 썸네일 이미지 추가
	navigation_wrap.appendChild(domFrag);

	//상품 메인 이미지
	let img_main = data.img_main;
	img_main.forEach((main) => {
		const main_img_box = document.createElement("div");

		let img_main_html = `
			<img class="detail__img" data-imgtype="${main.img_type}" data-size="${main.img_size}" src="${cdn_img}${main.img_location}"/>
		`;

		main_img_box.classList.add("swiper-slide");
		main_img_box.dataset.imgtype = main.img_type;
		main_img_box.dataset.imgsize = main.img_size;
		main_img_box.innerHTML = img_main_html;

		domFrag.appendChild(main_img_box);
	});

	//MAIN_IMG_WRAP 상품 메인 이미지 이미지 추가
	main_img_wrap.appendChild(domFrag);

	//상품 컬러 HTML 생성
	let product_color_html = "";

	if (set_type != "CL") {
		let product_color = data.product_color;
		if (product_color != null && product_color.length > 0) {
			product_color.forEach(color => {
				let color_select = "";
				if (color.product_idx == data.product_idx) {
					color_select = "select";
				}
				let color_rgb = color.color_rgb;
				let multi = color_rgb.split(";");
				if (multi.length === 2) {
					product_color_html += `
						<div class="color-line ${color_select}" data-idx="${color.product_idx}" data-stock="${color.stock_status}" style="--background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
							<p class="color-name">${color.color}</p>
							<div class="color multi" data-title="${color.color}"></div>
						</div>
					`;
				} else {
					product_color_html += `
						<div class="color-line ${color_select}" data-idx="${color.product_idx}" data-stock="${color.stock_status}" data-title="${color.color}" style="--background-color:${multi[0]}">
							<p class="color-name">${color.color}</p>
							<div class="color" data-title="${color.color}"></div>
						</div>
					`;
				}
			});
		}
	}

	//상품 사이즈 HTML 생성
	let product_size_html = "";

	let product_size = data.product_size;
	if (product_type == "B") {
		if (product_size != null && product_size.length > 0) {
			product_size_html += `
				<div class="product__size">
					<div class="size__title">
						<span>Size</span>
						<span class="red_noti hidden">Only a few left</span>
						<span class="red_noti_mo hidden">Only a few left</span>
						
					</div>
					<div class="size__box product_box">
			`;

			product_size.forEach(size => {
				product_size_html += `
					<li class="size product_size" data-product_idx="${size.product_idx}" data-option_idx="${size.option_idx}" data-size_type="${size.size_type}" data-soldout="${size.stock_status}">
						${size.option_name}
						${size.stock_status == 'STCL' ? '<div class="red-dot"></div>' : ''}
						${size.stock_status == 'STSC' ? '<div class="sold-line"></div>' : ''}
					</li>
				`;
			});

			product_size_html += `			
					</div>
				</div>
			`;
		}
	} else if (product_type == "S") {
		if (set_type == "SZ") {
			product_size_html += `
				<div class="product__size">
			`;

			product_size.forEach(size => {
				product_size_html += `
						<span>${size.product_name}</span>
						<span class="red_noti hidden">Only a few left</span>
						<span class="red_noti_mo hidden">Only a few left</span>
						
						<div class="size__box product_box">
				`;

				let set_option_info = size.set_option_info;
				set_option_info.forEach(option => {
					product_size_html += `
							<li class="size product_size" data-product_idx="${option.product_idx}" data-option_idx="${option.option_idx}" data-size_type="${option.size_type}" data-soldout="${option.stock_status}">
								${option.option_name}
								${option.stock_status == 'STCL' ? '<div class="red-dot"></div>' : ''}
								${option.stock_status == 'STSC' ? '<div class="sold-line"></div>' : ''}
							</li>
					`;
				});

				product_size_html += `
						</div>
				`;
			});

			product_size_html += `			
				</div>
			`;
		} else if (set_type == "CL") {
			product_size_html += `
				<div class="product__size">
			`;

			product_size.forEach(size => {
				product_size_html += `
						<span>${size.product_name}</span>
						<span class="red_noti hidden">Only a few left</span>
						<span class="red_noti_mo hidden">Only a few left</span>
						
						<div class="color__box product_box">
				`;

				let set_option_info = size.set_option_info;
				set_option_info.forEach(option => {
					product_size_html += `
							<div class="color-line">
								<p class="color-name">${option.color}</p>
								<div class="color product_size" data-title="${option.color}" data-product_idx="${option.product_idx}" data-option_idx="${option.option_idx}" data-title="${option.color}" style="--background-color:${option.color_rgb}" data-soldout="${option.stock_status}"></div>
							</div>
					`;
				});

				product_size_html += `
					</div>
				`;
			});

			product_size_html += `
				</div>
			`;
		}
	}

	//위시 리스트 등록/삭제 버튼 HTML 생성
	//let wishlist_title = "<div class='whislist-tilte'>wishlist_title</div>"

	let wish_btn_html = "";
	
	let whish_flg = `${data.whish_flg}`;
	let whish_img = "";
	
	let txt_dataset = `data-location="foryou" data-wish_flg="${whish_flg}" data-product_idx="${data.product_idx}"`;

	let login_status = getLoginStatus();
	if (login_status == "true") {
		if (whish_flg == 'true') {
			whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist-bk.svg" alt="">`;
		} else if (whish_flg == 'false') {
			whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist.svg" alt="">`;
		}
	} else {
		whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist.svg" alt="">`;
	}
	
	wish_btn_html = `
		<div class="wish-btn btn_update_wish" product_idx="${data.product_idx}" ${txt_dataset}>
			${whish_img}
		</div>
	`;
	
	//상품 하단 메세지
	
	// 교환 / 환불 메시지 화면
	let refund_msg_html = "";

	if (refund_msg_flg == true) {
		refund_msg_html =
			`
				<div class="detail__refund__box"> 
					<div class='close-box'>
						<div class="close-btn">
							<svg xmlns="http://www.w3.org/2000/svg" width="12.707" height="12.707" viewBox="0 0 12.707 12.707">
								<path data-name="선 1772" transform="rotate(135 6.103 2.736)" style="fill:none;stroke:#343434" d="M16.969 0 0 .001"></path>
								<path data-name="선 1787" transform="rotate(45 -.25 .606)" style="fill:none;stroke:#343434" d="M16.969.001 0 0"></path>
							</svg>
						</div>
					</div>
					<div class='refund__msg'>
						<p data-i18n="pd_refund_msg_01">제품의 특성상 교환 / 환불이 불가합니다.</p>
						<p data-i18n="pd_refund_msg_02">동의하시겠습니까?</p>
					</div>
					<div class="refund-basket-btn"> 
						<img src="/images/svg/basket.svg" alt=""> 
						<span class="basket-title" data-i18n="pd_basket_msg_06">내용 확인 후 쇼핑백에 담기</span> 
					</div> 
				</div>
			`;

		document.querySelectorAll(".detail__refund__msg").forEach(el => el.innerHTML = xssDecode(refund));
	} else {
		refund_msg_html = "";
	}
	
	document.querySelector('.detail__refund__msg.mobile').innerText = refund;
	
	//상품 정보 HTML 생성 (WEB)
	infoBoxHtml = `
		<div class="product__title">${data.product_name}</div>
		${data.discount == 0 ?
			`<div class="product__price" data-soldout="${data.stock_status}" data-saleprice="${sales_price}" data-discount="${data.discount}" data-dis="false">
				<span>${data.price}</span>
			</div>`
			:
			`<div class="product__price" data-soldout="${data.stock_status}" data-saleprice="${sales_price}" data-dis="true">
				<span class="sp">${sales_price}</span>
				<span class="cp" data-discount="${data.discount}" >${data.price}</span>
				<span class="di">${data.discount}%</span>
			</div>`
		}
		
		<div class="color__box">
			${product_color_html}
		</div>
		
		${product_size_html}
		
		<div class="basket__wrap--btn">
			<div class="basket__box--btn">
				<div class="basket-btn" >
					<img src="/images/svg/basket.svg" alt="">
					<span class="basket-title" data-i18n="pd_basket_msg_05">쇼핑백에 담기</span>
				</div>
				${wish_btn_html}
			</div>
			${refund_msg_html}
		</div>

		<div class="detail__btn__wrap web">
			<div class="detail__btn__row web">
				<div class="img-box">
					<img src="/images/svg/sizeguide.svg" alt="">
				</div>
				<div class="btn-title" data-i18n="pd_size_guide">사이즈가이드</div>
				<div class="detail__content__box"></div>
			</div>
			<div class="detail__btn__row web">
				<div class="img-box">
					<img src="/images/svg/material.svg" alt=""></div>
				<div class="btn-title" data-i18n="pd_material">소재</div>
				<div class="detail__content__box"></div>
			</div>
			<div class="detail__btn__row web">
				<div class="img-box">
					<img src="/images/svg/information.svg" alt="">
				</div>
				<div class="btn-title" data-i18n="pd_details">상세정보</div>
				<div class="detail__content__box"></div>
			</div>
			<div class="detail__btn__row web">
				<div class="img-box">
					<img src="/images/svg/precaution.svg" alt="">
				</div>
				<div class="btn-title" data-i18n="pd_care">취급 유의사항</div>
				<div class="detail__content__box"></div>
			</div>
		</div>
		<div class="detail__refund__msg">${refund}</div>
		
	`;

	//모바일 전용 쇼핑백담기버튼 추가
	let mobile_basket_btn_wrap = document.createElement("div");
	mobile_basket_btn_wrap.className = "basket__wrap--btn nav";

	mobile_basket_btn_wrap.innerHTML = `
		<div class="basket__box--btn">
			<div class="basket-btn" >
				<img src="/images/svg/basket.svg" alt="">
				<span class="basket-title" data-i18n="pd_basket_msg_05">쇼핑백에 담기</span>
			</div>
			${wish_btn_html}
		</div>
		
		<div class="mobile-wishlist-wrap"></div>
		${refund_msg_html}
	`;

	document.querySelector(".rM-detail-containner").appendChild(mobile_basket_btn_wrap);

	const product_info = document.createElement("div");
	product_info.classList.add("info__box");
	product_info.innerHTML = infoBoxHtml;

	info_wrap.appendChild(product_info);
	
	clickBtnUpdateWish();
	
	if (info_wrap.dataset.refund_msg_flg == true) {
		document.querySelectorAll(".detail__refund__msg").forEach(el => data.innerHTML = xssDecode(refund));
	} else {
		$('.detail__refund__msg').css('display','none');
	}

	//----------

	let relevant_idx = data.relevant_idx;
	if (relevant_idx != null) {
		const styling = new StylingRender(relevant_idx);
	}

	// 재고상태 숫자표기()
	checkSizeStatus(data.product_type);
	// sizeNodeCheck();
	clickProductColor();
	hoverProductSizeBtn(data.product_type, data.set_type);
	hoveroutProductSizeBtn(data.product_type, data.set_type);
	clickProductSizeBtn(data.product_type, data.set_type);

	basketStatusBtn(data.product_type, data.product_idx);

	// 컬러 표기
	followScrollBtn();
	viewportImg();
	addChangeBtnMsgEvent();

	// detailBtnHandler();

	//디테일 설명
	innerSideBar();
	

	if (info_wrap.dataset.soldflg == true) {
		let $$product_btn = document.querySelectorAll(".basket-btn");
		changeBasketBtnStatus($$product_btn,0);
		document.querySelectorAll('.info__wrap .product_box .product_size').forEach(el => el.dataset.soldout = 'STSO');
	}
}

function selectSetProductOption(set_type) {
	let set_param = null;
	if (set_type == "SZ") {
		set_param = document.querySelector('.set_size');
	} else if (set_type == "CL") {
		set_param = document.querySelector('.set_color');
	}
}

function getSizeGuideInfo(product_type, prouct_idx) {
	$.ajax({
		type: "post",
		url: api_location + "/product/size/get",
		headers: {
			"country": getLanguage()
		},
		data: {
			product_type: product_type,
			product_idx: prouct_idx
		},
		async: false,
		dataType: "json",
		error: function () {
			// notiModal("사이즈 가이드 조회에 실패했습니다.");
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0109", null);
		},
		success: function (d) {
			let code = d.code;

			if(code == 200) {
				let data = d.data;

				if(data != null) {
					let web		= document.querySelector(".detail__sidebar__wrap");
					setSizeGuide(data,web,"WEB");
					
					let mobile	= document.querySelector(".rM-detail-containner");
					setSizeGuide(data,mobile,"MOB");

					let btn = $(".detail-content .product_btn");

					if(btn.length > 0) {
						addProductBtnEvent("web");
						addProductBtnEvent("mobile");
					} else {
						$(".sizeguide-wrap").removeClass("hidden");
					}
					addSizeguideBtnEvent("web");
					addSizeguideBtnEvent("mobile");
				}
			}
		}
	});
}

function setSizeGuide(data,size,size_type) {
	let contentBody = size.querySelector(".content-body");
	
	let productBtnWrapSwiper = document.createElement("div");
	productBtnWrapSwiper.className = "swiper-container product_btn_swiper";

	let productBtnWrap = document.createElement("div");
	productBtnWrap.className = "swiper-wrapper product_btn_wrap";
	productBtnWrapSwiper.appendChild(productBtnWrap);

	contentBody.appendChild(productBtnWrapSwiper);
	
	if (data != null && data.length > 0) {
		let option_size_txt = data.option_size_txt;
		
		data.forEach(function(row) {
			let productName = row.product_name;

			if(productName != null) {
				let productBtn = document.createElement("div");
				productBtn.className = "product_btn swiper-slide";
				productBtn.textContent = productName;

				productBtn.dataset.product_idx = row.product_idx;
				productBtnWrap.appendChild(productBtn);
			} else {
				productBtnWrap.classList.add("hidden");
			}

			contentBody.appendChild(writeSizeGuideHTML(row,size_type));
			
			let dimensions = row.dimensions;
			if (dimensions != null) {
				setSVGValue(dimensions);
			}
		});
	}
}

function writeSizeGuideHTML(row,size_type) {
	let sizeguideWrap = document.createElement("div");
	sizeguideWrap.className = "sizeguide-wrap hidden";

	if(row.product_name != null) {
		sizeguideWrap.dataset.product_idx = row.product_idx;
	}
	
	let sizeguideBoxSwiper = document.createElement("div");
	sizeguideBoxSwiper.className = "swiper-container sizeguide-swiper";

	let sizeguideBox = document.createElement("div");
	sizeguideBox.className = "swiper-wrapper sizeguide-btn-box";

	sizeguideBoxSwiper.appendChild(sizeguideBox);
	
	let sizeguideDct = document.createElement("div");
	sizeguideDct.className = "sizeguide-dct-wrap";

	let noti_size_guide = "";
	
	let sizeguideNoti = document.createElement("div");
	sizeguideNoti.className = "sizeguide-noti";
	
	let model = row.model
	if(model != null) {
		noti_size_guide = `
			<span data-i18n="pd_model_msg_01">모델 신장 </span>
			<span>${model}</span>
			<span data-i18n="pd_model_msg_02">, 착용 사이즈는 </span>
			<span>${row.model_wear}</span>
			<span data-i18n="pd_model_msg_03">입니다.</span>
		`;
	} else {
		sizeguideNoti.classList.add("hidden");
	}
	
	sizeguideNoti.innerHTML = noti_size_guide;
	
	let size_guide_svg = null;
	if (size_type == "WEB") {
		size_guide_svg = row.svg_web;
	} else if (size_type == "MOB") {
		size_guide_svg = row.svg_mob;
	}
	
	let img_size_guide = document.createElement("div");
	img_size_guide.className = "sizeguide-img";
	
	if (size_guide_svg != null) {
		img_size_guide.innerHTML = size_guide_svg;
	}
	
	let dimensions = row.dimensions;
	if (dimensions != null) {
		let dimensionsKeys = Object.keys(dimensions);
		dimensionsKeys.forEach(key => {
			let btn = document.createElement("div");
			btn.className = "sizeguide-btn swiper-slide";
			btn.dataset.dimension = key;
			btn.textContent = key;

			sizeguideBox.appendChild(btn);

			let dctWrap = document.createElement("ul");
			dctWrap.className = "sizeguide-dct hidden";
			dctWrap.dataset.dimension = key;
			
			let dcts = dimensions[key];
			let dctHtml = "";
			
			for (let i=0; i<dcts.length; i++) {
				dctHtml += `
					<li class="dct-row">
						<span>${dcts[i].title}</span>
						<span>${dcts[i].desc}</span>
						<span class="dct-value">${dcts[i].value}</span>
					</li>
				`;
			}
			
			dctWrap.innerHTML = dctHtml;
			sizeguideDct.appendChild(dctWrap);
		});
	}
	
	if(row.product_name != null) {
		sizeguideWrap.appendChild(sizeguideNoti);
		sizeguideWrap.appendChild(sizeguideBoxSwiper);
	} else {
		sizeguideWrap.appendChild(sizeguideBoxSwiper);
		sizeguideWrap.appendChild(sizeguideNoti);
	}
	sizeguideWrap.appendChild(img_size_guide);
	sizeguideWrap.appendChild(sizeguideDct);

	return sizeguideWrap;
}

function setSVGValue(dimensions) {
	let keys = Object.keys(dimensions);
	keys.forEach(key => {
		let dcts = dimensions[key];
		let dctHtml = "";
		
		for (let i=0; i<dcts.length; i++) {
			let option_size = document.querySelectorAll('.option_size_' + (i+1));
			if (option_size != null) {
				option_size.forEach(size => {
					size.textContent = dcts[i].value;
				});
			}
		}
	});
}

function addProductBtnEvent(size) {
	let container = null;

	if(size == "web") {
		container = $(".detail__sidebar__wrap");
	} else {
		container = $(".rM-detail-containner");
	}

	let btn = container.find(".product_btn");

	btn.on("click", function() {
		let productIdx = $(this).data("product_idx");

		btn.removeClass("select");
		$(this).addClass("select");

		container.find(".sizeguide-wrap").addClass("hidden");

		let sizeguideWrap = container.find(`.sizeguide-wrap[data-product_idx='${productIdx}']`);

		sizeguideWrap.removeClass("hidden");
		sizeguideWrap.find(".sizeguide-btn").eq(0).click();
	});

	btn.eq(0).click();
}

function addSizeguideBtnEvent(size) {
	let container = null;

	if(size == "web") {
		container = $(".detail__sidebar__wrap");
	} else {
		container = $(".rM-detail-containner");
	}

	let btn = container.find(".sizeguide-btn");

	btn.on("click", function() {
		let dimension = $(this).data("dimension");

		btn.removeClass("select");
		$(this).addClass("select");

		container.find(".sizeguide-dct").addClass("hidden");
		container.find(`.sizeguide-dct[data-dimension='${dimension}']`).removeClass("hidden");
	});

	btn.eq(0).click();
}

//메인 스와이프 관련 함수 
let pd_mainSwiper = null;
let pd_pagingSwiper = null;

function pdResponsiveSwiper() {
	let breakpoint = window.matchMedia('screen and (min-width:1025px)');
	if (breakpoint.matches === true) {
		if (pd_mainSwiper !== null) {
			pd_mainSwiper.destroy();
			pd_mainSwiper = null;
		}
		if (pd_pagingSwiper !== null) {
			pd_pagingSwiper.destroy();
			pd_pagingSwiper = null;
		}
	} else if (breakpoint.matches === false) {
		if (pd_pagingSwiper == null) {
			pd_pagingSwiper = initPagingSwiper();
		}
		if (pd_mainSwiper == null) {
			pd_mainSwiper = initMainSwiper();
			pd_mainSwiper.on('slideChange', function () {
				$(".swiper-pagination-detail-fraction .swiper-pagination-current").html(pd_mainSwiper.activeIndex + 1);
			});
		}
		pd_pagingSwiper.controller.control = pd_mainSwiper;

	}

};
function initMainSwiper() {
	return new Swiper('#main__swiper-detail', {
		pagination: {
			el: ".swiper-pagination-detail-bullets",
			dynamicBullets: true,
			clickable: true,
			bulletWidth: 280,
		},
	});
}
function initPagingSwiper() {
	return new Swiper("#main__swiper-detail", {
		pagination: {
			el: ".swiper-pagination-detail-fraction",
			type: "fraction",
		},
	});
}
//스크롤 버튼 
function followScrollBtn() {
	const detailProduct = document.querySelectorAll(".main__swiper .swiper-slide");
	const thumbBtns = document.querySelectorAll(".thumb__box");
	thumbBtns.forEach(el => el.addEventListener("click", function () {
		let thumbIdx = (this.dataset.type) - 1;
		let result = [...detailProduct].find((el, idx) => idx === thumbIdx);
		let scrollTo = result.offsetTop;
		toScroll(scrollTo);
		if (pd_mainSwiper == null) {
			return false;
		}
		if (pd_mainSwiper.__swiper__ == true) {
			pd_mainSwiper.slideTo(thumbIdx)
		}
	}));
	function toScroll(targetValue) {
		window.scrollTo({
			top: targetValue,
			left: 0,
			behavior: 'smooth'
		});
	};
}
//이미지 확대 함수
function viewportImg() {
	let img = new Image();
	let $$slide = document.querySelectorAll(".detail__img__wrap .swiper-slide img");
	let closebtn = document.createElement("div");
	closebtn.innerHTML = `
		<img src="/images/svg/img-close-btn.svg">
	`
	closebtn.className = "viewport__closebtn"
	let imageWrap = document.createElement("div");
	imageWrap.className = "viewport__wrap--img";
	$$slide.forEach(el => {
		el.addEventListener("click", function (e) {
			let src = e.target.getAttribute("src");
			img.className = "viewport-img";
			img.setAttribute("src", src)
			imageWrap.appendChild(img);
			imageWrap.appendChild(closebtn);
			document.body.appendChild(imageWrap);
			document.body.style.overflow = "hidden";
			let $viewportWrap = document.querySelector(".viewport__wrap--img")
			let imgScale = document.querySelector('.viewport-img');


			// $viewportWrap.scrollIntoView({block: "center"});
			if (window.matchMedia('screen and (min-width:1025px)').matches) {
				$viewportWrap.addEventListener("click", webImgClose);
			} else {
				$viewportWrap.removeEventListener("click", webImgClose);
				//img의 transform 값이 변경되면 weight를 변경해야함
				var weight = 2.5
				var viewportW = $(".viewport-img").width();
				var imgW = viewportW * weight;
				$viewportWrap.scrollBy((imgW - viewportW) / 2, 0);
			}
		});

		function webImgClose() {
			document.body.style.overflow = "inherit";
			this.remove();
		}
	})
	closebtn.addEventListener("click", function () {
		document.body.style.overflow = "inherit";
		document.querySelector(".viewport__wrap--img").remove();
	});

}

/**
 * @author SIMJAE  
 * @description 사이즈 선택 이벤트
 */

function hoverProductSizeBtn(product_type, set_type){
	let size_option = document.querySelectorAll(".detail__wrapper .product_box .product_size");

	let select_obj_cnt = 0;

	size_option.forEach(size => {
		size.addEventListener("mouseover", function (e) {
			let size_status = e.currentTarget.dataset.status;
			let stock_status = e.currentTarget.dataset.soldout;
			//재고있음 상태의 사이즈 선택
			if (size_status == 2) {
				if (product_type == "S") {
					let product_box = null;
					if (set_type == "SZ") {
						product_box = e.currentTarget.parentNode;
					} else if (set_type == "CL") {
						product_box = e.currentTarget.parentNode.parentNode;
					}
					
					if (stock_status == "STCL") {
						product_box.parentNode.querySelector('.red_noti').classList.remove('hidden');
					} else {
						product_box.parentNode.querySelector('.red_noti').classList.add('hidden');
					}
				} else {
					if (stock_status == "STCL") {
						document.querySelector('.red_noti').classList.remove('hidden');
					} else {
						document.querySelector('.red_noti').classList.add('hidden');
					}
				}
			} 
			else{
				if (product_type == "S") {
					let product_box = null;
					if (set_type == "SZ") {
						product_box = e.currentTarget.parentNode;
					} else if (set_type == "CL") {
						product_box = e.currentTarget.parentNode.parentNode;
					}
					
					product_box.parentNode.querySelector('.red_noti').classList.add('hidden');
				} else {
					document.querySelector('.red_noti').classList.add('hidden');
				}
			}
		});
	});
}

function hoveroutProductSizeBtn(product_type, set_type){
	let size_option = document.querySelectorAll(".detail__wrapper .product_box .product_size");

	let check_obj = null;
	let select_obj_cnt = 0;

	size_option.forEach(size => {
		size.addEventListener("mouseout", function () {
			let sel_size = document.querySelector('.product_size');
			let size_status = null;
			let stock_status = null; 

			if(sel_size != null){
				size_status = sel_size.dataset.status;
				stock_status = sel_size.dataset.soldout;
			}
			
			//재고있음 상태의 사이즈 선택
			if (size_status == 2) {
				if (product_type == "S") {
					let product_box = null;

					check_obj = $(".product_size.select[data-status='2']");
					select_obj_cnt = document.querySelectorAll(".product_box").length;

					if (set_type == "SZ") {
						product_box = sel_size.parentNode;
					} else if (set_type == "CL") {
						product_box = sel_size.parentNode.parentNode;
					}
					
					if (stock_status == "STCL") {
						product_box.parentNode.querySelector('.red_noti').classList.remove('hidden');
					} else {
						product_box.parentNode.querySelector('.red_noti').classList.add('hidden');
					}
				} else {
					check_obj = $(".product_size.select[data-status='2']");
					select_obj_cnt = 1;
					
					if (stock_status == "STCL") {
						document.querySelector('.red_noti').classList.remove('hidden');
					} else {
						document.querySelector('.red_noti').classList.add('hidden');
					}
				}
			}
			else{
				if (product_type == "S") {
					let product_box = null;
					if (set_type == "SZ") {
						product_box = sel_size.parentNode;
					} else if (set_type == "CL") {
						product_box = sel_size.parentNode.parentNode;
					}
					product_box.parentNode.querySelector('.red_noti').classList.add('hidden');
				} else {
					document.querySelector('.red_noti').classList.add('hidden');
				}
			}
		});
	});
}

function clickProductSizeBtn(product_type, set_type) {
	let size_option = document.querySelectorAll(".detail__wrapper .product_box .product_size");

	let web_basket_btn = document.querySelector(".info__box .basket-btn");
	let mobile_basket_btn = document.querySelector(".rM-detail-containner .basket-btn");
	let $$basket_btn = [web_basket_btn, mobile_basket_btn];

	let check_obj = null;
	let select_obj_cnt = 0;

	size_option.forEach(size => {
		size.addEventListener("click", function (e) {
			console.log(product_type);
			
			let size_status = e.currentTarget.dataset.status;

			size_option.forEach(size => {
				if (size.dataset.status !== size_status) {
					size.classList.remove("select");
				}
			});

			e.currentTarget.classList.toggle("select");
			let stock_status = e.currentTarget.dataset.soldout;

			//재고있음 상태의 사이즈 선택
			if (size_status == 2) {
				if (product_type == "S") {
					let tmp_option_idx = e.currentTarget.dataset.option_idx;
					let product_box = null;

					check_obj = $(".product_size.select[data-status='2']");
					select_obj_cnt = document.querySelectorAll(".product_box").length;

					if (set_type == "SZ") {
						product_box = e.currentTarget.parentNode;
					} else if (set_type == "CL") {
						product_box = e.currentTarget.parentNode.parentNode;
					}
					
					if (stock_status == "STCL" && e.currentTarget.classList.contains('select')) {
						product_box.parentNode.querySelector('.red_noti').classList.remove('hidden');
					} else {
						product_box.parentNode.querySelector('.red_noti').classList.add('hidden');
					}
					
					let product_size = product_box.querySelectorAll(".product_size");

					product_size.forEach(set => {
						console.log(set);
						
						/*
						if (set.dataset.option_idx != tmp_option_idx) {
							set.classList.remove("select");
						}
						*/
					});
				} else {
					check_obj = $(".product_size.select[data-status='2']");
					select_obj_cnt = 1;
					
					if (stock_status == "STCL" && e.currentTarget.classList.contains('select')) {
						document.querySelector('.red_noti').classList.remove('hidden');
					} else {
						document.querySelector('.red_noti').classList.add('hidden');
					}
				}

				if (check_obj.length == 0 || check_obj.length < select_obj_cnt) {
					mobile_basket_btn.className = "basket-btn";
					web_basket_btn.className = "basket-btn";
				} else {
					mobile_basket_btn.className = "basket-btn basket";
					web_basket_btn.className = "basket-btn basket";
				}

				changeBasketBtnStatus($$basket_btn, 2);
			} else if (size_status == 1) {
				if (product_type == "S") {
					let tmp_option_idx = e.currentTarget.dataset.option_idx;
					let product_box = null;

					check_obj = $(".product_size.select[data-status='1']");
					select_obj_cnt = document.querySelectorAll(".product_box").length;

					if (set_type == "SZ") {
						product_box = e.currentTarget.parentNode;
					} else if (set_type == "CL") {
						product_box = e.currentTarget.parentNode.parentNode;
					}

					let product_size = product_box.querySelectorAll(".product_size");

					product_size.forEach(set => {
						if (set.dataset.option_idx != tmp_option_idx) {
							set.classList.remove("select");
						}
					});
				} else {
					check_obj = $(".product_size.select[data-status='1']");
					select_obj_cnt = 1;
				}

				if (check_obj.length == 0) {
					mobile_basket_btn.className = "basket-btn";
					web_basket_btn.className = "basket-btn";

					changeBasketBtnStatus($$basket_btn, 2);
				} else if (check_obj.length == select_obj_cnt) {
					mobile_basket_btn.className = "basket-btn reorder";
					web_basket_btn.className = "basket-btn reorder";

					changeBasketBtnStatus($$basket_btn, 1);
				}
			} else if (size_status == 0) {
				changeBasketBtnStatus($$basket_btn, 0);
			}
		});
	});
}
/**
 * @author SIMJAE
 * @description 사이즈 상태를 숫자로 반환
 */
function checkSizeStatus(product_type) {
	let stock_status = 0;

	let product_box = document.querySelectorAll(".detail__wrapper .product_box");
	let sizes = document.querySelectorAll(".detail__wrapper .product_box .product_size");

	if (product_type == "S") {
		let box_cnt = 0;
		product_box.forEach(box => {
			let soldout_cnt = 0;
			
			let product_size = box.querySelectorAll('.product_size');
			product_size.forEach(size => {
				let tmp_stock_status = size.dataset.soldout;
				switch (tmp_stock_status) {
					case "STSO":
						size.dataset.status = 0;
						soldout_cnt++;
						
						break;

					case "STSC":
						size.dataset.status = 1;
						soldout_cnt++;
						
						break;

					default:
						size.dataset.status = 2;
						
						break;
				}
			});
			
			if (soldout_cnt == product_size.length) {
				box_cnt++;
			}
		});
		
		if (box_cnt < product_box.length) {
			stock_status = 2;
		}

		return stock_status;
	} else if (product_type == "B") {
		let result = [...sizes].map(el => {
			let tmp_soldout = el.dataset.soldout;
			switch (tmp_soldout) {
				case "STSO":
					el.dataset.status = 0;
					return stock_status = 0;

					break;

				case "STSC":
					el.dataset.status = 1;
					return stock_status = 1;

					break;

				default:
					el.dataset.status = 2;
					return stock_status = 2;

					break;
			}
		});
		
		return statusArrCheck(result);
	}
}
/**
 * @author SIMJAE
 * @param {Array} list 사이즈 status 배열
 * @description 사이즈 상태에서 구매가능한 사이즈가 있을시 리턴값 max값
 * @returns result
 */
const statusArrCheck = (list) => {
	// 0 : 완전품절 || 1: 리오더가능 || 2: 재고 선택가능 || 3: commin-soon
	let result = Math.max(...list);

	return result;
}
/**
 * @author SIMJAE  
 * @description 사이즈 선택시 쇼핑백에 담기 버튼 상태 변경
 */
function changeBasketBtnStatus(el, idx) {
	el.forEach(btn => {
		switch (parseInt(idx)) {
			case 0:
				btn.querySelector("span").dataset.i18n = "pd_basket_msg_01";
				btn.querySelector("span").innerHTML = "품절";
				btn.querySelector("img").setAttribute("src", "");
				btn.querySelector("img").classList.add("hidden");
				btn.parentNode.dataset.status = 0;
				btn.dataset.status = 0;

				break;

			case 1:
				btn.querySelector("span").dataset.i18n = "pd_basket_msg_02";
				btn.querySelector("span").innerHTML = "재입고 알림 신청하기";
				btn.querySelector("img").classList.remove("hidden");
				btn.querySelector("img").setAttribute("src", "/images/svg/reflesh-bk.svg");
				btn.parentNode.dataset.status = 1;
				btn.dataset.status = 1;

				break;

			case 2:
				btn.querySelector("span").dataset.i18n = "pd_basket_msg_05";
				btn.querySelector("span").innerHTML = "쇼핑백에 담기";
				btn.querySelector("img").classList.remove("hidden");
				btn.querySelector("img").setAttribute("src", "/images/svg/basket.svg");
				btn.parentNode.dataset.status = 2;
				btn.dataset.status = 2;

				break;

			case 3:
				btn.querySelector("span").dataset.i18n = "pd_basket_msg_03";
				btn.querySelector("img").classList.add("hidden");
				btn.querySelector("span").innerHTML = "comming soon";
				btn.parentNode.dataset.status = 3;
				btn.dataset.status = 3;

				break;

			case 4:
				btn.querySelector("span").dataset.i18n = "pd_basket_msg_04";
				btn.querySelector("span").dataset.i18n = "pd_choose_an_option";
				btn.querySelector("span").innerHTML = "옵션을 선택해주세요";
				btn.querySelector("img").setAttribute("src", "/images/svg/pd-unoption.svg");
				btn.querySelector("img").classList.remove("hidden");
				btn.parentNode.dataset.status = 4;
				btn.dataset.status = 4;

				break;
		}
		let key = btn.querySelector("span").dataset.i18n;
		btn.querySelector("span").textContent = i18next.t(key);
	});
}

function basketStatusBtn(product_type,product_idx) {
	const $$product_btn = document.querySelectorAll(".basket-btn");
	const $$size = document.querySelectorAll(".detail__wrapper .size");
	const size_result = checkSizeStatus(product_type);

	changeBasketBtnStatus($$product_btn,size_result);

	$$product_btn.forEach(el => {
		el.addEventListener("click",function(e) {
			let option_info = [];
			let size_status = e.currentTarget.dataset.status;
			
			if (product_type == "B") {
				$$size.forEach(size => {
					if (size.classList.contains("select")) {
						option_info.push(size.dataset.option_idx);
					}
				});
				
				if (option_info.length == 0) {
					if(size_status == 0) {
						changeBasketBtnStatus($$product_btn, 0);
					} else {
						changeBasketBtnStatus($$product_btn, 4);
					}
				} else {
					if (size_status == 1) {
						addProductReorder(product_type,product_idx,option_info);
					}
					
					if (size_status == 2) {
						if ($('.info__wrap').data('refund_msg_flg') == true) {
							let detail_refund_box = $('.detail__refund__box');
							detail_refund_box.addClass('open');

							$('.detail__refund__box .close-btn').unbind('click');
							$('.detail__refund__box .refund-basket-btn').unbind('click');

							$('.detail__refund__box .close-btn').bind('click',function (){
								detail_refund_box.removeClass('open');
							});
							$('.detail__refund__box .refund-basket-btn').bind('click',function(){
								addBasketInfo(product_type, product_idx, option_info);
								detail_refund_box.removeClass('open');
							});
						} else {
							addBasketInfo(product_type,product_idx,option_info);
						}
					}
					if(size_status == 0) {
						changeBasketBtnStatus($$product_btn, 0);
					}
				}
			} else if (product_type == "S") {
				let product_box = document.querySelectorAll('.product_box');
				product_box.forEach(box => {
					let product_size = box.querySelectorAll('.product_size');
					product_size.forEach(set => {
						if (set.classList.contains('select')) {
							let set_product_idx = set.dataset.product_idx;
							let set_option_idx = set.dataset.option_idx;
							
							let tmp_set_param = {
								'product_idx': set_product_idx,
								'option_idx': set_option_idx
							};

							option_info.push(tmp_set_param);
						}
					});
				});
				
				let cnt_size = $('.product_size').length;
				if (cnt_size == option_info.length) {
					if (size_status == 1) {
						addProductReorder(product_type,product_idx,option_info);
					} else if (size_status == 2) {
						addBasketInfo(product_type,product_idx,option_info);
					}
				} else {
					makeMsgNoti(getLanguage(), "MSG_F_WRN_0052", null);
					// notiModal('구매하려는 세트 상품을 전부 선택해주세요.');
					return false;
				}
			}
		});
	});
}

// 쇼핑백에 담기 마우스 enter/ leave 이벤트
function addChangeBtnMsgEvent() {
	let basketBtn = document.querySelector(".info__box .basket-btn");

	basketBtn.addEventListener("mouseenter", (e) => {
		let status = e.currentTarget.dataset.status;
		let select_result = document.querySelectorAll(".product_box .select").length;

		if (status == 2 && select_result == 0) {
			e.currentTarget.querySelector("span").dataset.i18n = "pd_choose_an_option";
			e.currentTarget.querySelector("span").innerHTML = "옵션을 선택해주세요";
			e.currentTarget.querySelector("span").textContent = i18next.t("pd_choose_an_option");
			e.currentTarget.querySelector("img").setAttribute("src", "/images/svg/pd-unoption.svg");
			e.currentTarget.querySelector("img").classList.remove("hidden");
		}
	});

	basketBtn.addEventListener("mouseleave", (e) => {
		let status = e.currentTarget.dataset.status;
		let select_result = document.querySelectorAll(".product_box .select").length;

		if (status == 2 && select_result == 0) {
			e.currentTarget.querySelector("span").dataset.i18n = "pd_basket_msg_05";
			e.currentTarget.querySelector("span").innerHTML = "쇼핑백에 담기";
			e.currentTarget.querySelector("span").textContent = i18next.t("pd_basket_msg_05");
			e.currentTarget.querySelector("img").classList.remove("hidden");
			e.currentTarget.querySelector("img").setAttribute("src", "/images/svg/basket.svg");
		}
	});
}
/**
 * @author SIMJAE  
 * @description 쇼핑백에 담기 가능한 제품 쇼핑백에 담기
 */
function addBasketInfo(product_type, product_idx, option_info) {
	$.ajax({
		type: "post",
		url: api_location + "order/basket/add",
		headers: {
			"country": getLanguage()
		},
		data: {
			'add_type': 'product',
			'product_type': product_type,
			'product_idx': product_idx,
			'option_info': option_info,
		},
		dataType: "json",
		error: function (d) {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0023", null);
			// notiModal("쇼핑백 추가처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200 && $('.basket-btn').data('status') == 2) {
				let basket_cnt = d.data.basket_cnt;
					
				let header_basket = document.querySelector('.flex.basket__btn.side-bar');
				header_basket.dataset.cnt = basket_cnt;
				
				// 사이드바
				let sideContainer = document.querySelector("#sidebar");
				let sideBg = document.querySelector(".side__background");
				let sideWrap = document.querySelector(".side__wrap");

				if (getLoginStatus() == 'false') {
					location.href = '/login';
					alert("로그인 후 시도해주세요.");
					return
				} else {
					let sideBarCloseBtn = document.querySelector('.sidebar-close-btn');
					sideBarCloseBtn.addEventListener("click", sidebarClose);

					const basket = new Basket("basket", true);
					basket.writeHtml();

					if (sideContainer.classList.contains("open")) {
						sidebarClose();
					} else {
						sidebarOpen();
					}

					function sidebarClose() {
						sideContainer.classList.remove("open");
						sideWrap.classList.remove("open");
						sideBg.classList.remove("open");
						$("#dimmer").removeClass("show");
					}

					function sidebarOpen() {
						sideContainer.classList.add("open");
						sideWrap.classList.add("open");
						sideBg.classList.add("open");
						$("#dimmer").addClass("show");
					}

					addCartForGA(option_info);
				}
			} else {
				if(d.msg != null){
					//notiModal(d.msg);
					makeMsgNoti(getLanguage(), d.msg, null);
				}
				else{
					makeMsgNoti(getLanguage(), "MSG_F_ERR_0023", null);
				}
			}
		}
	});
}

function addProductReorder(product_type,product_idx,option_idx) {
	$.ajax({
		type: "post",
		url: api_location + "order/reorder/add",
		data: {
			'add_type': 'product',
			'product_type': product_type,
			'product_idx': product_idx,
			'option_info': option_idx,
		},
		dataType: "json",
		error: function () {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0014", null);
			// notiModal("재입고 알림 신청처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				makeMsgNoti(getLanguage(), "MSG_F_INF_0002", null);
				// notiModal('선택한 상품의 재입고 알림 신청이 완료되었습니다.');
			} else {
				notiModal(d.msg);
			}
		}
	});
}

//현재 상품 컬러 체크 && 페이지 이동
function clickProductColor() {
	const color_box = document.querySelector(".color__box");
	const colors = color_box.querySelectorAll(".color-line");

	let product_idx = document.querySelector("main").dataset.productidx;
	colors.forEach(el => {
		if (el.dataset.idx === product_idx) {
			el.classList.add("select");
			el.remove();

			let cloneNode = el.cloneNode(true);
			color_box.prepend(cloneNode);
		}

		el.addEventListener("mouseover", function (e) {
			//document.querySelector(".color-line").classList.add("select");
			el.classList.add("select");
		});

		el.addEventListener("mouseout", function (e) {
			//document.querySelector(".color-line.select").classList.remove("select");
			if (!el.classList.contains('.set_product')) {
				el.classList.remove("select");
			}
		});

		el.addEventListener("click", function (e) {
			let targetIdx = e.currentTarget.dataset.idx;
			window.location.href = `/product/detail?product_idx=${targetIdx}`;
		});
	});
}

/**
 * @author SIMJAE  
 * @description 모바일 상품 상세정보 이벤트 핸들러 
 */
function mobileDetailBtnHanddler() {
	let $$btn = document.querySelectorAll(".rM-detail-containner .detail__btn__row");
	let controllBtn = document.querySelector(".rM-detail-containner .detail__btn__control");
	let prevBtn = document.querySelector(".rM-detail-containner .detail-btn-prev");
	let nextBtn = document.querySelector(".rM-detail-containner .detail-btn-next");
	let currentIdx = 0;

	$$btn.forEach((btn, idx) => {
		btn.addEventListener("click", function (e) {
			let deatilContentBox = document.querySelector(".rM-detail-containner .detail__content__box");

			if (e.currentTarget.classList.contains("select")) {
				deatilContentBox.classList.add("hidden");
				e.currentTarget.classList.remove("select");
				e.currentTarget.offsetParent.classList.remove("open");
			} else {
				deatilContentBox.classList.remove("hidden");
				let detailContainer = document.querySelector(".rM-detail-containner .basket__wrap--btn.nav");
				let stylingWithWrap = document.querySelector(".styling-with-wrap");
				let isNoSidebar = stylingWithWrap.classList.contains("no_sidebar");

				if(isNoSidebar == true) {
					detailContainer.scrollIntoView({
						block: "end",
						behavior:"smooth"
					});
				}
				
				$$btn.forEach(el => el.classList.remove("select"));
				btn.classList.add("select");
				
				e.currentTarget.offsetParent.classList.add("open");
				mobileSizeGuideContentBody(idx);
			}

			currentIdx = clickControllBtnEvent();
			updateControllBtnCss(idx);
			sizeguideBtnEvent();
		});
	});

	prevBtn.addEventListener("click", function (e) {
		if (currentIdx == 0) { return false; }
		currentIdx--;
		updateSelectElem(currentIdx);
		updateControllBtnCss(currentIdx);
		mobileSizeGuideContentBody(currentIdx);
	});
	nextBtn.addEventListener("click", function (e) {
		if (currentIdx == 4) { return false; }
		currentIdx++;
		updateSelectElem(currentIdx);
		updateControllBtnCss(currentIdx);
		mobileSizeGuideContentBody(currentIdx);
	});

	//컨트롤러 버튼 css 갱신
	function updateControllBtnCss(idx) {
		let prevBtn = document.querySelector(".rM-detail-containner .detail-btn-prev");
		let nextBtn = document.querySelector(".rM-detail-containner .detail-btn-next");
		if (idx == 0) {
			prevBtn.style.opacity = "0";
			nextBtn.style.opacity = "inherit";
		} else if (idx == 3) {
			prevBtn.style.opacity = "inherit";
			nextBtn.style.opacity = "0";
		} else {
			nextBtn.style.opacity = "inherit";
			prevBtn.style.opacity = "inherit";
		}
	}
	//선택되어있는 idx불러오기
	function clickControllBtnEvent() {
		let currentIdx;
		[...$$btn].find((el, idx) => {
			if (el.classList.contains("select")) { currentIdx = idx; }
		});
		return currentIdx;
	}

	function updateSelectElem(idx) {
		let btn = $(".rM-detail-containner .detail__btn__row");
		btn.removeClass("select");
		btn.eq(idx).addClass("select");
	}
	function mobileSizeGuideContentBody(idx) {
		let detailContentBox = document.querySelector(".rM-detail-containner .detail__content__box");
		let sizeguide = detailContentBox.querySelector(".detail-content.sizeguide");
		let material = detailContentBox.querySelector(".detail-content.material");
		let productinfo = detailContentBox.querySelector(".detail-content.productinfo");
		let precaution = detailContentBox.querySelector(".detail-content.precaution");

		if (idx == 0) {
			sizeguide.classList.remove("hidden");
			material.classList.add("hidden");
			productinfo.classList.add("hidden");
			precaution.classList.add("hidden");
		} else if (idx == 1) {
			material.classList.remove("hidden");
			sizeguide.classList.add("hidden");
			productinfo.classList.add("hidden");
			precaution.classList.add("hidden");
		} else if (idx == 2) {
			productinfo.classList.remove("hidden");
			sizeguide.classList.add("hidden");
			material.classList.add("hidden");
			precaution.classList.add("hidden");
		} else if (idx == 3) {
			precaution.classList.remove("hidden");
			sizeguide.classList.add("hidden");
			material.classList.add("hidden");
			productinfo.classList.add("hidden");
		}
	}

	function sizeguideBtnEvent() {
		let $$sizeBtn = document.querySelectorAll(".rM-detail-containner .sizeguide-btn");
		$$sizeBtn.forEach((el, idx) => el.addEventListener("click", function () {
			$$sizeBtn.forEach(el => el.classList.remove("select"));
			this.classList.add("select");
		}));
	}
}
/**
 * @author SIMJAE  
 * @description 웹 상품 상세정보 이벤트 핸들러 
 */
function webDetailBtnHanddler() {
	let $$detailBtn = document.querySelectorAll(".info__box .detail__btn__row");
	let $detailWrap = document.querySelector(".info__box .detail__btn__wrap.web");
	let currentIdx = 0;

	$$detailBtn.forEach((btn, idx) => {
		btn.addEventListener("click", function (e) {
			
			if (e.currentTarget.classList.contains("select")) {
				// unSelectBtn(btn,e);
				sideBarClose();
				$('#quickview').removeClass("hidden");
			} else {
				let detailWrapper = document.querySelector(".detail__wrapper");
				let stylingWithWrap = document.querySelector(".styling-with-wrap");
				let isNoSidebar = stylingWithWrap.classList.contains("no_sidebar");

				if(isNoSidebar == true) {
					detailWrapper.scrollIntoView({
						block: "end",
						behavior:"smooth"
					});
				}

				sideBarOpen(e);
				selectBtn(btn);
				allCloseWrap();
				$('#quickview').addClass("hidden");
			}
			currentIdx = clickControllBtnEvent();
			webSizeGuideContentBody(idx);
			changeLanguageR();
		});
	});
	//선택되어있는 idx불러오기
	function clickControllBtnEvent() {
		let currentIdx;
		[...$$detailBtn].find((el, idx) => {
			if (el.classList.contains("select")) { currentIdx = idx; }
		});
		return currentIdx;
	}

	const $detailSidebarWrap = document.querySelector(".detail__sidebar__wrap");
	const $sidebarBg = document.querySelector(".detail__sidebar__wrap .sidebar__background");
	const $sidebarWrap = document.querySelector(".detail__sidebar__wrap .sidebar__wrap");
	const $sidebarCloseBtn = document.querySelector(".detail__sidebar__wrap .sidebar__close__btn");

	function unSelectBtn(btn, e) {
		e.currentTarget.offsetParent.classList.remove("open");
		e.currentTarget.classList.remove("select");
	}

	function selectBtn(btn) {
		$$detailBtn.forEach(el => el.classList.remove("select"));
		btn.classList.add("select");
	}

	function webSizeGuideContentBody(idx) {
		let detailContentBox = document.querySelector(".detail__sidebar__wrap .detail__content__box");
		let sizeguide = detailContentBox.querySelector(".detail-content.sizeguide");
		let material = detailContentBox.querySelector(".detail-content.material");
		let productinfo = detailContentBox.querySelector(".detail-content.productinfo");
		let precaution = detailContentBox.querySelector(".detail-content.precaution");

		if (idx == 0) {
			sizeguide.classList.remove("hidden");
			material.classList.add("hidden");
			productinfo.classList.add("hidden");
			precaution.classList.add("hidden");
		} else if (idx == 1) {
			material.classList.remove("hidden");
			sizeguide.classList.add("hidden");
			productinfo.classList.add("hidden");
			precaution.classList.add("hidden");
		} else if (idx == 2) {
			productinfo.classList.remove("hidden");
			sizeguide.classList.add("hidden");
			material.classList.add("hidden");
			precaution.classList.add("hidden");
		} else if (idx == 3) {
			precaution.classList.remove("hidden");
			sizeguide.classList.add("hidden");
			material.classList.add("hidden");
			productinfo.classList.add("hidden");
		}
	}
	//이벤트 달기
	function sideBarOpen(e) {
		e.target.offsetParent.classList.add("open");
		$detailSidebarWrap.classList.add("open");
		$sidebarBg.classList.add("open");
		$sidebarWrap.classList.add("open");
		$sidebarCloseBtn.addEventListener("click", sideBarClose)
	}

	function sideBarClose() {
		$detailWrap.classList.remove("open");
		$detailSidebarWrap.classList.remove("open");
		$sidebarBg.classList.remove("open");
		$sidebarWrap.classList.remove("open");
		$$detailBtn.forEach(el => el.classList.remove("select"));
	}
}

function stylingObserver() {
	const target = document.querySelector('.styling-with-wrap');
	const ioCallback = (entries, io) => {
		
		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				target.classList.add("no_sidebar");
				let $detailWrap = document.querySelector(".info__box .detail__btn__wrap.web");
				let $$detailBtn = document.querySelectorAll(".info__box .detail__btn__row");
				const $detailSidebarWrap = document.querySelector(".detail__sidebar__wrap");
				const $sidebarBg = document.querySelector(".detail__sidebar__wrap .sidebar__background");
				const $sidebarWrap = document.querySelector(".detail__sidebar__wrap .sidebar__wrap");

				$detailWrap.classList.remove("open");
				$detailSidebarWrap.classList.remove("open");
				$sidebarBg.classList.remove("open");
				$sidebarWrap.classList.remove("open");
				$$detailBtn.forEach(el => el.classList.remove("select"));

				let urlParts = location.href.split('?')[0].split('/');
                let path = '/' + urlParts.slice(3).join('/');
				if(path != "/product/detail"){
					$('#quickview').removeClass("hidden");
				}
			} else {
				target.classList.remove("no_sidebar");
			}
		});
	};

	const stylingObserve = new IntersectionObserver(ioCallback, { threshold: 0.4 });
	stylingObserve.observe(target);
}

/**
 * @author SIMJAE
 * @description 웹 상품정보 사이드바 
 */
function innerSideBar() {
	let sideWrap = document.createElement("div");
	sideWrap.className = "detail__sidebar__wrap"
	sideWrap.innerHTML = `
		<div class="sidebar__background" data-modal="detail">
			<div class="sidebar__wrap" data-modal="detail">
				<div class="detail--box--btn"></div>
				<div class="sidebar__box" data-modal="detail">
					<div class="sidebar__header">
						<img class="sidebar__close__btn" src="/images/svg/close.svg" alt="">
					</div>
					<div class="sidebar__body">
						<div class="detail__content__box">
							<div class="detail-content sizeguide hidden">
								<div class="content-header"><span data-i18n="pd_size_guide">사이즈가이드</span></div>
								<div class="content-body"></div>
							</div>
							<div class="detail-content material hidden">
								<div class="content-header"><span data-i18n="pd_material">소재</span></div>
								<div class="content-body"></div>
							</div>
							<div class="detail-content productinfo hidden">
								<div class="content-header"><span data-i18n="pd_details">제품 상세 정보</span></div>
								<div class="content-body"></div>
							</div>
							<div class="detail-content precaution hidden">
								<div class="content-header"><span data-i18n="pd_care">취급 유의 사항</span></div>
								<div class="content-body"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	`
	document.querySelector(".info__wrap").appendChild(sideWrap);
}

function addProductForGA(data, country) {

	if (data != null && country != null) {
		var productPrice = data.sales_price;//.replace(/[^0-9]/g,''); // replace 필요할 경우 사용

		let txt_currency = null;
		if (country == 'KR') {
			txt_currency = 'KRW';
		} else if (country == 'EN' || country == 'CN') {
			txt_currency = 'USD';
		}

		dataLayer.push({
			'event': 'view_item',
			'ecommerce': {
				'items': [{
					'item_id': data.product_idx,
					'item_name': data.product_name,
					'item_brand': data.brand,
					'item_category': '',
					'item_variant': '',
					'currency': txt_currency,
					'price': data.sales_price,
					'quantity': 1
				}]
			}
		});
	}
}

function addCartForGA(option_arr) {
	let country = getLanguage();

	let currency_str = null;
	if (country == 'KR') {
		currency_str = 'KRW';
	} else if (country == 'EN' || country == 'CN') {
		currency_str = 'USD';
	}
	getOptionProductList(option_arr, currency_str);
}

//장바구니 담기를 할 수 있는지와 장바구니에 담을 목록을 구한다.
function getOptionProductList(option_arr, currency_str) {
	var pList = [];

	$.ajax({
		type: "post",
		url: api_location + "product/option/get",
		headers: {
			"country": getLanguage(),
		},
		data: {
			"option_idx_arr": option_arr
		},
		async: false,
		dataType: "json",
		error: function () {
			return {
				'totalList': pList.length,
				'list': pList
			};
		},
		success: function (d) {
			if (d != null && d.data != null) {
				let data = d.data;
				data.forEach(function (row) {
					var pName = row.product_name;
					var pVariant = row.option_name;
					var pCategory = '';
					var pBrand = row.brand;
					var pQuantity = 1;
					var pPrice = row.sales_price;
					var productNo = row.product_code;
					pList.push({
						'item_name': pName,
						'item_variant': pVariant,
						'item_id': productNo,
						'price': pPrice,
						'quantity': pQuantity,
						'item_brand': pBrand,
						'item_category': pCategory
					});
				});
				dataLayer.push({
					'event': 'add_to_cart',
					'ecommerce': {
						'currencyCode': currency_str,
						'items': pList
					}
				});
			}
		}
	});
}

function showAndHideRedNoti(type) {
	let size_option = document.querySelectorAll(".detail__wrapper .product_box .product_size");
	let red_noti = document.querySelector(".red_noti");

	if (type == "B") {
		size_option.forEach(size => {
			size.addEventListener("mouseenter", (e) => {
				let size_status = e.currentTarget.dataset.status;
				if (size_status == 2) {
					if (e.currentTarget.dataset.soldout == "STCL") {
						red_noti.classList.remove("hidden");
					}
				}
			});

			size.addEventListener("mouseleave", () => {
				red_noti.classList.add("hidden");
			});
		});
	}
}
