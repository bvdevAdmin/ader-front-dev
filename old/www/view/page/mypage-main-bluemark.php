<link rel="stylesheet" href="/css/mypage/bluemark.css">
<div class="bluemark__wrap">
    <div class="tab__btn__container">
        <div class="tab__btn__item verify__form" form-id="verify__form__wrap">
            <span data-i18n="b_verify">인증</span>
        </div>
        <div class="tab__btn__item verify__list" form-id="verify__list__wrap">
            <span data-i18n="b_history">내역</span>
        </div>
    </div>
    <div class="bluemark__tab__wrap">
        <div class="bluemark__tab verify__form__wrap">
            <div class="title">
                <p class="title_name">Bluemark</p>
            </div>
            <div class="description">
                <p class="bluemark_info" data-i18n="my_b_bluemark_info_01">
                    Bluemark는 본 브랜드의 모조품으로부터 소비자의 혼란을 최소화하기 위해 제공되는 정품 인증 서비스입니다.
                </p>
                <p class="bluemark_info" data-i18n="my_b_bluemark_info_02">
                    ADER는 모조품 판매를 인지하고 소비자와 브랜드의 이미지를 보호하기 위하여 적극적으로 대응중입니다.
                </p>
                <div class="bluemark__err__msg"></div>
            </div>
            <div class="verify_form">
                <div class="bluemark-box">
                    <div class="mall-bluemark-box">
                        <div class="mall-select-box"></div>
                    </div>
                    <div class="offline-bluemark-box hidden">
                        <div class="offline-select-box"></div>
                    </div>
                    <div class="direct-input-wrap hidden">
                        <input data-i18n-placeholder="b_bluemark_purchase_mall" type="text" placeholder="구입처를 입력하세요.">
                    </div>
                    <div class="purchase-wrap calendar-bluemark dropdown hidden" data-selectdate="">
                        <div class="purchase-btn">
                            <span class="purchase-date">구매일</span>
                            <span class="tui-select-box-icon">select</span>
                        </div>
                        <div class="calendar">
                            <div class="calendar-header">
                                <button class="prev-month-btn">&lt;</button>
                                <h2 class="current-month"></h2>
                                <button class="next-month-btn">&gt;</button>
                            </div>
                            <div class="calendar-weekdays"></div>
                            <div class="calendar-days"></div>
                        </div>
                    </div>
                </div>
                <input data-i18n-placeholder="b_bluemark_serial_code" class="bluemark_serial_code" type="text" name="serial_code" placeholder="Bluemark 시리얼 코드">
            </div>
            <input class="bluemark-purchase-mall" type="hidden">
            <input class="bluemark-purchase-date" type="hidden">
            <div class="button">
                <button class="bluemark_verify_btn">VERIFY</button>
            </div>
        </div>
        <div class="bluemark__tab verify__success__wrap">
            <div class="title">
                <p>Bluemark</p>
                <div class="close_result_tab">
                    <img src="/images/mypage/tmp_img/X-12.svg" />
                </div>
            </div>
            <div class="description">
                <p data-i18n="b_bluemark_msg_01">Bluemark가 인증 된 해당 제품은 ADER 브랜드의 정품입니다.</p>
            </div>
            <div class="button">
                <div>CERTIFIED</div>
            </div>
        </div>
        <div class="bluemark__tab verify__fail__wrap">
            <div class="title">
                <p>Bluemark</p>
                <div class="close_result_tab">
                    <img src="/images/mypage/tmp_img/X-12.svg" />
                </div>
            </div>
            <div class="description fail_pc">
                <p data-i18n="b_bluemark_msg_02_1">Bluemark가 인증되지 않은 해당 제품은 ADER 브랜드의 정품이 아닌 가품입니다.</p>
                <p data-i18n="b_bluemark_msg_02_2">가품으로 의심되는 제품 또는 판매처를 발견하셨을 때에는 ADER 측에 문의 바랍니다.</p>
            </div>
            <div class="description fail_mobile">
                <p data-i18n="b_bluemark_msg_02_3">Bluemark가 인증되지 않은 해당 제품은</p>
                <p data-i18n="b_bluemark_msg_02_4">ADER 브랜드의 정품이 아닌 가품입니다.</p>
                <p data-i18n="b_bluemark_msg_02_5">가품으로 의심되는 제품 또는 판매처를 발견하셨을 때에는</p>
                <p data-i18n="b_bluemark_msg_02_6">ADER 측에 문의 바랍니다.</p>
            </div>
            <div class="button">
                <div>UNCERTIFIED</div>
            </div>
            <div class="footer">
                <p data-i18n="b_bluemark_msg_03">문의사항이 있으실 경우, 고객센터로 연락 주시기 바랍니다.</p>
                <p>customer_care@adererror.com</p>
            </div>
        </div>
        <div class="bluemark__tab verify__list__wrap">
            <div class="position__area">
                <div class="title">
                    <p>Bluemark</p>
                </div>
            </div>
            <div class="description verify_pc">
                <p data-i18n="b_bluemark_msg_04">인증된 블루마크 이력을 아래에서 확인할 수 있습니다.</p>
                <p data-i18n="b_bluemark_msg_05">블루마크 코드 양도를 희망하시는 경우 제품 양도하기를 클릭하여 정보 등록을
                    완료해 주시길 바랍니다.</p>
            </div>
            <div class="description verify_mobile">
                <p data-i18n="b_bluemark_msg_04">인증된 블루마크 이력을 아래에서 확인할 수 있습니다.</p>
                <p data-i18n="b_bluemark_msg_05">블루마크 코드 양도를 희망하시는 경우 제품 양도하기를 클릭하여<br>정보 등록을 완료해 주시길 바랍니다.</p>
                </span>
            </div>
            <form id="frm-bluemark-list">
                <input type="hidden" name="rows" value="10">
                <input type="hidden" name="page" value="1">
                <div class="contents__table">
                    <div class="pc__view">
                        <table class="border__bottom">
                            <colsgroup>
                                <col style="width:110px;">
                                <col style="width:120px;">
                                <col style="width:120px;">
                                <col style="width:130px;">
                            </colsgroup>
                            <tbody class="bluemark_list_table">
                            </tbody>
                        </table>
                        <div class="mypage__paging"></div>
                    </div>
                    <div class="mobile__view">
                        <table class="border__bottom">
                            <colsgroup>
                                <col style="width:27%;">
                                <col style="width:39%;">
                                <col style="width:34%;">
                            </colsgroup>
                            <tbody class="bluemark_list_table_mobile">
                            </tbody>
                        </table>
                        <div class="mypage__paging"></div>
                    </div>
                </div>
            </form>
            <div class="footer"></div>
            <div class="bluemark__tab voucher__handover__wrap">
                <div class="voucher__handover__wrap_container">
                    <div class="title_transfer">
                        <div class="bluemark_handover_title" data-i18n="b_transfer">제품 양도하기</div>
                        <div class="close_handover_tab">
                            <img src="/images/mypage/tmp_img/X-12.svg" />
                        </div>
                    </div>
                    <div class="description_transfer">
                        <p data-i18n="b_bluemark_msg_06">하단에 양도받을 아이디를 입력 후 버튼 클릭 시 블루마크 양도신청이 접수됩니다.</p>
                        <p data-i18n="b_bluemark_msg_07">정보는 향후 변경이 불가능하니 신청 전에 반드시 확인해 주시길 바랍니다.</p>
                    </div>
                    <div data-i18n="b_recipient_id">양도 받을 아이디</div>
                    <div class="bluemark_country">
                        <div class="country-select"></div>
                        <input id="handover_id" type="text" name="bluemark_handover_id" class="bluemark_handover_id" data-i18n-placeholder="b_recipient">
                    </div>
                    <div data-i18n="b_send" class="black_transfer_btn bluemark_idx">양도하기</div>
                    <p data-i18n="b_verify_history">인증 내역</p>
                    <div class="certified__wrap">
                        <div class="handover__info">
                            <div id="handover__info__area"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/scripts/mypage/mypage-common.js"></script>
<script src="/scripts/mypage/bluemark.js"></script>