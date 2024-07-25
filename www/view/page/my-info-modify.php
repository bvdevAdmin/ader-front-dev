<main class="my">
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/info">내 정보 관리</a></li>
			<li>계정 정보 수정</li>
		</ul>
	</nav>
	<button type="button" id="btn-mobile-history-back"></button>
	<section class="info modify">
		<h1>계정 정보 수정</h1>
		<article class="input">
			<form id="frm">
				<div class="form-inline">
					<big class="val" id="member-name"></big>
					<span class="control-label">이름</span>
				</div>
				<div class="form-inline">
					<div class="val" id="member-email"></div>
					<span class="control-label">이메일</span>
				</div>
				<div class="form-inline">
					<big class="val">· · · · · · · · · · </big>
					<span class="control-label">비밀번호</span>
					<button type="button" id="btn-change-pw">비밀번호 변경</button>
				</div>
				<div class="form-inline">
					<div class="val" id="member-tel"></div>
					<span class="control-label">휴대전화</span>
				</div>
				<div class="form-inline">
					<div class="val" id="member-birthday"></div>
					<span class="control-label">생년월일</span>
				</div>
				<div class="buttons">
					<button type="button">본인인증 하기</button>
					<button type="submit" class="black">저장하기</button>
				</div>
			</form>
		</article>
		<a href="/my/info/dropout" class="btn gray">회원 탈퇴</a>
	</section>
</main>