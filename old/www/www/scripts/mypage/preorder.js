document.addEventListener('DOMContentLoaded', function () {
    getTotalPreorder();
    getEntryPreorder();
});

function getTotalPreorder() {
    $('.preorder__container').html('');
    $.ajax({
    	type: "post",
        url: config.api + "mypage/preorder/list/get",
        dataType: "json",
        error: function () {
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0004', null);
            //notiModal('스탠바이 등록 처리중 오류가 발생했습니다.');
        },
        success: function (d) {
            let code = d.code;

            if (code == 200) {
                $('.preorder__container').html('');
                var data = d.data;

                if (data != null && data.length > 0) {
                    data.forEach(function (row) {
                        var usable_class = '';

                        if (row.entry_status == '종료') {
                            usable_class = 'non__usable__info'
                        }

                        var entry_date_str = '';

                        if (row.entry_status == 'Comming soon') {
                            entry_date_str = 'Comming soon';
                            row.entry_status = '';
                        }
                        else {
                            var entry_start_date_arr = [];
                            var entry_end_date_arr = [];
                            entry_start_data_arr = row.entry_start_date.split(' ');
                            entry_end_data_arr = row.entry_end_date.split(' ');

                            var entry_date_str = '';

                            if (row.entry_start_date == null) {
                                entry_date_str = 'Coming soon';
                            }
                            else {
                                if (entry_start_data_arr[0] == entry_end_data_arr[0]) {
                                    entry_date_str = entry_start_data_arr[0];
                                }
                                else {
                                    entry_date_str = entry_start_data_arr[0] + ' - ' + entry_end_data_arr[0];
                                }
                            }
                        }

                        var strDiv = '';
                        let entryStatusStr = "";

                        switch(getLanguage()) {
                            case "KR":
                                entryStatusStr = row.entry_status;
                                break;
                            case "EN":
                                if(row.entry_status == "Coming soon") {
                                    entryStatusStr = "Coming soon";
                                } else if(row.entry_status == "진행 중") {
                                    entryStatusStr = "Ongoing";
                                } else if(row.entry_status == "종료") {
                                    entryStatusStr = "End";
                                }
                                break;
                            case "CN":
                                if(row.entry_status == "Coming soon") {
                                    entryStatusStr = "马上就来";
                                } else if(row.entry_status == "진행 중") {
                                    entryStatusStr = "正在进行的活动";
                                } else if(row.entry_status == "종료") {
                                    entryStatusStr = "结束";
                                }
                                break;
                        }
                        strDiv = `
                        <div class="preorder__item ${usable_class}">
                            <img src="${cdn_img}${row.img_location}">
                            <p class="item__title">${row.product_name}</p>
                            <p class="item__description">${entry_date_str}</p>
                            <p class="item__status">${entryStatusStr}</p>
                        </div>
                    `;
                        $('.preorder__container').append(strDiv);
                    });
                } else {
                    let exception_msg = "";

                    switch (getLanguage()) {
                        case "KR" :
                            exception_msg = "진행중인 프리오더가 없습니다.";
                            break;
                        
                        case "EN" :
                            exception_msg = "There are no ongoing pre-orders.";
                            break;
                        
                        case "CN" :
                            exception_msg = "当前没有正在进行的预售。";
                            break;

                    }
                    let strDiv = `
                        <div class="no_preorder_list_msg">
                            ${exception_msg}
                        </div>
                    `;

                    $('.preorder__apply__form__wrap .info__wrap').append(strDiv);
                }
            }
        }
    });
}

