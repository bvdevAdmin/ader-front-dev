<main class="login">
	<section>
		<article class="account login">
			<h1>Login</h1>
			<form id="frm-login">
				<input type="hidden" name="r_url">
				<div class="form-inline inline-label">
					<input type="email" name="member_id" placeholder=" " required>
					<span class="control-label">E-mail</span>
				</div>
				<div class="form-inline inline-label">
					<button type="button" class="pw-view-toggle"></button>
					<input type="password" name="member_pw" placeholder=" " required>
					<span class="control-label">Password</span>
				</div>
				<button type="submit" class="btn black">Login</button>
				<div class="rows">
					<div class="left">
						<label>
							<input type="checkbox" name="save_id" value="y">
							<i></i>
							remember
						</label>
					</div>
					<div class="right">
						<a href="/en/find-account?type=id">E-mail</a>
						|
						<a href="/en/find-account?type=pw">Password</a>
					</div>
				</div>
			</form>
			<div class="sns-login">
				<p>Login with SNS account</p>
				<ul>
					<li><button type="button" class="login-kakao" id="btn-login-kakao">Kakao login</button></li>
					<li><button type="button" class="login-naver" id="btn-login-naver">Naver login</button></li>
					<li><button type="button" class="login-google" id="btn-login-google">Google login</button></li>
				</ul>
			</div>
			<hr />
			<div class="join">
				<p>Become a Blue member and enjoy our latests updates and events.</p>
				<a href="/en/join" class="btn">Create account</a>
			</div>
		</article>
	</section>
</main>

<script>

function setCookie(cookieName, value, exdays) {
	let exdate = new Date();
	exdate.setDate(exdate.getDate() + exdays);
	let cookieValue = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toGMTString());
	document.cookie = cookieName + "=" + cookieValue;
}

</script>

