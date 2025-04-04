<link rel="stylesheet" href="/css/user/main/login-check.css">
<main>
    <div class="password__search__card">
        <div class="card__header">
            <p class="font__large" data-i18n="m_find_password"></p>
            <span class="font__underline font__red result_msg"></span>
        </div>
        <div class="card__body">
            <div class="content__wrap">
                <div class="content__title" data-i18n="p_send_pw_reset_email"></div>
            </div>
            <form id="frm-find" method="post" onSubmit="password_find_check();return false;">
                <div class="content__wrap grid__two">
                    <div class="member_id_msg_wrap">
                        <p class="font__underline font__red member_id_msg_no_email" data-i18n="p_member_id_error_msg_01"></p>
                        <p class="font__underline font__red member_id_msg_write_email" data-i18n="p_member_id_error_msg_02"></p>
                        <p class="font__underline font__red member_id_msg_correct_email" data-i18n="p_member_id_error_msg_03"></p>
                    </div>
                    <div class="get_email_link_wrap">
                        <div class="left__area__wrap">
                            <input type="text" value="" id="member_id" name="member_id" data-i18n-placeholder="p_write_email_placeholder">
                        </div>
                        <div class="right__area__wrap">
                            <div class="black__small__btn pw_find_btn" id="link_btn" data-i18n="p_get_link_btn"></div>
                        </div>
                    </div>
                </div>
            </form>
        <div class="card__footer">
            <div class="content__wrap">
                <p class="font__large pc__sns__msg" data-i18n="p_sns_account_link_msg"></p>
                <div class="font__large moblie__sns__msg">
                    <p data-i18n="p_sns_account_link_msg_m_01"></p>
                    <p data-i18n="p_sns_account_link_msg_m_02"></p>
                </div> 
            </div>
            <div class="other__platform__btn" style="display:flex;">
                <img class="sns-login-btn kakao__btn" style="width:30px;height:30px;" src="https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user/btn/btn_kakao.png">
                <img class="sns-login-btn naver__btn" style="width:30px;height:30px;margin-right:10px;" src="https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user/btn/btn_naver.jpg">
				<img class="sns-login-btn google_btn" style="width:30px;height:30px;" src="https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user/btn/btn_google.png">
            </div>
        </div>
    </div>
</main>
<script src="/scripts/member/login-check.js"></script>

<script>
$(document).ready(function() {
	let country = getLanguage()
	if (country != "KR") {
		$('.kakao__btn').hide();
		$('.naver__btn').hide();
	}
});
</script>