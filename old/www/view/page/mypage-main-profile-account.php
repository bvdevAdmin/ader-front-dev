<div class="profile__tab profile__set__wrap">
    <div class="contents__table">
        <table class="border__bottom">
            <colsgroup>
                <col style="width:120px;">
                <col style="width:300px;">
                <col style="width:50px;">
            </colsgroup>
            <tbody>
                <tr>
                    <td data-i18n="p_full_name">이름</td>
                    <td>
                        <?= $_SESSION['MEMBER_NAME'] ?>
                    </td>
                </tr>
                <tr>
                    <td data-i18n="p_email">이메일</td>
                    <td>
                        <?= $_SESSION['MEMBER_EMAIL'] ?>
                    </td>
                </tr>
                <tr>
                    <td data-i18n="p_password">비밀번호</td>
                    <td>
						<?php
							if ($_SESSION['PW_STATUS'] == "T") {
						?>
							**************
						<?php						
							}
						?>
						<input class="user_update_pw" type="hidden">
                    </td>
                    <td>
						<?php
							if ($_SESSION['PW_STATUS'] == "T") {
						?>
							<div class="update_btn pw_update" data-i18n="p_edit">수정</div>
						<?php						
							}
						?>
                    </td>
                </tr>
                <tr>
                    <td data-i18n="p_mobile">전화번호</td>
                    <td>
                        <p class="td_user_tel"><?= $_SESSION['TEL_MOBILE'] ?></p>
                        <input class="user_update_tel" type="hidden">
                    </td>
                    <td>
                        <div class="update_btn tel_update" data-i18n="p_edit">수정</div>
                    </td>
                </tr>
                <tr>
                    <td data-i18n="p_birth">생년월일</td>
                    <td>
                        <?= $_SESSION['MEMBER_BIRTH'] ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="btn__area profile">
        <div class="btn_padding">
            <button class="black__full__width__btn profile_save_btn" type="button" data-i18n="p_save">저장</button>
        </div>
        <div>
            <button class="white__full__width__btn move_account_del"
                type="button" data-i18n="p_delete_account_01">계정삭제</button>
        </div>
    </div>
    <div class="footer"></div>
</div>
<div class="profile__tab profile__pw__update__wrap">
    <div class="title">
        <p data-i18n="p_change_password">비밀번호 변경</p>
        <div class="close close_pw_update">
            <img src="/images/mypage/tmp_img/X-12.svg" />
        </div>
    </div>
    <div class="description pw_update_error">
        <p>&nbsp;</p>
    </div>

    <div class="pw_form">
        <input class="current_pw" type="password" placeholder="현재 비밀번호"
            data-i18n-placeholder="p_current_password">
        <input class="tmp_update_pw" type="password" placeholder="새로운 비밀번호"
            data-i18n-placeholder="p_new_password">
        <input class="tmp_update_pw_check none_margin_bottom" type="password" placeholder="새로운 비밀번호 확인"
            data-i18n-placeholder="p_confirm_new_password">
    </div>

    <div class="contents_margin">
        <p data-i18n="p_password_requirements">비밀번호 입력 조건</p>
    </div>

    <div class="contents_margin">
        <p data-i18n="p_password_msg_01">영문+숫자+특수문자 조합, 8자~16자리</p>
        <p data-i18n="p_special_character_msg">입력 가능 특수문자</p>
        <span class="flex_text">·&nbsp;&nbsp;<p>!@#$%^()_-={}[]|;:<>,.?/</p></span>
        <p data-i18n="p_blank_character_msg">공백 입력 불가능</p>
    </div>

    <div>
        <button class="black__btn check_member_pw_btn" data-i18n="p_change" type="button">변경</button>
    </div>
