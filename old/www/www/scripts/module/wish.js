window.addEventListener('DOMContentLoaded', function () {
	getWishListInfo();
	wishResizeEvent();
	clickBtnBasketAdd();
});

function wishResizeEvent(){
	window.addEventListener('resize', function(){
		optionboxHeights();
	})
}
const wish_quickSwiper = new Swiper(".quick-swiper", {
	observeParents: true,
	observeSlideChildren: true,
	slidesPerView: "auto",
	breakpoints: {
		320: {
			spaceBetween: 10,
			slidesPerView: 4.6
		},
		420: {
			spaceBetween: 10,
			slidesPerView: 6
		},
		520: {
			spaceBetween: 10,
			slidesPerView: 7
		},
		620: {
			spaceBetween: 10,
			slidesPerView: 8
		},
	},
	navigation: {
		nextEl: ".swiper-button-next",
		prevEl: ".swiper-button-prev"
	}
});

wish_quickSwiper.on("click", function () {
	let idx = wish_quickSwiper.clickedIndex;
	if (idx != null) {
		let whishIdx = wish_quickSwiper.wrapperEl.children[idx].children[0].dataset.no;
		elementScroll("body-list", whishIdx);
	}
});

const getWishListInfo = () => {
	$.ajax({
		type: "post",
		url: api_location + "order/whish/list/get",
		dataType: "json",
		error: function () {
			makeMsgNoti(getLanguage(), "MSG_F_ERR_0016", null);
			// alert("위시리스트 등록상품 조회처리중 오류가 발생했습니다.");
		},
		success: function (d) {
			let code = d.code;
		
			if(code == 200) {
				let data = d.data;
				
				if (data == null) {
					initWishList();
				} else {
					writeWishList(data);
					
					optionboxHeights();
					clickProductSizeBtn();
					clickAddProductBtn();
					clickRemoveProductBtn();
					clickRemoveAllBtn();
					changeLanguageR();
				}
			} else {
				makeMsgNoti(getLanguage(), "MSG_F_ERR_0016", null);
			}
		}
	});
}

function initWishList() {
	let contentWrap = document.querySelector(".wishlist-section .left");
	contentWrap.classList.add("boder-eraser");

	let bodyWrap = document.querySelector(".wishlist-section .body-wrap");
	bodyWrap.classList.add("boder-eraser");

	let wishlistSection = document.querySelector(".wishlist-section");
	wishlistSection.classList.add("no-wishlist-section");

	bodyWrap.innerHTML = `
		<div class="no-wishlist-wrap">
			<div class="no-wishlist-msg" data-i18n="w_empty_msg"></div>
			<div class="continue-shopping-btn" data-i18n="s_continue_shopping"></div>
		</div>
	`;
	
	let coutinue_shopping_btn = document.querySelector('.continue-shopping-btn');
	coutinue_shopping_btn.addEventListener('click',function() {
		continueShopping();
	});
	changeLanguageR();
}

