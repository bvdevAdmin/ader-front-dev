<main class="login">
	<section>
		<article class="account login">
			<h1>로그인</h1>
			<form id="frm-login">
				<input type="hidden" name="r_url">
				<div class="form-inline inline-label">
					<input type="email" name="member_id" placeholder=" " required>
					<span class="control-label">E-mail</span>
				</div>
				<div class="form-inline inline-label">
					<button type="button" class="pw-view-toggle">afaf</button>
					<input type="password" name="member_pw" placeholder=" " required>
                    <input type="password" name="member_pw" placeholder=" " required>
					<span class="control-label">비밀번호</span>
				</div>
				<button type="submit" class="btn black">로그인</button>
				<div class="rows">
					<div class="left">
						<label>
							<input type="checkbox" name="save_id" value="y">
							<i></i>
							아이디저장
						</label>
					</div>
					<div class="right">
						<a href="/find-account">아이디</a>
						|
						<a href="/find-account#password">비밀번호 찾기</a>
					</div>
				</div>
			</form>
			<div class="sns-login">
				<p>SNS 계정으로 로그인하기22</p>
				<ul>
					<li><button type="button" class="login-kakao" id="btn-login-kakao">카카오 로그인</button></li>
					<li><button type="button" class="login-naver" id="btn-login-naver">네이버 로그인</button></li>
				</ul>
			</div>
			<hr />
			<div class="join">
				<p>회원가입을 하시면 다양한 혜택을 경험하실 수 있습니다.</p>
				<a href="/join" class="btn">회원가입</a>
			</div>
		</article>
	</section>
</main>

<script>
    <!-- SNS 로그인 스크립트 -->
    <?php
        include_once("../../../collaboration/class/class.kakaoOAuth.php");
        include_once("../../../collaboration/class/class.naverOAuth.php");

    ?>

    function initLoginHandler() {
        $('#btn-login-naver').unbind('click');
        <?php
            $naver = new Naver();
            echo $naver->login();
        ?>

        $('#btn-login-kakao').unbind('click');
        <?php
            $kakao_oauth = "https://kauth.kakao.com/oauth/";
            $client_kakao = "b43df682b08d3270e40a79b5c51506b5";
            $redirect_kakao = urlencode("https://dev2.adererror.com/kakao/login");

            $tmp_url = $kakao_oauth . "authorize?client_id=" . $client_kakao . "&response_type=code&scope=account_email,name,phone_number,birthyear&redirect_uri=" . $redirect_kakao . "&response_type=code&','_blank','width=320,height=480";

            echo "
                $('#btn-login-kakao').click(function() {
                    location.href = '" . $tmp_url . "';
                });
            ";
        ?>
    }
</script>