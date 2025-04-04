<link rel="stylesheet" href="/css/user/main/login.css">

<?php
function getUrlParamter($url, $sch_tag) {
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    return $query[$sch_tag];
}

$page_url = $_SERVER['REQUEST_URI'];
$r_url = getUrlParamter($page_url, 'r_url');

if (isset($_SESSION['MEMBER_IDX'])) {
    if ($r_url != null) {
        $url_str = $r_url;
    } else {
        $url_str = '/main';
    }
	
    echo "
		<script>
			location.href='".$url_str."';
		</script>
	";
}
?>

<main>
    <div class="login__card">
        <div class="card__header">
            <p class="font__large" data-i18n="m_login">로그인</p>
            <span class="font__underline font__red result_msg"></span>
        </div>
        <div class="card__body">
            <div id="login_page">
                <input class="param_r_url" type="hidden" name="r_url" value="<?=$r_url?>">
				
                <div class="content__wrap">
                    <div class="content__wrap__msg">
                        <div class="content__title" data-i18n="p_email">이메일</div>
                        <div class="font__underline font__red member_id_msg"></div>
                    </div>
					
                    <div class="content__row">
                        <input class="param_member_id" type="text" name="member_id" value="" data-wrap_login="page">
                    </div>
                </div>
				
                <div class="content__wrap">
                    <div class="content__wrap__msg">
                        <div class="content__title" data-i18n="p_password">비밀번호</div>
                        <div class="font__underline font__red member_pw_msg"></div>
                    </div>
                    <div class="content__row">
                        <input class="param_member_pw" type="password" name="member_pw" value="" data-wrap_login="page">
                    </div>
                </div>
				
                <div class="content__wrap login_btn">
                    <button type="button" class="black_btn btn_login" data-wrap_login="page" data-i18n="m_login"></button>
                </div>
            </div>
			
            <div class="content__wrap">
                <div class="content__row email_checkbox">
                    <div class="checkbox__label">
                        <input id="page_member_id_flg" type="checkbox" name="member_id_flg">
                        <label for="page_member_id_flg"></label>
                        <span class="font__small" data-i18n="p_save_mail">이메일 저장</span>
                    </div>
					
                    <span class="font__underline find_button link_pw" data-i18n="m_find_password">
                        비밀번호 찾기
                    </span>
                </div>
            </div>
            <div class="content__wrap sns_login_wrap"></div>
            <div class="contour__line"></div>
            <div class="content__wrap">
                <p class="font__large text__align__center" data-i18n="lm_menu_msg_02">회원가입을 하시면 다양한 혜택을 경험하실 수 있습니다.</p>
            </div>
        </div>
        <div class="card__footer">
            <button type="button" class="black_btn join_button btn_join" data-i18n="lm_create_account"></button>
        </div>
    </div>
</main>