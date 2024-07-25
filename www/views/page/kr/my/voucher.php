<main class="my">
    <?php include '_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/voucher">바우처</a></li>
		</ul>
	</nav>
	<section class="voucher wrap-720">
		<article>
			<h2 class="border">바우처 등록</h2>
			<div class="submit-ok">
				바우처 등록이 완료되었습니다.
				<ul class="dot">
					<li>하단의 내역 메뉴에서 자세한 확인이 가능합니다.</li>
				</ul>
				<div class="buttons">
					<button type="button" id="btn-voucher-submit-close">내역 확인하기</button>
				</div>
			</div>
			<div class="submit">
				<form id="frm-voucher">
					<div class="form-inline">
						<button type="submit">등록</button>
						<input type="text" name="voucher" placeholder=" " required>
						<div class="control-label inline">바우처 코드</div>
					</div>	
				</form>
				<ul class="dot">
					<li>대소문자를 구분하여 입력해 주세요.</li>
					<li>사용 기간이 만료된 바우처는 등록할 수 없습니다.</li>
					<li>바우처의 발급 및 사용 기간을 꼭 확인해 주세요.</li>
				</ul>
			</div>
			<h2 class="border">
				바우처 내역
				<a href="/kr/my/voucher/detail">자세히 보기</a>
			</h2>
			<ul class="voucher-list" id="list">
			</ul>
			<h2 class="border">유의사항</h2>
			<ul class="dot margin20">
				<li>1개의 주문 건에 1개의 바우처 사용이 가능합니다.</li>
				<li>주문 전체 취소 이후 바우처는 즉시 복원됩니다.</li>
				<li>유효기간이 지난 바우처는 재발행되지 않습니다.</li>
				<li>바우처에 따라 구매상품이 제한될 수 있습니다.</li>
			</ul>
		</article>
	</section>
</main>