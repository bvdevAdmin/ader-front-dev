<section class="pwchange width-480">
	<header>
		비밀번호 변경
		<button type="button" class="close"></button>
	</header>
	<section>
		<article>
			<div class="form-inline inline-label">
				<button type="button" class="pw-view-toggle"></button>
				<input type="password" name="member_pw" placeholder=" " required>
				<span class="vaild">비밀번호를 정확하게 기입해주세요.</span>
				<span class="control-label">새 비밀번호</span>
			</div>
			<div class="form-inline inline-label">
				<button type="button" class="pw-view-toggle"></button>
				<input type="password" name="member_pw2" placeholder=" " required>
				<span class="vaild">비밀번호가 일치하지 않습니다.</span>
				<span class="control-label">새 비밀번호 확인</span>
			</div>
			<div class="remark">
				<ul class="dot">
					<li>영문 + 숫자 + 특수문자 조합 8 - 16 자리</li>
					<li>입력가능 특수문자<br>!@#$%^()_-={}[]|;:<>,.?/</li>
					<li>공백 입력 불가능</li>
				</ul>
			</div>
		</article>
	</section>
	<footer>
		<button type="submit" class="btn black">변경</button>
	</footer>
</section>