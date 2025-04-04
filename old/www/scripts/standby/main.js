var standby_idx = $('main').attr('data-standby_idx');
var targetDate = null;

window.addEventListener('resize', function () {
    debounce(resizeProductWrapClone, 1000);
})
document.addEventListener('DOMContentLoaded', function () {
    getStandbyInfo();
});

function resizeProductWrapClone() {
    let standbyBreakpoint = window.matchMedia('screen and (min-width:1025px)');//미디어 쿼리 
    if (standbyBreakpoint.matches === true) {
        $('.standby_mobile_product_list').clone().html('.standby_web_product_list')
    } else if (standbyBreakpoint.matches === false) {
        $('.standby_web_product_list').clone().html('.standby_mobile_product_list');
    }
};

function getStandbyInfo() {
    $.ajax({
        type: "post",
        url: api_location + "standby/get",
        data: {
            'standby_idx': standby_idx
        },
        dataType: "json",
        error: function (d) {
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0005", null);
            // notiModal("스탠바이", "페이지 불러오기가 실패했습니다. 다시 진행해주세요.");
            redirectStandby();
        },
        success: function (d) {
            if (d != null) {
                if (d.code == 200) {
                    let data = d.data[0];
                    $('.standby-joinus-btn').find('span').attr('data-i18n', 'sb_stand');

                    targetDate = new Date(data.entry_end_date);

                    setInterval(updateCountdown, 1000);
                    $('.banner-img').find('img').attr('src', cdn_img + data.banner_location);
                    $('.info-product-name').text(data.title);
                    $('.info-product-description.standby_description').text(data.description);
                    $('.agreement_box').html(data.policy);

                    let entry_start_date = new Date(data.entry_start_date);
                    let entry_end_date = new Date(data.entry_end_date);
                    let purchase_start_date = new Date(data.purchase_start_date);
                    let purchase_end_date = new Date(data.purchase_end_date);
                    let order_link_date = new Date(data.order_link_date);

                    let entry_start_date_str = getDateFormat(entry_start_date);
                    let entry_end_date_str = getDateFormat(entry_end_date);
                    let purchase_start_date_str = getDateFormat(purchase_start_date);
                    let purchase_end_date_str = getDateFormat(purchase_end_date);
                    let order_link_date_str = getDateFormat(order_link_date);

                    let entry_interval_hour = (entry_end_date - entry_start_date) / (60 * 60 * 1000);
                    let purchase_interval_hour = (purchase_end_date - purchase_start_date) / (60 * 60 * 1000);
                    let entry_interval_str = '';
                    let hour = '';
                    switch (getLanguage()) {
                        case "KR":
                            hour = ['시간'];
                            break;

                        case "EN":
                            hour = ['hour'];
                            break;

                        case "CN":
                            hour = ['小时'];
                            break;
                    }

                    if (entry_interval_hour - Math.floor(entry_interval_hour) == 0) {
                        entry_interval_str = `(${Math.floor(entry_interval_hour)}${hour})`;
                    }
                    else {
                        entry_interval_str = `(${Math.floor(entry_interval_hour)}${hour} ${Math.floor((entry_interval_hour - Math.floor(entry_interval_hour)) * 60)}분)`;
                    }

                    let purchase_interval_str = '';
                    if (purchase_interval_hour - Math.floor(purchase_interval_hour) == 0) {
                        purchase_interval_str = `(${Math.floor(purchase_interval_hour)}${hour})`;
                    }
                    else {
                        purchase_interval_str = `(${Math.floor(purchase_interval_hour)}${hour} ${Math.floor((purchase_interval_hour - Math.floor(purchase_interval_hour)) * 60)}분)`;
                    }
                    let date_info = `
                        <li><span data-i18n="sb_entry_date"></span>${entry_start_date_str} ~ ${entry_end_date_str} ${entry_interval_str}</li>
                        <li><span data-i18n="sb_entry_link"></span>${order_link_date_str}</li>
                        <li><span data-i18n="sb_entry_buying"></span>${purchase_start_date_str} ~ ${purchase_end_date_str} ${purchase_interval_str}</li>
                    `;
                    $('.info-standby-date').html('');
                    $('.info-standby-date').append(date_info);
                    
                    getStandbyProductList(data.product_info);
                    
                    clickBtnUpdateWish();

                    entryBtnEventHandler();
                } else {
                    notiModal(d.msg);
                    redirectStandby();
                }
            } else {
                // notiModal("스탠바이", "스탠바이정보를 찾을 수 없습니다.");
                makeMsgNoti(getLanguage(), "MSG_F_ERR_0020", null);
                redirectStandby();
            }
        }
    });
}
function initEntryBtnEvent() {
    $('.standby-joinus-btn').unbind('click');
}
function entryBtnEventHandler() {
    $('.standby-joinus-btn').on('click', function () {
        let terms_flg = $('input:radio[name=terms]:checked').val();
        if (terms_flg == 'TRUE') {
            //여러번 클릭으로 인한 다중응모를 방지를 위해 
            //클릭시 disabled속성 및 해당 이벤트 언바인드
            initEntryBtnEvent();
            $(this).prop('disabled', true);

            location.href = `/standby/complete?standby_idx=${standby_idx}`;
        } else {
            makeMsgNoti(getLanguage(), "MSG_F_WRN_0022", null);
            // notiModal("스탠바이", "약관에 동의하지 않았습니다.");
        }
    })
}
// 스탠바이 시간 함수
function updateCountdown() {
    const currentDate = new Date();

    const diff = targetDate - currentDate;
    const days = Math.floor(diff / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
    const seconds = Math.floor((diff % (1000 * 60)) / 1000).toString().padStart(2, '0');

    const countdown = document.getElementById("countdown");
    countdown.innerHTML = `${days}:${hours}:${minutes}:${seconds}`;
}
// 상품 컬러칩 생성
const productColorHtml = (color, color_rgb) => {
    let productColorHtml = "";
    if (!color_rgb) {
        return null;
    } else {
        let multi = color_rgb.split(";");
        if (multi.length === 2) {
            productColorHtml += `
				<div class="color-line"	style="--background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
					<p class="color-name">${color}</p>
					<div class="color multi" data-title="${color}"></div>
				</div>
			`;
        } else {
            productColorHtml += `
				<div class="color-line"	data-title="${color}" style="--background:${multi[0]}">
					<p class="color-name">${color}</p>
					<div class="color" data-title="${color}"></div>
				</div>
			`;
        }
    }

    return productColorHtml;
};

function getStandbyProductList(product_info) {
    let productListWrapWeb = document.querySelector(".standby_web_product_list");
    let productListWrapMobile = document.querySelector(".standby_mobile_product_list");

    product_info.forEach(row => {

        let product_o_img = row.product_img.product_o_img;
        let product_p_img = row.product_img.product_p_img;
        let product_location = null;

        if (product_p_img.length != 0) {
            product_location = product_p_img[0].img_location;
        }
        if (product_o_img.length != 0) {
            product_location = product_o_img[0].img_location;
        }
        let standbyProductWeb = document.createElement("div");
        standbyProductWeb.classList.add("product", "standby", "half");
        
        let whish_flg = `${row.whish_flg}`;
        let whish_img = "";
		
		let txt_dataset = `data-location="standby" data-wish_flg="${row.whish_flg}" data-product_idx="${row.product_idx}"`;
		
        if (whish_flg == 'true') {
            whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist-bk.svg" alt="">`;
        } else if (whish_flg == 'false') {
            whish_img = `<img class="wish_img" data-status=${whish_flg} src="/images/svg/wishlist.svg" alt="">`;
        }

        let wishBtnDiv = `
        	<div class="wish__btn btn_update_wish" product_idx="${row.product_idx}" ${txt_dataset}>
        		${whish_img}
        	</div>
        `;

        let strDiv = "";
        strDiv +=
            `
                ${wishBtnDiv}
                <a href="/product/detail?product_idx=${row.product_idx}" class="docs-creator">
                    <div class="product-img">
                        <img class="prd-img" cnt="1" data-src="${cdn_img}${product_location}" src="${cdn_img}${product_location}" alt="">
                    </div>
                </a>
                <div class="product-info">
                    <div class="info-row">
                        <div class="name" data-soldout="">
                            <span>${row.product_name}</span>
                        </div>
                        <div class="price" data-soldout="${row.stock_status}" data-saleprice="${row.sales_price}" data-discount="${row.discount}" data-dis="true">
                            <span>${row.price}</span>
                        </div>
                    </div>
                    <div class="color-title">
                        <span>${row.color}</span>
                    </div>
                    <div class="info-row">
            `;
        
        let product_color_arr = row.product_color;
        let colorLength = product_color_arr.length;
        
        if (colorLength > 0) {
            strDiv += `<div class="color__box" data-maxcount="${colorLength < 6 ? "" : "over"}" data-colorcount="${colorLength < 6 ? colorLength : colorLength - 5}">`;
            product_color_arr.forEach(function (color_row, index) {
                if (index < 5) {
                    strDiv += `
                                <div class="color" data-color="${color_row.color_rgb}" data-productidx="${color_row.product_idx}" data-soldout="${color_row.stock_status}"
                                style="background-color:${color_row.color_rgb}"></div>
                    `;
                }
            })
            strDiv += `
                        </div>
            `;
        }
        
        strDiv +=
            `           <div class="size__box">
            `;
        let product_size_arr = row.product_size;
        console.log();
        let sizeLength = product_size_arr.length;
        if(sizeLength){
            product_size_arr.forEach(function (size_row, index){
                strDiv += `     <li class="size" data-sizetype="" data-productidx="${size_row.product_idx}" data-optionidx="${size_row.option_idx}" data-soldout="${size_row.stock_status}">${size_row.option_name}</li>`;
            })
        }
        strDiv += 
            `          </div>
                    </div>
                </div>
            `;
        standbyProductWeb.innerHTML = strDiv;
        
        let standbyProductMobile = document.createElement("div");
        standbyProductMobile.classList.add("product", "standby", "half");
        strDiv = "";
        strDiv +=
            `
                ${wishBtnDiv}
                <a href="/product/detail?product_idx=${row.product_idx}" class="docs-creator">
                    <div class="product-img">
                        <img class="prd-img" cnt="1" data-src="${cdn_img}${product_location}" src="${cdn_img}${product_location}" alt="">
                    </div>
                </a>
                <div class="product-info">
                    <div class="info-row">
                        <div class="name" data-soldout="">
                            <span>${row.product_name}</span>
                        </div>
                        <div class="price" data-soldout="${row.stock_status}" data-saleprice="${row.sales_price}" data-discount="${row.discount}" data-dis="true">
                            <span>${row.price}</span>
                        </div>
                    </div>
                    <div class="color-title">
                        <span>${row.color}</span>
                    </div>
                    <div class="info-row">
            `;
        if (colorLength > 0) {
            strDiv += `<div class="color__box" data-maxcount="${colorLength < 6 ? "" : "over"}" data-colorcount="${colorLength < 6 ? colorLength : colorLength - 5}">`;
            product_color_arr.forEach(function (color_row, index) {
                if (index < 5) {
                    strDiv += `
                                    <div class="color" data-color="${color_row.color_rgb}" data-productidx="${color_row.product_idx}" data-soldout="${color_row.stock_status}"
                                    style="background-color:${color_row.color_rgb}"></div>
                        `;
                }
            })
            strDiv += `
                            </div>
                `;
        }

        strDiv +=
            `       
                            <div class="size__box">
                                <li class="size" data-sizetype="" data-productidx="1" data-optionidx="17727" data-soldout="STSO">A1</li>
                                <li class="size" data-sizetype="" data-productidx="1" data-optionidx="17728" data-soldout="STSO">A2</li>
                            </div>
                        </div>
                    </div>
                `;
        standbyProductMobile.innerHTML = strDiv;
        
        productListWrapWeb.appendChild(standbyProductWeb);
        productListWrapMobile.appendChild(standbyProductMobile);
    });
}

function redirectStandby() {
    $('#notimodal-modal .close-btn').attr('onclick', 'location.href="/mypage?mypage_type=stanby_first"');
}
function getDateFormat(date) {
    let day = '';
    switch (getLanguage()) {
        case "KR":
            day = ['일', '월', '화', '수', '목', '금', '토'];
            break;

        case "EN":
            day = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
            break;

        case "CN":
            day = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
            break;
    }


    let getMonth = `${date.getMonth() + 1}`;
    let getDate = `${date.getDate()}`;
    let getDays = day[date.getDay()];

    let getHours = `${date.getHours()}`.padStart(2, '0');
    let getMinite = `${date.getMinutes()}`.padStart(2, '0');

    let date_str = `${getMonth}/${getDate}(${getDays}) ${getHours}:${getMinite}`;

    return date_str;
}