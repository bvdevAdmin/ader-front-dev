let bluemarkMallSelectBox = null;
let bluemarkOfflineSelectBox = null;
document.addEventListener('DOMContentLoaded', function () {
    countrySelectBox();
    getBluemarkList();

    bluemarkChangeEvent();
    makeCalendar("bluemark");
    addInitEvent();
    addVerifyBluemarkEvent();
    addHandoverBluemarkEvent();
    selectClickEventHandler();
});

let countryBluemark = null;

function countrySelectBox() {
    countryBluemark = new tui.SelectBox('.country-select', {
        data: [
            {
                label: 'KR',
                value: 'KR'
            },
            {
                label: 'EN',
                value: 'EN'
            },
            {
                label: 'CN',
                value: 'CN'
            }
        ],
        autofocus: false
    });
}

function addHandoverBluemarkEvent() {
    let handoverBtn = document.querySelector(".black_transfer_btn.bluemark_idx");

    handoverBtn.addEventListener("click", function () {
        handoverBluemark(this);
    });
}

function addVerifyBluemarkEvent() {
    let verifyBtn = document.querySelector(".bluemark_verify_btn");

    verifyBtn.addEventListener("click", function () {
        verifyBluemark();
    });
}

function selectClickEventHandler() {
    bluemarkMallSelectBox.on("open", function () {
        bluemarkOfflineSelectBox.close();
    });
    bluemarkOfflineSelectBox.on("open", function () {
        bluemarkMallSelectBox.close();
    });
}

function addInitEvent() {
    let bluemarkWrap = document.querySelector(".bluemark__wrap");
    let tabBtn = bluemarkWrap.querySelectorAll(".tab__btn__item");
    let closeResultBtn = bluemarkWrap.querySelectorAll(".close_result_tab");
    let closeHandoverBtn = bluemarkWrap.querySelector(".close_handover_tab");

    tabBtn.forEach(btn => btn.addEventListener("click", function () {
        tabClickTmp(this);
    }));

    closeResultBtn.forEach(btn => btn.addEventListener("click", function () {
        closeResultTab();
    }));

    closeHandoverBtn.addEventListener("click", function () {
        closeHandoverTab();
    });
}

function tabClickTmp(obj) {
    let formId = obj.getAttribute("form-id");

    if (formId == "verify__form__wrap") {
        $('.verify__list__wrap').hide();
        $('.verify__form__wrap').show();
        closeHandoverTab();
    } else {
        $('.verify__form__wrap').hide();
        $('.verify__list__wrap').show();
        initVerifyForm();
    }
}

function getBluemarkInfo(obj) {
    $('.bluemark__tab').not('.verify__list__wrap').hide();
    $('.voucher__handover__wrap').show();

    let idx = $(obj).attr('bluemark_idx');

    $.ajax({
        type: "post",
        url: api_location + "product/bluemark/get",
        data: {
            'bluemark_idx': idx,
            'country': getLanguage()
        },
        dataType: "json",
        error: function () {
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0033', null);
            //notiModal("블루마크 인증내역 조회에 실패했습니다.");
        },
        success: function (d) {
            let code = d.code;

            if (code == 200) {
                $('#handover__info__area').html('');
                let data = d.data;

                if (data != null) {
                    $('.bluemark_idx').attr('bluemark_idx', data.bluemark_idx);

                    var strDiv = `
                        <div class="flex__row">
                            <p>${data.product_name}</p>
                            <p>${data.member_id}</p>
                        </div>
                        <div class="flex__row">
                            <p>${data.color}</p>
                            <p>${data.reg_date}</p>
                        </div>
                        <div class="flex__row">
                            <p><span class="certified_btn">CERTIFIED</span>
                            <p>${data.serial_code}</p>
                        </div>
                    `;

                    $('#handover__info__area').append(strDiv);
                }
            }
        }
    });
}

