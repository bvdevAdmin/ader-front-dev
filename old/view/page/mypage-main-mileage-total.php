<div class="mileage__tab mileage__total__wrap">
    <div class="title">
        <p data-i18n="ml_mileage_history"></p>
    </div>
    <div class="contents__table bluemark total">
        <table class="border__bottom__th" style="width:100%">
            <colsgroup>
                <col style="width:240px;">
                <col style="width:240px;">
                <col style="width:230px;">
            </colsgroup>
            <thead>
                <th>
                    <p data-i18n="ml_current_mileage"></p>
                </th>
                <th>
                    <p data-i18n="ml_complete_mileage"></p>
                </th>
                <th>
                    <p data-i18n="ml_awaiting_refund"></p>
                </th>
            </thead>
            <tbody id="mileage_summary_table">
                <td id="mileage_point">
                    <p>0</p>
                </td>
                <td id="used_mileage">
                    <p>0</p>
                </td>
                <td id="refund_scheduled">
                    <p>0</p>
                </td>
            </tbody>
        </table>
    </div>
    <div class="description tab__total" style="margin-left: -6px;">
        <p data-i18n="ml_mileage_msg_01"></p>
        <p data-i18n="ml_mileage_msg_02"></p>
    </div>
    <div class="mileage_history_list_wrap">
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
                            <p data-i18n="m_date"></p>
                        </th>
                        <th>
                            <p data-i18n="m_order_number"></p>
                        </th>
                        <th>
                            <p data-i18n="m_history"></p>
                        </th>
                        <th>
                            <p data-i18n="m_price"></p>
                        </th>
                        <th>
                            <p data-i18n="ml_earned"></p>
                        </th>
                        <th>
                            <p data-i18n="ml_used_a"></p>
                        </th>
                    </thead>
                    <tbody class="mileage_total_list_web">
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
                            <p data-i18n="m_order_number"></p>
                        </th>
                        <th>
                            <p data-i18n="m_date"></p>
                        </th>
                        <th>
                            <p data-i18n="m_history"></p>
                        </th>
                        <th>
                            <p data-i18n="m_point"></p>
                        </th>
                    </thead>
                    <tbody class="mileage_total_list_mobile">
                    </tbody>
                </table>
                <div class="mypage__paging"></div>
            </div>
        </div>
    </div>
</div>

<script src="/scripts/mypage/mileage/mileage-total.js"></script>