function getEntryPreorder() {
    $('.preorder__result__form__wrap .pc__view').html('');
    $('.preorder__result__form__wrap .mobile__view').html('');
    $.ajax({
    	type: "post",
    	url: config.api + "mypage/preorder/entry/get",
        dataType: "json",
        error: function () {
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0004', null);
            //notiModal('프리오더 내역불러오기 오류가 발생했습니다.');
        },
        success: function (d) {
            let code = d.code;

            if (code == 200) {
                $('.preorder__result__form__wrap .pc__view').html('');
                $('.preorder__result__form__wrap .mobile__view').html('');
                var data = d.data;

                if (data != null && data.length > 0) {
                    data.forEach(function (row) {
                        var order_status_str = '';

                        if (row.order_status != null) {
                            var order_status_str = '';

                            switch (row.order_status) {
                                case 'PCP':
                                    order_status_str = '결제완료';
                                    break;
                                case 'PPR':
                                    order_status_str = '상품준비';
                                    break;
                                case 'DPR':
                                    order_status_str = '배송준비';
                                    break;
                                case 'DPG':
                                    order_status_str = '배송중';
                                    break;
                                case 'DCP':
                                    order_status_str = '배송완료';
                                    break;
                                case 'POP':
                                    order_status_str = '프리오더 준비';
                                    break;
                                case 'POD':
                                    order_status_str = '프리오더 상품 생산';
                                    break;
                                case 'OCC':
                                    order_status_str = '주문 취소';
                                    break;
                                case 'OEX':
                                    order_status_str = '주문 교환';
                                    break;
                                case 'OEP':
                                    order_status_str = '주문 교환 완료';
                                    break;
                                case 'ORF':
                                    order_status_str = '주문 환불';
                                    break;
                                case 'ORP':
                                    order_status_str = '주문 환불 완료';
                                    break;
                            }
                        }

                        strDivPc = `
                        <div class="info">
                            <div class="preorder__tab__contents">
                                <div class="contents__info">
                                    <div class="info">
                                        <span class="info__title" data-i18n="o_order_number">주문번호</span>
                                        <span class="info__value">${row.order_code == null ? '' : row.order_code}</span>
                                    </div>
                                    <div class="info">
                                        <span class="info__title" data-i18n="o_order_date">주문일</span>
                                        <span class="info__value">${row.order_date == null ? '' : row.order_date.split(' ')[0]}</span>
                                    </div>
                                    <div class="detail__btn"><span data-i18n="o_view_details">자세히 보기</span></div>
                                </div>
                                <div class="contents__table">
                                    <table>
                                        <colsgroup>
                                            <col style="width:120px">
                                            <col style="width:240px">
                                            <col style="width:120px">
                                            <col style="width:120px">
                                            <col style="width:120px">
                                            <col style="width:120px">
                                            <col style="width:110px">
                                        </colsgroup>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <img src="${cdn_img}${row.img_location}">
                                                </td>
                                                <td>
                                                    <p>${row.product_name}</p>
                                                </td>
                                                <td>
                                                    <div class="color_wrap">
                                                        <p>${row.color}</p>
                                                        <div class="color_chip" style="background-color:${row.color_rgb}"></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p>${row.option_name}</p>
                                                </td>
                                                <td>
                                                    <p>Qty: ${row.product_qty}</p>
                                                </td>
                                                <td>
                                                    <p>${row.sales_price}</p>
                                                </td>
                                                <td>
                                                    <p>${order_status_str}</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                        var order_detail_str = '';
                        switch (row.order_status) {
                            case '주문완료':
                                order_detail_str = `
                                <div class="detail__btn" style="display:flex;align-items: center;gap: 10px;justify-content: space-between;margin-bottom:10px;">
                                    <p style="margin-bottom:0px;">주문완료</p>
                                    <img src="/images/mypage/mypage_order_cancel_btn.svg">
                                </div>
                                <p class="detail__info">배송준비 단계로 넘어가면 취소 불가합니다.</p>
                            `;
                                break;
                            case '배송중':
                                order_detail_str = `
                                <div class="detail__btn" style="display:flex;justify-content:space-between;gap: 10px;margin-bottom:10px;">
                                    <p style="margin-bottom:0px;" data-i18n="o_shipped">배송 중</p>
                                    
                                </div>
                                <p class="detail__info underline">${company_name}<br>${company_tel}</p>
                            `;
                                break;
                            case '배송완료':
                                order_detail_str = `
                                <div class="detail__btn" style="display:flex;align-items: center;gap: 10px;justify-content: space-between;margin-bottom:10px;">
                                    <p style="margin-bottom:0px;">배송완료</p>
                                    <img src="/images/mypage/mypage_return_apply_btn.svg">
                                </div>
                                <p class="detail__info" data-i18n="o_return_msg">반품접수는 제품 수령 후 7일 이내 가능합니다.</p>
                            `;
                        }
                        strDivMobile = `
                        <div class="info">
                            <div class="preorder__tab__contents">
                                <div class="contents__info">
                                    <div class="info">
                                        <span class="info__title" data-i18n="o_order_number">주문번호</span>
                                        <span class="info__value">${row.order_code == null ? '' : row.order_code}</span>
                                    </div>
                                    <div class="info">
                                        <span class="info__title" data-i18n="o_order_date">주문일</span>
                                        <span class="info__value">${row.order_date == null ? '' : row.order_date.split(' ')[0]}</span>
                                    </div>
                                    <div class="detail__btn"><span data-i18n="o_view_details">자세히 보기</span></div>
                                </div>
                                <div class="contents__table" style="margin-top: 10px;">
                                    <table>
                                        <colsgroup>
                                            <col style="width:27%;">
                                            <col style="width:30%;">
                                            <col style="width:10%;">
                                            <col style="width:33%;">
                                        </colsgroup>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <img src="${cdn_img}${row.img_location}">
                                                </td>
                                                <td class="preorder_info_mob">
                                                    <p class="preorder_product_name">${row.product_name}</p>
                                                    <p>${row.sales_price}</p>
                                                    <div class="color_wrap">
                                                        <p>${row.color}</p>
                                                        <div class="color_chip" style="background-color:${row.color_rgb}"></div>
                                                    </div>
                                                    <p>${row.option_name}</p>
                                                </td>
                                                <td>
                                                    <p>Qty:${row.product_qty}</p>
                                                </td>
                                                <td class="status__info">
                                                    ${order_detail_str}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                        $('.preorder__result__form__wrap .pc__view').append(strDivPc);
                        $('.preorder__result__form__wrap .mobile__view').append(strDivMobile);
                    });
                } else {
                    let exception_msg = "";

                    switch (getLanguage()) {
                        case "KR" :
                            exception_msg = "프리오더 신청내역이 없습니다.";
                            break;
                        
                        case "EN" :
                            exception_msg = "There is no Pre-order apply history.";
                            break;
                        
                        case "CN" :
                            exception_msg = "没有预售申请记录。";
                            break;

                    }

                    let strDiv = `
                        <div class="no_preorder_list_msg">
                            ${exception_msg}
                        </div>
                    `;

                    $('.preorder__result__form__wrap .pc__view').append(strDiv);
                    $('.preorder__result__form__wrap .mobile__view').append(strDiv);
                }
            }
        }
    });
}