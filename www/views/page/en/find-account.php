<main class="my">
	<nav>
		<ul>
			<li><a href="/en">HOME</a></li>
			<li><a href="/en/find-account">Find E-mail/Password</a></li>
		</ul>
	</nav>
	<section class="account">
		<article class="find">
			<h1>Find E-mail/Password</h1>
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>Find E-mail</li>
						<li>Find password</li>
					</ul>
				</div>
				<section>
					<div id="find-id">
						<form id="frm-find">
							<div class="--describe">
								Using mobile number that you signed up,<br>
								you canf find your E-mail.
							</div>
							<div class="form-inline inline-label">
								<button type="button">Verify</button>
								<input type="tel" name="tel" placeholder=" " required>
								<span class="control-label">Mobile number</span>
							</div>
							<div class="number-confirm" id="id-number-confirm">
								<div class="form-inline  inline-label">
									<input type="number" placeholder=" " required>
									<div class="timeleft">0:00</div>
									<span class="control-label"></span>
								</div>
								<button type="button" class="time-extend">send again</button>
							</div>
							<div class="buttons">
								<button type="submit">Verify</button>
							</div>
						</form>
					</div>
					<div id="find-id-success" class="hidden">
						<div class="--describe">
							Found your E-mail.<br><br>
							<div id="find-id-result-info"></div>
						</div>
						<div class="buttons">
							<button type="button" class="submit">Login</button>
							<button type="button">Find password</button>
						</div>
					</div>
					<div id="find-id-fail" class="hidden">
						<div class="--describe">
							No E-mail matches the information you signup.<br><br>
							If you have difficulty finding your E-mail, <br>
							Please contact Customer Center 02-792-2232.
						</div>
						<div class="buttons">
							<a href="/join" class="btn submit">Register</a>
						</div>
					</div>
				</section>
				<section>
					<div id="find-pw">
						<form id="frm-find-pw">
							<div class="--describe">
								Please enter your E-mail.<br><br>
								Customers who registered by SNS are <br>
								use the SNS id you signed up for.
							</div>
							<div class="form-inline inline-label">
								<input type="email" name="member_id" placeholder=" " required autocomplete="off">
								<span class="control-label">Pelase enter your E-mail propperly</span>
							</div>
							<div class="form-inline inline-label">
								<button type="button">Verify</button>
								<input type="tel" name="tel" placeholder=" " required>
								<span class="control-label">Mobile number</span>
							</div>
							<div class="number-confirm" id="id-number-confirm">
								<div class="form-inline  inline-label">
									<input type="number" placeholder=" " required>
									<div class="timeleft">0:00</div>
									<span class="control-label"></span>
								</div>
								<button type="button" class="time-extend">send again</button>
							</div>
							<div class="buttons">
								<button type="submit">Verify</button>
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
					</div>
					<div id="find-pw-change" class="hidden">
						<div class="--describe">
							Please enter the new password<br><br>
							<small>English + Number + Special Character Combination 8-16 digits</small>
						</div>
						<form id="frm-find-pw-change">
							<div class="form-inline inline-label">
								<input type="password" name="pw" placeholder=" " required>
								<span class="control-label">New password</span>
							</div>
							<div class="form-inline inline-label">
								<input type="password" name="pw_confirm" placeholder=" " required>
								<span class="control-label">Password confirm</span>
							</div>
							<div class="buttons">
								<button type="submit">Verify</button>
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
					</div>
					<div id="find-pw-change" class="hidden">
						<div class="--describe">
							New password setting completed.<br><br>
							<small>Please login again with new password.</small>
						</div>
						<div class="buttons">
							<button type="submit">Login</button>
						</div>
					</div>
				</section>
			</div>
		</article>
	</section>
</main>