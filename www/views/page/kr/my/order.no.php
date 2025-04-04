<main class="my">
	<nav>
		<ul>
			<li>장바구니</li>
			<li>주문/결제</li>
		</ul>
	</nav>
	<section class="pay ok wrap-720">
		<article>
			<h2>주문/결제</h2>
			<div class="infobox">
				<p>주문이 완료되었습니다.</p>
				<small class="info-text">
					주문 번호 <span id="order-number" data-no=""></span><br>
					주문 날짜 <span id="order-date"></span>
				</small>
			</div>

			<dl class="fold">
				<dt><h2 class="border">주문 제품</h2></dt>
				<dd>
					<ul class="list" id="list-product"></ul>
				</dd>
			</dl>

			<dl class="fold">
				<dt><h2 class="border">주문 취소 제품</h2></dt>
				<dd>
					<ul class="list" id="list-cancel"></ul>
				</dd>
			</dl>

			<dl class="fold">
				<dt><h2 class="border">주문 교환 제품</h2></dt>
				<dd>
					<ul class="list" id="list-exchange"></ul>
				</dd>
			</dl>

			<dl class="fold">
				<dt><h2 class="border">주문 반품 제품</h2></dt>
				<dd>
					<ul class="list" id="list-refund"></ul>
				</dd>
			</dl>

			<dl class="fold">
				<dt><h2 class="border">결제 정보</h2></dt>
				<dd>
					<dl class="price-info">
						<dt>제품 합계</dt>
						<dd id="price-total"></dd>
						<dt>회원 할인 합계</dt>
						<dd id="price-member"></dd>
						<dt>바우처 사용</dt>
						<dd id="use-voucher"></dd>
						<dt>적립금 사용</dt>
						<dd id="use-point"></dd>
						<dt>배송비</dt>
						<dd id="price-delivery"></dd>
					</dl>
					<dl class="price-info">
						<dt class="h27">최종 결제 금액</dt>
						<dd class="h27 total" id="price-pay-total"></dd>
					</dl>
					<dl class="price-info pay-method">
						<dt>결제 수단</dt>
						<dd id="pay-method"></dd>
						<dt>결제 일시</dt>
						<dd id="pay-date"></dd>
						<dt></dt>
						<dd><button type="button" id="btn-view-receipt" class="btn small">영수증 보기</button></dd>
					</dl>
				</dd>
			</dl>

			<dl class="fold-recent">
				<dt><h2 class="border">결제 현황</h2></dt>
				<dd>
					<dl class="price-info">
						<dt>제품 합계</dt>
						<dd id="t_price-total"></dd>
						<dt>회원 할인 합계</dt>
						<dd id="t_price-member"></dd>
						<dt>바우처 사용</dt>
						<dd id="t_use-voucher"></dd>
						<dt>적립금 사용</dt>
						<dd id="t_use-point"></dd>
						<dt>배송비</dt>
						<dd id="t_price-delivery"></dd>
						<dt>반환 배송비</dt>
						<dd id="t_delivery-return"></dd>
					</dl>
					<dl class="price-info">
						<dt class="h27">결제 취소 금액</dt>
						<dd class="h27 total" id="t_price-cancel"></dd>
					</dl>
					<dl class="price-info">
						<dt class="h27">잔여 결제 금액</dt>
						<dd class="h27 total" id="t_remain_price"></dd>
					</dl>
				</dd>
			</dl>

			<dl class="fold fold-cancel">
				<dt><h2 class="border">결제 취소 정보</h2></dt>
				<dd>
					<dl class="price-info">
						<dt>제품 합계</dt>
						<dd id="c_price-total"></dd>
						<dt>회원 할인 합계</dt>
						<dd id="c_price-member"></dd>
						<dt>바우처 사용</dt>
						<dd id="c_use-voucher"></dd>
						<dt>적립금 사용</dt>
						<dd id="c_use-point"></dd>
						<dt>추가 배송비</dt>
						<dd id="c_price-delivery"></dd>
						<dt>반환 배송비</dt>
						<dd id="c_delivery-return"></dd>
					</dl>
					<dl class="price-info">
						<dt class="h27">결제 취소 금액</dt>
						<dd class="h27 total" id="c_price-cancel"></dd>
					</dl>
				</dd>
			</dl>

			<dl class="fold fold-refund">
				<dt><h2 class="border">결제 반품 정보</h2></dt>
				<dd>
					<dl class="price-info">
						<dt>제품 합계</dt>
						<dd id="r_price-total"></dd>
						<dt>회원 할인 합계</dt>
						<dd id="r_price-member"></dd>
						<dt>바우처 사용</dt>
						<dd id="r_use-voucher"></dd>
						<dt>적립금 사용</dt>
						<dd id="r_use-point"></dd>
						<dt>추가 배송비</dt>
						<dd id="r_price-delivery"></dd>
						<dt>반환 배송비</dt>
						<dd id="r_delivery-return"></dd>
					</dl>
					<dl class="price-info">
						<dt class="h27">결제 취소 금액</dt>
						<dd class="h27 total" id="r_price-cancel"></dd>
					</dl>
				</dd>
			</dl>

			<dl class="fold fold-delivery">
				<dt><h2 class="border">배송지 정보</h2></dt>
				<dd>
                    <dl class="delivery-info">
						<dt>배송지</dt>
						<dd id="to_place"></dd>
						<dt>수령인</dt>
						<dd id="to_name"></dd>
						<dt>수령인 전화번호</dt>
						<dd id="to_mobile"></dd>
						<dt>우편번호</dt>
						<dd id="to_zipcode"></dd>
                        <dt>배송주소</dt>
						<dd id="to_addr"></dd>
						<dt>상세주소</dt>
						<dd id="to_detail_addr"></dd>
					</dl>
				</dd>
			</dl>

            <dl class="fold fold-delivery">
				<dt><h2 class="border">배송 정보</h2></dt>
				<dd>
                    <dl class="delivery-info">
						<dt>배송 상태</dt>
						<dd id="delivery-status"></dd>
						<dt>배송 회사</dt>
						<dd id="delivery-company"></dd>
						<dt>운송장 번호</dt>
						<dd id="delivery-num"></dd>
						<dt>배송 예정일</dt>
						<dd id="delivery-date"></dd>
                        <dt>배송 시작일</dt>
						<dd id="delivery-start-date"></dd>
                        <dt>배송 종료일</dt>
						<dd id="delivery-end-date"></dd>
					</dl>
				</dd>
			</dl>
            
            <dl>
                <dd>
                    <h3>주문 취소 안내</h3>
					<ul class="dot">
						<li>'결제완료' 단계에서만 취소가 가능합니다.</li>
						<li>주문 취소는 [마이페이지] - [주문내역] 에서 직접 취소가 가능합니다.</li>
					</ul>
					<h3>교환 및 반품 안내</h3>
					<ul class="dot">
						<li>교환 및 반품 접수는 제품 수령일로부터 7일 이내 신청 가능합니다.</li>
						<li>주문 상태가 '배송 완료'일 경우, [마이페이지] - [주문내역] 에서 교환 및 반품 신청이 가능합니다.</li>
						<li>주문 상태가 '배송 중'으로 보여질 경우, 1:1 문의를 통하여 접수 부탁드립니다.</li>
					</ul>
                </dd>
            </dl>
			
			<div class="buttons">
				<a class="btn btn_update">교환/반품 접수하기</a>
				<a href="/kr" class="btn">계속 쇼핑하기</a>
				<a href="/kr/my/order" class="btn">주문내역 보러 가기</a>
			</div>
		</article>
	</section>
</main>