function verifyBluemark() {
    let country = getLanguage();
    let mall_type = $('.mall-select-box .tui-select-box-selected').attr('data-value');
    let verify_form = document.querySelector(".verify_form");
    let serial_code = verify_form.querySelector(".bluemark_serial_code").value;
    let purchase_mall = $('.bluemark-purchase-mall').val();
    let purchase_date = $('.bluemark-purchase-date').val();

    let mallSelectMsg = "";
    let mallMsg = "";
    let dateMsg = "";
    let serialMsg = "";

    switch (country) {
        case "KR":
            mallSelectMsg = "구입처를 선택해주세요.";
            mallMsg = "구입처를 입력해주세요.";
            dateMsg = "구매 날짜를 입력해주세요.";
            serialMsg = "시리얼 코드를 입력해주세요.";
            break;
        case "EN":
            mallSelectMsg = "Please select a place to purchase.";
            mallMsg = "Please enter the place of purchase.";
            dateMsg = "Please enter the purchase date.";
            serialMsg = "Please enter the serial code.";
            break;
        case "CN":
            mallSelectMsg = "请选择购买地点。";
            mallMsg = "请输入购买地点。";
            dateMsg = "请输入购买日期。";
            serialMsg = "请输入序列号。";
            break;
    }

    if (typeof mall_type != 'undefined') {
        if (purchase_mall == null || purchase_mall.length == 0) {
            $('.bluemark__err__msg').text(mallMsg);
            return false
        }
        if (mall_type > 1) {
            if (purchase_date == null || purchase_date.length == 0) {
                $('.bluemark__err__msg').text(dateMsg);
                return false;
            }
        }
        if (serial_code == null || serial_code.length == 0) {
            $('.bluemark__err__msg').text(serialMsg);
            return false;
        }


        $.ajax({
            type: "post",
            url: api_location + "product/bluemark/add",
            data: {
                "mall_type": mall_type,
                "country": country,
                "serial_code": serial_code,
                "purchase_mall": purchase_mall,
                "purchase_date": purchase_date
            },
            dataType: "json",
            error: function () {
                makeMsgNoti(getLanguage(), 'MSG_F_ERR_0031', null);
                //notiModal("블루마크 인증처리에 실패했습니다.");
            },
            success: function (d) {
                $('.bluemark__tab').hide();
                let code = d.code;
                if (code == 200) {
                    getBluemarkList();
                    $('.verify__success__wrap').show();
                } else {
                    $('.verify__fail__wrap').show();
                }
            }
        });
    }
    else {
        $('.bluemark__err__msg').text(mallSelectMsg);
        return false;
    }
}

function closeResultTab() {
    $('.bluemark__tab').hide();
    $('.verify__form__wrap').show();
    $('.verify__list__wrap').hide();
    $('.verify__form__wrap').show();
    initVerifyForm();
}

function getBluemarkList() {
    var use_form = $('#frm-bluemark-list');
    let listTable = $('.bluemark_list_table');
    let listTableMobile = $('.bluemark_list_table_mobile');

    var rows = use_form.find('input[name="rows"]').val();
    var page = use_form.find('input[name="page"]').val();

    $.ajax({
        type: "post",
        data: {
            "country": getLanguage(),
            'rows': rows,
            'page': page
        },
        dataType: "json",
        url: api_location + "product/bluemark/list/get",
        error: function () {
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0035', null);
            //notiModal("블루마크 내역 조회처리에 실패했습니다.");
        },
        success: function (d) {

            if (d.code == 200) {
                let data = d.data;

                listTable.html('');
                listTableMobile.html('');

                if (data != null) {
                    let strDiv = "";
                    let strMobileDiv = "";
                    let product_color_html = "";

                    data.forEach((row) => {
                        let color_rgb = row.color_rgb;
                        let multi = color_rgb.split(";");

                        if (multi.length === 2) {
                            product_color_html = `
                                <div class="color-line" style="--background-color:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
                                    <p class="color-name">${row.color}</p>
                                    <div class="color multi" data-title="${row.color}"></div>
                                </div>
                            `;
                        } else {
                            product_color_html = `
                                <div class="color-line" style="--background-color:${multi[0]}">
                                    <p class="color-name">${row.color}</p>
                                    <div class="color" data-title="${row.color}"></div>
                                </div>
                            `;
                        }

                        strDiv += `
                            <tr class="bluemark_list_tr">
                                <td>
                                    <img class="bluemark_list_img" src="${cdn_img}${row.img_location}">
                                </td>
                                <td class="vertical__top">
                                    <p>${row.serial_code}</p>
                                    <p>${row.product_name}</p>
                                    <p>${row.sales_price}</p>
                                    ${product_color_html}
                                    <p>${row.option_name}</p>
                                </td>
                                <td class="bluemark_purchase_td">
                                    <p class="bluemark_purchase_date">${row.reg_date}</p>
                                    <p>${row.purchase_mall}</p>
                                </td>
                                <td class="handover__btn">
                                    <div class ="b_transfer_btn" data-i18n="b_transfer" bluemark_idx="${row.bluemark_idx}">
                                        제품 양도하기
                                    </div>
                                </td>
                            </tr>
                        `;

                        strMobileDiv += `
                            <tr class="bluemark_list_tr">
                                <td>
                                    <img class="bluemark_list_img" src="${cdn_img}${row.img_location}">
                                </td>
                                <td class="vertical__top">
                                    <p>${row.serial_code}</p>
                                    <p>${row.product_name}</p>
                                    <p>${row.sales_price}</p>
                                    ${product_color_html}
                                    <p>${row.option_name}</p>
                                </td>
                                <td class="handover__btn">
                                    <p>${row.reg_date}</p>
                                    <p>${row.purchase_mall}</p>
                                    <div class ="b_transfer_btn" data-i18n="b_transfer" bluemark_idx="${row.bluemark_idx}">
                                        제품 양도하기
                                    </div>
                                </td>
                            </tr>
                        `;
                    });

                    listTable.append(strDiv);
                    listTableMobile.append(strMobileDiv);

                    addOpenhandoverTabEvent();

                    var showing_page = Math.ceil(d.total / rows);
                    mypagePaging({
                        total: d.total,
                        el: use_form.find(".mypage__paging"),
                        page: page,
                        row: rows,
                        show_paging: showing_page,
                        use_form: use_form
                    }, getBluemarkList);

                    changeLanguageR();
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
                            exception_msg = "没有查询到相关资料。";
                            break;

                    }

                    listTable.append(`
                        <tr>
                            <td class="bluemark_no_history_td" colspan="4">
                                <p>${exception_msg}</p>
                            </td>
                        </tr>
                    `);

                    listTableMobile.append(`
                        <tr>
                            <td class="bluemark_no_history_td" colspan="3">
                                <p>${exception_msg}</p>
                            </td>
                        </tr>
                    `);
                }
            } else {
                makeMsgNoti(getLanguage(), 'MSG_F_ERR_0035', null);
                //notiModal("블루마크 내역 조회처리에 실패했습니다.");
            }
        }
    });
}