function writeWishList(data) {
	const body_wrap = document.querySelector(".content .body-wrap.list");
	body_wrap.innerHTML = ''

	let product_wrap = document.createElement("div");
	product_wrap.classList.add("product-wrap");

	let write_product_html = "";

	data.forEach(el => {
		let product_color_html = "";
		let color_rgb = el.color_rgb;
		let multi = color_rgb.split(";");

		if (multi.length === 2) {
			product_color_html += `
				<div class="color-line" >
					<div class="color multi" data-title="${multi}" style="background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);"></div>
				</div>
			`;
		} else {
			product_color_html += `
				<div class="color-line">
					<div class="color" data-title="${multi}" style="background-color:${multi[0]}"></div>
				</div>
			`;
		}
		
		let product_size_html = "";

		let product_size = el.product_size;
		if (product_size != null && product_size.length > 0) {
			if (el.product_type == "B") {
				product_size_html += `
					<div class="option-box">
						<div class="info-row" style="display:block;">
							<div class="size__box product_box">
				`;

				let product_size_head = product_size[0];
				let product_size_tail = product_size.slice(1);

				let stock_status = product_size_head.stock_status;

				let stock_class = "";
				if (stock_status == "STCL") {
					stock_class = "stock-stcl";
				} else if (stock_status == "STSC") {
					stock_class = "stock-stsc";
				}

				product_size_html += `
								<li class="product_size size ${stock_class}" data-product_type="${el.product_type}" data-product_idx="${el.product_idx}" data-option_idx="${product_size_head.option_idx}" data-reorder="false" data-sizetype="${product_size_head.size_type}" data-soldout="${product_size_head.stock_status}">
									${product_size_head.option_name}<p></p>
								</li>
				`;

				product_size_tail.forEach(size => {
					product_size_html += `
								<li class="product_size size" data-product_type="${el.product_type}" data-product_idx="${el.product_idx}" data-option_idx="${size.option_idx}" data-reorder="false" data-sizetype="${size.size_type}" data-soldout="${size.stock_status}">
									${size.option_name}<p></p>
								</li>
					`;
				});

				product_size_html += `
							</div>
						</div>
					</div>
				`;
			} else if (el.product_type == "S" && el.set_type != null) {
				let set_type = el.set_type;
				if (set_type == "SZ") {
					product_size_html += `
						<div class="option-box set-option">
							<div class="info-row" style="display:block;">
					`;

					product_size.forEach(size => {
						product_size_html += `
								<span>${size.product_name}</span>
								<div class="size__box product_box">`;

						let set_option_info = size.set_option_info;
						set_option_info.forEach(option => {
							product_size_html += `
									<li class="product_size size" data-product_type="${el.product_type}" data-product_idx="${option.product_idx}" data-option_idx="${option.option_idx}" data-reorder="false" data-sizetype="${option.size_type}" data-soldout="${option.stock_status}">${option.option_name}<p></p></li>
							`;
						});

						product_size_html += `
								</div>
						`;
					});

					product_size_html += `
							</div>
						</div>
					`;
				} else if (set_type == "CL") {
					product_size_html += `
						<div class="option-box set-option">
							<div class="info-row" style="display:block;">
					`;

					product_size.forEach(size => {
						product_size_html += `
							<span>${size.product_name}</span>
							<div class="color__box product_box">
						`;

						let set_option_info = size.set_option_info;
						set_option_info.forEach(option => {
							let set_color_rgb = option.color_rgb;
							let set_multi = set_color_rgb.split(";");

							let class_disable = "";
							if (option.stock_status == "STSO") {
								class_disable = "disable";
							}

							if (set_multi.length === 2) {
								product_size_html += `
										<div class="color-line">
											<p class="color-name">${option.color}</p>
											<div class="product_size color multi ${class_disable}" data-title="${set_multi}" date-product_type="${el.product_type}" data-product_idx="${option.product_idx}" data-option_idx="${option.option_idx}" style="background-color:linear-gradient(90deg, ${set_multi[0]} 50%, ${set_multi[1]} 50%);" data-soldout="${option.stock_status}"></div>
										</div>
								`;
							} else {
								product_size_html += `
										<div class="color-line">
											<p class="color-name">${option.color}</p>
											<div class="product_size color ${class_disable}" data-title="${set_multi}" data-product_type="${el.product_type}" data-product_idx="${option.product_idx}" data-option_idx="${option.option_idx}" style="background-color:${set_multi[0]}" data-soldout="${option.stock_status}"></div>
										</div>
								`;
							}
						});

						product_size_html += `
							</div>
						`;
					});

					product_size_html += `
							</div>
						</div>
					`;
				}
			}
		}


		write_product_html += `
			<div class="body-list product" data-wish_idx=${el.wish_idx} data-product_type="${el.product_type}" data-set_type="${el.set_type}" data-product_idx=${el.product_idx}>
				<div class="product-info">
					<div class="remove-btn"> 
						<img src="/images/svg/sold-line.svg">
						<img src="/images/svg/sold-line.svg">
					</div>
					<a href="/product/detail?product_idx=${el.product_idx}" class="docs-creator">
						<img class="prd-img" cnt="1" src="${cdn_img}${el.product_img}" alt="">
					</a>
					<div class="info-box">
						<div class="info-row">
							<div class="name" data-soldout="">
								<span>${el.product_name}</span>
							</div>
							${el.discount == 0 ? `
								<div class="price" data-soldout="${el.stock_status}" data-saleprice="${el.sales_price}" data-discount="${el.discount}" data-dis="false">
									${el.price}
								</div>
								` : `
								<div class="price" data-soldout="${el.stock_status}" data-saleprice="${el.sales_price}" data-discount="${el.discount}" data-dis="true">
									<span>${el.price}</span>
								</div>
							`}
						</div>
						<div class="info-row">
							<div class="color-title">
								<span>${el.color}</span>
							</div>
							<div class="color__box" data-maxcount="" data-colorcount="1" >
								${product_color_html}
							</div>
						</div>
						<div class="option-wrap">
							${product_size_html}
						`;
		
		let soldout_cnt = 0;
		for (let i=0; i<product_size.length; i++) {
			if (product_size[i].stock_status == "STSO") {
				soldout_cnt++;
			}
		}
		
		if (soldout_cnt == product_size.length) {
			write_product_html += `
							<div data-product_idx="${el.product_idx}" class="product-select-btn soldout">
								<img class="hidden" src="">
								<span data-i18n="w_select_soldout"></span>
							</div>
			`;
		} else {
			write_product_html += `
							<div data-product_idx="${el.product_idx}" class="product-select-btn">
								<img class="hidden" src="">
								<span data-i18n="w_select"></span>
							</div>
			`;
		}
		write_product_html += `
						</div>
					</div>
				</div>
			</div>
		`;
	});

	product_wrap.innerHTML = write_product_html;
	body_wrap.appendChild(product_wrap);

	let hidden_cnt = document.querySelector("#wish-product-cnt");
	let product_cnt = body_wrap.querySelectorAll(".list .product").length;
	hidden_cnt.value = product_cnt;
}

