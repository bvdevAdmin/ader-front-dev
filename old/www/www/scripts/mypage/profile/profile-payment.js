document.addEventListener("DOMContentLoaded", function () {
    addPaymentBtnEvent();
    paymentSelectBox();
});

function addPaymentBtnEvent() {
    $(".add_new_payment_btn").on("click", function () {
        $('.profile__tab').hide();
        $('.profile__payment__update__wrap').show();
    });

    $(".profile__payment__update__wrap .close").on("click", function () {
        $('.profile__payment__update__wrap').hide();
        $('.profile__payment__wrap').show();
    });
}

/** 결제수단 불러오기 */
function getPaymentList() {

}
/** 결제수단 추가하기 */
function addPayment() {
    let payName = "";
}
/** 결제수단 업데이트하기 */
function putPayment() {

}
/** 결제수단 지우기 */
function deletePayment() {

}
/** 카드 유효성 체크 함수 */
function checkCardType(cardNumber) {
    // 카드 번호 유효성 검사
    if (/[^0-9-\s]+/.test(cardNumber)) {
        return "invalid";
    }

    // 카드 종류 체크
    if (/^4/.test(cardNumber)) {
        return "Visa";
    } else if (/^5[1-5]/.test(cardNumber)) {
        return "MasterCard";
    } else if (/^3[47]/.test(cardNumber)) {
        return "American Express";
    } else if (/^(?:2131|1800|35\d{3})\d{11}$/.test(cardNumber)) {
        return "JCB";
    } else {
        return "unknown";
    }
}

let paymentMonth = null;
let paymentYear = null;

function paymentSelectBox() {

    paymentMonth = new tui.SelectBox('.payment-select-box.month', {
        placeholder: '12',
        data: [
            {
                label: '1',
                value: '1'
            },
            {
                label: '2',
                value: '2'
            },
            {
                label: '3',
                value: '3'
            },
            {
                label: '4',
                value: '4'
            },
            {
                label: '5',
                value: '5'
            },
            {
                label: '6',
                value: '6'
            },
            {
                label: '7',
                value: '7'
            },
            {
                label: '8',
                value: '8'
            },
            {
                label: '9',
                value: '9'
            },
            {
                label: '10',
                value: '10'
            },
            {
                label: '11',
                value: '11'
            },
            {
                label: '12',
                value: '12'
            }
        ],
        autofocus: false
    });

    paymentYear = new tui.SelectBox('.payment-select-box.year', {
        placeholder: '2028',
        data: [
            {
                label: '2023',
                value: '2023'
            },
            {
                label: '2024',
                value: '2024'
            },
            {
                label: '2025',
                value: '2025'
            },
            {
                label: '2026',
                value: '2026'
            },
            {
                label: '2027',
                value: '2027'
            },
            {
                label: '2028',
                value: '2028'
            },
            {
                label: '2029',
                value: '2029'
            }
        ],
        autofocus: false
    });

    paymentMonth.on('open', function () {
        paymentYear.close();
    })
    paymentYear.on('open', function () {
        paymentMonth.close();
    })


    // function getPaymentData() {

    //     paymentMonth.select();
    //     paymentYear.select();
    // }

    // let placeholder_txt = "";

    // deliveryCompany = new tui.SelectBox('.housing-company-list', {
    //     placeholder: placeholder_txt,
    //     data: [
    //         {
    //             label: 'DHL',
    //             value: '1'
    //         }
    //     ],
    //     autofocus: false
    // });
    // break;

}

