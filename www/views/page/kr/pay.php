<main class="my">
	<nav>
		<ul>
			<li>장바구니</li>
			<li>주문/결제</li>
		</ul>
	</nav>
	<button type="button" id="btn-mobile-history-back"></button>
	<section class="pay">
		<form id="frm">
			<article class="delivery">
				<dl class="fold">
					<dt><h2 class="border">배송 정보</h2></dt>
					<dd>
						<button type="button" id="btn-delivery-change" class="btn small">변경</button>
						<div id="delivery-info">
                            
						</div>
						<div class="form-inline height45">
							<select class="order_memo" name="order_memo">
								<option value="0">배송시 요청사항을 선택해주세요.</optoin>
							</select>
							<input class="delivery_message hidden" type="text" name="delivery_message" value="">
							<span class="control-label"></span>
						</div>
					</dd>
				</dl>

				<input type="hidden" name="to_road_addr">
				<input type="hidden" name="to_lot_addr">
				<input type="hidden" name="to_detail_addr">
				<input type="hidden" name="price_charge_point">
				<input type="hidden" name="to_place">
				<input type="hidden" name="to_name">
				<input type="hidden" name="to_mobile">
				<input type="hidden" name="to_zipcode">
				<input type="hidden" name="order_to_idx" id="order_to_idx">
			</article>
			<article class="goods">
				<dl class="fold">
					<dt><h2 class="border">주문 제품</h2></dt>
					<dd>
						<ul class="list" id="list"></ul>
					</dd>
				</dl>
			</article>
			<article class="voucher">
				<dl class="fold">
					<dt><h2 class="border">바우처/적립금</h2></dt>
					<dd>
						<div class="form-inline height45">
							<div class="tui_select" id="voucher-select"></div>	
							
							<span class="control-label">바우처 <small>(사용가능 <span id="voucher-useful">0</span>장 / 보유 <span id="voucher-has">0</span>장)</small></span>
						</div>
						<div class="form-inline height45">
							<button type="button" class="black width150" id="mileage-button">모두 적용</button>
							<input type="number" name="price_mileage_point" id="mileage-point" value="0" step="1">
							<span class="control-label" id="mileage-display">적립금 <small>(사용가능 0)</small></span>
						</div>
					</dd>
				</dl>
			</article>
			<article class="result">
				<dl class="fold">
					<dt><h2 class="border">결제 정보</h2></dt>
					<dd>
						<dl>
							<dt>제품 합계</dt>
							<dd id="result-goods-total">0</dd>
							<dt>회원 할인 합계</dt>
							<dd id="result-goods-discount">0</dd>
							<dt>바우처 사용</dt>
							<dd id="result-use-voucher">0</dd>
							<dt>적립금 사용</dt>
							<dd id="result-use-mileage">0</dd>
							<dt>배송비</dt>
							<dd id="result-delivery-fee">0</dd>
						</dl>
						<dl class="total">
							<dt>최종 결제 금액</dt>
							<dd id="result-total">0</dd>
						</dl>
					</dd>
				</dl>
				
				<h3>유의사항</h3>
				<ul class="dot">
					<li>바우처 사용 이후, 결제 완료 상태에서 주문 전체 취소 시 복원이 가능합니다.</li>
					<li>주문으로 인한 적립금은 주문 건이 배송 완료 상태로 변경되고 7일이 지난 후 적립됩니다.</li>
					<li>사용 된 적립금은 결제완료 된 제품에 사용 유지되며, 전체 취소의 경우에 환불됩니다.</li>
					<li>소액 결제의 경우 PG사 정책에 따라 결제 금액 제한이 있을 수 있으며, 자세한 사항은 PG 사로 문의 부탁드립니다.</li>
				</ul>
				
				<div class="agree">
					<label>
						<input type="checkbox" name="terms_agree" value="y">
						<i></i>
						<a href="/kr/terms-of-use" target="_blank">이용약관</a>, <a href="/kr/privacy-policy" target="_blank">개인정보수집 및 이용</a> 에 동의합니다. (필수)
					</label>
				</div>
				
				<div class="buttons">
					<button type="submit" class="black">결제하기</button>
				</div>
			</article>
		</form>
	</section>
</main>