function optionboxHeights() {
	let itemPerRow = 0;
	if (window.innerWidth > 1025) {
		itemPerRow = 4;
	}
	else{
		itemPerRow = 2;
	}
	let optionBoxList = document.querySelectorAll('.option-box');
	let optionBoxInfoRowList = document.querySelectorAll('.option-box .info-row');

	let heightArr = [];
	optionBoxInfoRowList.forEach(function(el, index){
		if(index % itemPerRow == 0){
			heightArr[Math.floor(index / itemPerRow)] = el.offsetHeight;
		}
		else{
			if(heightArr[Math.floor(index / itemPerRow)] < el.offsetHeight){
				heightArr[Math.floor(index / itemPerRow)] = el.offsetHeight;
			}
		}
	});
	//heightArr[0] : 0 ~ 3 의 높이 최대값 = 200px
	//heightArr[1] : 4 ~ 5 의 높이 최대값 = 150px
	optionBoxList.forEach(function(el,index){
		el.style.height = `${heightArr[Math.floor(index / itemPerRow)]  + 20}px`;
	})
	//
	

	/*
	let optionBoxItems = Array.from(document.querySelectorAll('.option-box'));

	let allItems = [ ...optionBoxItems];

	let maxHeights = [];
	let itemsPerGroup = 2; 
	if (window.innerWidth > 1025) {
		for (let i = 0; i < allItems.length; i += 4) {
			let row = allItems.slice(i, i + 4);
			let maxHeight = Math.max(...row.map(item => item.querySelector('.info-row').offsetHeight));
			maxHeights.push(maxHeight);
		}

		for (let i = 0; i < allItems.length; i++) {
			let groupIndex = Math.floor(i / 4);
			allItems[i].querySelector('.info-row').style.height = maxHeights[groupIndex] + 'px';
		}
	} else {
		for (let i = 0; i < allItems.length; i += itemsPerGroup) {
			let row = allItems.slice(i, i + itemsPerGroup);
			let maxHeight = Math.max(...row.map(item => item.querySelector('.info-row').offsetHeight));
			maxHeights.push(maxHeight);
		  }
		
		  for (let i = 0; i < allItems.length; i++) {
			let groupIndex = Math.floor(i / itemsPerGroup);
			allItems[i].querySelector('.info-row').style.height = maxHeights[groupIndex] + 'px';
		  }
	}
	*/
}

