function StylingRender(relevant_idx) {
    let relevantIdx = relevant_idx;
    console.log("🏂 ~ file: styling.js:4 ~ StylingRender ~ relevantIdx", relevantIdx)
    this.makeHtml = (() => {
        const sectionWrap = document.querySelector(".styling-with-wrap")
        const wrap = document.createElement("aside");
        wrap.classList.add("styling-wrap");
        const dom = 
            `
                <div class="left__title"><span>Styling with   ></span></div>
                <div class="swiper-grid">
                    <div class="styling-swiper swiper">
                        <div class="swiper-wrapper">
        
                        </div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            `
        wrap.innerHTML = dom;
        sectionWrap.appendChild(wrap);
    })();
    
    this.load = (() =>{
        $.ajax({
            type: "post",
            data: {
                "relevant_idx": relevantIdx,
                "country": getLanguage()
            },
            url: api_location + "common/relevant/get",
            error: function() {
                makeMsgNoti(getLanguage(), "MSG_F_ERR_0087", null);
                // notiModal("상품 진열 페이지 불러오기 처리에 실패했습니다.");
            },
            success: function(d) {
				let data = d.data;
                
                if(data != null) {
                    let productRecommendListHtml = "";
                    const stylingSwiper = document.querySelector(".styling-swiper ");
                    const swiperWrap = document.querySelector(".styling-swiper .swiper-wrapper");
                    const domFrag = document.createDocumentFragment();
    
                    data.forEach(el => {
                        let prdListSlide = document.createElement("div");
                        prdListSlide.classList.add("swiper-slide");
                        let wishBtnHtml = () => {
                            let wishObj = {
                                location : 'foryou',
                                wishStatus: el.whish_flg,
                                productIdx: el.product_idx,
                                url: new URL(location.href)
                            }
                            
                            let whish_flg = `${el.whish_flg}`;
                            let whish_img = "";
                            
                            let txt_dataset = `data-location="foryou" data-wish_flg="${el.whish_flg}" data-product_idx="${el.product_idx}"`;
                            
                            let login_status = getLoginStatus();
                            
                            if (login_status == "true") {
                                if (whish_flg == 'true') {
                                    whish_img = `<img class="wish_img" data-status=${el.whish_flg} src="/images/svg/wishlist-bk.svg" alt="">`;
                                } else if (whish_flg == 'false') {
                                    whish_img = `<img class="wish_img" data-status=${el.whish_flg} src="/images/svg/wishlist.svg" alt="">`;
                                }
                            } else {
                                whish_img = `<img class="wish_img" data-status=${el.whish_flg} src="/images/svg/wishlist.svg" alt="">`;
                            }

                            return `
                            	<div class="wish__btn btn_update_wish" product_idx="${el.product_idx}" ${txt_dataset}>
                            		${whish_img}
                            	</div>
                            `;
                        }
                        
                        let product_size = el.product_size;
    
                        let saleprice = el.sales_price;
                        let colorCtn = el.product_color.length;
    
                        productRecommendListHtml =
                            `<div class="product">
                            ${wishBtnHtml()}
                                
                                <a href="/product/detail?product_idx=${el.product_idx}">
                                    <div class="product-img swiper">
                                        <img class="prd-img" cnt="${el.product_idx}" src="${cdn_img}${el.product_img}" alt="">
                                    </div>
                                </a>
                                <div class="product-info">
                                    <div class="info-row">
                                        <div class="name"data-soldout=${el.stock_status == "STCL" ? "STCL" : ""}><span>${el.product_name}</span></div>
                                        ${el.discount == 0 ? `<div class="price" data-soldout="${el.stock_status}" data-saleprice="${saleprice}" data-discount="${el.discount}" data-dis="false">${el.price}</div>`:`<div class="price" data-soldout="${el.stock_status}" data-saleprice="${saleprice}" data-discount="${el.discount}" data-dis="true"><span>${el.price}</span></div>`} 
                                    </div>
                                    <div class="info-row">
                                        <div class="color-title"><span>${el.color}</span></div>
                                        <div class="color__box" data-maxcount="${colorCtn < 6 ?"":"over"}" data-colorcount="${colorCtn < 6 ? colorCtn: colorCtn - 5}">
                                            ${
                                                el.product_color.map((color, idx) => {
                                                    let maxCnt = 5;
                                                    if(idx < maxCnt){
                                                        return `<div class="color" data-color="${color.color_rgb}" data-productidx="${color.product_idx}" data-soldout="${color.stock_status}" style="background-color:${color.color_rgb}"></div>`;
                                                    }
                                                }).join("")
                                            }
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="size__box">
                                            ${
                                            el.product_size.map((size) => {
                                                return`<li class="size" data-sizetype="" data-productidx="${size.product_idx}" data-optionidx="${size.option_idx}" data-soldout="${size.stock_status}">${size.option_name}</li>`;
                                                }).join("")
                                            }  
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        prdListSlide.innerHTML  = productRecommendListHtml;
                        domFrag.appendChild(prdListSlide);
                    });
                    
                    swiperWrap.appendChild(domFrag);
                    
                    clickBtnUpdateWish();
                } else{
                    document.querySelector('.styling-with-wrap').style.display = 'none';
                }
            }
        });
    })();
    this.swiper = (() => {
        return new Swiper(".styling-swiper", {
            navigation: {
                nextEl: ".styling-swiper .swiper-button-next",
                prevEl: ".styling-swiper .swiper-button-prev",
            },
            pagination: {
                el: ".styling-swiper .swiper-pagination",
                clickable: true,
                disabledClass:'swiper-button-disabled'
            },
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