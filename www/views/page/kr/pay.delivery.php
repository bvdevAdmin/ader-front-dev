<section class="modal-pay-delivery">
	<header>
		배송지 정보
		<button type="button" class="close"></button>
	</header>
	<article>
		<div class="tab">
			<div class="tab-container">
				<ul>
					<li>배송지 목록</li>
					<li>새로 입력</li>
				</ul>
			</div>
			<section>
				<article>
					<p class="empty">
						등록된 배송지 목록이 없습니다.<br>
						<small>상단의 새로 입력 메뉴에서 배송지 정보를 등록해주세요.</small>
					</p>
					<form>
						<ul id="delivery-list"></ul>
						<button type="button" class="btn black">입력 완료</button>
					</form>
				</article>
			</section>
			<section>
				<article>
					<form>
						<div class="form-inline inline-label">
							<input type="text" name="" placeholder=" ">
							<span class="control-label">배송지명 / 예시)집</span>
						</div>
						<div class="form-inline inline-label">
							<input type="text" name="" placeholder=" ">
							<span class="control-label">이름</span>
						</div>
						<div class="form-inline inline-label">
							<input type="text" name="" placeholder=" ">
							<span class="control-label">휴대전화</span>
						</div>
						<div class="form-inline inline-label">
							<input type="text" name="" placeholder=" ">
							<span class="control-label">우편번호</span>
						</div>
						<div class="form-inline inline-label">
							<input type="text" name="" placeholder=" ">
							<span class="control-label">주소</span>
						</div>
						<div class="form-inline inline-label">
							<input type="text" name="" placeholder=" ">
							<span class="control-label">상세주소</span>
						</div>
						<div class="form-inline inline-label">
							<select></select>
							<span class="control-label">배송메세지</span>
						</div>
						<button type="button" class="btn black">입력 완료</button>
					</form>
				</article>
			</section>
		</div>
	</article>
</section>

<script>
$(document).ready(function() {
	$("section.modal .tab-container > ul > li").eq(0).click();
});
</script>