/*-------------------------이벤트 핸들러-------------------------- */
// 상품 사이즈 선택
function clickProductSizeBtn() {
	let product_size = document.querySelectorAll('.product_size');
	product_size.forEach(size => {
		size.addEventListener('click', function (e) {
			let wish_product = e.currentTarget.offsetParent;
			let product_type = wish_product.dataset.product_type;
			let set_type = wish_product.dataset.set_type;

			let size_btn = e.currentTarget;
			let product_box = null;
			if (set_type != "CL") {
				product_box = size_btn.parentNode;
			} else {
				product_box = size_btn.parentNode.parentNode;
			}

			let size_status = size_btn.dataset.status;
			let stock_status = size_btn.dataset.soldout;

			let wish_btn = wish_product.querySelector('.product-select-btn');

			//상품 재고 상태 반영
			if (stock_status != "STSO") {
				if (stock_status != "STSC") {
					initProductSizeByFlg(wish_product,false);
					initProductSize(product_box,"STSC");
					if (size_btn.classList.contains('select')) {
						size_btn.classList.toggle("select");
					} else {
						if (product_type == "S") {
							initProductSize(product_box,null);
							size_btn.classList.toggle("select");
						} else {
							size_btn.classList.toggle("select");
						}
						
						wish_btn.classList.remove('reorder');
						wish_btn.classList.remove('option');
						wish_btn.querySelector("span").dataset.i18n = "w_select";
						wish_btn.querySelector("span").textContent = i18next.t("w_select");
						
						wish_btn.querySelector("img").classList.add("hidden");
						wish_btn.querySelector("img").setAttribute("src", "");
					}
				} else {
					initProductSizeByFlg(wish_product,true);
					initProductSize(product_box,null);
					size_btn.classList.toggle('select');
					
					wish_btn.classList.add('reorder');
					wish_btn.querySelector("span").dataset.i18n = "pd_basket_msg_02";

					wish_btn.querySelector("img").classList.remove("hidden");
					wish_btn.querySelector("img").setAttribute("src", "/images/svg/reflesh-bk.svg");
				}
			}
		});
	});
}

function initProductSize(product_box,stock_status) {
	let product_size = product_box.querySelectorAll('.product_size');
	product_size.forEach(size => {
		if (stock_status != null) {
			let tmp_stock_status = size.dataset.soldout;
			if (tmp_stock_status == stock_status) {
				size.classList.remove('select');
			}
		} else {
			size.classList.remove('select');
		}
	});
}

function initProductSizeByFlg(wish_product,stsc_flg) {
	let product_size = wish_product.querySelectorAll('.product_size');
	product_size.forEach(size => {
		let tmp_stock_status = size.dataset.soldout;
		
		if (stsc_flg == true) {
			if (tmp_stock_status != "STSC") {
				size.classList.remove('select');
			}
		} else {
			if (tmp_stock_status == "STSC") {
				size.classList.remove('select');
			}
		}
		
	});
}

