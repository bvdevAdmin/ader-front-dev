<link rel="stylesheet" href="/css/mypage/reorder.css">
<div class="reorder__wrap">
    <div class="tab__btn__container">
        <div class="tab__btn__item reorder_tab" form-id="reorder__apply__wrap" data-list_type="apply">
            <span data-i18n="r_reorder_history">신청내역</span>
        </div>
        <div class="tab__btn__item reorder_tab" form-id="reorder__alarm__wrap" data-list_type="alarm">
            <span data-i18n="r_reorder_notified">알림완료</span>
        </div>
        <div class="tab__btn__item reorder_tab" form-id="reorder__cancel__wrap" data-list_type="cancel">
            <span data-i18n="r_reorder_cancelled">취소완료</span>
        </div>
    </div>
    <div class="reorder__tab__wrap">
        <div class="reorder__tab reorder__apply__wrap">
            <div class="title">
                <p data-i18n="r_reorder_noti_history"></p>
            </div>
            <div class="description reorder__apply_pc">
                <p data-i18n="r_reorder_msg_01">해당 제품이 재입고되면 메시지를 발송해 드립니다.</p>
                <p data-i18n="r_reorder_msg_02">스팸 메시지로 등록 시 메시지 수신이 제한될 수 있습니다.</p>
                <p data-i18n="r_reorder_msg_03">재입고 알림을 신청하시면 회원님의 SMS 수신 동의 여부와 관계없이 발송됩니다.</p>
            </div>
            <div class="description reorder__apply_mobile">
                <p data-i18n="r_reorder_msg_01">해당 제품이 재입고되면 메시지를 발송해드립니다.</p>
                <p data-i18n="r_reorder_msg_02">스팸 메시지로 등록 시 SMS 발송이 제한될 수 있습니다.</p>
                <p data-i18n="r_reorder_msg_03">재입고 알림을 신청하시면 회원님의 SMS 수신 동의 여부와 관계없이 발송됩니다.</p>
            </div>
            <form id="frm-reorder-list">
                <input type="hidden" name="rows" value="10">
                <input type="hidden" name="page" value="1">
                <div class="contents__table" style="padding: 0;">
                    <div class="pc__view">
                        <table class="border__bottom">
                            <colgroup>
                                <col style="width:120px;">
                                <col style="width:120px;">
                                <col style="width:110px;">
                                <col style="width:110px;">
                                <col style="width:110px;">
                                <col style="width:140px;">
                            </colgroup>
                            <tbody id="apply_reorder_result_table" class="reorder__result__table">
                            </tbody>
                        </table>
                        <div class="mypage__paging"></div>
                    </div>
                    <div class="mobile__view">
                        <table class="border__bottom">
                            <colgroup>
                                <col style="width:27%;">
                                <col style="width:27%;">
                                <col style="width:20%;">
                                <col style="width:26%;">
                            </colgroup>
                            <tbody id="apply_reorder_result_table_mobile" class="reorder__result__table">
                            </tbody>
                        </table>
                        <div class="mypage__paging"></div>
                    </div>
                </div>
            </form>
            <div class="footer"></div>
        </div>
        <div class="reorder__tab reorder__alarm__wrap">
            <div class="title">
                <p data-i18n="r_reorder_notified_history">알림완료 내역</p>
            </div>
            <div class="contents__table">
                <div class="pc__view">
                    <table class="border__bottom">
                        <colgroup>
                            <col style="width:120px;">
                            <col style="width:120px;">
                            <col style="width:110px;">
                            <col style="width:110px;">
                            <col style="width:110px;">
                            <col style="width:140px;">
                        </colgroup>
                        <tbody id="alarm_reorder_result_table" class="reorder__result__table">
                        </tbody>
                    </table>
                </div>
                <div class="mobile__view">
                    <table class="border__bottom">
                        <colgroup>
                            <col style="width:27%;">
                            <col style="width:27%;">
                            <col style="width:20%;">
                            <col style="width:26%;">
                        </colgroup>
                        <tbody id="alarm_reorder_result_table_mobile" class="reorder__result__table">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="footer"></div>
        </div>
        <div class="reorder__tab reorder__cancel__wrap">
            <div class="title">
                <p data-i18n="r_reorder_noti_cancelled">알림취소 내역</p>
            </div>
            <div class="contents__table">
                <div class="pc__view">
                    <table class="border__bottom">
                        <colgroup>
                            <col style="width:120px;">
                            <col style="width:120px;">
                            <col style="width:110px;">
                            <col style="width:110px;">
                            <col style="width:110px;">
                            <col style="width:140px;">
                        </colgroup>
                        <tbody id="cancel_reorder_result_table" class="reorder__result__table">
                        </tbody>
                    </table>
                </div>
                <div class="mobile__view">
                    <table class="border__bottom">
                        <colgroup>
                            <col style="width:27%;">
                            <col style="width:27%;">
                            <col style="width:20%;">
                            <col style="width:26%;">
                        </colgroup>
                        <tbody id="cancel_reorder_result_table_mobile" class="reorder__result__table">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="footer"></div>
        </div>
    </div>
</div>

<script src="/scripts/mypage/reorder.js"></script>