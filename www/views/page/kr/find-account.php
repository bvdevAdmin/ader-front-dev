<main class="my">
	<nav>
		<ul>
			<li><a href="/">HOME</a></li>
			<li><a href="/find-account">아이디/비밀번호 찾기</a></li>
		</ul>
	</nav>
	<section class="account">
		<article class="find">
			<h1>아이디 / 비밀번호 찾기</h1>
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>아이디 찾기</li>
						<li>비밀번호 찾기</li>
					</ul>
				</div>
				<section>
					<div id="find-id">
						<form id="frm-find">
							<div class="--describe">
								회원가입 시 등록하신 휴대폰 번호로<br>
								아이디를 확인하실 수 있습니다.
							</div>
							<div class="form-inline inline-label">
								<button type="button">인증번호</button>
								<input type="tel" name="tel" placeholder=" " required>
								<span class="control-label">휴대폰 번호</span>
							</div>
							<div class="number-confirm" id="id-number-confirm">
								<div class="form-inline  inline-label">
									<input type="number" placeholder=" " required>
									<div class="timeleft">0:00</div>
									<span class="control-label"></span>
								</div>
								<button type="button" class="time-extend">시간연장</button>
							</div>
							<div class="buttons">
								<button type="submit">확인</button>
							</div>
						</form>
					</div>
					<div id="find-id-success" class="hidden">
						<div class="--describe">
							아이디를 찾았습니다.<br><br>
							<div id="find-id-result-info"></div>
						</div>
						<div class="buttons">
							<button type="button" class="submit">로그인</button>
							<button type="button">비밀번호 찾기</button>
						</div>
					</div>
					<div id="find-id-fail" class="hidden">
						<div class="--describe">
							입력하신 정보와 일치하는 아이디가 없습니다.<br><br>
							아이디 찾기에 어려움이 있으시다면<br>
							고객센터 02-792-2232로 문의 바랍니다.
						</div>
						<div class="buttons">
							<a href="/join" class="btn submit">회원가입</a>
						</div>
					</div>
				</section>
				<section>
					<div id="find-pw">
						<form id="frm-find-pw">
							<div class="--describe">
								이메일 주소를 입력해주세요.<br><br>
								SNS 간편가입 회원은<br>
								가입하신 SNS 로그인을 이용해 주세요.
							</div>
							<div class="form-inline inline-label">
								<input type="email" name="member_id" placeholder=" " required>
								<span class="control-label">이메일 주소 <small>(@ 까지 정확하게 입력해 주세요)</small></span>
							</div>
							<div class="form-inline inline-label">
								<button type="button">인증번호</button>
								<input type="tel" name="tel" placeholder=" " required>
								<span class="control-label">휴대폰 번호</span>
							</div>
							<div class="number-confirm" id="id-number-confirm">
								<div class="form-inline  inline-label">
									<input type="number" placeholder=" " required>
									<div class="timeleft">0:00</div>
									<span class="control-label"></span>
								</div>
								<button type="button" class="time-extend">시간연장</button>
							</div>
							<div class="buttons">
								<button type="submit">확인</button>
							</div>
						</form>
						<div class="sns-login">
							<p>SNS 계정으로 로그인하기</p>
							<ul>
								<li><button type="button" class="login-kakao" id="btn-login-kakao">카카오 로그인</button></li>
								<li><button type="button" class="login-naver" id="btn-login-naver">네이버 로그인</button></li>
							</ul>
						</div>
					</div>
					<div id="find-pw-change" class="hidden">
						<div class="--describe">
							새로운 비밀먼호를 입력해 주세요.<br><br>
							<small>영문 + 숫자 + 특수문자 조합 8-16자리</small>
						</div>
						<form id="frm-find-pw-change">
							<div class="form-inline inline-label">
								<input type="password" name="pw" placeholder=" " required>
								<span class="control-label">새 비밀번호</span>
							</div>
							<div class="form-inline inline-label">
								<input type="password" name="pw_confirm" placeholder=" " required>
								<span class="control-label">비밀번호 재입력</span>
							</div>
							<div class="buttons">
								<button type="submit">확인</button>
							</div>
						</form>
						<div class="sns-login">
							<p>SNS 계정으로 로그인하기</p>
							<ul>
								<li><button type="button" class="login-kakao" id="btn-login-kakao">카카오 로그인</button></li>
								<li><button type="button" class="login-naver" id="btn-login-naver">네이버 로그인</button></li>
							</ul>
						</div>
					</div>
					<div id="find-pw-change" class="hidden">
						<div class="--describe">
							새로운 비밀번호 설정이 완료 되었습니다.<br><br>
							<small>새로운 비밀번호로 다시 로그인해 주세요.</small>
						</div>
						<div class="buttons">
							<button type="submit">로그인</button>
						</div>
					</div>
				</section>
			</div>
		</article>
	</section>
</main>