<main class="my">
	<button type="button" id="btn-mobile-history-back"></button>
	<nav>
		<ul>
			<li><a href="/">HOME</a></li>
			<li><a href="/join">회원가입</a></li>
		</ul>
	</nav>
	<section class="account">
		<article class="join">
			<h1>회원가입</h1>
			<form id="frm-join">
				<div class="form-inline inline-label">
					<input type="email" name="member_id" placeholder=" " required>
					<span class="vaild">이메일을 정확하게 기입해주세요.</span>
					<span class="control-label">이메일</span>
				</div>
				<div class="form-inline inline-label">
					<button type="button" class="pw-view-toggle"></button>
					<input type="password" name="member_pw" placeholder=" " required>
					<span class="vaild">비밀번호를 정확하게 기입해주세요.</span>
					<div class="remark">
						<ul class="dot">
							<li>영문 + 숫자 + 특수문자 조합 8 - 16 자리</li>
							<li>입력가능 특수문자<br>!@#$%^()_-={}[]|;:<>,.?/</li>
							<li>공백 입력 불가능</li>
						</ul>
					</div>
					<span class="control-label">비밀번호</span>
				</div>
				<div class="form-inline inline-label">
					<button type="button" class="pw-view-toggle"></button>
					<input type="password" name="member_pw2" placeholder=" " required>
					<span class="vaild">비밀번호가 일치하지 않습니다.</span>
					<div class="remark">
						<ul class="dot">
							<li>영문 + 숫자 + 특수문자 조합 8 - 16 자리</li>
							<li>입력가능 특수문자<br>!@#$%^()_-={}[]|;:<>,.?/</li>
							<li>공백 입력 불가능</li>
						</ul>
					</div>
					<span class="control-label">비밀번호 확인</span>
				</div>

				<div class="buttons">
					<button type="button" id="btn-personal-certify">휴대폰 본인인증</button>
				</div>
				
				<div id="personal-certify-ok" class="hidden">
					<div class="form-inline inline-label">
						<input type="text" name="member_name" placeholder=" " required>
						<span class="control-label">이름</span>
					</div>
					<div class="form-inline inline-label">
						<!--<button type="button" id="btn-personal-certify">인증</button>-->
						<input type="tel" name="tel_mobile" placeholder=" " required>
						<span class="vaild">'-'가 포함되지 않은 휴대폰 전화를 입력해주세요.</span>
						<span class="control-label">휴대전화</span>
					</div>
					<div class="agrees">
						<label class="check"><input type="checkbox" name="agree_all"><i></i>전체동의</label>
						<ul>
							<li>
								<label class="check"><input type="checkbox" name="agree_terms"><i></i></label>
								<a href="/terms-of-use" target="_blank">이용약관</a>, <a href="/privacy-policy" target="_blank">개인정보수집 및 이용</a> 에 동의합니다. (필수)
							</li>
							<li>
								<label class="check"><input type="checkbox" name="agree_receive_sms"><i></i></label>
								SMS 마케팅정보 수신을 동의합니다. (선택)
							</li>
							<li>
								<label class="check"><input type="checkbox" name="agree_receive_email"><i></i></label>
								이메일 마케팅정보 수신을 동의합니다. (선택)
							</li>
						</ul>
					</div>
					<div class="buttons">
						<button type="submit">가입하기</button>
					</div>
				</div>
			</form>
			<section class="join-ok">
				<p>회원가입이 완료되었습니다.</p>
				<p>ADERERROR의 제품 즐겨찾기, 적립금, 바우처, 문의하기, 멤버십 등 다양한 혜택을 누려보세요.</p>
				<p>
					공식 카카오톡 채널을 추가하시면 제품 발매 정보와 협업 컬렉션 등 다양한 소식을 빠르게<br>
					확인할 수 있습니다.
				</p>
				<div class="buttons">
					<a href="/login" class="btn">로그인</a>
				</div>
			</section>
		</article>
	</section>
</main>