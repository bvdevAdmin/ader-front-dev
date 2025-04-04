<section class="pwchange width-480">
	<header>
		<font class="t_01"></font>
		<button type="button" class="close"></button>
	</header>
	<section>
		<article>
			<div class="form-inline inline-label">
				<button type="button" class="pw-view-toggle"></button>
				<input type="password" name="member_pw" placeholder=" " required>
				<span class="vaild t_02"></span>
				<span class="control-label t_03"></span>
			</div>
			<div class="form-inline inline-label">
				<button type="button" class="pw-view-toggle"></button>
				<input type="password" name="pw_confirm" placeholder=" " required>
				<span class="vaild t_04"></span>
				<span class="control-label t_05"></span>
			</div>
			<div class="remark">
				<ul class="dot">
					<li class="t_06"></li>
					<li class="t_07"></li>
					<li class="t_08"></li>
				</ul>
			</div>
		</article>
	</section>
	<footer>
		<button type="buttom" class="btn black btn_pw t_09"></button>
	</footer>
</section>

<script>
$(document).ready(function() {
	let t_column = {
		KR : {
			't_01' : "비밀번호 변경",
			't_02' : "비밀번호를 정확하게 기입해주세요.",
			't_03' : "새 비밀번호",
			't_04' : "비밀번호가 일치하지 않습니다.",
			't_05' : "새 비밀번호 확인",
			't_06' : "영문 + 숫자 + 특수문자 조합 8 - 16 자리",
			't_07' : "입력가능 특수문자<br>!@#$%^()_-={}[]|;:<>,.?/",
			't_08' : "공백 입력 불가능",
			't_09' : "변경"
		},
		EN : {
			't_01' : "Change password",
			't_02' : "Please enter the password correctly",
			't_03' : "New password",
			't_04' : "Password does not match",
			't_05' : "Password confirm",
			't_06' : "English + Number + Character Combination 8 - 16 digits",
			't_07' : "Enterable characters<br>!@#$%^()_-={}[]|;:<>,.?/",
			't_08' : "Unable to enter blanks",
			't_09' : "Change"
		}
	}

	for (let i=1; i<=9; i++) {
		$(`.t_0${i}`).text(`${t_column[config.language][`t_0${i}`]}`);
	}

	clickBTN_pw();
});

</script>