function addOpenhandoverTabEvent() {
    let openTabBtn = document.querySelectorAll(".b_transfer_btn");

    openTabBtn.forEach(btn => btn.addEventListener("click", function () {
        getBluemarkInfo(this);
    }));
}

function handoverBluemark(obj) {
    let bluemark_idx = $(obj).attr('bluemark_idx');
    let handover_id = $('#handover_id').val();
    let country = $('.country-select .tui-select-box-selected').attr('data-value');

    if (country == null || country.length < 1) {
        makeMsgNoti(getLanguage(), 'MSG_F_WRN_0051', null);
        //notiModal(selectCountryMsg);
        return false;
    }

    if (handover_id == null || handover_id.length < 1) {
        makeMsgNoti(getLanguage(), 'MSG_F_WRN_0021', null);
        //notiModal(handoverIdMsg);
        return false;
    }

    $.ajax({
        type: "post",
        url: api_location + "product/bluemark/put",
        data: {
            "bluemark_idx": bluemark_idx,
            "handover_id": handover_id,
            "country": country
        },
        dataType: "json",
        error: function () {
            closeHandoverTab();
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0034', null);
            //notiModal(handoverFailMSg);
        },
        success: function (d) {
            let code = d.code;
            if (code == 200) {
                getBluemarkList();
                closeHandoverTab();
                makeMsgNoti(getLanguage(), 'MSG_F_INF_0005', null);
                //notiModal(handoverSuccessMsg);
            } else {
                if (d.msg != null) {
                    notiModal(d.msg);
                }
                else {
                    makeMsgNoti(getLanguage(), 'MSG_F_ERR_0034', null);
                }
                closeHandoverTab();
            }
        }
    });
}

// 닫기버튼
function closeHandoverTab() {
    let initText = $("#handover_country option").eq(0).text();
    $('.voucher__handover__wrap').hide();
    $('#handover__info__area').html("");
    $('#handover_country').val("KR");
    $('.bluemark_country .select-selected').text(initText);
    $('#handover_id').val("");
}

// 블루마크 구매처 셀렉트 박스 설정
let mallSelectPlaceholder = "";
let mailSelectParam = [];

switch (getLanguage()) {
    case "KR":
        mallSelectPlaceholder = "구매처";
        mailSelectParam = [
            { label: "자사 온라인 몰", value: '1'},
            { label: "자사 오프라인 몰", value: '2'},
            { label: "W컨셉", value: '3'},
            { label: "카카오", value: '4'},
            { label: "트렌비", value: '5'},
            { label: "기타", value: '6'}
        ]
        break;

    case "EN":
        mallSelectPlaceholder = "Mall";
        mailSelectParam = [
            { label: "ADER Online Mall", value: '1'},
            { label: "ADER Offline Mall", value: '2'},
            { label: "W CONCEPT", value: '3'},
            { label: "KAKAO", value: '4'},
            { label: "TRENBE", value: '5'},
            { label: "ETC", value: '6'}
        ]
        break;

    case "CN":
        mallSelectPlaceholder = "购买处";
        mailSelectParam = [
            { label: "官网", value: '1'},
            { label: "实体店​", value: '2'},
            { label: "微信小程序​", value: '3'},
            { label: "天猫旗舰店", value: '4'},
            { label: "其他", value: '5'}
        ]

        break;
}

