<style>
    .draw__wrap {
        margin-top: 40px;
        width: 100%;
        display: grid;
        grid-template-columns: repeat(16, 1fr);
    }

    .draw__wrap .title p {
        font-size: 13px;
    }

    .draw__wrap .title {
        margin-top: 0px;
        margin-bottom: 10px;
    }

    .draw__wrap td p {
        height: auto;
    }

    .draw__tab__btn__container {
        grid-column: 1/17;
        margin: 0 auto;
        display: grid;
        gap: 10px;
        grid-template-columns: 70px 50px 60px 50px 60px;
        font-size: 11px;
    }

    .draw__tab.draw__apply__form__wrap {
        width: 710px;
    }

    .draw__apply__form__wrap {
        margin: 0 auto;
    }

    .draw__tab .description {
        padding-left: 6px;
    }

    .draw__container {
        margin: 0 auto;
        width: 100%;
        gap: 10px;
        display: grid;
        place-items: center;
        grid-template-columns: 1fr 1fr 1fr
    }

    .draw__item {
        width: 100%;
        padding-top: 30px;
    }

    .draw__item img {
        width: 100%;
    }

    .draw__item .item__title {
        margin-top: 15px;
        font-size: 13px;
        font-family: var(--ft-no-fu);
    }

    .draw__item .item__description {
        margin-top: 10px;
        font-size: 11px;
        font-family: var(--ft-no-fu);
    }

    .draw__item .item__status {
        margin-top: 20px;
        font-size: 11px;
        font-family: var(--ft-no-fu);
        margin-bottom: 30px;
    }

    .draw__tab__contents {
        width: 100%;
        margin: 0 auto;
        padding-top: 0px;
        padding-bottom: 100px;
    }

    .draw__wrap .draw_product_name {
        width: 70px;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }

    .draw__tab__contents .vertical__middle {
        height: 156px;
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        justify-content: center;
    }

    .draw__wrap .footer {
        margin-bottom: 100px;
    }

    .date__cols__title {
        margin-top: 0px;
    }

    .date__cols__info {
        margin-top: 40px;
    }

    .draw__notice__wrap {
        width: 485px;
        margin: 0 auto;
    }

    .draw__tab.draw__ongoing__wrap .footer p {
        font-family: var(--ft-no-fu);
        font-size: 11px;
        font-weight: normal;
        font-stretch: normal;
        font-style: normal;
        line-height: normal;
        letter-spacing: normal;
        text-align: left;
    }

    .draw__apply__form__wrap .description p {
        margin-left: 6px;
        margin-bottom: 0;
    }

    @media (max-width: 1024px) {
        .draw__tab__wrap {
            grid-column: 1/17;
            width: 100%;
            margin: 0 auto;
            margin-top: 40px;
        }

        .draw__tab.draw__apply__form__wrap {
            width: 100%;
        }

        .draw__container {
            margin: 0 auto;
            width: 100%;
            gap: 10px;
            display: grid;
            place-items: center;
            grid-template-columns: repeat(2, 1fr);
        }

        .draw__wrap {
            margin-top: 20px;
        }

        .draw__wrap .mobile__view {
            margin: 0;
        }

        .contents__info .info span {
            font-size: 11px;
        }

        .draw__item .item__title {
            margin-top: 10px;
        }

        .draw__item .item__description {
            margin-top: 5px;
        }

        .draw__item .item__status {
            margin-top: 10px;
            margin-bottom: 40px;
        }

        .draw__tab__contents {
            padding-bottom: 60px
        }

        .draw__tab__contents table td {
            padding-top: 0px;
            padding-bottom: 0px;
        }

        .draw__tab__contents table img {
            margin-bottom: 10px;
        }

        .draw__wrap .contents__table {
            padding-top: 10px;
            margin-bottom: 10px;
        }
    }

    @media (min-width: 600px) {
        .draw__tab__wrap {
            grid-column: 1/17;
            width: 580px;
            margin: 0 auto;
            margin-top: 40px;
        }
    }

    @media (min-width: 1024px) {
        .draw__tab__wrap {
            grid-column: 1/17;
            width: 950px;
            margin: 0 auto;
            margin-top: 50px;
        }

        .draw__wrap .contents__table {
            padding-top: 0px;
            margin-bottom: 10px;
        }
    }

    @media (max-width: 350px) {
        .draw__wrap .swiper-slide.tab__btn__item {
            padding: 0 15px 0 15px
        }

        .draw__tab__btn__container {
            display: none;
        }
    }

    @media (min-width: 350px) {
        .draw__wrap .swiper.tab__btn {
            display: none;
        }
    }