//오른쪽사이드에 상품 추가하는 버튼 
function clickAddProductBtn() {
	let $$add_product_btn = document.querySelectorAll(".product-select-btn");

	let add_product = {}

	$$add_product_btn.forEach((el, index) => {
		el.addEventListener("click", function (e) {
			if (!el.classList.contains('soldout')) {
				let wish_product = e.currentTarget.offsetParent;
				
				let wish_idx = wish_product.dataset.wish_idx;
				let product_idx = wish_product.dataset.product_idx;
				let product_type = wish_product.dataset.product_type;
				let set_type = wish_product.dataset.set_type;
				let img_src = wish_product.querySelector(".prd-img").getAttribute("src");
				let product_name = wish_product.querySelector(".name span").innerHTML;
				
				let wish_btn = e.currentTarget;
				if (wish_btn.classList.contains("select")) {
					resetSizeBox(wish_idx);
					removeAddList(wish_idx);

					wish_btn.classList.remove("select");
					wish_btn.classList.remove("option");

					wish_btn.querySelector("span").dataset.i18n = "w_select";
					wish_btn.querySelector("span").textContent = i18next.t("w_select");
				} else {
					let check_obj = null;
					let select_obj_cnt = 0;

					check_obj = wish_product.querySelectorAll('.product_box');
					check_obj.forEach(check => {
						let tmp_size = check.querySelectorAll('.product_size');
						tmp_size.forEach(tmp => {
							if (tmp.classList.contains('select')) {
								select_obj_cnt++;
							}
						});
					});

					if (product_type == "S") {
						if (check_obj.length > select_obj_cnt) {
							// notiModal('세트상품을 전부 선택 한 경우에만 구매가 가능합니다.')
							makeMsgNoti(getLanguage(), 'MSG_F_WRN_0026', null);
							return false;
						}
					}

					let size_text_info = new Array();
					let size_data_info = new Array();

					let product_size = wish_product.querySelectorAll('.product_size');
					product_size.forEach(size => {
						if (size.classList.contains('select')) {
							if (set_type == "CL") {
								size_text_info.push(size.dataset.title);
							} else {
								size_text_info.push(size.innerHTML);
							}

							if (product_type == "B") {
								size_data_info.push(size.dataset.option_idx);
							} else if (product_type == "S") {
								size_data_info.push({
									'product_idx': size.dataset.product_idx,
									'option_idx': size.dataset.option_idx
								});
							}
						}
					});
					
					/* --------사이즈 선택없이 버튼 누를경우 --------*/
					if (!size_data_info.length > 0) {
						wish_btn.classList.add("option");
						wish_btn.children[1].dataset.i18n = "w_select_option";
						wish_btn.children[1].textContent = i18next.t("w_select_option");
						
						return false;
					}
					
					/* --------품절, 리오더 사이즈를 선택하고 버튼을 눌렀을때 --------*/
					if (wish_btn.classList.contains('reorder')) {
						$.ajax({
							type: "post",
							url: api_location + "order/reorder/add",
							data: {
								'add_type': 'wish',
								'product_type': product_type,
								'product_idx': product_idx,
								'option_info': size_data_info,
							},
							dataType: "json",
							error: function () {
								makeMsgNoti(getLanguage(), 'MSG_F_ERR_0014', null);
								// notiModal("재입고 알림 신청처리중 오류가 발생했습니다.");
							},
							success: function (d) {
								if (d.code == 200) {
									document.querySelectorAll('.product_size').forEach(size => {
										size.classList.remove('select');
									});
									
									wish_btn.classList.remove('reorder');
									wish_btn.querySelector("span").dataset.i18n = "w_select";
									wish_btn.querySelector("span").textContent = i18next.t("w_select");
									
									wish_btn.querySelector("img").classList.add("hidden");
									wish_btn.querySelector("img").setAttribute("src", "");
							
									makeMsgNoti(getLanguage(), 'MSG_F_INF_0002', null);
									// notiModal('선택한 상품의 재입고 알림 신청이 완료되었습니다.');
								} else {
									notiModal(d.msg);
								}
							}
						});
					} else {
						add_product.wish_idx = wish_idx;
						add_product.product_type = product_type;
						add_product.set_type = set_type;

						add_product.img_src = img_src;
						add_product.product_name = product_name;

						add_product.size_text_info = size_text_info;
						add_product.size_data_info = size_data_info;

						e.currentTarget.classList.add("select");
						writeAddBoxHtml(add_product);

						check_obj.forEach(check => {
							check.classList.add("disable");
						});

						el.querySelector("span").dataset.i18n = "w_remove";
						el.querySelector("span").textContent = i18next.t("w_remove");
						
						if (el.dataset.status == 1 || el.dataset.status == 0) {
							let reorder = size_data_info.map(el => {
								el.dataset.reorder = "true"
								return el
							});
						}
					}
				}

				showAddWrapBtns();
			}
		});
	})
}

