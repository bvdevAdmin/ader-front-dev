//order-basket-list(page) 
function BasketPage(el) {
	const prototypes = { el }
	prototypes.el = el;

	let parm = prototypes;

	//쇼핑백 상품 리스트 조회
	getBasketProductList();

	//쇼핑백 상품 리스트 조회
	function getBasketProductList() {
		$.ajax({
			type: "post",
			url: api_location + "order/basket/list/get"
			dataType: "json",
			async:false,
			error: function () {
				makeMsgNoti(getLanguage(), "MSG_F_ERR_0028", null);
                // notiModal("쇼핑백 상품 리스트 조회처리중 오류가 발생했습니다.");
			},
			success: function (d) {
				let data = d.data;

				let basket_so_info = data.basket_so_info;
				let basket_st_info = data.basket_st_info;

				if (basket_so_info.length > 0 || basket_st_info.length > 0) {
					writeProductListDomTree(basket_st_info, basket_so_info);
				} else {
					basketEmpty();
				}
			}
		});

		function basketEmpty() {
			let list__body = document.querySelector(".basket__wrap .list__body");
			let emptyDiv = document.createElement("div");
			emptyDiv.className = "empty-data";
			emptyDiv.innerHTML = `<h1 data-i18n="s_basket_empty">쇼핑백이 비어있습니다.</h1>`
			list__body.appendChild(emptyDiv);
			$('.checkbox__box').hide();
			$('.pay__box').hide();
			$('.basket__wrap .list__body .product__wrap').remove();
			changeLanguageR();
		}
	}

	function writeProductListDomTree(st_info, so_info) {
		$('.product__wrap').remove();
		$('.sold__list__box').remove();

		let docFrag = document.createDocumentFragment();
		let stin_html = "";
		let stso_html = "";

		let stin_product_wrap = document.createElement("div");
		stin_product_wrap.classList.add("product__wrap");

		let stso_product_wrap = document.createElement("div");
		stso_product_wrap.classList.add("sold__list__box");

		docFrag.appendChild(stin_product_wrap);

		let bodyWidth = document.getElementsByTagName("body")[0].offsetWidth;

		//재고상품 있는 경우 
		if (st_info.length > 0) {
			st_info.forEach(el => {
				let product_type = el.product_type;
				let set_type = el.set_type;
				let color_html = "";

				let sales_price = (el.sales_price).toLocaleString('ko-KR');
				let color_rgb = el.color_rgb;

				let multi = color_rgb.split(";");
				if (multi.length === 2) {
					color_html += `
						<div class="color__box">
							<div class="color-title">${el.color}</div>
							<div class="color-line" data-basket_idx="${el.basket_idx}" style="--background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
								<div class="color multi" data-title="${el.color}"></div>
							</div>
						</div>
					`;
				} else {
					color_html += `
						<div class="color__box">
							<div class="color-title">${el.color}</div>
							<div class="color-line" data-basket_idx="${el.basket_idx}" data-title="${el.color}" style="--background-color:${multi[0]}" >
								<div class="color" data-title="${el.color}"></div>
							</div>
						</div>
					`;
				}

				stin_html += `
					<div class="product__box product_box_${el.basket_idx}" data-stock_status="${el.stock_status}"  data-basket_idx="${el.basket_idx}" data-basket_qty="${el.basket_qty}" data-product_idx="${el.product_idx}" data-option_idx="${el.option_idx}" data-product_qty="${el.product_qty}">
						<label class="cb__custom self" for="">
							<input class="prd__cb self__cb" type="checkbox" name="stock">
							<div class="cb__mark"></div>
						</label>
						<a href="/product/detail?product_idx=${el.product_idx}">
							<div class="prd__img" style="background-image:url('${cdn_img}${el.product_img}') ;"></div>
						</a>
						<div class="prd__content" data-sales_price="${el.sales_price}" >
							${el.refund_flg == 0 && bodyWidth >= 1024 ? `<div class="prd__title">${el.product_name}<p class="refund_msg" data-i18n="s_no_ex_re"></p></div>` : `<div class="prd__title">${el.product_name}</div>`}
							<div class="price">${sales_price}</div>
							${color_html}
							<div class="prd__size">
								<div class="size__box">
									<li data-soldout="${el.stock_status}">${el.option_name}</li>
								</div>
								${el.refund_flg == 0 && bodyWidth < 1024 ? `<p class="refund_msg" data-i18n="s_no_ex_re"></p>` : ``}
							</div>
							<div class="prd__qty">
								<div>Qty</div>
								<div class="minus__btn"><img src="/images/svg/minus-basket.svg"></div>
								<input class="count__val" type="text" value="${el.basket_qty}" readonly>
								<div class="plus__btn"><img src="/images/svg/plus-basket.svg"></div>
								<div class="price_total" data-price_total="${el.sales_price * el.basket_qty}" data-stock_status="${el.stock_status}">${sales_price}</div>
							</div>
						</div>
					</div>
				`;

				if (product_type == "S") {
					let set_product_info = el.set_product_info;
					if (set_product_info != null && set_product_info.length > 0) {
						let set_product_html = writeSetProductInfo(set_product_info);
						stin_html += set_product_html;
					}
				}
			});

			docFrag.querySelector('.product__wrap').innerHTML = stin_html;
			document.querySelector('.list__box .list__body').appendChild(docFrag);
			// 첫 화면은 모든 체크박스 체크
			let selfCheck = document.querySelectorAll('.prd__cb');
			let allCheck = document.querySelector('.all__cb');
			selfCheck.forEach(el => el.setAttribute("checked", true));
			allCheck.setAttribute("checked", true);
			let price_product = calcCheckedPrice();
			payBoxSumPrice(price_product);
		}

		deleteBasketInfo();
		deleteAllBasketInfo();

		if (so_info.length > 0) {
			//품절상품이 있을 경우  
			let product_html = "";
			let docFrag = document.createDocumentFragment();
			docFrag.appendChild(stso_product_wrap);

			stso_html += `
				<div class="list__header">
					<div class="icon__box">
						<img src="/images/svg/basket.svg" alt="">
						<div>품절제품</div>
					</div>
					<div class="checkbox__box checkbox_stso">
						<label class="cb__custom all" for="sold">
							<input class="prd__cb all__cb" type="checkbox" name="sold">
							<div class="cb__mark"></div>
						</label>
						<div class="flex gap-10">
							<u class="ufont so__checked__btn" btn="stock">선택 삭제</u>
							<u class="ufont so__all__btn" btn="stock">모두 삭제</u>
						</div>			
					</div>
				</div>
				<div class="list__body">
				</div>
			`;

			stso_product_wrap.innerHTML = stso_html;

			so_info.forEach(el => {
				let product_type = el.product_type;
				let set_type = el.set_type;

				let color_html = "";

				let sales_price = (el.sales_price).toLocaleString('ko-KR');
				let color_rgb = el.color_rgb;
				let multi = color_rgb.split(";");
				if (multi.length === 2) {
					color_html += `
						<div class="color__box">
							<div class="color-title">${el.color}</div>
							<div class="color-line" data-basket_idx="${el.basket_idx}" style="--background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
							<div class="color multi" data-soldout="${el.stock_status}" data-title="${el.color}"></div>
							</div>
						</div>
					`;
				} else {
					color_html += `
						<div class="color__box">
							<div class="color-title">${el.color}</div>
							<div class="color-line" data-basket_idx="${el.basket_idx}" data-title="${el.color}" style="--background-color:${multi[0]}" >
								<div class="color" data-soldout="${el.stock_status}" data-title="${el.color}"></div>
							</div>
						</div>
					`;
				}

				let product_color_html = "";

				let product_color = el.product_color;
				product_color.forEach(color => {
					let optionColorData = color.color_rgb;
					let optionColorMulti = optionColorData.split(";");
					if (optionColorMulti.length === 2) {
						product_color_html += `
						<div class="color-line" data-product_idx="${color.product_idx}" style="--background:linear-gradient(90deg, ${optionColorMulti[0]} 50%, ${optionColorMulti[1]} 50%);">
							<div class="color multi"data-title="${color.color}"data-soldout="${color.stock_status}"></div>
						</div>
					`;
					} else {
						product_color_html += `
							<div class="color-line" data-product_idx="${color.product_idx}" style="--background-color:${optionColorMulti[0]}" >
								<div class="color"data-title="${color.color}" data-soldout="${color.stock_status}"></div>
							</div>
						`;
					}
				});

				let reorder_class = el.reorder_flg ? "" : "disaBleBtn";
				let reorder_text = el.reorder_flg ? "재입고 알림 신청완료" : "재입고 알림 신청하기";

				product_html += `
					<div class="product__box" data-basket_idx="${el.basket_idx}" data-stock_status="${el.stock_status}" data-product_idx="${el.product_idx}" data-option_idx="${el.option_idx}" data-reorder_flg="${el.reorder_flg}">
						<label class="cb__custom self" for="">
							<input class="prd__cb self__cb" type="checkbox" name="sold">
							<div class="cb__mark"></div>
						</label>
						<a href="/product/detail?product_idx=${el.product_idx}">
							<div class="prd__img" style="background-image:url('${cdn_img}${el.product_img}') ;"></div>
						</a>
						<div class="prd__content">
							<div class="prd__title">${el.product_name}</div>
							${el.discount == 0 ? `<div class="price" data-soldout="${el.stock_status}" data-sales_price="${sales_price}" data-discount="${el.discount}" data-dis="false">${el.price.toLocaleString('ko-KR')}</div>` : `<div class="price" data-soldout="${el.stock_status}" data-sales_price="${sales_price}" data-discount="${el.discount}" data-dis="true"><span>${el.price.toLocaleString('ko-KR')}</span></div>`} 
							${color_html}
							<div class="prd__size">
								<div class="size__box">
									<li data-soldout="${el.stock_status}">${el.option_name}</li>
								</div>
							</div>
							<div class="option__box">
								<div class="option__change__btn open">
									<img src="/images/svg/edit.svg" alt="">
									<u data-i18n="s_change_options">옵션 변경하기</u>
								</div>
								<div class="reorder__btn ${reorder_class}">
									<img src="/images/svg/reflesh.svg" alt="">
									<u>${reorder_text}</u>
								</div>
							</div>
							<div class="option__select__box hide">
								<div class="option__select__head">
									<div class="option__color">${el.color}</div>
									<div class="close__btn option">
										<span class="line"></span>
										<span class="line"></span>
									</div>
								</div>
								<div class="color__box">${product_color_html}</div>
								<div class="size__box">
								${el.product_size.map((size) => {
					return `<li class="option__size" data-product_idx="${size.product_idx}" data-option_idx="${size.option_idx}" data-stock_status="${size.stock_status}">${size.option_name}</li>`;
				}).join("")
					}
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
						product_html += set_product_html;
					}
				}

				docFrag.querySelector(".list__body").innerHTML = product_html;
				let reorderBtn = document.querySelector(".reorder__btn u");
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
				})
			});

			document.querySelector('.list__box .list__body').appendChild(docFrag);
			soldCheckedDeleteBtn();
			soldAllDeleteBtn();
		}

		clickCheckboxSTIN();
		clickCheckboxSTSO();

		clickCntBtn();

		optionBoxCloseBtn();
		clickPutBasketOption();
		setBasketOption();
		payBtnEvent();
		clickReorderBtn();
	}

	function writeSetProductInfo(data) {
		let set_product_html = "";

		data.forEach(set => {
			let color_html = "";

			let color_rgb = data.color_rgb;
			let multi = color_rgb.split(";");

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

			stin_html += `
				<div class="product__box">
					<label class="cb__custom self" for="">
						<input class="prd__cb self__cb" type="checkbox" name="stock">
						<div class="cb__mark"></div>
					</label>
					<a href="/product/detail?product_idx=${set.product_idx}">
						<div class="prd__img" style="background-image:url('${cdn_img}${set.product_img}') ;"></div>
					</a>
					<div class="prd__content">
						<div class="prd__title">${set.product_name}</div>
						${color_html}
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

			return set_product_html;
		});
	}
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
			getBasketProductList();
			/*
			if (product__box.length == 0) {
				getBasketProductList();
			}
			*/
		});
	};

	//품절상품 전체삭제 버튼
	function soldAllDeleteBtn() {
		const $checkedDelete = document.querySelector(".so__all__btn");
		$checkedDelete.addEventListener("click", () => {
			selfCheckbox("sold", false);
			let product__box = document.querySelectorAll(".basket__wrap .product__box");
			getBasketProductList()
			
			let checkbox_box = document.querySelectorAll('.checkbox__box');
			checkbox_box.forEach(box => {
				if (box.classList.contains('checkbox_stso')) {
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
				let code = d.code;
				if (code != 200) {
					notiModal(d.msg);
				}
			}
		});
	}

	//쇼핑백 상품 수량 변경
	const putBasketQty = (action_type, basket_idx, basket_qty, product_idx) => {
		let tmp_qty = basket_qty
		$.ajax({
			type: "post",
			url: api_location + "order/basket/put",
			data: {
				"basket_idx": basket_idx,
				"stock_status": "STIN",
				"basket_qty": basket_qty,
				"product_idx": product_idx
			},
			dataType: "json",
			async:false,
			error: function () {
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0026', null);
                // notiModal("쇼핑백 상품 정보 수정 처리에 실패했습니다.");
			},
			success: function (d) {
				if (d.code != 200) {
					if (action_type == "plus" && basket_qty > 1) {
						tmp_qty = (tmp_qty - 1);

						let product_box = document.querySelector(".product_box_" + basket_idx);
						product_box.dataset.basket_qty = tmp_qty;
						product_box.querySelector('.count__val').value = tmp_qty;

						let sales_price = product_box.querySelector('.prd__content').dataset.sales_price;
						let price_total = sales_price * tmp_qty;

						product_box.querySelector('.price_total').dataset.price_total = price_total;
						product_box.querySelector('.price_total').innerText = price_total.toLocaleString('ko-KR');

						let price_product = calcCheckedPrice();
						payBoxSumPrice(price_product);
					}

					notiModal(d.msg);
				}
			}
		});
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
				msgBox.innerText = '품절제품을 삭제 후 결제를 진행해주세요';
				msgBox.dataset.i18n = 's_basket_msg_01';
				msgBox.textContent = i18next.t('s_basket_msg_01');
				return false;
			}
			if (checkCnt == 0) {
				msgBox.innerText = '결제하실 상품을 선택해주세요.';
				return false;
			}

			if (selectArr.length > 0) {
				msgBox.innerText = '';
				location.href = "/order/confirm?&basket_idx=" + selectArr;
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
				let $plus_btn = this.parentNode.querySelector(".plus__btn");

				let basket_idx = this.offsetParent.dataset.basket_idx;
				let stock_status = this.offsetParent.dataset.stock_status;
				//let basket_qty = this.offsetParent.dataset.basket_qty;
				let product_idx = this.offsetParent.dataset.product_idx;

				let price_total = parseInt(this.parentNode.querySelector(".price_total").textContent.replace(/,/g, ''));
				price_total -= parseInt(this.parentNode.dataset.init);

				this.parentNode.querySelector('.price_total').dataset.price_total = price_total;

				let tmp_cnt = this.parentNode.querySelector(".count__val").value;
				tmp_cnt = parseInt(tmp_cnt) - 1;
				let basket_qty = tmp_cnt;

				this.parentNode.querySelector(".count__val").value = tmp_cnt;
				this.parentNode.querySelector(".price_total").textContent = price_total.toLocaleString('ko-KR');

				if (tmp_cnt == "1") {
					this.classList.add('disableBtn');
					setTotalPrice = this.parentNode.dataset.init;
				} else {
					$plus_btn.classList.remove('disableBtn');
				}

				let price_product = calcCheckedPrice();
				payBoxSumPrice(price_product);
				putBasketQty("minus".basket_idx, basket_qty, product_idx);

			});
		});

		//수량 업버튼 클릭 이벤트
		$$plus_btn.forEach(el => {
			el.addEventListener("click", function () {
				let $minus_btn = this.parentNode.querySelector(".minus__btn");

				let basket_idx = this.offsetParent.dataset.basket_idx;
				let stock_status = this.offsetParent.dataset.stock_status;
				//let basket_qty = this.offsetParent.dataset.basket_qty;
				let product_idx = this.offsetParent.dataset.product_idx;

				let price_total = parseInt(this.parentNode.querySelector(".price_total").textContent.replace(/,/g, ''));
				price_total += parseInt(this.parentNode.dataset.init);

				this.parentNode.querySelector('.price_total').dataset.price_total = price_total;

				let tmp_cnt = this.parentNode.querySelector(".count__val").value;
				tmp_cnt = parseInt(tmp_cnt) + 1;
				let basket_qty = tmp_cnt;

				this.parentNode.querySelector(".count__val").value = tmp_cnt;
				this.parentNode.querySelector(".price_total").innerText = price_total.toLocaleString('ko-KR');

				if (tmp_cnt == "9") {
					this.classList.add('disableBtn');
				} else {
					$minus_btn.classList.remove('disableBtn');
				}

				let price_product = calcCheckedPrice();

				payBoxSumPrice(price_product);
				putBasketQty("plus", basket_idx, basket_qty, product_idx);
			});
		});

	};

	//재고있음(STIN) 체크박스 클릭 이벤트
	function clickCheckboxSTIN() {
		const $all_stin_checkbox = document.querySelector(".prd__cb.all__cb"); //
		const $stin_checkbox = document.querySelectorAll(".product__wrap .self__cb");
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
							let checked_stin = document.querySelectorAll(".product__wrap input[name='stock']:checked");
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
		let $all_stso_checkbox = null;
		if ($(".sold__list__box .all__cb").length > 0) {
			$all_stso_checkbox = document.querySelector(".sold__list__box .all__cb[name='sold']");
		}

		let $stso_checkbox = null;
		if ($(".sold__list__box .self__cb").length > 0) {
			$stso_checkbox = document.querySelectorAll(".sold__list__box .self__cb[name='sold']");
		}

		if ($all_stso_checkbox != null) {
			$all_stso_checkbox.addEventListener("click", function () {
				$stso_checkbox.forEach(el => {
					el.checked = this.checked;
				});
			});
		}

		if ($stso_checkbox != null) {
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

	function clickPutBasketOption() {
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
	}

	function putBasketOption(basket_idx, product_idx, option_idx) {
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
	}

	function setBasketOption() {
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
							notiModal(d.msg);
						}
					}
				});
			}
		}));
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
				let result = d.data;
				setReorderFlg(product_idx);
			}
		});
	}

	function setReorderFlg(productIdx) {
		const productBox = [...document.querySelectorAll(".sold__list__box .product__box")].find(el => el.dataset.product_idx == productIdx);
		productBox.dataset.reflg = true;
		productBox.querySelector(".reorder__btn u").innerHTML = "재입고 알림 신청완료";
		productBox.querySelector(".reorder__btn u").dataset.i18n = "w_basket_msg_04";
		productBox.querySelector(".reorder__btn u").textContent = i18next.t("w_basket_msg_04");
		productBox.querySelector(".reorder__btn u").classList.add('disableBtn');
	}
}