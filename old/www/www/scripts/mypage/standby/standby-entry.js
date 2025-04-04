document.addEventListener('DOMContentLoaded', function () {
	getEntryStandby();
});

function getEntryStandby() {
    initEntryList();
    $.ajax({
        type: "post",
        url: config.api + "mypage/standby/entry/get",
        dataType: "json",
        error: function () {
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0019", null);
            // notiModal("스탠바이", "신청내역 목록을 불러오지 못했습니다.");
        },
        success: function (d) {
            let code = d.code;
            
            if (code == 200) {
                let data = d.data;
                if (data != null && data.length > 0) {
                    data.forEach(function (row) {
                        let product_name_list = '';
                        let entry_pc_div = '';
                        let entry_mo_div = '';
                        let detail_pc_div = '';
                        let detail_mo_div = '';
                        let btn_color_class = row.purchase_status == '구매하기' ? 'black' : 'white';
                        // 구매하기
                        // 구매완료
                        // 구매종료
                        // 구매대기
                        // 구매진행중
                        let i18nClass = "";
                        switch(row.purchase_status) {
                            case "구매대기":
                                i18nClass = "d_wait_purchase";
                                break;
                            case "미구매":
                                i18nClass = "d_not_purchase";
                                break;
                            case "구매하기":
                                i18nClass = "d_purchase";
                                break;
                            case "구매 진행중":
                                i18nClass = "d_ongoing_purchase";
                                break;
                            case "구매완료":
                                i18nClass = "d_complete_purchase";
                                break;
                            case "구매 종료":
                                i18nClass = "d_end_purchase";
                                break;
                        }
                        let btn_div = `<button class="${btn_color_class}__full__width__btn" data-i18n="${i18nClass}">${row.purchase_status}</button>`;

                        if(row.standby_product_info != null && row.standby_product_info.length > 0){
                            row.standby_product_info.forEach(function(product_row){
                                let sales_price = 0;
                                switch(country){
                                    case 'KR':
                                        sales_price = product_row.sales_price_kr;
                                        break;
                                    case 'EN':
                                        sales_price = product_row.sales_price_en;
                                        break;
                                    case 'CN':
                                        sales_price = product_row.sales_price_cn;
                                        break;
                                }
                                product_name_list += `<p>${product_row.product_name}</p>`;
                                let product_info = '';
                                if(product_row.status == '구매완료'){
                                    product_info += `
                                        <p>${sales_price}</p>
                                        <p>${product_row.color}</p>
                                        <p>${product_row.product_option.join(',')}</p>
                                    `;
                                }
                                detail_pc_div += `   
                                    <div class="details__table">
                                        <table>
                                            <colsgroup>
                                                <col style="width:120px;">
                                                <col style="width:360px;">
                                                <col style="width:230px;">
                                            </colsgroup>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <img src="${cdn_img}${product_row.img_location}">
                                                    </td>
                                                    <td class="vertical__top contsnts_td">
                                                        <div class="product__container">
                                                            <div class="items__title">
                                                                <p>${product_row.product_name} / ${product_row.color}</p>
                                                            </div>
                                                            <div class="items__info">
                                                                ${product_info}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="vertical__top entry_status_td">
                                                        <div class="order__status">${product_row.status}</div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                `;

                                detail_mo_div += `  
                                    <div class="details__table">
                                        <table>
                                            <colsgroup>
                                                <col style="width:27%;">
                                                <col style="width:53%;">
                                                <col style="width:10%;">
                                            </colsgroup>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <img src="${cdn_img}${product_row.img_location}">
                                                    </td>
                                                    <td class="contsnts_td">
                                                        <div class="product__container">
                                                            <div class="items__title">
                                                                <p>${product_row.product_name} / ${product_row.color}</p>
                                                            </div>
                                                            <div class="items__info">
                                                                ${product_info}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="vertical__top entry_status_td">
                                                        <div class="order__status mobile">${product_row.status}</div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                `;
                            })
                        }
                        let detail_btn = '';
                        if(row.order_idx != "NPC" || row.purchase_status == '구매 진행중'){
                            detail_btn = `
                                <div class="detail__btn" standby-idx="${row.standby_idx}">
                                    <span data-i18n="ss_view_detail">자세히 보기</span>
                                </div>
                            `;
                        }
                        entry_pc_div = `
                            <div class="info">
                                <div class="stanby__tab__contents" standby-idx="${row.standby_idx}">
                                    <div class="contents__info">
                                        <div class="info">
                                            <span class="info__title" data-i18n="d_participated_at">응모일시</span>
                                            <span class="info__value">${row.apply_date == null ? '' : row.apply_date}</span>
                                        </div>
                                        ${detail_btn}
                                    </div>
                                    <div class="contents__table">
                                        <table>
                                            <colsgroup>
                                                <col style="width:120px;">
                                                <col style="width:360px;">
                                                <col style="width:230px;">
                                            </colsgroup>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <img src="${cdn_img + row.thumbnail_location}">
                                                    </td>
                                                    <td class="vertical__top contsnts_td">
                                                        <div class="list__container">
                                                            <div class="items__title">
                                                                <p>${row.title}</p>
                                                            </div>
                                                            <div class="items__info">
                                                                ${product_name_list}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="date__wrap">
                                                        <div class="date__container">
                                                            <div class="date__items">
                                                                <p class="items__title" data-i18n="d_date">응모기간</p>
                                                                <p class="items__info">${row.entry_start_date} - ${row.entry_end_date}</p>
                                                            </div>
                                                            <div class="date__items">
                                                                <p class="items__title" data-i18n="d_send_sns">문자발송</p>
                                                                <p class="items__info">${row.order_link_date}</p>
                                                            </div>
                                                            <div class="date__items">
                                                                <p class="items__title" data-i18n="d_purchase_date">구매기간</p>
                                                                <p class="items__info">${row.purchase_start_date} - ${row.purchase_end_date}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    ${detail_pc_div}
                                    <p class="standby_warning_msg">* 구매 내역은 마이페이지 > 주문내역에서 확인 가능합니다.</p>
                                    ${btn_div}
                                </div>
                            </div>
                        `;
                        entry_mo_div = `
                            <div class="info">
                                <div class="stanby__tab__contents" standby-idx="${row.standby_idx}">
                                    <div class="contents__info">
                                        <div class="info">
                                            <span class="info__title" data-i18n="d_participated_at">응모일시</span>
                                            <span class="info__value">${row.apply_date == null ? '' : row.apply_date}</span>
                                        </div>
                                        ${detail_btn}
                                    </div>
                                    <div class="contents__table">
                                        <table>
                                            <colsgroup>
                                                <col style="width:27%;">
                                                <col style="width:63%;">
                                            </colsgroup>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <img src="${cdn_img + row.thumbnail_location}">
                                                    </td>
                                                    <td class="contsnts_td">
                                                        <div class="list__container">
                                                            <div class="items__title">
                                                                <p>${row.title}</p>
                                                            </div>
                                                            <div class="items__info">
                                                                ${product_name_list}
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p data-i18n="d_date">
                                                            응모 기간
                                                        </p>
                                                    </td>
                                                    <td colspan="2">
                                                        <p>${row.entry_start_date} - ${row.entry_end_date}</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p data-i18n="d_send_sns">
                                                            문자발송 기간
                                                        </p>
                                                    </td>
                                                    <td colspan="2">
                                                        <p>${row.order_link_date}</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p data-i18n="d_purchase_date">
                                                            구매 기간
                                                        </p>
                                                    </td>
                                                    <td colspan="2">
                                                        <p>${row.purchase_start_date} - ${row.purchase_end_date}</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    ${detail_mo_div}
                                    <p class="standby_warning_msg">* 구매 내역은 마이페이지 > 주문내역에서 확인 가능합니다.</p>
                                    ${btn_div}
                                </div>
                            </div>
                        `;
                        $('.stanby__result__form__wrap .pc__view').append(entry_pc_div);
                        $('.stanby__result__form__wrap .mobile__view').append(entry_mo_div);
                    });
                } else {
                    let exception_msg = "";

                    switch (getLanguage()) {
                        case "KR" :
                            exception_msg = "스탠바이 응모내역이 없습니다.";
                            break;
                        
                        case "EN" :
                            exception_msg = "There is no STAND BY entry history.";
                            break;
                        
                        case "CN" :
                            exception_msg = "没有STAND BY的报名历史。";
                            break;

                    }
                    let strDiv = `
                        <div class="no_standby_list_msg">${exception_msg}</div>
                    `;

                    $('.stanby__result__form__wrap .pc__view').append(strDiv);
                    $('.stanby__result__form__wrap .mobile__view').append(strDiv);
                }

                $('.details__table').hide();
                $('.standby_warning_msg').hide();
                initDetailBtnEventHandler();
                detailBtnEventHandler();
            }
            else {
                if(d.msg != null){
                    notiModal(d.msg);
                    if (d.code = 401) {
                        $('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
                    }
                }
                else{
                    makeMsgNoti(getLanguage(), "MSG_F_ERR_0019", null);
                }
            }
        }
    });
}
function initEntryList(){
    $('.stanby__result__form__wrap .pc__view').html('');
    $('.stanby__result__form__wrap .mobile__view').html('');
}
function initDetailBtnEventHandler(){
    $('.stanby__tab__contents .detail__btn').unbind('click');
}
function detailBtnEventHandler(){
    $('.stanby__tab__contents .detail__btn').on('click', function(){
        let standby_idx = $(this).attr('standby-idx');
        let click_tab = $(`.stanby__tab__contents[standby-idx=${standby_idx}]`);

        click_tab.find('.details__table').toggle();
        click_tab.find('.standby_warning_msg').toggle();
    })
}