</style>
<div class="draw__wrap">
    <div class="draw__tab__btn__container">
        <div class="tab__btn__item" form-id="draw__apply__form__wrap" onclick="getTotalDraw()">
            <span data-i18n="d_enter_draw">응모</span>
        </div>
        <div class="tab__btn__item" form-id="draw__result__wrap" onclick="getEntryDraw('result')">
            <span data-i18n="d_all">전체</span>
        </div>
        <div class="tab__btn__item" form-id="draw__ongoing__wrap" onclick="getEntryDraw('ongoing')">
            <span data-i18n="d_in_process">진행중</span>
        </div>
        <div class="tab__btn__item" form-id="draw__win__wrap" onclick="getEntryDraw('win')">
            <span data-i18n="d_won">당첨</span>
        </div>
        <div class="tab__btn__item" form-id="draw__not__win__wrap" onclick="getEntryDraw('not__win')">
            <span data-i18n="d_lost">미당첨</span>
        </div>
    </div>
    <div class="swiper tab__btn">
        <div class="swiper-wrapper">
            <div class="swiper-slide tab__btn__item" form-id="draw__apply__form__wrap" onclick="getTotalDraw()">
                <span data-i18n="d_enter_draw">응모</span>
            </div>
            <div class="swiper-slide tab__btn__item" form-id="draw__result__wrap" onclick="getEntryDraw('result')">
                <span data-i18n="d_all">전체</span>
            </div>
            <div class="swiper-slide tab__btn__item" form-id="draw__ongoing__wrap" onclick="getEntryDraw('ongoing')">
                <span data-i18n="d_in_process">진행중</span>
            </div>
            <div class="swiper-slide tab__btn__item" form-id="draw__win__wrap" onclick="getEntryDraw('win')">
                <span data-i18n="d_won">당첨</span>
            </div>
            <div class="swiper-slide tab__btn__item" form-id="draw__not__win__wrap" onclick="getEntryDraw('not__win')">
                <span data-i18n="d_lost">미당첨</span>
            </div>
        </div>
    </div>
    <div class="draw__tab__wrap">
        <div class="draw__tab draw__apply__form__wrap">
            <div class="title">
                <p data-i18n="d_draw_status_enter_draw">드로우 응모하기</p>
            </div>
            <p class="next__line__exist">
            <div class="description">
                <div class="pc__view">
                    <div class="flex_text" style="margin-left: -6px;">·&nbsp;&nbsp;&nbsp;
                        <p data-i18n="d_draw_msg_01" style="margin-left:0;">안내 문구 삽입 필요</p>
                    </div>
                    <p data-i18n="d_draw_msg_02"></p>
                    <p data-i18n="d_draw_msg_03"></p>
                    <p data-i18n="d_draw_msg_04"></p>
                    </span>
                </div>
                <div class="mobile__view">
                    <div class="flex_text" style="margin-left: -6px;">·&nbsp;&nbsp;&nbsp;
                        <p data-i18n="d_draw_msg_05" style="margin-left:0;">안내 문구 삽입 필요</p>
                    </div>
                    <p data-i18n="d_draw_msg_06"></p>
                    <p data-i18n="d_draw_msg_07"></p>
                    <p data-i18n="d_draw_msg_08"></p>
                    <p data-i18n="d_draw_msg_09"></p>
                    </span>
                </div>

            </div>
            <div class="info__wrap">
                <div class="draw__container">
                </div>
            </div>
            <div class="footer"></div>
        </div>
        <div class="draw__tab draw__result__wrap">
            <div class="pc__view">
                <div class="info__wrap">
                </div>
            </div>
            <div class="mobile__view">
                <div class="info__wrap">
                </div>
            </div>
            <div class="footer"></div>
        </div>
        <div class="draw__tab draw__ongoing__wrap">
            <div class="pc__view">
                <div class="info__wrap"></div>
            </div>
            <div class="mobile__view">
                <div class="info__wrap"></div>
            </div>
            <div class="footer">
                <span class="flex_text">·&nbsp;<p data-i18n="d_draw_after_msg_01">드로우 응모 이후 사이즈 수정은 불가합니다.</p></span>
                <span class="flex_text">·&nbsp;<p data-i18n="d_draw_after_msg_02">당첨자에 한하여 개별 메시지로 연락드립니다.</p></span>
                <span class="flex_text">·&nbsp;<p>스팸 메시지로 등록 시 메시지 수신이 제한될 수 있습니다.</p></span>
            </div>
        </div>
        <div class="draw__tab draw__win__wrap">
            <div class="pc__view">
                <div class="info__wrap"></div>
            </div>
            <div class="mobile__view">
                <div class="info__wrap"></div>
            </div>
        </div>
        <div class="draw__tab draw__not__win__wrap">
            <div class="pc__view">
                <div class="info__wrap"></div>
            </div>
            <div class="mobile__view">
                <div class="info__wrap"></div>
            </div>
            <div class="footer"></div>
        </div>
    </div>
