/**
 * @author SIMJAE
 * @description 쇼핑백 생성자 함수
 * @param {String} el 클래스이름 
 * @param {boolean} useSidebar true: 사이드바 , false: 쇼핑백페이지 
 */
function Basket(el, useSidebar) {
    const prototypes = { el, useSidebar }
    prototypes.el = el;
    prototypes.useSidebar = useSidebar;

    let parm = prototypes;
    
	//슬라이드 사용시 
    if (parm.useSidebar === true) {
        this.writeHtml = () => {
            let sideBox = document.querySelector(`.side__box`);
            let sideWrap = document.querySelector(`#sidebar .side__wrap`);
            sideWrap.dataset.module = "basket";
			
			let delivery_price = 2500;
			let txt_delivery_price = 0;
			
			if (getLanguage() != "KR") {
				let delivery_data = getDeliveryPrice();
				delivery_price = delivery_data.price_delivery;
				txt_delivery_price = delivery_data.txt_price_delivery;
			}
			
            let contentHtml = `
				<section class="basket__wrap">
					<div class="list__box">
						<div class="list__header">
							<div class="icon__box">
                                <div>
                                    <img src="/images/svg/basket.svg" alt="">
                                    <div class="basket_title" data-i18n="s_shoppingbag">쇼핑백</div>
                                </div>
                            </div>
							<div class="checkbox__box checkbox_stin">
								<label class="cb__custom all" for="">
									<input class="prd_cb all__cb" type="checkbox" name="stock">
									<div class="cb__mark"></div>
								</label>
								<div class="flex gap-10">
									<u class="ufont st__checked__btn" btn="stock" data-i18n="s_remove_selected">선택 삭제</u>
									<u class="ufont st__all__btn" btn="stock" data-i18n="s_remove_all">모두 삭제</u>
								</div>
							</div>
						</div>
						<div class="list__body"></div>
					</div>
					<div class="pay__box">
                        <div class="pay_box_row_wrap">
                            <div class="pay__row" style="display:none;">
                                <div data-i18n="s_subtotal">제품합계</div>
                                <div class="product__total__price">0</div>
                            </div>
                            <div class="pay__row">
                                <div data-i18n="s_shipping_total">배송비</div>
                                <div class="deli__price" data-price_delivery="${delivery_price}">${txt_delivery_price}</div>
                            </div>
                            <div class="pay__row">
                                <div data-i18n="s_order_total">합계</div>
                                <div class="pay__total__price">0</div>
                            </div>
                        </div>
                        <div class="pay_box_btn_wrap">
                            <div class="pay__btn" id="pay_btn">
                                <span data-i18n="s_checkout">결제하기</span>
                            </div>
                            <div class="check_basket_btn" onClick="location.href='/order/basket/list'">
                                <img src="/images/svg/basket-bk_v1.0.svg" alt="">
                                <span data-i18n="s_goto_shoppingbag">쇼핑백 보러가기</span>
                            </div>
                        </div>
                        <p class="pay__notiy">&nbsp;</p> 
					</div>
				</section>
			`;

            sideBox.innerHTML = contentHtml;
            changeLanguageR();
        };
    }

    //쇼핑백 상품 리스트 조회
    getBasketProductList();

    //쇼핑백 상품 리스트 조회
    function getBasketProductList() {
        $.ajax({
            type: "post",
            url: api_location + "order/basket/list/get",
            dataType: "json",
            error: function () {
                makeMsgNoti(getLanguage(), "MSG_F_ERR_0028", null);
                // notiModal("쇼핑백 상품 리스트 조회처리중 오류가 발생했습니다.");
            },
            success: function (d) {
                let data = d.data;

                let sideWrap = $("#sidebar .side__wrap");
				
                let basket_st_info = data.basket_st_info;	//재고있음 쇼핑백 상품 리스트
				let basket_so_info = data.basket_so_info;	//재고없음 쇼핑백 상품 리스트
				
                if (sideWrap.hasClass('open')) {
                    if (basket_so_info.length > 0 || basket_st_info.length > 0) {
						/* 쇼핑백 상품 리스트 화면 표시 */
                        writeProductListDomTree(basket_st_info, basket_so_info);
                    } else {
						/* 쇼핑백 상품이 존재하지 않을 경우 예외처리 */
                        productNull();
                    }
                }
            }
        });
		
		/* 쇼핑백 상품이 존재하지 않을 경우 예외처리 */
        function productNull() {
			let list__body = document.querySelector(".basket__wrap .list__body");
            
            let empty_div = document.createElement("div");
            empty_div.className = "empty-data";
            empty_div.innerHTML = `
                <h1 data-i18n="s_basket_empty">쇼핑백이 비어있습니다.</h1>
                <div class="continue-shopping-btn" data-i18n="s_continue_shopping">쇼핑 계속하기</div>
            `;
			
            list__body.appendChild(empty_div);
			
			$('.product__wrap').remove();
            $('.checkbox__box').hide();
            $('.pay__box').hide();
            
			/* 쇼핑 계속하기 버튼 */
            let coutinue_shopping_btn_list = document.querySelectorAll('.continue-shopping-btn');
            coutinue_shopping_btn_list.forEach(function(btn){
                btn.addEventListener('click', function () {
                    continueShopping();
                });
            })
            changeLanguageR();
        }
    }
	
    function createColorLine(product_idx,data,status) {
        let colorLineHtml = "";
        let colorMulti = data.color_rgb.split(";");

        if (status == "product") {
            if (colorMulti.length > 1) {
                colorLineHtml = `
					<div class="color-line" data-product_idx="${data.product_idx}" style="--background:linear-gradient(90deg, ${colorMulti[0]} 50%, ${colorMulti[1]} 50%);">
						<div class="color multi" data-soldout="${data.stock_status}" data-title="${data.color}"></div>
					</div>
				`;
            } else {
                colorLineHtml = `
					<div class="color-line" data-product_idx="${data.product_idx}" data-title="${data.color}" style="--background-color:${colorMulti[0]}" >
						<div class="color" data-soldout="${data.stock_status}" data-title="${data.color}"></div>
					</div>
				`;
            }
        } else if (status == "option") {
            if (colorMulti.length > 1) {
                colorLineHtml = `
					<div class="color-line" data-product_idx="${data.product_idx}" style="--background:linear-gradient(90deg, ${colorMulti[0]} 50%, ${colorMulti[1]} 50%);">
						<div class="color multi"data-title="${data.color}"data-soldout="${data.stock_status}"></div>
					</div>
				`;
            } else {
                colorLineHtml = `
					<div class="color-line" data-product_idx="${data.product_idx}" style="--background-color:${colorMulti[0]}" >
						<div class="color"data-title="${data.color}" data-soldout="${data.stock_status}"></div>
					</div>
				`;
            }
        }
        return colorLineHtml;
    }
	
	/* 쇼핑백 상품 리스트 화면 표시 */
    function writeProductListDomTree(st_info, so_info) {
		/* 재고있음 쇼핑백 상품 리스트 초기화 */
        $('.product__wrap').remove();
		/* 재고없음 쇼핑백 상품 리스트 초기화 */
        $('.sold__list__box').remove();
		
		let docFrag = document.createDocumentFragment();
		let write_STIN_HTML = "";
        let write_STSO_HTML = "";
		
        let stin_product_wrap = document.createElement("div");
        stin_product_wrap.classList.add("product__wrap");
		
		let bodyWidth = document.getElementsByTagName("body")[0].offsetWidth;
		
        docFrag.appendChild(stin_product_wrap);
        
		/* 재고있음 상태의 쇼핑백 상품 리스트 */
        if (st_info.length > 0) {
            st_info.forEach((el,idx) => {
				let last_basket_product = "";
				if (st_info.length == parseInt(idx) + 1) {
					last_basket_product = "last_basket_product";
				}
				
                let product_type = el.product_type;
                let product_color_HTML = "";
                let color_rgb = el.color_rgb;

                let multi = color_rgb.split(";");
                if (multi.length === 2) {
                    product_color_HTML += `
						<div class="color__box">
							<div class="color-title">${el.color}</div>
							<div class="color-line" data-basket_idx="${el.basket_idx}" style="--background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
							<div class="color multi" data-title="${el.color}"></div>
							</div>
						</div>
					`;
                } else {
                    product_color_HTML += `
						<div class="color__box">
							<div class="color-title">${el.color}</div>
							<div class="color-line" data-basket_idx="${el.basket_idx}" data-title="${el.color}" style="--background-color:${multi[0]}" >
								<div class="color" data-title="${el.color}"></div>
							</div>
						</div>
					`;
                }
				
                let product_name_HTML = "";
                let qty_disable = "";
                let refund_flg = el.refund_flg;

                if (product_type != "S") {
					/* 세트상품 상품 이름 표시 */
                    if (refund_flg == false) {
                        product_name_HTML += `
							<div class="prd__title">
								${el.product_name}
							</div>
						`;
                    } else {
                    	product_name_HTML += `
                            <div class="prd__title">
                                <span>${el.product_name}</span>
                                <p class="refund_msg web_refund_msg" data-i18n="s_no_ex_re"></p>
                            </div>
                        `;
                    }
                } else {
					/* 일반상품 상품 이름 표시 */
                    qty_disable = "disableBtn";
                    if (refund_flg == false) {
                        product_name_HTML += `
							<div class="prd__title">
								<div class="product_name">
									${el.product_name}
								</div>
								<img class="set_toggle" src="/images/mypage/mypage_down_tab_btn.svg" data-basket_idx="${el.basket_idx}" data-action_type="show">
							</div>
						`;
                    } else {
                    	product_name_HTML += `
							<div class="prd__title">
								<div class="product_name">
									<span>${el.product_name}</span>
									<p class="refund_msg web_refund_msg" data-i18n="s_no_ex_re"></p>
								</div>
								<img class="set_toggle" src="/images/mypage/mypage_down_tab_btn.svg" data-basket_idx="${el.basket_idx}" data-action_type="show">
							</div>
						`;
                    }
                }
				
				let refund_msg_HTML = "";
				if (refund_flg == true) {
					product_refund_HTML = `<p class="refund_msg mobile_refund_msg" data-i18n="s_no_ex_re"></p>`;
				}
				
                write_STIN_HTML += `
					<div class="product__box ${last_basket_product}" data-stock_status="${el.stock_status}"  data-basket_idx="${el.basket_idx}" data-basket_qty="${el.basket_qty}" data-product_idx="${el.product_idx}" data-option_idx="${el.option_idx}" data-product_qty="${el.product_qty}">
						<label class="cb__custom self" for="">
							<input class="prd__cb self__cb" type="checkbox" name="stock">
							<div class="cb__mark"></div>
						</label>
						<a href="/product/detail?product_idx=${el.product_idx}">
							<div class="prd__img" style="background-image:url('${cdn_img}${el.product_img}');"></div>
						</a>
						<div class="prd__content" data-sales_price="${el.sales_price}" >
                            ${product_name_HTML}
							<div class="price">${el.sales_price_txt}</div>
							${product_color_HTML}
							<div class="prd__size">
								<div class="size__box">
									<li data-soldout="${el.stock_status}">${el.option_name}</li>
                                </div>
                                ${refund_msg_HTML}
							</div>
							<div class="prd__qty">
                                <div>Qty</div>
                                <div class="minus__btn ${qty_disable}">
                                    <img src="/images/svg/minus-basket.svg">
                                </div>
								
                                <input class="count__val" type="text" value="${el.basket_qty}" readonly>
								
								<div class="plus__btn ${qty_disable}">
									<img src="/images/svg/plus-basket.svg">
								</div>
								
								<div class="price_total" data-price_total="${el.sales_price * el.basket_qty}" data-stock_status="${el.stock_status}">
									${el.sales_price_txt}
								</div>
							</div>
						</div>
					</div>
				`;

                if (product_type == "S") {
                    let set_product_info = el.set_product_info;
                    if (set_product_info != null && set_product_info.length > 0) {
                        let set_product_html = writeSetProductInfo(set_product_info);
                        write_STIN_HTML += set_product_html;
                    }
                }
            });

            docFrag.querySelector('.product__wrap').innerHTML = write_STIN_HTML;
            document.querySelector('.list__box .list__body').appendChild(docFrag);
            let refund_web_msg = document.querySelectorAll('.product__wrap .product__box .web_refund_msg');
            let refund_mobile_msg = document.querySelectorAll('.product__wrap .product__box .moblie_refund_msg');

            if (window.innerWidth > 1025) {
                refund_mobile_msg.forEach(msg => {
                    msg.classList.add("hidden");
                });
            } else {
                refund_web_msg.forEach(msg => {
                    msg.classList.add("hidden");
                });
            }
            // 첫 화면은 모든 체크박스 체크
            let selfCheck = document.querySelectorAll('.prd__cb');
            let allCheck = document.querySelector('.all__cb');
            selfCheck.forEach(el => el.setAttribute("checked", true));
            allCheck.setAttribute("checked", true);
            let price_product = calcCheckedPrice();
            payBoxSumPrice(price_product);
        }
		
		let stso_product_wrap = document.createElement("div");
        stso_product_wrap.classList.add("sold__list__box");
		
		/* 재고없음 상태의 쇼핑백 상품 리스트 */
        if (so_info.length > 0) {
			let docFrag = document.createDocumentFragment();
            docFrag.appendChild(stso_product_wrap);
			
            write_STSO_HTML += `
				<div class="list__header">
					<div class="icon__box">
						<img src="/images/svg/basket.svg" alt="">
						<div>품절제품</div>
					</div>
					<div class="checkbox__box checkbox_stso">
						<label class="cb__custom all" for="">
							<input class="prd_cb all__cb" type="checkbox" name="sold">
							<div class="cb__mark"></div>
						</label>
						<div class="flex gap-10">
							<u class="ufont so__checked__btn" btn="stock" data-i18n="s_remove_selected">선택 삭제</u>
							<u class="ufont so__all__btn" btn="stock" data-i18n="s_remove_all">모두 삭제</u>
						</div>			
					</div>
				</div>
				<div class="list__body">
				</div>
			`;

            stso_product_wrap.innerHTML = write_STSO_HTML;
			
			let write_STSO_product_HTML = "";
            so_info.forEach(el => {
				console.log(el);
                let product_type = el.product_type;
                let set_type = el.set_type;
                
				let product_color_HTML = "";
                
				let color_rgb = el.color_rgb;
                let multi = color_rgb.split(";");
				
				/* 상품 컬러칩 HTML */
                if (multi.length > 1) {
                    product_color_HTML += `
						<div class="color__box">
							<div class="color-title">${el.color}</div>
							<div class="color-line" data-basket_idx="${el.basket_idx}" style="--background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
							<div class="color multi" data-soldout="${el.stock_status}" data-title="${el.color}"></div>
							</div>
						</div>
					`;
                } else {
                    product_color_HTML += `
						<div class="color__box">
							<div class="color-title">${el.color}</div>
							<div class="color-line" data-basket_idx="${el.basket_idx}" data-title="${el.color}" style="--background-color:${multi[0]}" >
								<div class="color" data-soldout="${el.stock_status}" data-title="${el.color}"></div>
							</div>
						</div>
					`;
                }
				
				/* 상품 이름 HTML */
                let product_name_HTML = "";
                let refund_flg = el.refund_flg;
				
                if (product_type != "S") {
                    if (refund_flg == false) {
                        product_name_HTML += `
							<div class="prd__title">
								${el.product_name}
							</div>
						`;
                    } else {
                    	product_name_HTML += `
							<div class="prd__title">
								<span>${el.product_name}</span>
								<p class="refund_msg web_refund_msg" data-i18n="s_no_ex_re"></p>
							</div>
						`;
                    }
                } else {
                    if (refund_flg == false) {
                        product_name_HTML += `
							<div class="prd__title">
								<div class="product_name">
									${el.product_name}
								</div>
								<img class="set_toggle" src="/images/mypage/mypage_down_tab_btn.svg" data-basket_idx="${el.basket_idx}" data-action_type="show">
							</div>
						`;
                    } else {
                    	product_name_HTML += `
							<div class="prd__title">
								<div class="product_name">
									${el.product_name}
								</div>
								<img class="set_toggle" src="/images/mypage/mypage_down_tab_btn.svg" data-basket_idx="${el.basket_idx}" data-action_type="show">
							</div>
						`;
                    }
                }
				
				/* 품절 상품 옵션 변경 - 상품 컬러 */
                let change_product_color_HTML = "";
				
				/* 품절 상품 옵션 변경 - 상품 사이즈 */
                let change_product_size_HTML = "";

                let product_size = el.product_size;
                let product_color = el.product_color;

                if (product_type == "B") {
					/* 일반 상품 옵션 변경 컬러 HTML */
                    product_color.forEach(color => {
                        change_product_color_HTML += createColorLine(el.product_idx,color, "product");
                    });
					
					/* 일반 상품 옵션 변경 사이즈 HTML */
                    product_size.forEach((size) => {
						let disable_btn = "";
						if (size.stock_status != "STIN") {
							disable_btn = "disableBtn";
						}
						
                        change_product_size_HTML += `
							<li class="option__size ${disable_btn}" data-product_idx="${size.product_idx}" data-option_idx="${size.option_idx}" data-stock_status="${size.stock_status}">${size.option_name}</li>
						`;
                    });
                } else if (product_type == "S") {
                    if (set_type == "CL") {
						/* 컬러 세트 상품 옵션 변경 컬러 HTML */
                        product_size.forEach(option => {
                            let setOptionInfo = option.set_option_info;

                            change_product_color_HTML += `
								<div class="product_option_wrap">
									<span>${option.product_name}</span>
									<div class="color__box">
										${setOptionInfo.map(row => {
                                return createColorLine(el.product_idx,row, "option");
                            }).join("")}
									</div>
								</div>
							`;
                        });
						
                        change_product_size_HTML = "";
                    }
					
                    if (set_type == "SZ") {
						let disable_btn = "";
						if (size.stock_status != "STIN") {
							disable_btn = "disableBtn";
						}
						
						change_product_color_HTML = "";
						
                        product_size.forEach((size) => {
                            change_product_size_HTML += `
								<li class="option__size ${disable_btn}" data-product_idx="${size.product_idx}" data-option_idx="${size.option_idx}" data-stock_status="${size.stock_status}">${size.option_name}</li>
							`;
                        });
                    }
                }
				
				/* 리오더 버튼 클래스 지정 */
				let reorder_class = "";
				let reorder_text = "";
				if (el.reorder_flg == true) {
					reorder_class = "disableBtn";
					reorder_text = "재입고 알림 신청완료";
				} else {
					reorder_class = "";
					reorder_text = "재입고 알림 신청하기";
				}
				
				/* 품절 상품 가격 HTML */
				let soldout_price_HTML = "";
				if (el.discount > 0) {
					soldout_price_HTML = `
						<div class="price" data-soldout="${el.stock_status}" data-sales_price="10000" data-discount="${el.discount}" data-dis="true">
							<span>${el.price_txt}</span>
						</div>
					`;
				} else {
					soldout_price_HTML = `
						<div class="price" data-soldout="${el.stock_status}" data-sales_price="${el.sales_price}" data-discount="${el.discount}" data-dis="false">${el.price_txt}</div>
					`;
				}
				
				/* 옵션 변경 버튼 HTML */
				let change_option_HTML = "";
				if (product_type != "S") {
					change_option_HTML = `
						<div class="option__change__btn open">
							<img src="/images/svg/edit.svg" alt="">
							<u data-i18n="s_change_options">옵션 변경하기</u>
						</div>
					`;
				}

                write_STSO_product_HTML += `
					<div class="product__box" data-basket_idx="${el.basket_idx}" data-stock_status="${el.stock_status}" data-product_idx="${el.product_idx}" data-option_idx="${el.option_idx}" data-reorder_flg="${el.reorder_flg}">
						<label class="cb__custom self" for="">
							<input class="prd__cb self__cb" type="checkbox" name="sold">
							<div class="cb__mark"></div>
						</label>
						<a href="/product/detail?product_idx=${el.product_idx}">
							<div class="prd__img" style="background-image:url('${cdn_img}${el.product_img}');"></div>
						</a>
						<div class="prd__content">
                            ${product_name_HTML}
							${soldout_price_HTML} 
							${product_color_HTML}
							<div class="prd__size">
								<div class="size__box">
									<li data-soldout="${el.stock_status}">${el.option_name}</li>
								</div>
							</div>
							
                            <div class="option__box">
								${change_option_HTML}
								<div class="reorder__btn ${reorder_class}">
									<img src="/images/svg/reflesh.svg" alt="">
                                    <u>${reorder_text}</u>
								</div>
							</div>
							
							<div class="option__select__box hide">
								<div class="option__select__head">
									<div class="close__btn option">
										<span class="line"></span>
										<span class="line"></span>
									</div>
								</div>
								<div class="color__box">
                                    ${change_product_color_HTML}
                                </div>
								<div class="size__box">
                                    ${change_product_size_HTML}
								</div>
								<div class="option__change__btn apply">
									<img src="/images/svg/edit.svg" alt="">
									<u data-i18n="s_change_options">옵션 변경하기</u>
								</div>
							</div>
						</div>
					</div>
				`;

                if (product_type == "S") {
                    let set_product_info = el.set_product_info;
                    if (set_product_info != null && set_product_info.length > 0) {
                        let set_product_html = writeSetProductInfo(set_product_info);
                        write_STSO_product_HTML += set_product_html;
                    }
                }

                docFrag.querySelector(".list__body").innerHTML = write_STSO_product_HTML;

                let reorderBtn = document.querySelectorAll(".reorder__btn u");

                reorderBtn.forEach(btn => {
                    if (el.reorder_flg == true) {
                        btn.dataset.i18n = "w_basket_msg_04";
                        btn.textContent = i18next.t("w_basket_msg_04");
                    } else {
                        btn.dataset.i18n = "s_subscribe_for_restock_notification";
                        btn.textContent = i18next.t("s_subscribe_for_restock_notification");
                    }
                })
                
				let optChangeBtn = document.querySelectorAll(".option__change__btn u");
                optChangeBtn.forEach(btn => {
                    btn.dataset.i18n = "s_change_options";
                    btn.textContent = i18next.t("s_change_options");
                });
            });

            document.querySelector('.list__box .list__body').appendChild(docFrag);
            soldCheckedDeleteBtn();
            soldAllDeleteBtn();
            changeLanguageR();
        }
		
		/* 쇼핑백 부분삭제 */
		deleteBasketInfo();
		/* 쇼핑백 전체삭제 */
        deleteAllBasketInfo();
		
		/* 세트상품 토글버튼 클릭처리 */
        clickSetToggle();
		
		/* 재고있음 쇼핑백 상품 리스트 체크박스 클릭 */
        clickCheckboxSTIN();
		/* 재고없음 쇼핑백 상품 리스트 체크박스 클릭 */
        clickCheckboxSTSO();
		
		/* 쇼핑백 상품 수량 변경 버튼 클릭 */
        clickCntBtn();
		
		/* 변경 가능 한 쇼핑백 옵션 상품 조회처리 */
		setBasketOption();
		
        optionBoxCloseBtn();
        clickPutBasketOption();
		
        payBtnEvent();
        clickReorderBtn();
		
        changeLanguageR();
    }

    function writeSetProductInfo(data) {
        let set_product_html = "";

        data.forEach(set => {
            let color_rgb = set.color_rgb;
            let multi = color_rgb.split(";");

            let set_color_html = "";
            if (multi.length === 2) {
                set_color_html += `
					<div class="color__box">
						<div class="color-title">${set.color}</div>
						<div class="color-line" style="--background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
							<div class="color multi"></div>
						</div>
					</div>
				`;
            } else {
                set_color_html += `
					<div class="color__box">
						<div class="color-title">${set.color}</div>
						<div class="color-line" style="--background-color:${multi[0]}" >
							<div class="color"></div>
						</div>
					</div>
				`;
            }

            set_product_html += `
				<div class="product__box set_product hidden" data-parent_idx="${set.parent_idx}">
					<div class="prd__img" style="background-image:url('${cdn_img}${set.product_img}') ;"></div>
					<div class="prd__content">
						<div class="prd__title">${set.product_name}</div>
						${set_color_html}
						<div class="prd__size">
							<div class="size__box">
								<li data-soldout="${set.stock_status}">${set.option_name}</li>
							</div>
						</div>
						<div class="prd__qty">
							
						</div>
					</div>
				</div>
			`;
        });

        return set_product_html;
    }

    function clickSetToggle() {
        let set_toggle = document.querySelectorAll('.set_toggle');
        set_toggle.forEach(toggle => {
            toggle.addEventListener('click', function (e) {
                let toggle_btn = e.currentTarget;

                let basket_idx = toggle_btn.dataset.basket_idx;
                let action_type = toggle_btn.dataset.action_type;

                let set_product = document.querySelectorAll('.set_product');
                set_product.forEach(set => {
                    if (set.dataset.parent_idx == basket_idx) {
                        set.classList.toggle('hidden');
                    }
                });

                if (action_type == "show") {
                    toggle_btn.dataset.action_type = "hide";
                    toggle_btn.src = "/images/mypage/mypage_up_tab_btn.svg";
                } else if (action_type == "hide") {
                    toggle_btn.dataset.action_type = "show";
                    toggle_btn.src = "/images/mypage/mypage_down_tab_btn.svg";
                }
            });
        });
    }

    let refund_msg_delay = 500;
    let refund_msg_timer = null;
    window.addEventListener("resize", function () {
        let refund_msg_w = document.querySelectorAll('.product__wrap .product__box .web_refund_msg');
        let refund_msg_m = document.querySelectorAll('.product__wrap .product__box .moblie_refund_msg');
        clearTimeout(refund_msg_timer);
        refund_msg_timer = setTimeout(function () {
            if (window.innerWidth <= 1025) {
                refund_msg_w.forEach(msg => {
                    msg.classList.add("hidden");
                });
                refund_msg_m.forEach(msg => {
                    msg.classList.remove("hidden");
                });
            }
            if (window.innerWidth > 1025) {
                refund_msg_m.forEach(msg => {
                    msg.classList.add("hidden");
                });
                refund_msg_w.forEach(msg => {
                    msg.classList.remove("hidden");
                });
            }
        }, refund_msg_delay);
    });

    const selfCheckbox = (status, checked) => {
        let $$checkedSelfBox = document.querySelectorAll(`.self__cb[name='${status}']${checked ? ":checked" : ""}`);

        let basket_idx = [];
        $$checkedSelfBox.forEach(el => {
            let tmp_idx = el.parentNode.parentNode.dataset.basket_idx;
            basket_idx.push(tmp_idx);

            el.parentNode.parentNode.remove();
        });

        deleteBasketProduct(basket_idx);

        let price_product = calcCheckedPrice();
        payBoxSumPrice(price_product);
    }

    //재고상품 선택삭제 버튼
    function deleteBasketInfo() {
        const $checkedDelete = document.querySelector(".st__checked__btn");
        $checkedDelete.addEventListener("click", () => {
            let selfCheckbox_cnt = document.querySelectorAll(".self__cb[name='stock']:checked").length;
            let msgBox = document.querySelector(".pay__notiy");
            if (selfCheckbox_cnt == 0) {
                msgBox.innerText = '삭제하실 상품을 선택해주세요.';
            } else {
                msgBox.innerText = ' ';
                selfCheckbox("stock", true);
                let product__box = document.querySelectorAll(".basket__wrap .product__box");
                if (product__box.length == 0) {
                    getBasketProductList();
                }
            }
        });
    };

    //재고상품 전체삭제 버튼
    function deleteAllBasketInfo() {
        const $checkedDelete = document.querySelector(".st__all__btn");
        $checkedDelete.addEventListener("click", () => {
            selfCheckbox("stock", false);
            let product__box = document.querySelectorAll(".basket__wrap .product__box");
			getBasketProductList()
			
			let checkbox_box = document.querySelectorAll('.checkbox__box');
			checkbox_box.forEach(box => {
				if (!box.classList.contains('checkbox_stso')) {
					$(box).remove();
				}
			});
			/*
            if (product__box.length == 0) {
                getBasketProductList()
            }
			*/
        });
    };

    //품절상품 선택삭제 버튼
    function soldCheckedDeleteBtn() {
        const $checkedDelete = document.querySelector(".so__checked__btn");
        $checkedDelete.addEventListener("click", (e) => {
            selfCheckbox("sold", true);
            let product__box = document.querySelectorAll(".basket__wrap .product__box");
            if (product__box.length == 0) {
                getBasketProductList()
            }
        });
    };

    //품절상품 전체삭제 버튼
    function soldAllDeleteBtn() {
        const $checkedDelete = document.querySelector(".so__all__btn");
        $checkedDelete.addEventListener("click", () => {
            selfCheckbox("sold", false);
			getBasketProductList()
			
			let checkbox_box = document.querySelectorAll('.checkbox__box');
			checkbox_box.forEach(box => {
				if (box.classList.contains('checkbox_stso')) {
					$(box).remove();
				}
			});
			//productNull();
            /*
            let product__box = document.querySelectorAll(".basket__wrap .product__box");
            if (product__box.length == 0) {
                getBasketProductList()
            }
            */
        });
    };

    //삭제 api
    const deleteBasketProduct = (basketIdx) => {
        $.ajax({
            type: "post",
			url: api_location + "order/basket/delete",
            data: {
                "basket_idx": basketIdx
            },
            dataType: "json",
            async:false,
            error: function () {
                makeMsgNoti(getLanguage(), "MSG_F_ERR_0027", null);
                // notiModal("쇼핑백 상품 정보 삭제 처리에 실패했습니다.");
            },
            success: function (d) {
                if (d.code == 200) {
                    let basket_cnt = d.data.basket_cnt;

                    let header_basket = document.querySelector('.flex.basket__btn.side-bar');
                    header_basket.dataset.cnt = basket_cnt;
                } else {
                    notiModal(d.msg);
                }
            }
        });
    }

    //쇼핑백 상품 수량 변경
    const putBasketQty = (product_type, action_type, basket_idx, product_idx, basket_qty) => {
        let result_qty = 0;

        $.ajax({
            type: "post",
            url: api_location + "order/basket/put",
            data: {
                "product_type": product_type,
                "basket_idx": basket_idx,
                "stock_status": "STIN",
                "basket_qty": basket_qty,
                "product_idx": product_idx
            },
            dataType: "json",
            async: false,
            error: function () {
                makeMsgNoti(getLanguage(), 'MSG_F_ERR_0026', null);
                // notiModal("쇼핑백 상품 정보 수정 처리에 실패했습니다.");
            },
            success: function (d) {
                if (d.code != 200) {
                    if (action_type == "plus") {
                        if (basket_qty > 1) {
                            result_qty = (basket_qty - 1);
                        } else {
                            result_qty = 1;
                        }
                    } else if (action_type == "minus") {
                        result_qty = (basket_qty + 1);
                    }

                    notiModal(d.msg);
                } else {
                    result_qty = basket_qty;
                }
            }
        });

        return result_qty;
    }

    //쇼핑백 리스트 그려주는 함수
    function payBtnEvent() {
        let payBtn = document.querySelector(".pay__box .pay__btn");
        payBtn.addEventListener("click", function () {
            let selfBox = document.querySelectorAll(".self__cb[name='stock']");
            let soldSelfBox = document.querySelectorAll(".self__cb[name='sold']:checked");
            let msgBox = document.querySelector(".pay__notiy");
            let selectArr = [];
            let checkCnt = 0;

            selfBox.forEach(el => {
                if (el.checked) {
                    checkCnt++;
                    selectArr.push(el.parentNode.parentNode.dataset.basket_idx);
                }
            })

            if (soldSelfBox.length > 0) {
                msgBox.innerText = '품절제품을 삭제 후 결제를 진행해주세요.';
                msgBox.dataset.i18n = 's_basket_msg_01';
                msgBox.textContent = i18next.t('s_basket_msg_01');
                return false;
            }

            if (checkCnt == 0) {
                msgBox.innerText = '결제하려는 상품을 선택해주세요.';
                return false;
            }

            if (selectArr.length > 0) {
                $.ajax({
                    type: "post",
                    url: api_location + "order/basket/check",
                    data: {
                        'basket_idx': selectArr
                    },
                    dataType: "json",
                    async: false,
                    error: function () {
                        // notiModal("쇼핑백 상품 체크처리중 오류가 발생했습니다.");
                        makeMsgNoti(getLanguage(), 'MSG_F_ERR_0025', null);
                    },
                    success: function (d) {
                        if (d.code == 200) {
                            msgBox.innerText = '';
                            location.href = "/order/confirm?&basket_idx=" + selectArr;
                        } else {
                            notiModal(d.msg);
                        }
                    }
                });
            }
        });
    }

    //쇼핑백 상품 수량 수량 변경
    function clickCntBtn() {
        let $$minus_btn = document.querySelectorAll(".minus__btn");
        let $$plus_btn = document.querySelectorAll(".plus__btn");

        let $$basket_cnt = document.querySelectorAll(".count__val");

        let setTotalPrice = 0;

        //업 & 다운버튼 CSS 초기화 
        $$basket_cnt.forEach(el => {
            //el.value = 1;
            let sales_price = el.offsetParent.querySelector(".prd__content").dataset.sales_price;
            el.parentNode.dataset.init = sales_price;

            let basket_qty = el.offsetParent.dataset.basket_qty;
            let price_product = sales_price * basket_qty;

            el.parentNode.querySelector(".price_total").textContent = price_product.toLocaleString('ko-KR');

            let tmp_cnt = parseInt(el.value);
            if (tmp_cnt == 1) {
                el.parentNode.querySelector(".minus__btn").classList.add('disableBtn');
            }

            if (tmp_cnt == 9) {
                el.parentNode.querySelector(".plus__btn").classList.add('disableBtn');
            }
        });

        //수량 다운버튼 클릭이벤트
        $$minus_btn.forEach(el => {
            el.addEventListener("click", function () {
                let basket_product = this.offsetParent;

                let $plus_btn = basket_product.querySelector(".plus__btn");
                let product_type = this.dataset.product_type;

                let basket_idx = basket_product.dataset.basket_idx;
                let product_idx = basket_product.dataset.product_idx;

                let count_val = basket_product.querySelector('.count__val');
                let basket_qty = parseInt(count_val.value) - 1;
                let sales_price = basket_product.querySelector('.prd__content').dataset.sales_price;

                let result_qty = putBasketQty(product_type, "plus", basket_idx, product_idx, basket_qty);
                if (result_qty == "1") {
                    this.classList.add('disableBtn');
                    $plus_btn.classList.remove('disableBtn');
                } else if (result_qty == "9") {
                    this.classList.remove('disableBtn');
                    $plus_btn.classList.add('disableBtn');
                } else {
                    this.classList.remove('disableBtn');
                    $plus_btn.classList.remove('disableBtn');
                }

                count_val.value = result_qty;

                let product_price = (result_qty * sales_price);

                let price_total = basket_product.querySelector('.price_total');
                price_total.dataset.price_total = product_price;
                price_total.innerText = product_price.toLocaleString('ko-KR');

                let price_product = calcCheckedPrice();
                payBoxSumPrice(price_product);
            });
        });

        //수량 업버튼 클릭 이벤트
        $$plus_btn.forEach(el => {
            el.addEventListener("click", function () {
                let basket_product = this.offsetParent;

                let $minus_btn = basket_product.querySelector(".minus__btn");
                let product_type = this.dataset.product_type;

                let basket_idx = basket_product.dataset.basket_idx;
                let product_idx = basket_product.dataset.product_idx;

                let count_val = basket_product.querySelector('.count__val');
                let basket_qty = parseInt(count_val.value) + 1;
                let sales_price = basket_product.querySelector('.prd__content').dataset.sales_price;

                let result_qty = putBasketQty(product_type, "plus", basket_idx, product_idx, basket_qty);
                if (result_qty == "1") {
                    $minus_btn.classList.add('disableBtn');
                    this.classList.remove('disableBtn');
                } else if (result_qty == "9") {
                    $minus_btn.classList.remove('disableBtn');
                    this.classList.add('disableBtn');
                } else {
                    $minus_btn.classList.remove('disableBtn');
                    this.classList.remove('disableBtn');
                }

                count_val.value = result_qty;

                let product_price = (result_qty * sales_price);

                let price_total = basket_product.querySelector('.price_total');
                price_total.dataset.price_total = product_price;
                price_total.innerText = product_price.toLocaleString('ko-KR');

                let price_product = calcCheckedPrice();
                payBoxSumPrice(price_product);
            });
        });
    };

    //재고있음(STIN) 체크박스 클릭 이벤트
    function clickCheckboxSTIN() {
        const $all_stin_checkbox = document.querySelector(".side__box .all input[name='stock']"); //
        const $stin_checkbox = document.querySelectorAll(".side__box .product__wrap .self__cb");
        const $$productBox = document.querySelectorAll(".product__box");

        let checkbox_name = $all_stin_checkbox.getAttribute("name");
        let price_product = 0;

        //전체선택 체크박스 클릭 이벤트
        $all_stin_checkbox.addEventListener("click", function () {
            let stock_list = document.querySelectorAll("input[name='stock']");
            stock_list.forEach(el => {
                el.checked = this.checked;
            });

            let price_product = calcCheckedPrice();
            payBoxSumPrice(price_product);
        });

        //개별 체크박스 클릭 이벤트
        $stin_checkbox.forEach(el => {
            el.addEventListener("click", (e) => {
                let input_name = e.currentTarget.getAttribute("name");
                if (input_name == "stock") {

                    let product_box = e.currentTarget.parentNode.parentNode;
                    let price_total = parseInt(product_box.querySelector(".price_total").dataset.price_total);

                    if (e.target.checked) {
                        //체크시
                        if (checkbox_name == "stock") {
                            let checked_stin = document.querySelectorAll("input[name='stock']:checked");
                            if ($stin_checkbox.length == checked_stin.length) {
                                $all_stin_checkbox.checked = true;
                            }
                            price_total += price_total;
                        }
                    } else {
                        //체크 해제됬을떄
                        $all_stin_checkbox.checked = false;
                        price_total -= price_total;
                    }

                    let price_product = calcCheckedPrice();
                    payBoxSumPrice(price_product);
                }
            });
        });
    }

    //재고없음(STSO) 전체선택 체크박스 클릭 이벤트
    function clickCheckboxSTSO() {
        let $all_stso_checkbox = document.querySelector(".sold__list__box .all__cb[name='sold']");
        let $stso_checkbox = document.querySelectorAll(".sold__list__box .self__cb[name='sold']");
        if ($all_stso_checkbox != null) {
            $all_stso_checkbox.addEventListener("click", function () {
                let soldout_list = document.querySelectorAll(".sold__list__box .self__cb[name='sold']");
                soldout_list.forEach(el => {
                    el.checked = this.checked;
                });
            });
        }
        $stso_checkbox.forEach(el => {
            el.addEventListener("click", function () {
                let checkedStso = document.querySelectorAll(".sold__list__box .self__cb[name='sold']:checked");
                if ($stso_checkbox.length == checkedStso.length) {
                    $all_stso_checkbox.checked = true;
                } else {
                    $all_stso_checkbox.checked = false;
                }
            })
        })
    }

    /************************* 공통함수 **************************/
    //선택한 상품만 가격 합산
    function calcCheckedPrice() {
        let price_product = 0;

        let $$basket_checkbox = document.querySelectorAll(".self__cb[name='stock']:checked");
        $$basket_checkbox.forEach(el => {
            let tmp_price = parseInt(el.parentNode.parentNode.querySelector(".price_total").dataset.price_total);
            price_product += tmp_price;
        });

        return price_product;
    }

    //선택한 상품 결제박스 합계 표기
    function payBoxSumPrice(price_product) {
        let $txt_price_product = document.querySelector(".product__total__price");
        let $txt_price_total = document.querySelector(".pay__total__price");
        let $txt_price_delivery = document.querySelector(".deli__price");

        let free_delivery = 80000;
        let price_delivery = parseInt($txt_price_delivery.dataset.price_delivery);
        let price_total = (price_product + price_delivery);

        if (price_total == price_delivery) {
            price_total = 0;
        }

        if (free_delivery <= price_product) {
            price_total -= price_delivery;
            price_delivery = 0;
        }

        if (price_total == 0) {
            price_delivery = 0;
        }

        $txt_price_product.textContent = price_product.toLocaleString('ko-KR');;
        $txt_price_total.textContent = price_total.toLocaleString('ko-KR');

        $txt_price_total.textContent = price_total.toLocaleString('ko-KR');
        $txt_price_delivery.textContent = price_delivery.toLocaleString('ko-KR');
    }

    function optionBoxCloseBtn() {
        const $$closeBtn = document.querySelectorAll(".close__btn.option");
        $$closeBtn.forEach(el => {
            el.addEventListener("click", function () {
                this.offsetParent.querySelectorAll(".color-line").forEach(el => el.classList.remove("select"));
                this.offsetParent.querySelectorAll(".option__size").forEach(el => el.classList.remove("select"));
                this.offsetParent.classList.add("hide");
            });
        });
    }
	
	/* 품절 상품 옵션 변경 */
    function clickPutBasketOption() {
		/*
        const $$option_change_btn = document.querySelectorAll(".option__change__btn");
        $$option_change_btn.forEach(el => {
            el.addEventListener("click", function (ev) {
                setBasketOption();

                if (this.classList.contains("apply")) {
                    let basket_idx = this.parentNode.parentNode.parentNode.dataset.basket_idx;

                    let colorValue = [...this.parentNode.querySelectorAll(".color-line")].find(el => el.classList.contains("select"));
                    let product_idx = colorValue?.dataset.product_idx;

                    let sizeValue = [...this.parentNode.querySelectorAll(".option__size")].find(el => el.classList.contains("select"));
                    let option_idx = sizeValue?.dataset.option_idx;

                    if (product_idx === undefined || option_idx === undefined) {
                        return false;
                    }

                    this.offsetParent.classList.add("hide");

                    putBasketOption(basket_idx, product_idx, option_idx);
                } else if (this.classList.contains("open")) {
                    let $$option_select_box = document.querySelectorAll(".option__select__box");
                    $$option_select_box.forEach(el => el.classList.add("hide"));
                    this.parentNode.nextElementSibling.classList.remove("hide");
                }
            });
        });
		*/
		
		let btn_change_option = document.querySelectorAll(".option__change__btn");
		btn_change_option.forEach(btn => {
			btn.addEventListener("click", function (e) {
				let el = e.currentTarget;
				
				/* 옵션 변경하기 */
				if (el.classList.contains("apply")) {
					let product_box = el.parentNode.parentNode.parentNode;
					let option_select_box = el.parentNode;
					
					let basket_idx = product_box.dataset.basket_idx;
					
					let product_idx = 0;
					let option_idx = 0;
					
					let color_line = option_select_box.querySelectorAll('.color-line');
					color_line.forEach(color => {
						if (color.classList.contains('select')) {
							product_idx = color.dataset.product_idx;
						}
					});
					
					let option_size = option_select_box.querySelectorAll('.option__size');
					option_size.forEach(size => {
						if (size.classList.contains('select')) {
							option_idx = size.dataset.option_idx;
						}
					});
					
					if (product_idx > 0 && option_idx > 0) {
						option_select_box.classList.add("hide");
						putBasketOption(basket_idx,product_idx,option_idx);
					}
				} else if (el.classList.contains("open")) {
					let option_select_box = document.querySelectorAll(".option__select__box");
					option_select_box.forEach(box => {
						box.classList.add("hide");
					});
					this.parentNode.nextElementSibling.classList.remove("hide");
				}
			});
		});
    }

    function putBasketOption(basket_idx, product_idx, option_idx) {
		/*
        $.ajax({
            type: "post",
            url: api_location + "order/basket/put",
            data: {
                'basket_idx': basket_idx,
                'stock_status': 'STSO',
                'product_idx': product_idx,
                'option_idx': option_idx
            },
            dataType: "json",
			async:false,
            error: function () {
                makeMsgNoti(getLanguage(), "MSG_F_ERR_0024", null);
                // notiModal("쇼핑백 옵션 변경처리중 오류가 발생했습니다.");
            },
            success: function (d) {
                if (d.code == 200) {
                    getBasketProductList();
                } else {
                    notiModal(d.msg);
                }
            }
        });
		*/
		
		$.ajax({
			type: "post",
			url: api_location + "order/basket/put",
			data: {
				'basket_idx': basket_idx,
				'stock_status': 'STSO',
				'product_idx': product_idx,
				'option_idx': option_idx
			},
			dataType: "json",
			async:false,
			error: function () {
				// notiModal("쇼핑백 옵션 변경처리중 오류가 발생했습니다.");
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0024', null);
			},
			success: function (d) {
				if (d.code == 200) {
					getBasketProductList();
				} else {
					notiModal(d.msg);
				}
			}
		});
    }
	
	/* 변경 가능 한 쇼핑백 옵션 상품 조회처리 */
    function setBasketOption() {
		/*
        const $$option_color = document.querySelectorAll(".option__select__box .color-line");
        $$option_color.forEach(el => el.addEventListener("click", (ev) => {
            let { product_idx } = ev.currentTarget.dataset;

            $$option_color.forEach(el => el.classList.remove("select"));
            ev.currentTarget.classList.add("select");

            if (ev.currentTarget.classList.contains("select")) {
                $.ajax({
                    type: "post",
                    url: api_location + "order/basket/get",
                    data: {
                        "product_idx": product_idx
                    },
                    dataType: "json",
					async:false,
                    error: function () {
                    },
                    success: function (d) {
                        if (d.code == 200) {
                            let data = d.data.product_size;
                            let colorName = data[0].color;
                            let sizeResult = data.map(el =>
                                `<li class="option__size" data-product_idx="${el.product_idx}" data-option_idx="${el.option_idx}" data-stock_status="${el.stock_status}">${el.option_name}</li>`
                            ).join("");

                            ev.target.offsetParent.querySelector(".option__color").innerHTML = colorName;
                            ev.target.offsetParent.querySelector(".size__box").innerHTML = sizeResult;

                            setBasketOptionSTSC();
                        } else {
                            notiModal(d.msg)
                        }
                    }
                });
            }
        }));
		*/
		
		let option_color = document.querySelectorAll(".option__select__box .color-line");
		option_color.forEach(color => {
			color.addEventListener('click',function(e) {
				let el = e.currentTarget;
				
				if (!el.classList.contains('select')) {
					let color = el.querySelector('.color');
					if (color.dataset.soldout != "STSO") {
						el.classList.add('select');
						
						let product_idx = el.dataset.product_idx;
						let basket_idx = el.dataset.basket_idx;
						
						$.ajax({
							type: "post",
							url: api_location + "order/basket/get",
							data: {
								"basket_idx": basket_idx,
								"product_idx": product_idx
							},
							dataType: "json",
							async: false,
							success: function (d) {
								if (d.code == 200) {
									let data = d.data;
									if (data != null) {
										let option_select_box = el.parentNode.parentNode;
										
										let size_result = "";
										
										let product_size = data.product_size;
										product_size.forEach(function(row) {
											let color_name = row.color;
											size_result += `
												<li class="option__size" data-product_idx="${row.product_idx}" data-option_idx="${row.option_idx}" data-stock_status="${row.stock_status}">
													${row.option_name}
												</li>
											`;
										});
										
										option_select_box.querySelector('.size__box').innerHTML = size_result;
										
										setBasketOptionSTSC();
									}
								} else {
									notiModal(d.msg)
								}
							}
						});
					}
				} else {
					el.classList.remove('select');
				}
			});
		});
    }

    function setBasketOptionSTSC() {
        let $$option_size = document.querySelectorAll(".option__size");

        $$option_size.forEach(el => {
            if (el.dataset.stock_status == "STSO" || el.dataset.stock_status == "STSC") {
                el.classList.add("disableBtn")
            }
        });

        $$option_size.forEach(el => el.addEventListener("click", (ev) => {
            let event_target = ev.currentTarget;

            $$option_size.forEach(el => el.classList.remove("select"));
            event_target.classList.add("select");
        }));
    }

    function clickReorderBtn() {
        const $$reorderBtn = document.querySelectorAll(".reorder__btn");
        $$reorderBtn.forEach(el => {
            el.addEventListener("click", (ev) => {
                let { basket_idx, product_idx, option_idx, reorder_flg } = ev.currentTarget.offsetParent.dataset;

                if (reorder_flg == false) {
                    addReorderInfo(basket_idx, product_idx, option_idx);
                }
            });
        });
    }

    function addReorderInfo(basket_idx, product_idx, option_idx) {
        $.ajax({
            type: "POST",
            url: api_location + "order/reorder/add",
            data: {
                "add_type": "basket",
                "product_idx": product_idx,
                "basket_idx": basket_idx,
                "option_idx": option_idx
            },
            dataType: "json",
			async:false,
            error: function () {
            },
            success: function (d) {
				console.log(d);
				let code = d.code;
				if (code == 200) {
					let result = d.data;
					let product_idx = data.product_idx;
					
					setReorderFlg(product_idx);
				} else {
					notiModal(d.msg);
				}
            }
        });
    }

    function setReorderFlg(productIdx) {
        const productBox = [...document.querySelectorAll(".sold__list__box .product__box")].find(el => el.dataset.product_idx == productIdx);
        productBox.dataset.reflg = true;
        productBox.querySelector(".reorder__btn u").innerHTML = "재입고 알림 신청완료";
        productBox.querySelector(".reorder__btn u").dataset.i18n = "w_basket_msg_04";
        productBox.querySelector(".reorder__btn u").textContent = i18next.t("w_basket_msg_04");
        productBox.querySelector(".reorder__btn u").classList.add("disableBtn");
    }
}

function getDeliveryPrice() {
	let delivery_data = new Array();
	
	$.ajax({
		type: "post",
		url: api_location + "order/deliver/get",
		dataType: "json",
		async: false,
		error: function () {
			// notiModal("쇼핑백 상품 리스트 조회처리중 오류가 발생했습니다.");
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0028', null);
		},
		success: function (d) {
			if (d.code == 200) {
				delivery_data = d.data;
			} else {
				notiModal(d.msg);
			}
		}
	});
	
	return delivery_data;
}