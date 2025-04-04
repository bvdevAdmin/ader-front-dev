<div class="mileage__tab mileage__use__wrap">
    <div class="title">
        <p data-i18n="ml_used_mileage">사용 내역</p>
    </div>
    <form id="frm-mileage-use-list">
        <input class="mileage_list_rows" type="hidden" name="rows" value="10">
        <input class="mileage_list_page" type="hidden" name="page" value="1">
        <div class="contents__table">
            <div class="pc__view">
                <table class="border__bottom border__bottom__th">
                    <colsgroup>
                        <col style="width:120px;">
                        <col style="width:120px;">
                        <col style="width:120px;">
                        <col style="width:120px;">
                        <col style="width:120px;">
                        <col style="width:110px;">
                    </colsgroup>
                    <thead>
                        <th>
                            <p data-i18n="m_date">일자</p>
                        </th>
                        <th>
                            <p data-i18n="m_order_number">주문번호</p>
                        </th>
                        <th>
                            <p data-i18n="m_history">내용</p>
                        </th>
                        <th>
                            <p data-i18n="m_price">구매금액</p>
                        </th>
                        <th>
                            <p data-i18n="ml_used_a">사용</p>
                        </th>
                        <th>
                            <p data-i18n="m_balance">잔액</p>
                        </th>
                    </thead>
                    <tbody class="mileage_use_list_web">
                    </tbody>
                </table>
                <div class="mypage__paging"></div>
            </div>
            <div class="mobile__view">
                <table class="border__bottom border__bottom__th">
                    <colsgroup>
                        <col style="width:25%;">
                        <col style="width:25%;">
                        <col style="width:25%;">
                        <col style="width:25%;">
                    </colsgroup>
                    <thead>
                        <th>
                            <p data-i18n="m_order_number">주문번호</p>
                        </th>
                        <th>
                            <p data-i18n="m_date">일자</p>
                        </th>
                        <th>
                            <p data-i18n="m_history">내용</p>
                        </th>
                        <th>
                            <p data-i18n="m_point"></p>
                        </th>
                    </thead>
                    <tbody class="mileage_use_list_mobile">
                    </tbody>
                </table>
                <div class="mypage__paging"></div>
            </div>
        </div>
    </form>
</div>

<script src="/scripts/mypage/mileage/mileage-use.js"></script>
