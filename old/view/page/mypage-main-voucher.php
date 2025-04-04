<link rel="stylesheet" href="/css/mypage/voucher.css">
<div class="voucher__wrap">
    <div class="tab__btn__container">
        <div class="tab__btn__item" form-id="voucher__amount__form__wrap">
            <span data-i18n="v_my_voucher">보유 현황</span>
        </div>
        <div class="tab__btn__item" form-id="use__voucher__form__wrap">
            <span data-i18n="v_used_history">사용 현황</span>
        </div>
        <div class="tab__btn__item" form-id="voucher__regist__form__wrap">
            <span data-i18n="v_registration">바우처 등록</span>
        </div>
        <div class="tab__btn__item" form-id="voucher__notice__form__wrap">
            <span data-i18n="ml_notice">유의사항</span>
        </div>
    </div>
    <div class="voucher__tab__wrap">
        <div class="voucher__tab voucher__regist__form__wrap">
            <div class="title">
                <p data-i18n="v_registration">바우처 등록</p>
            </div>
            <div class="description">
                <span data-i18n="v_voucher_msg_01">발급받은 바우처 번호를 입력하세요.</span>
            </div>
            <div class="form voucher_input_wrap">
                <input type="text" id="voucher_issue_code">
                <div class="black__full__width__btn voucher_regist_btn" data-i18n="v_register">등록</div>
            </div>
            <div class="footer">
                <p data-i18n="v_voucher_msg_02">바우처의 발급 및 사용 기간을 꼭 확인해 주세요.</p>
                <p data-i18n="v_voucher_msg_03">대소문자를 구분하여 입력해 주세요.</p>
            </div>
        </div>
        <div class="voucher__tab voucher__amount__form__wrap">
            <div class="title">
                <p data-i18n="v_available_voucher">사용 가능 바우처</p>
            </div>
            <div class="info__wrap possession">
                <div class="info voucher_possession_list">
            
                </div>
            </div>
        </div>
        <div class="voucher__tab use__voucher__form__wrap">
            <div class="title">
                <p data-i18n="v_voucher_history">바우처 사용 내역</p>
            </div>
            <div class="info__wrap use">
                <div class="info voucher_use_list">
                    
                </div>
            </div>
        </div>
        <div class="voucher__tab voucher__notice__form__wrap">
            <div class="title">
                <p data-i18n="v_voucher_notice">바우처 유의사항</p>
            </div>
            <div class="info non__border">
                <p data-i18n="v_voucher_msg_04">1개의 주문 건에 1개의 바우처 사용이 가능합니다.</p>
                <p data-i18n="v_voucher_msg_05">바우처의 사용 기한 만료 이후 사용 및 주문이 불가합니다.</p>
                <p data-i18n="v_voucher_msg_06">주문 취소 이후 바우처 복원은 최대 40분이 소요됩니다.</p>
                <p data-i18n="v_voucher_msg_07">유효기간이 지난 바우처는 재발행 되지 않습니다.</p>
            </div>
        </div>
    </div>
</div>
<script src="/scripts/mypage/voucher.js"></script>