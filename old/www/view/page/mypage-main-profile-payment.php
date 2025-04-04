<div class="profile__tab profile__payment__wrap">
    <div class="profile__payment__list__wrap">
        <div class="payment__wrap default">
            <div class="title">
                <p data-i18n="p_dafault_payment_method">기본 결제수단</p>
            </div>
            <table>
                <tbody class="delivery__table__wrap">

                </tbody>
            </table>
        </div>
        <div class="payment__wrap other">
            <div class="title">
                <p data-i18n="p_other_payment_method">다른 결제수단</p>
            </div>
            <table>
                <tbody class="other__delivery__table__wrap">

                </tbody>
            </table>
        </div>
    </div>
    <div class="black__full__width__btn add_new_payment_btn" data-i18n="p_add_new_payment_method">새로운 결제수단 추가</div>
</div>
<div class="profile__tab profile__payment__update__wrap">
    <div class="close">
        <img src="/images/mypage/tmp_img/X-12.svg" />
    </div>
    <div class="title">
        <p data-i18n="p_save_pay">결제수단 저장</p>
    </div>
    <div class="description">
        <p data-i18n="p_member_msg_10"></p>
    </div>
    <div class="input__form__wrap">
        <div class="input__form__rows">
            <div class="rows__title" data-i18n="p_card_full_name">카드 명의</div>
            <input class="payment__name" placeholder="이름" data-i18n-placeholder="p_full_name"></input>
        </div>
        <div class="input__form__rows">
            <div class="rows__title" data-i18n="p_card_number">카드번호</div>
            <input class="payment__number" placeholder="( - ) 없이 숫자만 입력" data-i18n-placeholder="p_member_msg_02"></input>
        </div>
        <div class="input__form__rows">
            <div class="rows__title" data-i18n="p_expiration_date">유효기간</div>
            <div class="payment_select_wrap">
                <div class="payment-select-box month">
                </div>
                <div class="payment-select-box year">
                </div>
            </div>
        </div>
        <div class="input__form__rows">
            <label>
                <input type="checkbox" class="checkbox_payment">
                <span data-i18n="p_default_pay_method">기본 결제수단으로 저장</span>
            </label>
        </div>
        <div class="profile_btn_padding">
            <button class="black__full__width__btn account" data-i18n="p_save">저장</button>
        </div>
    </div>
</div>
<script src="/scripts/mypage/profile/profile-payment.js"></script>