document.addEventListener("DOMContentLoaded", function () {
    mileageGetSummary();
    getMileageTotalList();
});

function mileageGetSummary() {
    $.ajax({
        type: "post",
        data: {
            "country": getLanguage()
        },
        dataType: "json",
        url: api_location + "mypage/mileage/get",
        error: function (d) {
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0066', null);
            // notiModal("마일리지", "마일리지 정보조회에 실패했습니다.");
        },
        success: function (d) {
            let code = d.code;

            if (code == 200) {
                if (d.data != null) {
                    let data = d.data;

                    let mileage_point = document.querySelector("#mileage_point p");
                    let used_mileage = document.querySelector("#used_mileage p");
                    let refund_scheduled = document.querySelector("#refund_scheduled p");

                    mileage_point.textContent = data.mileage_balance;
                    used_mileage.textContent = data.refund_scheduled;
                    refund_scheduled.textContent = data.used_mileage;
                }
            } else {
                if (d.msg != null) {
                    notiModal("마일리지", d.msg);
                    if (d.code = 401) {
                        $('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
                    }
                }
                else {
                    makeMsgNoti(getLanguage(), 'MSG_F_ERR_0066', null);
                }
            }
        }
    });
}

function getMileageTotalList() {
    let mileageTotalWrap = document.querySelector(".mileage__total__wrap");
    let mileageTotalListWeb = mileageTotalWrap.querySelector(".mileage_total_list_web");
    let mileageTotalListMobile = mileageTotalWrap.querySelector(".mileage_total_list_mobile");
    let showPaging = mileageTotalWrap.querySelector(".mypage__paging");
    let rows = mileageTotalWrap.querySelector(".mileage_list_rows").value;
    let page = mileageTotalWrap.querySelector(".mileage_list_page").value;

    mileageTotalListWeb.innerHTML = "";
    mileageTotalListMobile.innerHTML = "";

    $.ajax({
        type: "post",
        data: {
            "country": getLanguage(),
            "list_type": "ALL",
            "rows": rows,
            "page": page
        },
        dataType: "json",
        url: api_location + "mypage/mileage/list/get",
        error: function () {
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0066", null);
            // notiModal("마일리지", "마일리지 정보조회에 실패했습니다.");
        },
        success: function (d) {
            let data = d.data;
            let total = d.total;

            if (data != null) {
                mileageTotalListWeb.innerHTML = writeMileageListHtmlWeb(data, "ALL");
                mileageTotalListMobile.innerHTML = writeMileageListHtmlMobile(data, "ALL");

                let showing_page = Math.ceil(total / rows);
                mypagePaging({
                    total: total,
                    el: showPaging,
                    page: page,
                    row: rows,
                    show_paging: showing_page,
                    use_form: mileageTotalWrap,
                    list_type: "ALL"
                }, getMileageTotalList);
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

                mileageTotalListWeb.innerHTML =
                    `
                        <tr class="mileage_list_tr">
                            <td class="mileage_no_history" colspan="6">
                                <p>${exception_msg}</p>
                            </td>
                        </tr>
                    `;

                mileageTotalListMobile.innerHTML =
                    `
                        <tr class="mileage_list_tr">
                            <td class="mileage_no_history" colspan="4">
                                <p>${exception_msg}</p>
                            </td>
                        </tr>
                    `;
            }
        }
    });

}