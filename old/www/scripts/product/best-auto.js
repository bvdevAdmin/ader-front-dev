document.addEventListener("DOMContentLoaded", function () {
    getBestAutoInfo();
	
    $("#quickview").removeClass("hidden");
});

const getBestAutoInfo = () => {
    let { menu_type, menu_idx, page_idx } = document.querySelector(".best_auto_wrap").dataset;

    $.ajax({
        type: "post",
        url: api_location + "product/best/auto/get",
        headers: {
        	"country": getLanguage()
        },
        data: {
            'menu_type': menu_type,
            'menu_idx': menu_idx,
            'page_idx': page_idx
        },
        dataType: "json",
        async: false,
        error: function () {
            // notiModal("베스트 상품 조회처리중 오류가 발생했습니다.");
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0107", null);
        },
        success: function (d) {
            if (d.code == 200) {
                let data = d.data;
				if (data != null && data.length > 0) {
					writetBestAutoInfoList(data);
				}
            } else {
                notiModal(d.msg);
            }
        }
    });
}

// 상품 컬러칩 생성
const writeBestAutoColorHtml = (color, color_rgb, stock_status) => {
    let product_color_html = "";

    if (!color_rgb) {
        return null;
    } else {
        let multi = color_rgb.split(";");
        if (multi.length === 2) {
            product_color_html += `
				<div class="color-line">
					<p class="color-name no-blank hidden">${color}</p>
					<p class="color-name blank">&nbsp;</p>
					<div class="color multi" data-stock="${stock_status}" style="--background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);"></div>
				</div>
			`;
        } else {
            product_color_html += `
				<div class="color-line">
					<p class="color-name no-blank hidden">${color}</p>
					<p class="color-name blank">&nbsp;</p>
					<div class="color" data-stock="${stock_status}" style="--background:${multi[0]}"></div>
				</div>
			`;
        }
    }

    return product_color_html;
};

function writetBestAutoInfoList(data) {
    let best_auto_body = document.querySelector(".best_auto_body");
    best_auto_body.innerHTML = "";

    let best_auto_list_html = "";
    data.forEach(row => {
        let wish_btn_html = bestAutoWishBtn(row.product_idx, row.whish_flg);

        let product_type = row.product_type;

        let class_product_type = "";
        let product_color_html = "";
        let set_msg = "";
        let product_stock_class = ""

        if (product_type == "B") {
            class_product_type = "basic_product";

            if (row.product_color.length > 0) {
                let product_color = row.product_color;

                product_color.forEach(row => {
                    product_color_html += writeBestAutoColorHtml(row.color, row.color_rgb, row.stock_status);
                });
            } else {
                product_color_html = "";
                product_stock_class = " sold_out_price";
            }
        } else if (product_type == "S") {
            set_msg = `<div class="product_option">세트 제품은 상세 페이지에서 <span class = "text_br"><br></span>자세한 옵션 확인이 가능합니다.</div>`;
        }
		
		let dis = "false";
		if (row.discount > 0) {
			dis = "true"
		}
		
        best_auto_list_html += `
			<div class="best_box ${class_product_type}">
				<div class="best_img" data-product_idx="${row.product_idx}">
					<img src="${cdn_img}${row.img_location}">
					<div class="product_ranking">
						TOP ${row.display_num}
					</div>
				</div>
				<div class="best_info">
					${wish_btn_html}
					<div class="product_name">${row.product_name}</div>
					<div class="product_price ${product_stock_class}" data-discount="${row.discount}" data-dis="${dis}" data-saleprice="${row.sales_price}">
						<span>${row.price}</span>
					</div>
					<div class="product_color">${product_color_html}</div>  
					${set_msg}
				</div>
			</div>
		`;
    });

    best_auto_body.innerHTML = best_auto_list_html;

    clickBestAutoImg();
    hoverBestAutoColor();
    setBestAutoListFadeIn();
    
    clickBtnUpdateWish();
}

function clickBestAutoImg() {
    let best_img = document.querySelectorAll('.best_img');
    best_img.forEach(img => {
        img.addEventListener('click', function () {
            let product_idx = img.dataset.product_idx;

            $.ajax({
                type: "post",
                url: api_location + "product/best/check",
                data: {
                    "product_idx": product_idx
                },
                dataType: "json",
                error: function () {
                    makeMsgNoti(getLanguage(), "MSG_F_ERR_0030", null);
                    // notiModal("상세페이지 이동중 오류가 발생했습니다.");
                },
                success: function (d) {
                    let code = d.code;

                    if (code == 200) {
                        location.href = "/product/detail?product_idx=" + product_idx;
                    } else {
                        notiModal(d.msg);
                    }
                }
            })
        });
    });
}

function hoverBestAutoColor() {
    let colorChip = document.querySelectorAll(".color-line .color");
    colorChip.forEach(chip => {
        chip.addEventListener("mouseenter", function (e) {
            let parentNode = e.target.parentNode;
            let name = parentNode.querySelector(".color-name.no-blank");
            let blank = parentNode.querySelector(".color-name.blank");

            name.classList.remove("hidden");
            blank.classList.add("hidden");
        });
        chip.addEventListener("mouseleave", function (e) {
            let parentNode = e.target.parentNode;
            let name = parentNode.querySelector(".color-name.no-blank");
            let blank = parentNode.querySelector(".color-name.blank");

            blank.classList.remove("hidden");
            name.classList.add("hidden");
        });
    });
}

function bestAutoWishBtn(product_idx, whish_flg) {
    let wish_btn_html = "";
    
    let whish_img = "";
    
    let txt_dataset = `data-location="best" data-wish_flg="${whish_flg}" data-product_idx="${product_idx}"`;
    
    let login_status = getLoginStatus();
    if (login_status == "true") {
        if (whish_flg == true) {
            whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist-bk.svg" alt="">`;
        } else if (whish_flg == false) {
            whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist.svg" alt="">`;
        }
    } else {
        whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist.svg" alt="">`;
    }

    wish_btn_html = `
    	<div class="wish__btn btn_update_wish" product_idx="${product_idx}" ${txt_dataset}>
    		${whish_img}
    	</div>
    `;

    return wish_btn_html;
}

function setBestAutoListFadeIn() {
    let product_list_body = document.querySelector('.best_auto_body');
    product_list_body.style.minHeight = 0;

    let product = product_list_body.querySelectorAll('.best_box');
    product.forEach((el, idx) => {
        let setTimeoutId = setTimeout(function () {
            BestAutoFadeIn(el);
        }, 100 * idx);
    });
}

function BestAutoFadeIn(el) {
    el.style.opacity = 0;
    var tick = function () {
        el.style.opacity = +el.style.opacity + 0.01;
        if (+el.style.opacity < 1) {
            (window.requestAnimationFrame && requestAnimationFrame(tick)) || setTimeout(tick, 16)
        }
    };
    tick();
}
