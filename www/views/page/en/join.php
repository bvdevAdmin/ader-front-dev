<main class="my">
	<button type="button" id="btn-mobile-history-back"></button>
	<nav>
		<ul>
			<li><a href="/en">HOME</a></li>
			<li><a href="/en/join">Create Account</a></li>
		</ul>
	</nav>
	<section class="account">
		<article class="join">
			<h1>Create Account</h1>
			<form id="frm-join">
				<div class="form-inline inline-label">
					<input type="email" name="member_id" placeholder=" " required autocomplete="off">
					<span class="vaild">Please enter your email propperly.</span>
					<span class="control-label">E-mail</span>
				</div>
				<div class="form-inline inline-label">
					<button type="button" class="pw-view-toggle"></button>
					<input type="password" name="member_pw" placeholder=" " required autocomplete="off">
					<span class="vaild">Please enter your password accurately.</span>
					<div class="remark">
						<ul class="dot">
							<li>English + numeric + special character combination 8-16 digits</li>
							<li>Enterable special characters<br>!@#$%^()_-={}[];:<>, ./li>
							<li> Unable to enter blank</li>
						</ul>
					</div>
					<span class="control-label">Password</span>
				</div>
				<div class="form-inline inline-label">
					<button type="button" class="pw-view-toggle"></button>
					<input type="password" name="member_pw2" placeholder=" " required>
					<span class="vaild">The passwords do not match.</span>
					<div class="remark">
						<ul class="dot">
							<li>English + numeric + special character combination 8-16 digits</li>
							<li>Enterable special characters<br>!@#$%^()_-={}[];:<>, ./li>
							<li> Unable to enter blank</li>
						</ul>
					</div>
					<span class="control-label">Password confirm</span>
				</div>

				<div class="buttons">
					<button type="button" id="btn-personal-certify">Confirm</button>
				</div>
				
				<div id="personal-certify-ok" class="hidden">
					<div class="form-inline inline-label">
						<input type="text" name="member_name" placeholder=" " required>
						<span class="control-label">Name</span>
					</div>
					<div class="form-inline inline-label">
						<input type="tel" name="tel_mobile" placeholder=" " required>
						
						<input type="hidden" name="member_gender" value="">
						<input type="hidden" name="member_birth" value="">
						
						<span class="vaild">Please enter the mobile number without (-).</span>
						<span class="control-label">Mobile number</span>
					</div>
					<div class="agrees">
						<label class="check"><input type="checkbox" name="agree_all"><i></i>Accept all</label>
						<ul>
							<li>
								<label class="check"><input type="checkbox" name="agree_terms"><i></i></label>
								<a href="/en/terms-of-use" target="_blank">Terms and Conditions</a>, <a href="/en/privacy-policy" target="_blank">I agree with </a> collecting and using personal information. (Required)
							</li>
							<li>
								<label class="check"><input type="checkbox" name="agree_receive_sms"><i></i></label>
								I agree to receive SMS marketing information. (Optional)
							</li>
							<li>
								<label class="check"><input type="checkbox" name="agree_receive_email"><i></i></label>
								I agree to receive email marketing information (optional)
							</li>
						</ul>
					</div>
					<div class="buttons">
						<button type="submit">Create account</button>
					</div>
				</div>
			</form>
			<section class="join-ok">
				<p>You have successfully registered.</p>
				<p>Enjoy ADERERROR's product favorites, reserves, vouchers, inquiries, memberships and more.</p>
				<p>
				If you add an official Kakao Talk channel,<br>
				you will quickly receive various news<br>
				such as product release information and collaboration collection<br>
				I can check it out.
				</p>
				<div class="buttons">
					<a href="/en/login" class="btn">Login</a>
				</div>
			</section>
		</article>
	</section>
</main>