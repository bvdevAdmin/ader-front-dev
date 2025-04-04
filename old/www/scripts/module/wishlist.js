function WishlistRender() {
    this.makeHtml = (() => {
        const sectionWrap = document.querySelector(".wishlist-wrap")
        const wrap = document.createElement("aside");
        wrap.classList.add("wish-wrap");
        const dom = `
			<div class="left__title">
				<span class = "allview">Wish list&nbsp;&nbsp;&nbsp;></span>
				<span class= "allview_under" onclick="location.href='/order/whish'" data-i18n="lm_view_all">전체보기</span>
			</div>
			<div class="swiper-grid">
				<div class="wish-swiper swiper">
					<div class="swiper-wrapper">
						
					</div>
					<div class="swiper-button-prev"></div>
					<div class="swiper-button-next"></div>
				</div>
			</div>
		`;
		
        wrap.innerHTML = dom;
        sectionWrap.appendChild(wrap);
    })();

    this.load = (() => {
        $.ajax({
            type: "post",
            data:{'country' : getLanguage()},
            dataType: "json",
            url: api_location + "order/whish/list/get",
            error: function () {
                makeMsgNoti(getLanguage(), 'MSG_F_ERR_0095', null);
                // alert("위시리스트 페이지 불러오기 처리중 오류가 발생했습니다.");
            },
            success: function (d) {
				if (d.code == 200) {
					let data = d.data;
					if (data != null) {
						let productRecommendListHtml = "";
						const swiperWrap = document.querySelector(".wish-swiper .swiper-wrapper");
						const domFrag = document.createDocumentFragment();
		
						data.forEach(el => {
							let product_list_slide = document.createElement("div");
							product_list_slide.classList.add("swiper-slide");
							
							let whish_img = "";
		
							let login_status = getLoginStatus();
		
							if (login_status == "true") {
								whish_img = `
									<div class="remove-btn"> 
										<img src="/images/svg/sold-line.svg">
										<img src="/images/svg/sold-line.svg">
									</div>
								`;
							} else {
								whish_img = `
									<div class="remove-btn"> 
										<img src="/images/svg/sold-line.svg">
										<img src="/images/svg/sold-line.svg">
									</div>
								`;
							}
		
							let product_size = el.product_size;
		
							let sales_price = el.sales_price;
							let color_cnt = el.product_color.length;
		
							productRecommendListHtml = `
								<div class="product wish_list_mp">
									<div class="wish__btn hidden btn_remove_wish" product_idx="${el.product_idx}">
										${whish_img}
									</div>
									
									<a href="/product/detail?product_idx=${el.product_idx}">
										<div class="product-img swiper">
											<img class="prd-img" cnt="${el.product_idx}" src="${cdn_img}${el.product_img}" alt="">
										</div>
									</a>
									<div class="product-info">
										<div class="info-row">
											<div class="name"data-soldout=${el.stock_status == "STCL" ? "STCL" : ""}><span>${el.product_name}</span></div>
											${el.discount == 0 ? `<div class="price" data-soldout="${el.stock_status}" data-saleprice="${sales_price}" data-discount="${el.discount}" data-dis="false">${el.price}</div>` : `<div class="price" data-soldout="${el.stock_status}" data-saleprice="${sales_price}" data-discount="${el.discount}" data-dis="true"><span>${el.price}</span></div>`} 
										</div>
										<div class="color-title"><span>${el.color}</span></div>
										<div class="info-row">
											<div class="color__box" data-maxcount="${color_cnt < 6 ? "" : "over"}" data-colorcount="${color_cnt < 6 ? color_cnt : color_cnt - 5}">
												${el.product_color.map((color, idx) => {
													let maxCnt = 5;
													if (idx < maxCnt) {
														return `<div class="color" data-color="${color.color_rgb}" data-productidx="${color.product_idx}" data-soldout="${color.stock_status}" style="background-color:${color.color_rgb}"></div>`;
													}
												}).join("")
												}
											</div>
											<div class="size__box">
												${el.product_size.map((size) => {
													return `<li class="size" data-sizetype="" data-productidx="${size.product_idx}" data-optionidx="${size.option_idx}" data-soldout="${size.stock_status}">${size.option_name}</li>`;
												}).join("")
												}  
											</div>
										</div>
									</div>
								</div>
							`;
							
							product_list_slide.innerHTML = productRecommendListHtml;
							domFrag.appendChild(product_list_slide);
						});
						
						swiperWrap.appendChild(domFrag);
						
						let whish_list = document.querySelectorAll(".wish_list_mp");
						whish_list.forEach(list => list.addEventListener("mouseenter", function() {
							let remove_btn = list.querySelector(".wish__btn");
							remove_btn.classList.remove("hidden");
						}));
						
						whish_list.forEach(list => list.addEventListener("mouseleave", function() {
							let remove_btn = list.querySelector(".wish__btn");
							remove_btn.classList.add("hidden");
						}));
						
						clickBtnRemoveWish();
					} else {
						let swiperContainer = document.querySelector(".swiper-grid");
						let swiperMsgWrap = document.createElement("div");
						
						swiperMsgWrap.className = "no_wishlist_msg";
                        swiperMsgWrap.dataset.i18n = "w_empty_msg";
						swiperContainer.appendChild(swiperMsgWrap);
					}
				} else {
					
				}
            }
        });
        
    })();
    this.update = () => {
        $.ajax({
            type: "post",
            data:{'country' : getLanguage()},
            dataType: "json",
            url: api_location + "order/whish/list/get",
            error: function () {
                makeMsgNoti(getLanguage(), 'MSG_F_ERR_0095', null);
                // alert("위시리스트 페이지 불러오기 처리중 오류가 발생했습니다.");
            },
            success: function (d) {
                const swiperWrap = document.querySelector(".wish-swiper .swiper-wrapper");

                let data = d.data;

                if( data == null ) {
                    let swiperContainer = document.querySelector(".swiper-grid");
                    let swiperMsgWrap = document.createElement("div");
                    swiperWrap.innerHTML = '';
                    swiperMsgWrap.className = "no_wishlist_msg";
                    swiperMsgWrap.dataset.i18n = "w_empty_msg";
                    swiperContainer.appendChild(swiperMsgWrap);
                } else {
                    let productRecommendListHtml = "";
                    const domFrag = document.createDocumentFragment();
                    data.forEach(el => {
                        let product_list_slide = document.createElement("div");
                        product_list_slide.classList.add("swiper-slide");
                        let whish_img = "";
    
                        let login_status = getLoginStatus();
    
                        if (login_status == "true") {
                            whish_img = `
								<div class="remove-btn"> 
									<img src="/images/svg/sold-line.svg">
									<img src="/images/svg/sold-line.svg">
								</div>
							`;
                        } else {
                            whish_img = `
								<div class="remove-btn"> 
									<img src="/images/svg/sold-line.svg">
									<img src="/images/svg/sold-line.svg">
								</div>
							`;
                        }
    
                        let product_size = el.product_size;
    
                        let sales_price = el.sales_price;
                        let color_cnt = el.product_color.length;
    
                        productRecommendListHtml =
                            `<div class="product wish_list_mp">
                                <div class="wish__btn hidden btn_remove_wish" product_idx="${el.product_idx}">
                                    ${whish_img}
                                </div>
                                
                                <a href="/product/detail?product_idx=${el.product_idx}">
                                    <div class="product-img swiper">
                                        <img class="prd-img" cnt="${el.product_idx}" src="${cdn_img}${el.product_img}" alt="">
                                    </div>
                                </a>
                                <div class="product-info">
                                    <div class="info-row">
                                        <div class="name"data-soldout=${el.stock_status == "STCL" ? "STCL" : ""}><span>${el.product_name}</span></div>
                                        ${el.discount == 0 ? `<div class="price" data-soldout="${el.stock_status}" data-saleprice="${sales_price}" data-discount="${el.discount}" data-dis="false">${el.price}</div>` : `<div class="price" data-soldout="${el.stock_status}" data-saleprice="${sales_price}" data-discount="${el.discount}" data-dis="true"><span>${el.price}</span></div>`} 
                                    </div>
                                    <div class="color-title"><span>${el.color}</span></div>
                                    <div class="info-row">
                                        <div class="color__box" data-maxcount="${color_cnt < 6 ? "" : "over"}" data-colorcount="${color_cnt < 6 ? color_cnt : color_cnt - 5}">
                                            ${el.product_color.map((color, idx) => {
                                                let maxCnt = 5;
                                                if (idx < maxCnt) {
                                                    return `<div class="color" data-color="${color.color_rgb}" data-productidx="${color.product_idx}" data-soldout="${color.stock_status}" style="background-color:${color.color_rgb}"></div>`;
                                                }
                                            }).join("")
                                            }
                                        </div>
                                        <div class="size__box">
                                            ${el.product_size.map((size) => {
                                                return `<li class="size" data-sizetype="" data-productidx="${size.product_idx}" data-optionidx="${size.option_idx}" data-soldout="${size.stock_status}">${size.option_name}</li>`;
                                            }).join("")
                                            }  
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        product_list_slide.innerHTML = productRecommendListHtml;
                        domFrag.appendChild(product_list_slide);
                    });
                    
					//초기화
                    swiperWrap.innerHTML = '';
                    if(document.querySelector('.no_wishlist_msg') !== null ){
                        document.querySelector('.no_wishlist_msg').remove();
                    }
                    
                    swiperWrap.appendChild(domFrag);
					
					let whish_list = document.querySelectorAll(".wish_list_mp");
                    whish_list.forEach(list => list.addEventListener("mouseenter", function() {
                        let remove_btn = list.querySelector(".wish__btn");
                        remove_btn.classList.remove("hidden");
                    }));
                    
					whish_list.forEach(list => list.addEventListener("mouseleave", function() {
                        let remove_btn = list.querySelector(".wish__btn");
                        remove_btn.classList.add("hidden");
                    }));
					
					clickBtnRemoveWish();
                }
            }
        });
        
    };
    this.swiper = (() => {
        return new Swiper(".swiper-grid .wish-swiper", {
            watchOverflow: true,
            navigation: {
                nextEl: ".wish-swiper .swiper-button-next",
                prevEl: ".wish-swiper .swiper-button-prev",
            },
            pagination: {
                el: ".wish-swiper .swiper-pagination",
                clickable: true,
                disabledClass: 'swiper-button-disabled'
            },
            autoplayDisableOnInteraction: false,
            grabCursor: true,
            breakpoints: {
                // when window width is >= 320px
                320: {
                    slidesPerView: 2.647
                },
                400: {
                    slidesPerView: 3.3
                },
                500: {
                    slidesPerView: 4.3
                },
                920: {
                    slidesPerView: 5.3
                },
                1400: {
                    slidesPerView: 5.3
                }
            }
        });
    })();
    this.changeWishBtnStatus = (productIdx) => {
        let $$wishBtn = document.querySelectorAll('.wish__btn');
        $$wishBtn.forEach((el) => {
            if(el.getAttribute('product_idx') == productIdx){
                el.querySelector('img').dataset.status = false;
                el.querySelector('img').setAttribute('src', '/images/svg/wishlist.svg');
            }
        })
    }
   
}

function clickBtnRemoveWish() {
	let btn_remove_wish = document.querySelectorAll('.btn_remove_wish');
	if (btn_remove_wish != null) {
		btn_remove_wish.forEach(btn => {
			btn.addEventListener('click',function(e) {
				let el = e.currentTarget;
				deleteWish(el);
			});
		});
	}
}