bluemarkMallSelectBox = new tui.SelectBox('.mall-select-box', {
    placeholder: mallSelectPlaceholder,
    data: mailSelectParam,
    autofocus: false
});

bluemarkOfflineSelectBox = new tui.SelectBox('.offline-select-box', {
    data: [
        {
            label: 'ADER Seongsu Space',
            value: '1'
        },
        {
            label: 'ADER Hongdae Space',
            value: '2'
        },
        {
            label: 'ADER Sinsa Space',
            value: '3'
        },
        {
            label: 'ADER Seomyeon Space',
            value: '4'
        },
        {
            label: 'ADER Haeundae Plug Shop',
            value: '5'
        },
        {
            label: 'ADER Daejeon Plug Shop',
            value: '6'
        },
        {
            label: 'ADER Hannam Plug Shop',
            value: '7'
        }

    ],
    autofocus: false
});

function bluemarkChangeEvent() {
    let mallInput = document.querySelector(".bluemark-purchase-mall");
    let direct = document.querySelector(".direct-input-wrap");
    let directMallInput = direct.querySelector("input");

    bluemarkMallSelectBox.on("change", ev => {
        mallInput.value = "";
        let mall_select_label = ev.curr.getLabel();
        let mall_select_value = ev.curr.getValue();
        $(".mall-bluemark-box").find(".tui-select-box-placeholder").addClass("tui-selected");

        purchaseDateStatus(mall_select_value);

        function purchaseDateStatus(mall_select_value) {
            let offline = document.querySelector(".offline-bluemark-box");
            let dateDrop = document.querySelector(".calendar-bluemark.dropdown");

            initForm();
            if (mall_select_value == 1) {
                direct.classList.add("hidden");
                directMallInput.value = "";
                offline.classList.add("hidden");
                dateDrop.classList.add("hidden");
                mallInput.value = mall_select_label;
            } else if (mall_select_value == 2) {
                direct.classList.add("hidden");
                directMallInput.value = "";
                offline.classList.remove("hidden");
                dateDrop.classList.remove("hidden");
                mallInput.value = "ADER Seongsu Space";
            } else if (mall_select_value == 3 || mall_select_value == 4 || mall_select_value == 5) {
                offline.classList.add("hidden");
                direct.classList.add("hidden");
                directMallInput.value = "";
                dateDrop.classList.remove("hidden");
                mallInput.value = mall_select_label;
            } else if (mall_select_value == 6) {
                offline.classList.add("hidden");
                direct.classList.remove("hidden");
                dateDrop.classList.remove("hidden");
                directMallInput.value = "";
            }
        }
    });

    bluemarkOfflineSelectBox.on("change", ev => {
        let offline_select_label = ev.curr.getLabel();
        mallInput.value = offline_select_label;
        $(".offline-bluemark-box").find(".tui-select-box-placeholder").addClass("tui-selected");
    });
    directMallInput.addEventListener("input", () => {
        document.querySelector(".bluemark-purchase-mall").value = directMallInput.value;
    });
}

function initVerifyForm() {
    let direct = document.querySelector(".direct-input-wrap");
    let offline = document.querySelector(".offline-bluemark-box");
    let dateDrop = document.querySelector(".calendar-bluemark.dropdown");
    $('.mall-select-box .tui-select-box-selected').removeClass('tui-select-box-selected');

    direct.classList.add("hidden");
    offline.classList.add("hidden");
    dateDrop.classList.add("hidden");

    document.querySelector(".mall-bluemark-box .tui-select-box-placeholder").innerHTML = mallSelectPlaceholder;
    document.querySelector('.mall-select-box select').value = null;
    bluemarkMallSelectBox.dropdown.selectedItem = null;
    initForm();
}

function initForm() {
    $('.verify_form').find('input').val('');
    $('.bluemark-purchase-mall').val('');
    $('.bluemark-purchase-date').val('');
    $('.bluemark__err__msg').text('');
    resetDate();
    changeLanguageR();
}

function resetDate() {
    let dateInput = document.querySelector(".bluemark-purchase-date");
    let purchaseDate = document.querySelector(".purchase-date");

    dateInput.value = "";
    purchaseDate.dataset.selectdate = "";
    purchaseDate.innerHTML = "구매일";
    purchaseDate.dataset.i18n = "b_bluemark_purchase_date";
}