function clickRemoveProductBtn() {
	const remove_btn = document.querySelectorAll(".remove-btn");

	remove_btn.forEach(el => {
		el.addEventListener("click", function (e) {
			wish_idx = e.currentTarget.offsetParent.dataset.wish_idx;

			$.ajax({
				type: "post",
				url: api_location + "order/whish/delete",
				data: {
					"whish_idx": wish_idx,
				},
				dataType: "json",
				error: function () {
					
				},
				success: function (d) {
					if (d.code == 200) {
						let product = document.querySelectorAll(".product-wrap .product");
						let result = [...product].find(el => el.dataset.wish_idx === wish_idx);
						console.log(result);
						if (result != null) {
							result.remove();
							removeAddList(wish_idx);
							optionboxHeights();
							
							foryou.changeWishBtnStatus(result.dataset.idx)
							document.querySelector('.header__wrap .wishlist__btn').dataset.cnt = d.data;
						}

						product = document.querySelectorAll(".product-wrap .product");
						if (product.length === 0) {
							initWishList();
						}
						document.querySelector('.header__wrap .wishlist__btn').dataset.cnt = d.data;
						getWishListInfo();

						let delProdHeartImg = $(`.wish__btn[product_idx=${d.result_prod_idx}]`).find('img');
						delProdHeartImg.attr('data-status', false);
						delProdHeartImg.attr('src', '/images/svg/wishlist.svg');
					} else {
						notiModal(d.msg);
					}
				}
			});
		});
	});
}


function writeAddBoxHtml(data) {
	let body_wrap = document.querySelector(".add-list-wrap .body-wrap");
	let swiperWrap = document.querySelector(".add-list-wrap .quick-swiper .swiper-wrapper");
    let slideEl = document.createElement("div");

	let wish_idx = data.wish_idx;
	let product_type = data.product_type;
	let set_type = data.set_type;
	let size_text_info = data.size_text_info;
	let size_data_info = data.size_data_info;
	
	let add_box = document.createElement("div");
	add_box.dataset.wish_idx = wish_idx;
	add_box.dataset.product_type = product_type;
	add_box.dataset.option_info = JSON.stringify(data.size_data_info);

	let size_text_html = "";
	if (set_type != "CL") {
		size_text_info.forEach(el => {
			size_text_html += `
				<span>${el}</span>
			`;
		});
	} else {
		size_text_info.forEach(el => {
			let multi = el.split(",");
			if (multi.length === 2) {
				size_text_html += `
					<div class="color-line" style="--background-color:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
						<div class="color multi" data-title="${multi}"></div>
					</div>
				`;
			} else {
				size_text_html += `
					<div class="color-line">
						<div class="color" data-title="${multi}" style="background-color:${multi[0]}"></div>
					</div>
				`;
			}
		});
	}


	let add_box_html = `
		<img src="${data.img_src}" alt="">
		<div class="product-title">
			<span>${data.product_name}</span>
			<div class="size-list">
				${size_text_html}
			</div>
		</div>
	`;
	let quick_item_html = `
		<img src="${data.img_src}" alt="">
	`;

	add_box.innerHTML = add_box_html;
	add_box.classList.add("add-box");
	body_wrap.appendChild(add_box);

	slideEl.innerHTML = quick_item_html;
	slideEl.classList.add("swiper-slide");
    slideEl.dataset.no = data.wish_idx;
	swiperWrap.appendChild(slideEl);
	wish_quickSwiper.update();

	showAddWrapBtns();
}

