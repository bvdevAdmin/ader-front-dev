<main class="login">
	<section>
		<article class="account login">
			<h1>로그인</h1>
			<form id="frm-login">
				<input type="hidden" name="r_url">
				<div class="form-inline inline-label">
					<input type="email" name="member_id" placeholder=" " required tabindex="1">
					<span class="control-label">E-mail</span>
				</div>
				<div class="form-inline inline-label">
					<button type="button" class="pw-view-toggle" tabindex="-1"></button>
					<input type="password" name="member_pw" placeholder=" " required tabindex="2">
					<span class="control-label">비밀번호</span>
				</div>
				<button type="submit" class="btn black">로그인</button>
				<div class="rows">
					<div class="left">
						<label>
							<input type="checkbox" name="save_id" value="y" tabindex="3">
							<i></i>
							아이디저장
						</label>
					</div>
					<div class="right">
						<a href="/kr/find-account?type=id">아이디</a>
						|
						<a href="/kr/find-account?type=pw">비밀번호 찾기</a>
					</div>
				</div>
			</form>
			<div class="sns-login">
				<p>SNS 계정으로 로그인하기</p>
				<ul>
					<li><button type="button" class="login-kakao" id="btn-login-kakao">카카오 로그인</button></li>
					<li><button type="button" class="login-naver" id="btn-login-naver">네이버 로그인</button></li>
					<li><button type="button" class="login-google" id="btn-login-google">구글 로그인</button></li>
				</ul>
			</div>
			<hr />
			<div class="join">
				<p>회원가입을 하시면 다양한 혜택을 경험하실 수 있습니다.</p>
				<a href="/kr/join" class="btn">회원가입</a>
			</div>
		</article>
	</section>
</main>