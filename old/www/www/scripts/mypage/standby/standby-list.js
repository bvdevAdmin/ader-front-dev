document.addEventListener('DOMContentLoaded', function () {
	getTotalStandby();
});

function getTotalStandby() {
    $.ajax({
        url: config.api + "mypage/standby/list/get",
        type: "post",
        data: {'country': getLanguage()},
        dataType: "json",
        error: function () {
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0059", null);
        //  notiModal("스탠바이", '목록을 불러오지 못했습니다.');
        },
        success: function (d) {
            let code = d.code;
            
            if (code == 200) {
                var data = d.data;

                if (data != null && data.length > 0) {

                    data.forEach(function (row) {
                        var usable_class = row.entry_status=='종료'?'non__usable__info':'usable__info';
                        
                        var entry_date_str = '';

                        if (row.entry_status == 'Comming soon') {
                            entry_date_str = 'Comming soon';
                            row.entry_status = '';
                        }
                        else {
                            var entry_start_date_arr = [];
                            var entry_end_date_arr = [];
                            entry_start_date_arr = row.entry_start_date.split(' ');
                            entry_end_date_arr = row.entry_end_date.split(' ');

                            var entry_date_str = '';
                            if (entry_start_date_arr[0] == entry_end_date_arr[0]) {
                                entry_date_str = entry_start_date_arr[0] + ' ' + entry_start_date_arr[1] + ' ~ ' + entry_end_date_arr[1];
                            }
                            else {
                                entry_date_str = row.entry_start_date + ' ~ ' + row.entry_end_date;
                            }
                        }

                        var strDiv = '';
                        let entryStatusStr = "";

                        switch (getLanguage()) {
                            case "KR" :
                                entryStatusStr = row.entry_status;
                                break;
                            case "EN" : 
                                if(row.entry_status == "Coming soon") {
                                    entryStatusStr = "Coming soon";
                                } else if(row.entry_status == "진행 중") {
                                    entryStatusStr = "In process";
                                } else if(row.entry_status == "종료") {
                                    entryStatusStr = "Ended";
                                }
                                break;
                            case "CN" :
                                if(row.entry_status == "Coming soon") {
                                    entryStatusStr = "马上就来";
                                } else if(row.entry_status == "진행 중") {
                                    entryStatusStr = "进行中";
                                } else if(row.entry_status == "종료") {
                                    entryStatusStr = "结束";
                                }
                                break;
                        }

                        strDiv = `
                            <div class="stanby__item ${usable_class}">
                                <img class="standby__thumbnail" idx="${row.standby_idx}" src="${cdn_img + row.thumbnail_location}">
                                <p class="item__title">${row.title}</p>
                                <p class="item__description">${entry_date_str}</p>
                                <p class="item__status">${entryStatusStr}</p>
                            </div>
                        `;
                        $('.stanby__container').append(strDiv);
                    });
                    standbyLinkInitHandler();
                    standbyLinkClickHandler();
                } else {
                    let exception_msg = "";

                    switch (getLanguage()) {
                        case "KR" :
                            exception_msg = "진행중인 스탠바이가 없습니다.";
                            break;
                        
                        case "EN" :
                            exception_msg = "There is no history.";
                            break;
                        
                        case "CN" :
                            exception_msg = "没有查询到相关资料。​";
                            break;

                    }
                    let strDiv = `
                        <div class="no_standby_list_msg">
                            ${exception_msg}
                        </div>
                    `;

                    $('.stanby__apply__form__wrap .info__wrap').append(strDiv);
                }
            }
            else {
                if(d.msg != null){
                    notiModal(d.msg);
                    if (d.code = 401) {
                        $('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
                    }
                }
                else{
                    makeMsgNoti(getLanguage(), "MSG_F_ERR_0059", null);
                }
            }
        }
    });
}
function standbyLinkInitHandler(){
    $('.usable__info').find('.standby__thumbnail').unbind('click');
    $('.non__usable__info').find('.standby__thumbnail').unbind('click');
}
function standbyLinkClickHandler(){
    $('.usable__info').find('.standby__thumbnail').on('click', function(){
        let standby_idx = $(this).attr('idx');
        location.href='/standby/entry?standby_idx=' + standby_idx;
    });
    $('.non__usable__info').find('.standby__thumbnail').on('click', function(){
        makeMsgNoti(getLanguage(), "MSG_F_WRN_0010", null);
        // let err_str = '종료된 스탠바이입니다.';
        // notiModal("스탠바이", err_str);
    });
}