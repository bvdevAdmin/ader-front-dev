<main class="my">
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/info">내 정보 관리</a></li>
			<li><a href="/my/info/address">배송지 목록</a></li>
			<li><a>배송지 추가</a></li>
		</ul>
	</nav>
	<button type="button" id="btn-mobile-history-back"></button>
	<section class="address wrap-480">
		<article class="add">
			<h1>배송지 추가</h1>
			<form id="frm">
				<input type="hidden" name="no">
				<input type="hidden" name="province_idx">
				<input type="hidden" name="to_lot_addr">
				<div class="form-inline inline-label">
					<input type="text" name="to_place" placeholder=" " required>
					<span class="control-label">배송지명 / 예시)집</span>
				</div>
				<div class="form-inline inline-label">
					<input type="text" name="to_name" placeholder=" " required>
					<span class="control-label">이름</span>
				</div>
				<div class="form-inline inline-label">
					<input type="tel" name="to_mobile" placeholder=" " required>
					<span class="control-label">휴대전화</span>
				</div>
				<div class="form-inline inline-label">
					<button type="button">검색</button>
					<input type="number" name="to_zipcode" placeholder=" " readonly required>
					<span class="control-label">우편번호</span>
				</div>
				<div class="form-inline inline-label">
					<input type="text" name="to_road_addr" placeholder=" " required>
					<span class="control-label">주소</span>
				</div>
				<div class="form-inline inline-label">
					<input type="text" name="to_detail_addr" placeholder=" " required>
					<span class="control-label">상세주소</span>
				</div>
				<div class="form-inline">
					<label><input type="checkbox" name="default_flg"><i></i>기본 배송지로 저장</label>
				</div>
				<div class="buttons">
					<button type="submit" class="black">등록하기</button>
				</div>
			</form>
		</article>
	</section>
</main>