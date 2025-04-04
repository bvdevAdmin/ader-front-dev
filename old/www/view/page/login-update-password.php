<link rel="stylesheet" href="/css/user/main/login-update-password.css">
<main>
    <div class="join__card">
        <div class="card__header">
            <p class="font__large">비밀번호 변경하기</p>
        </div>
        <form id="frm-update" method="post">
        <?php
                function getUrlParamter($url, $sch_tag) {
                    $parts = parse_url($url);
                    parse_str($parts['query'], $query);
                    return $query[$sch_tag];
                }
                
                $page_url = $_SERVER['REQUEST_URI'];
                $member_idx = getUrlParamter($page_url, 'member_idx');
                $country = getUrlParamter($page_url, 'country');
		?>
				<input id="member_idx" type="hidden" name="member_idx" value="<?=$member_idx?>">
                <input id="country" type="hidden" name="member_idx" value="<?=$country?>">
            <div class="card__body">
                <div class="content__wrap">
                    <div class="content__title warm__msg__area">
                        <p class="font__small">새로운 비밀번호</p>
                        <p class="font__underline warn__msg member_pw">비밀번호를 정확하게 기입해주세요</p>
                    </div>
                    <div class="contnet__row warm__msg__area">
                        <input type="password" name="member_pw">
                    </div>  
                </div>
                <div class="content__wrap">
                    <div class="content__title warm__msg__area">
                        <p class="font__small">비밀번호 확인</p>
                        <p class="font__underline warn__msg member_pw_confirm">비밀번호가 일치하지 않습니다</p>
                    </div>
                    <div class="contnet__row warm__msg__area">
                        <input type="password" name="member_pw_confirm">
                    </div>  
                </div>
            </div>
            <div class="card__footer">
                <div>
                    <input type="button" class="black__btn update_pw_btn" value="저장하기">
                </div>
            </div>
        </form>
    </div>
</main>
<script src="/scripts/member/login-update-password.js"></script>
<!-- <script src="/scripts/member/login.js"></script> -->