</div>
<div class="profile__tab profile__tel__update__wrap">
    <div class="title">
        <p data-i18n="p_change_mobile_number">전화번호 변경</p>
        <div class="close close_tel_update">
            <img src="/images/mypage/tmp_img/X-12.svg" />
        </div>
    </div>
    <div>
        <p data-i18n="p_member_msg_01"> 일회성 인증번호 발송을 위해 전화번호를 입력해 주세요.</p>
    </div>
    <div class="description tel_update_error">
        <p>&nbsp;</p>
    </div>
    <div class="pw_form">
        <input class="mobile_number_input" type="text" name="tel_certificate" data-i18n-placeholder="p_member_msg_02">
    </div>
    <div class="tel_update_description">
        <div>
            <span class="flex_text">
                <p data-i18n="p_member_msg_03_1">통신 요금제에 따라 문자메시지 발송 비용이 발생할 수 있으며,</p>
            </span>
            <span class="flex_text">&nbsp;&nbsp;
                <p data-i18n="p_member_msg_03_2">통신사의 문제로 인해 문자 메시지 발송이 지연될 수 있습니다.</p>
            </span>
        </div>
        <span class="flex_text">·&nbsp;&nbsp;
            <p data-i18n="p_privacy_policy_01" class="underline service_policy_link">개인정보처리방침</p>&nbsp;
            <p data-i18n="p_privacy_policy_02">및</p>&nbsp;
            <p data-i18n="p_privacy_policy_03" class="underline service_terms_link">이용약관</p>
        </span>
    </div>
    <div class="input__form__rows">
        <label>
            <input id="ck_sendcode_phone" type="checkbox">
            <span class="sms_privacy_des" data-i18n="p_member_msg_04">문자메시지 1회 수신을 위한 개인정보처리방침 및 이용약관 동의</span>
            <span class="alertms_tel"></span>
        </label>
    </div>
    <button class="black__btn send_code" type="button" data-i18n="p_send_code">인증번호 발송</button>
</div>
<div class="profile__tab profile__tel__update__confirm__wrap">
    <div class="title">
        <p data-i18n="p_change_mobile_number">전화번호 변경</p>
        <div class="close close_tel_update_confirm">
            <img src="/images/mypage/tmp_img/X-12.svg" />
        </div>
    </div>
    <div class="tel_update_msg_send_notice">
        <p class="to_update_mobile_number"></p>
        <p data-i18n="p_member_msg_05">&nbsp;&nbsp;인증 코드가 전송되었습니다.</p>
    </div>
    <div class="pw_form">
        <input class="auth_no" type="text" name="tel_insert" placeholder="인증 번호 입력" data-i18n-placeholder="p_type_code">
        <div class="re_msg_notice_text" data-i18n="p_resend_in_30">30초 내에 재전송</div>
    </div>
    <div>
        <p data-i18n="p_member_msg_03_1">통신 요금제에 따라 문자메시지 발송 비용이 발생할 수 있으며,</p>
        <p data-i18n="p_member_msg_03_2" class="margin_text">통신사의 문제로 인해 문자 메시지 발송이 지연될 수 있습니다.</p>

    </div>
    <div class="tel_update_btn_wrap">
        <button class="white__btn btn_re_send" type="button" data-i18n="p_resend_the_code">인증 코드 재전송</button>
        <button class="black__btn fin_check" type="button" data-i18n="p_verified">인증완료</button>
    </div>
</div>
<div class="profile__tab profile__account__delete__wrap">
    <div class="title">
        <p data-i18n="p_delete_account_02">계정삭제</p>
        <div class="close close_account_delete">
            <img src="/images/mypage/tmp_img/X-12.svg" />
        </div>
    </div>
    <div class="contents_margin">
        <p data-i18n="p_member_msg_06">부정 이용을 방지하기 위하여 회원탈퇴 후 48시간 이내로 재가입이 불가합니다.</p>
        <p data-i18n="p_member_msg_07">탈퇴 즉시 개인정보가 삭제되고 어떠한 방법으로도 복원할 수 없습니다.</p>
        <p data-i18n="p_member_msg_08">교환, 반품, 환불 및 사후처리(A/S)등을 위하여 전자상거래 등에서의</br>
            &nbsp;&nbsp;&nbsp;소비자보호에 관한 법률에 의거해 일정 기간동안 보관 후 파기됩니다.</p>
    </div>
    <div class="input__form__rows">
        <label>
            <input id="ck_account_delete" type="checkbox">
        </label>
        <span data-i18n="p_member_msg_09">위 유의사항을 모두 확인하였고, 계정 삭제에 동의합니다.</span>
    </div>
    <div class="account_delete_notice">
        <p class="alertms_del">&nbsp;</p>
    </div>
    <div class="account_delete_btn_wrap">
        <button class="white__btn del_cancel" data-i18n="o_cancel">취소</button>
        <button class="black__btn account_del" type="button" data-i18n="p_delete_account_02">계정삭제</button>
    </div>
</div>
<script src="/scripts/mypage/profile/profile-account.js"></script>