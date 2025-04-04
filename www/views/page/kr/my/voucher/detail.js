let t_column = {
    KR : {
        t_01 : "일 남음",
        t_02 : "사용종료"
    },
    EN : {
        t_01 : " days left",
        t_02 : "End of use"
    },
}

let txt_none = {
    KR : {
        avail   : "사용 가능한 바우처 내역이 존재하지 않습니다.",
        used    : "조회 가능한 사용완료 바우처 내역이 존재하지 않습니다."
    },
    EN : {
        avail   : "There is no available voucher history",
        used    : "There is no used voucher history"
    }
}

$(document).ready(function () {
    $.ajax({
        url: config.api + "/voucher/get",
        headers : {
            country : config.language
        },
        headers: {
            'country' : config.language
         },
        success: function (d) {
            if (d.code == 200) {
                if (d.data) {
                    if (d.data.voucher_usable != null && d.data.voucher_usable.length > 0) {
                        d.data.voucher_usable.forEach(row => {
                            const usable_class = row.usable_flg && row.date_interval > 0 ?'':'no-usable'
    
                            const date_interval = row.date_interval;
                            const expire_text   = date_interval > 0 ? `${date_interval}${t_column[config.language]['t_01']}` : date_interval == 0 ? getTodayDate() : row.date_interval < 0 ? t_column[config.language]['t_02'] : ''

                            $("#list-1").append(`
                                <li>
                                    <p class="${usable_class}">${row.voucher_name}</p>
                                    <div class="sale ${usable_class}">${row.sale_price_type}</div>
                                    <div class="date ${usable_class}">${row.usable_start_date} - ${row.usable_end_date}</div>
                                    <div class="expire ${usable_class}">${expire_text}</div>
                                </li>
                            `);
                        });
                    } else {
                        $("#list-1").append(`
                            <div class="list__none">
                                ${txt_none[config.language]['avail']}
                            </div>
                        `);
                    }
                    
                    if (d.data.voucher_used != null && d.data.voucher_used.length > 0) {
                        d.data.voucher_used.forEach(row => {
                            $("#list-2").append(`
                                <li>
                                    <p class="no-usable">${row.voucher_name}</p>
                                    <div class="sale no-usable">${row.sale_price_type}</div>
                                    <div class="date no-usable">${row.usable_start_date} - ${row.usable_end_date}</div>
                                    <div class="expire no-usable">${t_column[config.language]['t_02']}</div>
                                </li>
                            `);
                        });
                    } else {
                        $("#list-2").append(`
                            <div class="list__none">
                                ${txt_none[config.language]['used']}
                            </div>
                        `);
                    }
                }
            } else {
                alert(d.msg);
            }
        }
    });
});

function getTodayDate() {
    const today = new Date();
    const year  = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day   = String(today.getDate()).padStart(2, '0');
    return `${year}.${month}.${day}`;
}