<main class="my">
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/info">내 정보 관리</a></li>
			<li><a href="/kr/my/info/marketing">마케팅 정보 수신 및 활용 동의</a></li>
		</ul>
	</nav>
	<button type="button" id="btn-mobile-history-back"></button>
	<section class="marketing wrap-480">
		<h1>마케팅 정보 수신 및 활용 동의</h1>
		<article>
			<ul class="dot">
				<li>ADERERROR의 제품, 이벤트 및 프로모션, 멤버 혜택 관련 최신 소식을 받아보세요.</li>
				<li>개인정보 수집 및 이용에 대한 자세한 내용은 <a href="/kr/privacy-policy" target="_blank">개인 정보 취급 방침</a>을 확인하세요.</li>
			</ul>
			<form id="frm">
				<input type="hidden" name="action_type" value="MARKETING">
				
				<div class="select-target grid col-3">
					<div>
						<label class="check">
							<input type="checkbox" name="email" value="y"><i></i>이메일
						</label>
					</div>
					<div>
						<label class="check">
							<input type="checkbox" name="sms" value="y"><i></i>SMS
						</label>
					</div>
					<div>
						<label class="check">
							<input type="checkbox" name="tel" value="y"><i></i>전화
						</label>
					</div>
				</div>
                <div class="buttons">
    				<button type="submit" class="black">확인</button>
                </div>
			</form>
		</article>
	</section>
</main>