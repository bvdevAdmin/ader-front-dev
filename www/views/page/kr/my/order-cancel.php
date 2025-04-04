<link rel="stylesheet" href="/css/mypage/orderlist.css">

<main>
	<div class="order-main-title" data-i18n="o_cancel_order">주문 취소하기</div>
	<section class="order-detail-section">
		<div class="order-list-container">
			<div class="order-list-box">
				<div class="order-header">
					<div class="order-info">
						<div class="order-number">
							<span data-i18n="m_order_number">주문번호</span>
							<a href="">
								<span class="order-number-value"></span>
							</a>
						</div>
						<div class="order-date">
							<span data-i18n="o_order_date">주문날짜</span>
							<a href="">
								<span class="order-date-value"></span>
							</a>
						</div>
					</div>

					<div class="order-cancel-btn all wh">
						<span data-i18n="o_selectall_cancel">전체 선택</span>
					</div>	
				</div>

				<div class="order-refund-payment-info">
					<div class="order-header">
						<span class="header-tilte" data-i18n="o_refund_list">환불 내역</span>
					</div>
					
					<div class="order-refund-payment-body">
						<div class="order-detail-box payment-info">
							<div class="order-detail-row">
								<span data-i18n="o_subtotal">제품 합계</span>
								<span class="o_product">0</span>
							</div>

							<div class="order-detail-row">
								<span data-i18n="o_subtotal">회원 할인 합계</span>
								<span class="o_member">0</span>
							</div>

							<div class="order-detail-row">
								<span data-i18n="m_voucher">바우처</span>
								<span class="o_discount">0</span>
							</div>
							
							<div class="order-detail-row">
								<span data-i18n="m_mileage">적립금</span>
								<span class="o_mileage">0</span>
							</div>

							<div class="order-detail-row">
								<span data-i18n="o_subtotal">배송비</span>
								<span class="o_delivery">0</span>
							</div>

							<div class="order-detail-row">
								<span data-i18n="o_subtotal">반환 배송비</span>
								<span class="o_return">0</span>
							</div>

							<div class="order-detail-row">
								<span data-i18n="o_subtotal">취소 가능 금액</span>
								<span class="o_cancel">0</span>
							</div>
						</div>
					</div>
					
					<div class="order-detail-footer">
						<span data-i18n="o_order_total">합계</span>
						<span class="o_refund">0</span>
					</div>
				</div>
				
				<div class="order-body">
					
				</div>
			</div>
		</div>
	</section>

	<section class="order-detail-section order-input-box">
		<div class="order-select-box">
			<div class="tui_select reason_depth1_OCC"></div>
			<div class="tui_select reason_depth2_OCC"></div>
		</div>
		
		<div class="order-textarea-box">
			<textarea name="" id="order-cancel-reason" data-i18n-placeholder="o_detail_reason" placeholder="상세 사유를 입력하세요. (5글자 이상)" cols="30" rows="10" spellcheck="false"></textarea>
		</div>
	</section>

	<section class="order-detail-section">
		<div class="order_refund_price_wrap cancel_price_wrap hidden">
			<div>취소 상품에 대한 적립금 환불금액을 입력해주세요.</div>
			<div>
				<span>총 환불 금액: </span>
				<span class="c_refund">0</span>
				<span>원</span>
			</div>
			
			<div>
				<span>적립금 반환 가능 금액: </span>
				<span class="min_mileage">0</span><span>원</span>
				<span class="price_mileage_interval_mark"> ~ </span>
				<span class="max_mileage">0</span><span>원</span>
				<p class="price_mileage_max_warning_msg"> (적립금 반환금액은 절대 총 환불금액 보다 클 수 없음)</p>
			</div>
			
			<div>
				<input class="mileage_refund_price input_mileage" data-min="0" data-max="0" type="number" name="mileage_price" value="0">
				<span>원</span>
			</div>
			
			<div>
				<div>
					<span>총 환불 금액 </span>
					
					<span class="r_price">0</span>
					<span>원 중 </span>
					
					<span class="r_cancel">0</span>
					<span>원은 결제수단으로 환불되고</span>
					
					<span class="r_mileage">0</span>
					<span>원은 적립금으로 환불됩니다.</span>
				</div>
			</div>
		</div>
		
		<div class="order-noti-wrap order-cancel-noti">
			<div class="div_confirm_price_product">
				<span>총 환불 금액: </span>
				<span class="c_price">0</span>
				<span>원</span>
			</div>
			
			<p class="msg_order_cancel_voucher hidden" data-i18n="msg_order_cancel_voucher">바우처를 사용하신 경우, 총 환불금액에서 바우처 할인금액을 제외한 금액이 환불됩니다.</p>
			
			<p data-i18n="o_detail_cancel_01">취소 이후 주문 건의 합계 금액이 80,000원 이하일 경우, 2,500원의 배송비가 발생하며 환불 예정인 제품 금액에서 차감 이후 환불처리됩니다.</p>
			<p data-i18n="o_detail_cancel_02">취소 신청 완료 버튼 클릭 이후 즉시 주문 건 취소 및 환불 처리됩니다.</p>
			<p data-i18n="o_detail_cancel_03">결제 시 사용하신 바우처는 전체 취소의 경우에만 복원됩니다.</p>
			
			<div class="order-detail-btn cancel-complete-btn bk"><span class="header-tilte" data-i18n="o_cancel_request_completion">취소 신청 완료</span></div>
			<a href="/kr/my/order" class="order-detail-btn to_orderlist_btn wh"><span class="header-tilte" data-i18n="o_previous_page">이전 페이지</span></a>
		</div>
	</section>
</main>

<link rel="stylesheet" href="/scripts/static/toast-selectbox/toastui-select-box.min.css"/>
<script src="/scripts/static/toast-selectbox/toastui-select-box.min.js"></script>
