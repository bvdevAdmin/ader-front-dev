<main class="my">
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/info">내 정보 관리</a></li>
			<li>회원탈퇴</li>
		</ul>
	</nav>
	<section class="info drop">
		<h1>회원탈퇴</h1>
		<article class="dropout">
			<h2>소멸 예정 내역</h2>
			<ul class="table-info">
				<li>
					적립금<div class="number"><span id="my-point">0</span></div>
				</li>
				<li>
					바우처<div class="number text-underline"><span id="my-voucher"></span> <small>개</small></div>
				</li>
				<li>
					위시리스트<div class="number text-underline"><span class="wishlist" id="my-wishlist"></span></div>
				</li>
			</ul>
			
			<h2 class="border">탈퇴 사유</h2>
			<form id="frm">
				<div class="reason">
					<label class="check"><input type="checkbox" name="reason" value="배송불만족"><i></i>배송불만족</label>
					<label class="check"><input type="checkbox" name="reason" value="교환 / 반품 불만족"><i></i>교환 / 반품 불만족</label>
					<label class="check"><input type="checkbox" name="reason" value="제품 / 가격 / 품질 불만족"><i></i>제품 / 가격 / 품질 불만족</label>
					<label class="check"><input type="checkbox" name="reason" value="회원 혜택 부족"><i></i>회원 혜택 부족</label>
					<label class="check"><input type="checkbox" name="reason" value="개인정보 유출 우려"><i></i>개인정보 유출 우려</label>
					<label class="check"><input type="checkbox" name="reason" value="이용 빈도 낮음"><i></i>이용 빈도 낮음</label>
					<label class="check"><input type="checkbox" name="reason" value="사이트 이용 불편"><i></i>사이트 이용 불편</label>
					<label class="check"><input type="checkbox" name="reason" value="기타"><i></i>기타</label>
				</div>
				<div class="form-inline no-label">
					<div class="textarea" contentEditable="true"></div>
					<span class="placeholder">의견을 자유롭게 입력해주세요.</span>
				</div>
				<ul class="agree">
					<li>
						<label class="check"><input type="checkbox" name="agree_1" value="y"><i></i>회원탈퇴 안내를 모두 확인하였으며 탈퇴에 동의합니다. (필수)</label>
					</li>
					<li>
						<label class="check"><input type="checkbox" name="agree_2" value="y"><i></i>적립금 잔여금액, 바우처 자동 소멸에 동의합니다. (필수)</label>
					</li>
				</ul>
				<ul class="dot">
					<li>회원 탈퇴 후 재가입은 언제든 가능합니다.</li>
					<li>회원 탈퇴 시점을 기준으로 배송중 혹은 반품이나 교환 중에 있는 물품이 없을 때만 탈퇴 처리가 가능합니다.</li>
					<li>회원 탈퇴 시 보유하고 계신 바우처, 적립금이 자동 소멸되며 복구되지 않습니다.</li>
					<li>
						전자상거래 등에서의 소비자보호에 관한 법률, 통신비밀보호법 등 관련 법령의 규정에 의하여 아래와 같이 개인정보가 일정 기간 보관됩니다.
						<ul>
							<li>계약 또는 청약철회 등에 관한 기록 : 5년</li>
							<li>대금결제 및 재화 등의 공급에 관한 기록 : 5년</li>
							<li>소비자의 불만 또는 분쟁처리에 관한 기록 : 3년</li>
							<li>웹사이트 방문 기록 : 3개월</li>
						</ul>
					</li>
				</ul>
				<div class="buttons">
					<button type="submit" class="black">탈퇴</button>
					<button type="button">취소</button>
				</div>
			</form>
		</article>
		<article class="dropout-ok">
			<p>회원 탈퇴가 완료되었습니다.<br><br>그동안 이용해 주셔서 감사합니다.</p>
			<a href="/kr" class="btn">처음 화면으로 돌아가기</a>
		</article>
	</section>
</main>