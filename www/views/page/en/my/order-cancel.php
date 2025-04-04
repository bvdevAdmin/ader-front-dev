<link rel="stylesheet" href="/css/mypage/orderlist.css">

<main>
	<div class="order-main-title" data-i18n="o_cancel_order">Cancel order</div>
	<section class="order-detail-section">
		<div class="order-list-container">
			<div class="order-list-box">
				<div class="order-header">
					<div class="order-info">
						<div class="order-number">
							<span data-i18n="m_order_number">Order number</span>
							<a href="">
								<span class="order-number-value"></span>
							</a>
						</div>
						<div class="order-date">
							<span data-i18n="o_order_date">Order date</span>
							<a href="">
								<span class="order-date-value"></span>
							</a>
						</div>
					</div>

					<div class="order-cancel-btn all wh">
						<span data-i18n="o_selectall_cancel">Select all</span>
					</div>	
				</div>

				<div class="order-refund-payment-info">
					<div class="order-header">
						<span class="header-tilte" data-i18n="o_refund_list">Refund list</span>
					</div>
					
					<div class="order-refund-payment-body">
						<div class="order-detail-box payment-info">
							<div class="order-detail-row">
								<span data-i18n="o_subtotal">Subtotal</span>
								<span class="o_product">0</span>
							</div>

							<div class="order-detail-row">
								<span data-i18n="o_subtotal">Customer total</span>
								<span class="o_member">0</span>
							</div>

							<div class="order-detail-row">
								<span data-i18n="m_voucher">Voucher total</span>
								<span class="o_discount">0</span>
							</div>
							
							<div class="order-detail-row">
								<span data-i18n="m_mileage">Mileage total</span>
								<span class="o_mileage">0</span>
							</div>

							<div class="order-detail-row">
								<span data-i18n="o_subtotal">Shipping total</span>
								<span class="o_delivery">0</span>
							</div>
							
							<div class="order-detail-row">
								<span data-i18n="o_subtotal">Shipping return</span>
								<span class="o_return">0</span>
							</div>

							<div class="order-detail-row">
								<span data-i18n="o_subtotal">Cancelable total</span>
								<span class="o_cancel">0</span>
							</div>
						</div>
					</div>
					
					<div class="order-detail-footer">
						<span data-i18n="o_order_total">Total</span>
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
			<textarea name="" id="order-cancel-reason" data-i18n-placeholder="o_detail_reason" placeholder="Please enter a detailed reason. (At least 5 characters)" cols="30" rows="10" spellcheck="false"></textarea>
		</div>
	</section>

	<section class="order-detail-section">
		<div class="order_refund_price_wrap cancel_price_wrap hidden">
			<div>Please enter the amount of the return mileage for the canceled product.</div>
			<div>
				<span>Total refund price: </span>
				<span class="c_refund">0</span>
				<span>USD</span>
			</div>
			
			<div>
				<span>Refundable mileage: </span>
				<span class="min_mileage">0</span><span>USD</span>
				<span class="price_mileage_interval_mark"> ~ </span>
				<span class="max_mileage">0</span><span>USD</span>
				<p class="price_mileage_max_warning_msg"> (Refundable mileage can never be greater than the total refund amount)</p>
			</div>
			
			<div>
				<input class="mileage_refund_price input_mileage" data-min="0" data-max="0" type="number" name="mileage_price" value="0">
				<span>USD</span>
			</div>
			
			<div>
				<div>
					<span>In total refund price</span>
					
					<span class="r_price">0</span>
					<span>, is </span>
					
					<span class="r_cancel">0</span>
					<span>return by price, </span>
					
					<span class="r_mileage">0</span>
					<span>is return by mileage.</span>
				</div>
			</div>
		</div>
		
		<div class="order-noti-wrap order-cancel-noti">
			<div class="div_confirm_price_product">
				<span>Total refund price: </span>
				<span class="c_price">0</span>
				<span>USD</span>
			</div>
			
			<p class="msg_order_cancel_voucher hidden" data-i18n="msg_order_cancel_voucher">If you used a voucher, the total refund amount will be refunded excluding the voucher discount.</p>
			
			<p data-i18n="o_detail_cancel_01">If the total amount of the order after cancellation is less than 300 USD, a delivery fee will be charged, and this amount will be deducted from the refund of the cancelled product.</p>
			<p data-i18n="o_detail_cancel_02">Upon clicking the 'Cancellation Request Completion' button, the order will be immediately cancelled and a refund will be processed.</p>
			<p data-i18n="o_detail_cancel_03">The voucher used at the time of payment will only be restored in case of a total cancellation.</p>
			
			<div class="order-detail-btn cancel-complete-btn bk"><span class="header-tilte" data-i18n="o_cancel_request_completion">Cancellation Request Completion</span></div>
			<a href="/en/my/order" class="order-detail-btn to_orderlist_btn wh"><span class="header-tilte" data-i18n="o_previous_page">Previous page</span></a>
		</div>
	</section>
</main>

<link rel="stylesheet" href="/scripts/static/toast-selectbox/toastui-select-box.min.css"/>
<script src="/scripts/static/toast-selectbox/toastui-select-box.min.js"></script>