</div>

<script>
    $('.draw__result__form__wrap').hide();
    $('.draw__notice__wrap').hide();

    function getTotalDraw() {
        $('.draw__container').html('');
        $.ajax({
            url: config.api + "mypage/draw/list/get",
            type: "post",
            data: {
                'country':getLanguage()
            },
            dataType: "json",
            error: function () {
                alert('드로우 등록 처리중 오류가 발생했습니다.');
            },
            success: function (d) {
                let code = d.code;
                if (code == 200) {
                    $('.draw__container').html('');
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
                                if (entry_start_data_arr[0] == entry_end_data_arr[0]) {
                                    entry_date_str = entry_start_data_arr[0] + ' ' + entry_start_data_arr[1] + ' ~ ' + entry_end_data_arr[1];
                                }
                                else {
                                    entry_date_str = row.entry_start_date + ' ~ ' + row.entry_end_date;
                                }
                            }

                            var strDiv = '';
                            strDiv = `
                            <div class="draw__item ${usable_class}">
                                <img src="https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user${row.img_location}">
                                <p class="item__title">${row.product_name}</p>
                                <p class="item__description">${entry_date_str}</p>
                                <p class="item__status">${row.entry_status}</p>
                            </div>
                        `;
                            $('.draw__container').append(strDiv);
                        })
                    }
                }
                else {
                    let err_str = '드로우목록을 불러오지 못했습니다.';
                    if (d.msg != null) {
                        err_str = d.msg;
                    }
                    exceptionHandling("드로우", err_str);
                    $('#exception-modal .close-btn').attr('onclick', 'location.href="/login"');
                }
            }
        });
    }

    function getEntryDraw(type) {
        $('.draw__' + type + '__wrap .pc__view .info__wrap').html('');
        $('.draw__' + type + '__wrap .mobile__view .info__wrap').html('');

        var entry_status = '';
        switch (type) {
            case 'result':
                entry_status = 'ALL';
                break;
            case 'ongoing':
                entry_status = 'ONG';
                break;
            case 'win':
                entry_status = 'PRZ';
                break;
            case 'not__win':
                entry_status = 'NWN';
                break;
        }
        $.ajax({
            url: config.api + "mypage/draw/entry/get",
            type: "post",
            dataType: "json",
            data: { 
                'entry_status': entry_status,
                'country':getLanguage()
             },
            error: function () {
                alert('드로우 신청내역 오류가 발생했습니다.');
            },
            success: function (d) {
                let code = d.code;
                if (code == 200) {
                    $('.draw__' + type + '__wrap .pc__view .info__wrap').html('');
                    $('.draw__' + type + '__wrap .mobile__view .info__wrap').html('');
                    var data = d.data;
                    if (data != null && data.length > 0) {
                        data.forEach(function (row) {
                            var btnStr = '';
                            if (row.purchase_status == '구매하기') {
                                btnStr = `<button class="black__full__width__btn" style = "font-size:11px";>${row.purchase_status}</button>`;
                            }
                            else {
                                btnStr = `<button class="white__full__width__btn"  "font-size:11px">${row.purchase_status}</button>`;
                            }

                            strDivPc = `
                            <div class="info">
                                <div class="draw__tab__contents">
                                    <div class="contents__info">
                                        <div class="info">
                                            <span class="info__title">응모일시</span>
                                            <span class="info__value">${row.apply_date == null ? '' : row.apply_date}</span>
                                        </div>
                                    </div>
                                    <div class="contents__table">
                                        <table>
                                            <colsgroup>
                                                <col style="width:120px">
                                                <col style="width:120px">
                                                <col style="width:240px">
                                                <col style="width:240px">
                                                <col style="width:190px">
                                                <col style="width:40px;">
                                            </colsgroup>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <img src="https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user${row.img_location}">
                                                    </td>
                                                    <td class="vertical__middle">
                                                        <p class="draw_product_name">${row.product_name}</p>
                                                        <p>${row.sales_price}</p>
                                                        <p>${row.color}</p>
                                                        <p style="margin-bottom: 0;">${row.option_name}</p>
                                                    </td>
                                                    <td class="vertical__top">
                                                        <div class="date__cols__title">
                                                            <p>응모 기간</p>
                                                        </div>
                                                        <div class="date__cols__info">
                                                            <p>${row.entry_start_date}</p>
                                                            <p>- ${row.entry_end_date}</p>
                                                        </div>
                                                    </td>
                                                    <td class="vertical__top">
                                                        <div class="date__cols__title">
                                                            <p>당첨 발표일</p>
                                                        </div>
                                                        <div class="date__cols__info">
                                                            <p>${row.announce_date}</p>
                                                        </div>
                                                    </td>
                                                    <td class="vertical__top">
                                                        <div class="date__cols__title">
                                                            <p>구매 기간</p>
                                                        </div>
                                                        <div class="date__cols__info">
                                                            <p>${row.purchase_start_date}</p>
                                                            <p>- ${row.purchase_end_date}</p>
                                                        </div>
                                                    </td>
                                                    <td style="text-align:right;padding-right:0px;">
                                                        <p>${row.draw_status}</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    ${btnStr}
                                </div>
                            </div>
                        `;

                            strDivMobile = `
                            <div class="info">
                                <div class="draw__tab__contents">
                                    <div class="contents__info">
                                        <div class="info">
                                            <span class="info__title">응모일시</span>
                                            <span class="info__value">${row.apply_date == null ? '' : row.apply_date}</span>
                                        </div>
                                    </div>
                                    <div class="contents__table">
                                        <table>
                                            <colsgroup>
                                                <col style="width:27%;">
                                                <col style="width:43%;">
                                                <col style="width:20%;">
                                            </colsgroup>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <img src="https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user${row.img_location}">
                                                    </td>
                                                    <td>
                                                        <p class="draw_product_name">${row.product_name}</p>
                                                        <p>${row.sales_price}</p>
                                                        <p>${row.color}</p>
                                                        <p>${row.option_name}</p>
                                                    </td>
                                                    <td>
                                                        <p>${row.draw_status}</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p>
                                                            응모 기간
                                                        </p>
                                                    </td>
                                                    <td colspan="2">
                                                        <p>${row.entry_start_date} - ${row.entry_end_date}</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p>
                                                            당첨 발표일
                                                        </p>
                                                    </td>
                                                    <td colspan="2">
                                                        <p>${row.announce_date}</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p>
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
                                    ${btnStr}
                                </div>
                            </div>
                        `;
                            $('.draw__' + type + '__wrap .pc__view .info__wrap').append(strDivPc);
                            $('.draw__' + type + '__wrap .mobile__view .info__wrap').append(strDivMobile);
                        })
                    }
                }
                else {
                    let err_str = '드로우 응모내역을 불러오지 못했습니다';
                    if (d.msg != null) {
                        err_str = d.msg;
                    }
                    exceptionHandling("드로우", err_str);
                    $('#exception-modal .close-btn').attr('onclick', 'location.href="/login"');

                }
            }
        });
    }
</script>