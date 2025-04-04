<link rel="stylesheet" href="/css/mypage/orderlist.css">

<main>
	<div class="order-main-title" data-i18n="o_cancel_order"></div>
	<section class="order-detail-section">
		<div class="order-list-container">
			<div class="order-list-box">
				<div class="order-header">
					<div class="order-info">
						<div class="order-number">
							<span data-i18n="m_order_number"></span>
							<a href="">
								<span class="order-number-value"></span>
							</a>
						</div>
						<div class="order-date">
							<span data-i18n="o_order_date"></span>
							<a href="">
								<span class="order-date-value"></span>
							</a>
						</div>
					</div>

					<div class="order-cancel-btn all wh">
						<span data-i18n="o_selectall_cancel"></span>
					</div>	
				</div>

				<div class="order-refund-payment-info">
					<div class="order-header">
						<span class="header-tilte" data-i18n="o_refund_list"></span>
					</div>
					
					<div class="order-refund-payment-body">
						<div class="order-detail-box payment-info">
							<div class="order-detail-row">
								<span data-i18n="o_subtotal"></span>
								<span class="org_price_product">0</span>
							</div>
							
							<div class="order-detail-row">
								<span data-i18n="o_shipping_total"></span>
								<span class="org_price_delivery">0</span>
							</div>
							
							<div class="order-detail-row">
								<span data-i18n="m_voucher"></span>
								<span class="org_price_discount">0</span>
							</div>
							
							<div class="order-detail-row">
								<span data-i18n="m_mileage"></span>
								<span class="org_price_mileage">0</span>
							</div>
						</div>
					</div>
					
					<div class="order-detail-footer">
						<span data-i18n="o_order_total"></span>
						<span class="org_price_refund">0</span>
					</div>
				</div>
				
				<div class="order-body"></div>
			</div>
		</div>
	</section>

	<section class="order-detail-section order-input-box">
		<div class="order-select-box">
			<div class="tui_select reason_depth1_OCC" data-order_status="OCC"></div>
			<div class="tui_select reason_depth2_OCC"></div>
		</div>
		<div class="order-textarea-box">
			<textarea name="" id="order-cancel-reason" data-i18n-placeholder="o_detail_reason" cols="30" rows="10" spellcheck="false"></textarea>
		</div>

	</section>

	<section class="order-detail-section">
		<div class="order_refund_price_wrap cancel_price_wrap hidden">
			<div>취소 상품에 대한 적립금 환불액을 정해주세요.</div>
			<div>
				<span>총 환불 금액: </span>
				<span class="calc_price_product">0</span>
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
				<input class="mileage_refund_price input_mileage" type="number" value="0">
				<span>원</span>
			</div>
			
			<div>
				<div>
					<span>총 환불 금액 </span>
					
					<span class="res_price_product">0</span>
					<span>원 중 </span>
					
					<span class="res_price_cancel">0</span>
					<span>원은 결제수단으로 환불되고</span>
					
					<span class="res_price_mileage">0</span>
					<span>원은 적립금으로 환불됩니다.</span>
				</div>
			</div>
		</div>
		
		<div class="order-noti-wrap order-cancel-noti">
			<div class="div_confirm_price_product">
				<span>총 환불 금액: </span>
				<span class="confirm_price_product">0</span>
				<span>원</span>
			</div>
			
			<p class="msg_order_cancel_voucher hidden" data-i18n="msg_order_cancel_voucher"></p>
			
			<p data-i18n="o_detail_cancel_01"></p>
			<p data-i18n="o_detail_cancel_02"></p>
			<p data-i18n="o_detail_cancel_03"></p>
			
			<div class="order-detail-btn cancel-complete-btn bk"><span class="header-tilte" data-i18n="o_cancel_request_completion"></span></div>
			<div class="order-detail-btn to_orderlist_btn wh"><span class="header-tilte" data-i18n="o_previous_page"></span></div>
		</div>
	</section>
</main>

<script src="/scripts/mypage/order/order-common.js"></script>
<script src="/scripts/mypage/order/order-cancel.js"></script>