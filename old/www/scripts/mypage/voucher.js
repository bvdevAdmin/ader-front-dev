document.addEventListener("DOMContentLoaded", function () {
    voucherPossessionListGet();
    voucherUseListGet();
    addVoucherRegistEvent();
});

function addVoucherRegistEvent() {
    let btn = document.querySelector(".voucher_regist_btn");

    btn.addEventListener("click", voucherIssue);
}

function voucherPossessionListGet() {
    $.ajax({
        type: "post",
        url: api_location + "mypage/voucher/list/get",
        data: {
            "list_type": "possession"
        },
        dataType: "json",
        error: function () {
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0059', null);
            //notiModal("바우처", "목록을 불러오지 못했습니다.");
        },
        success: function (d) {
            let code = d.code;

            if (code == 200) {
                let data = d.data;
                let voucherList = $(".voucher_possession_list");
                let strDiv = "";
                voucherList.html("");

                if (data != null) {

                    data.forEach(row => {
                        strDiv += `
                            <div class="voucher_possession_content">
                                <div class="voucher_possession_row">
                                    <p>${row.voucher_issue_code}</p>
                                    <p>${row.usable_start_date} - ${row.usable_end_date}</p>
                                </div>
                                <div class="voucher_possession_row">
                                    <p>${row.voucher_name}</p>
                                    <span class="voucher_left_days_wrap">
                                        <p>${row.date_interval}</p><p class="gray__font" data-i18n="v_days_left">일 남음</p>
                                    </span>
                                </div>
                                <span>${row.sale_price_type}<p data-i18n="v_percent_off"></p></span> 
                                <span class="voucher_use_description">
                                    <p class="voucher_target_product" data-i18n="v_voucher_limit_price_01">· 바우처 대상 제품</p>
                                    <p>${row.min_price}</p>
                                    <p data-i18n="v_voucher_limit_price_02">원 초과 구매 시 사용 가능</p>
                                </span>
                            </div>
                        `;
                    });

                    voucherList.append(strDiv);
                } else {
                    let exception_msg = "";

                    switch (getLanguage()) {
                        case "KR":
                            exception_msg = "조회 결과가 없습니다.";
                            break;

                        case "EN":
                            exception_msg = "There is no history.";
                            break;

                        case "CN":
                            exception_msg = "没有查询到相关资料。​";
                            break;

                    }

                    strDiv = `
                        <div class="no_history_msg">${exception_msg}</div>
                    `;

                    voucherList.append(strDiv);
                }
                changeLanguageR();
            }
            if (code == 401) {
                if (d.msg != null) {
                    notiModal(d.msg);
                    $('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
                }
                else {
                    makeMsgNoti(getLanguage(), 'MSG_F_ERR_0059', null);
                }
            }
        }
    });
}

function voucherUseListGet() {
    $.ajax({
        type: "post",
        url: api_location + "mypage/voucher/list/get",
        data: {
            "list_type": "use"
        },
        dataType: "json",
        error: function () {
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0059', null);
            //notiModal("바우처", "목록을 불러오지 못했습니다.");
        },
        success: function (d) {
            let code = d.code;

            if (code == 200) {
                let data = d.data;
                let voucherList = $(".voucher_use_list");
                let strDiv = "";
                voucherList.html("");

                if (data != null) {

                    data.forEach(row => {
                        let useDate = "";
                        let useEndMsg = "";
                        let expired = ""
                        switch (getLanguage()) {
                            case "KR":
                                useEndMsg = "사용기간 만료";
                                break;
                            case "EN":
                                useEndMsg = "Expired";
                                break;
                            case "CN":
                                useEndMsg = "过期";
                                break;
                        }

                        if (row.date_interval < 0 && row.used_flg == 0) {
                            useDate = useEndMsg;
                            expired = "expired";
                        } else {
                            useDate = `
                                <span data-i18n="v_used_on">사용일</span>
                                <span>${row.update_date}</span>
                            `;
                            expired = '';
                        }

                        strDiv += `
                            <div class="voucher_use_content ${expired}">
                                <div class="info__title__container">
                                    <div class="info__title__item">
                                        <span data-i18n="v_voucher_code">바우처번호</span>
                                        <span>${row.voucher_issue_code}</span>
                                    </div>
                                    <div class="info__title__item use_date_wrap">${useDate}</div>
                                </div>
                                <div class="info__body__container">
                                    <p>${row.voucher_name}</p>
                                    <p>${row.sale_price_type}</p> 
                                    <span class="voucher_description">
                                        <p data-i18n="v_voucher_limit_price_01">· 바우처 대상 제품</p>
                                        &nbsp;${row.min_price}
                                        <p data-i18n="v_voucher_limit_price_02">원 초과 구매 시 사용 가능</p>
                                    </span>
                                </div>
                            </div>    
                        `;
                    });

                    voucherList.append(strDiv);
                } else {
                    let exception_msg = "";

                    switch (getLanguage()) {
                        case "KR":
                            exception_msg = "조회 결과가 없습니다.";
                            break;

                        case "EN":
                            exception_msg = "There is no history.";
                            break;

                        case "CN":
                            exception_msg = "没有查询到相关资料。​";
                            break;

                    }

                    strDiv = `
                        <div class="no_history_msg_voucher_use">${exception_msg}</div>  
                    `;

                    voucherList.append(strDiv);
                }
                changeLanguageR();
            }
            if (code == 401) {
                if (d.msg != null) {
                    notiModal(d.msg);
                    $('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
                }
                else {
                    makeMsgNoti(getLanguage(), 'MSG_F_ERR_0059', null);
                }
            }
        }
    });
}

function voucherIssue() {
    let voucher_issue_code = $('#voucher_issue_code').val();

    if (voucher_issue_code == '') {
        makeMsgNoti(getLanguage(), 'MSG_F_WRN_0043', null);
        //notiModal(voucherMsg);
        return false;
    }
    $.ajax({
        type: "post",
        url: api_location + "mypage/voucher/issue/add",
        data: {
            'voucher_issue_code': voucher_issue_code
        },
        dataType: "json",
        error: function () {
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0068', null);
            //notiModal("바우처", "등록에 실패했습니다. 다시 진행해주세요.");
        },
        success: function (d) {
            if (d.code == 200) {
                voucherPossessionListGet();
                $('#voucher_issue_code').val("");
                makeMsgNoti(getLanguage(), 'MSG_F_INF_0012', null);
                //notiModal("바우처", "바우처 등록에 성공했습니다.");
            } else {
                if (d.msg != null) {
                    $('#voucher_issue_code').val("");
                    notiModal("바우처", d.msg);
                    if (d.code == 401) {
                        $('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
                    }
                }
                else {
                    makeMsgNoti(getLanguage(), 'MSG_F_ERR_0068', null);
                }
            }
        }
    });
}