let shirinkStart = 10;
$(window).scroll(function () {
	let scroll = currentScroll();
	let banner = document.querySelector(".banner-wrap");
	let right_open = document.querySelector(".content.right.open");
	let addList = document.querySelector(".add-list-wrap");
	if (banner && addList && scroll >= shirinkStart) {
		banner.classList.add("shrink-banner");
		addList.classList.add("shrink-list");
		
		if (right_open != null) {
			right_open.classList.add("shrink-wrap");
		}
	} else {
		banner && banner.classList.remove("shrink-banner");
		addList && addList.classList.remove("shrink-list");
		
		if (right_open != null) {
			right_open.classList.remove("shrink-wrap");
		}
	}
})

function currentScroll() {
	return window.pageYOffset || document.documentElement.scrollTop;
}

const checkAddBoxDuplicate = (wish_idx) => {
	let check_result = false;

	let add_box = document.querySelectorAll(".add-list-wrap .add-box");

	let err_cnt = 0;
	add_box.forEach(box => {
		let tmp_wish_idx = box.dataset.wish_idx;
		if (wish_idx == tmp_wish_idx) {
			err_cnt++;
		}
	});

	if (!err_cnt > 0) {
		check_result = true;
	}

	return check_result
}

//쇼핑백에 담기 버튼
function basketAddBtnHandler() {
	let basketBtn = document.querySelector(".add-list-wrap .basket-link-btn");
	const addType = "whish";
	let addBox = document.querySelectorAll(".add-list-wrap .add-box");

	let wish_info = [];
	addBox.forEach(el => {
		let wish_idx = el.dataset.wish_idx;
		let product_type = el.dataset.product_type;
		let option_info = JSON.stringify(el.dataset.option_info);

		wish_info.push({
			'wish_idx': wish_idx,
			'product_type': product_type,
			'option_info': option_info
		});
	});

	if (wish_info != null) {
		$.ajax({
			type: "post",
			url: api_location + "order/basket/add",
			headers: {
				"country": getLanguage()
			},
			data: {
				'add_type': 'wish',
				'wish_info': wish_info,
			},
			dataType: "json",
			error: function () {
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0023', null);
				// notiModal("쇼핑백 추가처리중 오류가 발생했습니다.");
			},
			success: function (d) {
				if (d.code == 200) {
					location.href = '/order/basket/list';
				} else {
					notiModal(d.msg);
				}
			}
		});
	}
}

/*------------------------- 삭제 & 초기화 -------------------------- */
//찜리스트에 추가된 상품 제거 
const removeAddList = (wish_idx) => {
	let add_box = document.querySelectorAll(".add-list-wrap .add-box");
	let slide = document.querySelectorAll(".quick-swiper .swiper-slide");
	[...add_box].filter(el => {
		if (el.dataset.wish_idx == wish_idx) {
			el.remove();
		}
	});
	slide.forEach((el, idx) => {
		if (el.dataset.no == wish_idx) {
			wish_quickSwiper.removeSlide(idx);
			wish_quickSwiper.update();
		}
	});
}

//개별 위시리스트 상품 사이즈 버튼 초기화 
const resetSizeBox = (wish_idx) => {
	let product_box = document.querySelectorAll(".product_box");

	product_box.forEach((el, index) => {
		let wish_product = el.offsetParent;
		if (wish_product.dataset.wish_idx == wish_idx) {
			el.classList.remove("disable");

			let product_size = el.querySelectorAll('.product_size');
			product_size.forEach(size => {
				size.classList.remove('select');
			})
		}
	});
}

