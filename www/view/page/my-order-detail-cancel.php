<main class="my order">
	<?php include 'inc/my.summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/order">주문내역</a></li>
			<li><a>자세히보기</a></li>
			<li><a>주문취소</a></li>
		</ul>
	</nav>
	<section class="order cancel">
		<h1>주문취소</h1>
		<article class="cancel detail">
			<form>
				<dl class="detail-info">
					<dt>
						주문 번호 <span id="order-number"></span><br>
						신청 날짜 <span id="order-date"></span>
						<label>
							전체선택
							<input type="checkbox" name="__select_all" data-inp="no[]">
							<i></i>
						</label>
					</dt>
					<dd class="goods">
						<div class="image"></div>
						<label><input type="checkbox" name="no[]" value=""><i></i></label>
						<big>Montblanc beanie</big>
						<div class="price-qty">
							<div class="price">159,000</div><div class="qty">1</div>
						</div>
						<div class="color">Noir<span class="colorchip" style="background-color:#000"></span></div>
						<div class="size">A1</div>
					</dd>
				</dl>
				<ul class="dot">
					<li>취소 이후 주문 건의 합계 금액이 80,000원 이하일 경우, 2,500원의 배송비가 발생하며 환불 예정인 제품 금액에서 차감 이후 환불처리 됩니다.</li>
					<li>취소 신청 완료 버튼 클릭 이후 즉시 주문 건 취소 및 환불 처리됩니다.</li>
					<li>결제 시 사용하신 바우처는 전체 취소의 경우에만 복원됩니다.</li>
				</ul>
				<div class="buttons">
					<button type="submit">취소 선택</button>
				</div>
			</form>
		</article>
		<article class="cancel-submit detail">
			<form>
				<dl class="detail-info">
					<dt>
						주문 번호 <span id="order-number"></span><br>
						신청 날짜 <span id="order-date"></span>
					</dt>
					<dd class="goods">
						<div class="image"></div>
						<big>Montblanc beanie</big>
						<div class="price-qty">
							<div class="price">159,000</div><div class="qty">1</div>
						</div>
						<div class="color">Noir<span class="colorchip" style="background-color:#000"></span></div>
						<div class="size">A1</div>
					</dd>
				</dl>
				<div class="form-inline inline-label">
					<select name="reason">
					</select>
					<span class="control-label">취소 사유</span>
				</div>
				<div class="form-inline">
					<div class="textarea" contentEditable="true"></div>
					<span class="placeholder">상세 사유를 입력하세요.(5글자 이상)</span>
				</div>
				<ul class="dot">
					<li>취소 이후 주문 건의 합계 금액이 80,000원 이하일 경우, 2,500원의 배송비가 발생하며 환불 예정인 제품 금액에서 차감 이후 환불처리 됩니다.</li>
					<li>취소 신청 완료 버튼 클릭 이후 즉시 주문 건 취소 및 환불 처리됩니다.</li>
					<li>결제 시 사용하신 바우처는 전체 취소의 경우에만 복원됩니다.</li>
				</ul>
				<div class="buttons">
					<button type="submit">취소 신청</button>
				</div>
			</form>
		</article>
		<article class="cancel-submit-ok wrap-480">
			<p>주문 취소 신청이 완료되었습니다.</p>
			<ul class="dot">
				<li>주문 내역에서 해당 제품의 취소 진행과정을 열람하실 수 있습니다.</li>
				<li>취소 신청은 결제 완료 단계에서만 가능합니다.</li>
			</ul>
			<div class="buttons">
				<a href="/my/order#complete" class="btn">내역으로 돌아가기</a>
			</div>
		</article>
	</section>
</main>