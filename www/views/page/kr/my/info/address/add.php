<style>
.mt_40 {margin-top:40px!important;}
.post-change-result {padding:10px!important;z-index:99;}
@media (min-width: 1024px) {
.post-change-result {width: 100%;margin: 0 !important;background-color: #fff;overflow-y: auto;overflow-x: hidden;max-height: 285px;border: 1px solid #808080;border-top: 0px;top: -32px;}
.post-change-result {width: 100%;margin: 0 !important;background-color: #fff;overflow-y: auto;overflow-x: hidden;max-height: 285px;border: 1px solid #808080;border-top: 0px;top: -32px;}
}
</style>

<main class="my">
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/info">내 정보 관리</a></li>
			<li><a href="/kr/my/info/address">배송지 목록</a></li>
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
					<div id="postcodify" class="input-row"></div>	
					<div class="input-row" style="clear:both;">
						<div class="post-change-result"></div>
					</div>
				</div>
				<div class="form-inline inline-label">
					<input id="to_zipcode" type="number" name="to_zipcode" placeholder=" " readonly required>
					<span class="control-label">우편번호</span>
				</div>
				<div class="form-inline inline-label">
					<input id="to_road_addr" type="text" name="to_road_addr" placeholder=" " readonly required>
					<input id="to_lot_addr" type="hidden" name="to_lot_addr">
					<span class="control-label">주소</span>
				</div>
				<div class="form-inline inline-label">
					<input id="to_detail_addr" type="text" name="to_detail_addr" placeholder=" " required>
					<span class="control-label">상세주소</span>
				</div>
				<div class="form-inline">
					<label><input type="checkbox" name="default_flg" value="T"><i></i>기본 배송지로 저장</label>
				</div>
				<div class="buttons mt_40">
					<button type="submit" class="black">등록하기</button>
				</div>
			</form>
		</article>
	</section>
</main>