//찜리스트에 추가된상품 모두 제거 
function clickRemoveAllBtn() {
	let remove_all_btn = document.querySelector('.hd-title');
	remove_all_btn.addEventListener('click', function () {
		let $$productSelectBtn = document.querySelectorAll(".product-select-btn.select");

		let $$addBox = document.querySelectorAll(".body-wrap .add-box");
		$$addBox.forEach(el => {
			let whish_idx = el.dataset.wish_idx;
			el.remove();

			resetSizeBox(whish_idx);

			wish_quickSwiper.removeAllSlides();
            wish_quickSwiper.update();

			showAddWrapBtns();
		})
		$$productSelectBtn.forEach(el => {
			el.classList.remove("select");
			el.querySelector("span").dataset.i18n = "w_select";
			el.querySelector("span").textContent = i18next.t("w_select");
		});
	});
}

const statusArrCheck = (list) => {
	// 0 : 완전품절 || 1: 리오더가능 || 2: 재고 선택가능 || 3: commin-soon
	let result = Math.max(...list);
	return result;
}

/*------------------------- css 조작 스크립트 -------------------------- */
const showAddWrapBtns = () => {
	let contentRight = document.querySelector(".content.right");
	let addListWrap = document.querySelector(".add-list-wrap");
	let addbox = addListWrap.querySelectorAll(".body-wrap .add-box");

	let allRemoveBtn = addListWrap.querySelector(".hd-title");
	let basketLinkBtn = addListWrap.querySelector(".basket-link-btn");
	addListWrap.classList.remove("hidden");
	allRemoveBtn.classList.remove("hidden");


	if (addbox.length > 0) {
		contentRight.classList.add("open");

		addListWrap.classList.remove("hidden");
		allRemoveBtn.classList.remove("hidden");
		// contentRight.classList.remove("hidden");
	} else {
		contentRight.classList.remove("open");

		addListWrap.classList.add("hidden");
		allRemoveBtn.classList.add("hidden");
		// contentRight.classList.add("hidden");
	}
}
//퀵슬라이드 클릭시 스크롤 이동
function elementScroll(el, idx) {

	const headerHeight = document.querySelector("header").offsetHeight;
	const bannerHeight = document.querySelector(".banner-wrap").offsetHeight;
	// let elemTop = document.querySelectorAll(`.${el}`)[idx].offsetTop;
	let elemTop = [...document.querySelectorAll(`.body-list`)].find(el => el.dataset.wish_idx == idx).offsetTop;
	let result = elemTop - (headerHeight + bannerHeight);
	window.scrollTo(0, result);
}
let cntTarget = document.getElementById("wish-product-cnt");
let tempWrap = document.querySelector(".wishlist-section .body-wrap");
let bodyWrapL = document.querySelector(".wishlist-section .body-wrap.list");
let bodyWrapR = document.querySelector(".wishlist-section .right");
let wishlistSection = document.querySelector(".wishlist-section");

let btnObserver = new MutationObserver(mutations => {
	mutations.forEach(mutation => {
		if (cntTarget.value === '0') {
			initWishList();
		} else {
			tempWrap.classList.remove("boder-eraser");
			bodyWrapL.classList.remove("boder-eraser");
			wishlistSection.classList.remove("no-wishlist-section");
			bodyWrapR.classList.remove("height-eraser");
		}
	})
});
let btnObConfig = {
	attributes: true
}
btnObserver.observe(cntTarget, btnObConfig);

function clickBtnBasketAdd() {
	let btn_basket_add = document.querySelectorAll('.btn_basket_add');
	btn_basket_add.forEach(btn => {
		btn.addEventListener('click',function() {
			basketAddBtnHandler();
		});
	});
}

/*------------------------- css조작 스크립트 -------------------------- */
const foryou = new ForyouRender();