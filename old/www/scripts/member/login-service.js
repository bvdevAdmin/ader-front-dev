$(document).ready(function () {
    getNotice();
});

function getNotice() {
    $.ajax({
        type: "post",
        data: {
            country: getLanguage(),
        },
        dataType: "json",
        url: api_location + "common/notice/get",
        error: function (d) {
            makeMsgNoti(getLanguage(), 'MSG_F_ERR_0113', null);
        },
        success: function (d) {
            if (d.code == 200) {
                if (d.data != null && d.data.length > 0) {
                    $('.toggle__list').html('');
                    d.data.forEach(function (row) {
                        var fix_btn = '';
                        strDiv = '';

                        if (row.fix_flg == 1) {
                            fix_btn = `<img src="/images/mypage/mypage_fixed_icon.svg" style="float:left;margin-right:5px;">`;
                        }
                        else {
                            fix_btn = '';
                        }
                        strDiv = `
                    <div class="toggle__item">
                        <div class="question">
                            ${fix_btn}
                            <span>${row.title}</span>
                            <img src="/images/mypage/mypage_down_tab_btn.svg" class="down__up__icon" style="float:right;margin-top:10px;">
                        </div>
                        <div class="request" style="display:none">
                            ${row.contents}
                        </div>
                    </div>
                `;
                        $('.toggle__list').append(strDiv);
                    })
                } else {
                    makeMsgNoti(getLanguage(), 'MSG_F_ERR_0112', null);
                }
                $('.login_service_wrap .request p').css('text-align', 'left');
                $('.login_service_wrap .request img').css('width', '670px');
            } else {
                makeMsgNoti(getLanguage(), 'MSG_F_ERR_0113', null);
                if (d.code = 401) {
                    $('#exception-modal .close-btn').attr('onclick', 'location.href="/login"');
                }
            }

            $('.login_service_wrap .question').on('click', function () {
                $('.login_service_wrap .request').not($(this).next()).hide();
                $('.login_service_wrap .question').find('img.down__up__icon').attr('src', '/images/mypage/mypage_down_tab_btn.svg');

                if ($(this).next().css('display') == 'none') {
                    $(this).find('img.down__up__icon').attr('src', '/images/mypage/mypage_up_tab_btn.svg');
                }
                else {
                    $(this).find('img.down__up__icon').attr('src', '/images/mypage/mypage_down_tab_btn.svg');
                }
                $(this).next().toggle();
            });
        }
    });
}