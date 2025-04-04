<style>
.deli-company-list .tui-select-box-dropdown {height:300px;overflow-y:auto;}
</style>

<main>
	<div class="order-main-title" data-i18n ="o_title_exchange_return">Apply exchange / return</div>
	<section class="order-detail-section">
		<div class="order-detail-body">
			<div class="order-noti-wrap order-exchange-noti">
				<ul>
					<li data-i18n="o_exchange_return_info_01">If you want to change the size of the product, you can change it by selecting the 'Exchange request' button.</li>
					<li data-i18n="o_exchange_return_info_02">You can request a return by product through the Return Request button.</li>
					<li data-i18n="o_exchange_return_info_03">Depending on the reason for exchange or return, shipping charges may apply.</li>
				</ul>
			</div>
		</div>
	</section>
	<section class="order-detail-section order-exchange">
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
				</div>
				
				<div class="order-body"></div>
			</div>
		</div>
	</section>
	
	<section class="order-detail-section order-exchange">
		<div class="order-detail-body">
			<div class="order-noti-wrap">
				<span class="noti-title" data-i18n="o_how_return">How to return the product</span>
				
				<div class="order-exchange-box">
					<input type="hidden" class="delivery_type" value="">
					<div class="order-detail-btn btn_delivery APL wh" data-delivery_type="APL">
						<span class="header-tilte" data-i18n="o_ship_pickup">Apply to pick up</span>
					</div>
					
					<div class="order-detail-btn btn_delivery DRC wh" data-delivery_type="DRC">
						<span class="header-tilte" data-i18n="o_ship_directly">Ship directly</span>
					</div>
				</div>

				<div class="order-description-pickup hidden">
					<ul>
						<li>교환할 제품에 대해 '수거 신청'하시면 택배사에 직접 연락하지 않아도 되며, <br>수거 전 택배기사님이 연락 후 방문합니다.</li>
						<li>'수거 신청'의 경우 최초 배송받은 주소지로만 방문이 가능합니다.</li>
					</ul>
					<div class="deli-section"></div>
					<div class="charge-description">
						<div class="order-header">
							<span class="header-title" data-i18n="o_exchange_shippingfee">Exchange shipping Fee</span>
						</div>
						<div class="order-body">
							<div class="charge_description_APL"></div>
						</div>
					</div>
				</div>

				<div class="order-description-direct hidden">
					<ul>
						<li data-i18n="o_direct_info_01">If you are 'directly sending' the product to be exchanged, you can choose the courier you want and send it.</li>
						<li data-i18n="o_direct_info_02">After the product is sent prepaid, please register the details below.</li>
					</ul>
					<div class="order-header">
						<span class="header-title" data-i18n="o_return_address">Refund address</span>
					</div>
					<div class="order-body">
						<div class="order-detail-box delivery-info">
							<div class="info-wrap"><span class="info-title" data-i18n="p_recipient">Recipient</span><span>ADER</span></div>
							<div class="info-wrap"><span class="info-title" data-i18n="p_contact">Contact</span><span>02-792-2232</span></div>
							<div class="info-wrap"><span class="info-title" data-i18n="join_addr">Address</span>
							<span data-i18n="o_return_to_ader">84-37, Baegok-daero, Idong-eup, Cheoin-gu, Yongin-si, Gyeonggi-do, Republic of Korea</span></div>
							<div class="info-wrap">
								<span class="info-title">&nbsp</span>
								<span class="noti_red" data-i18n="o_return_info">
									If the return address is incorrectly entered or accurate delivery information is not registered,<br>
									receiving and inspection processing may be delayed.
								</span>
							</div>
						</div>
						
						<div class="deli-info">
							<div class="order-header">
								<span class="header-title" data-i18n="o_delivery_input">Input delivery information</span>
							</div>
							<span class="noti-title" data-i18n="o_delivery_company">Delivery company</span>
							<div class="deli-company-list"></div>
							<span class="noti-title" data-i18n="as_tracking_number">Tracking number</span>
							<div class="deli-number">
								<input class="housing_num" type="text" data-i18n-placeholder="j_num_only" placeholder="Type without (-) hyphen.">
							</div>
						</div>
						<div class="charge-description">
							<div class="order-header">
								<span class="header-title" data-i18n="o_exchange_shippingfee">Exchange shipping Fee</span>
							</div>
							<div class="order-body">
								<div class="charge_description_DRC" data-i18n="o_buyer_prepaid">The exchange is due to the buyer's responsibility, so the buyer's prepaid shipment is necessary.</div>
							</div>
						</div>
					</div>
				</div>
				

				<div>
					<div class="order-detail-btn btn_put_order_product">
						<span class="header-tilte" data-i18n="o_application_completed">Exchange and return application completed</span>
					</div>
					<div class="order-detail-btn" onClick="location.href='/mypage?mypage_type=orderlist'">
						<span class="header-tilte" data-i18n="o_previous_page">Previous page</span>
					</div>
				</div>

			</div>
		</div>
	</section>
	
	<!-- 교환신청 팝업 -->
	<div class="order-popup-container-OEX hidden">
		<div class="order-main-title-wrap">
			<div class="order-main-title" data-i18n="o_exchange_Request"></div>
			<div class="order-close-btn btn_init_order_popup" data-param_status="OEX">
				<div class="close"></div>
			</div>
		</div>
		<section class="order-detail-section order-popup">
			<div class="order-list-container">
				<div class="order-list-box">
					<div class="order-body">
					</div>
					<div class="option-btn-wrap">
						<div class="option-btn-box">
							<input class="current-product-idx" type="hidden" data-product_idx="">
							<div class="order-detail-btn same-size-btn wh"><span class="header-tilte" data-i18n="o_same_size">Select the same size</span></div>
							<div class="order-detail-btn check-size-btn wh"><span class="header-tilte" data-i18n="o_select_completed">Selection completed</span></div>
						</div>
						<span class="noti-title" data-i18n="o_reason_exchange">Reason for exchange</span>
							<div class="order-select-box">
								<div class="tui_select reason_depth1_OEX" data-order_status="OEX"></div>
								<div class="tui_select reason_depth2_OEX"></div>
							</div>
							<div class="order-textarea-box">
								<textarea class="reason_memo" id="order-exchange-reason" data-i18n-placeholder="o_detail_reason" placeholder="Please enter a detailed reason. (At least 5 characters)" cols="30" rows="10"></textarea>
							</div>
					</div>
				</div>
			</div>
		</section>
		
		<section class="order-detail-section order-input-box">
			<div class="order-detail-btn wh btn_tmp_order" data-param_status="OEX">
				<span class="header-tilte" data-i18n="o_save">Save</span>
			</div>
		</section>
	</div>
	
	<!-- 반품신청 팝업 -->
	<div class="order-popup-container-ORF hidden">
		<div class="order-main-title-wrap">
			<div class="order-main-title" data-i18n="o_return_request">Return request product</div>
			<div class="order-close-btn btn_init_order_popup" data-param_status="ORF">
				<div class="close"></div>
			</div>
		</div>
		<section class="order-detail-section order-popup">
			<div class="order-list-container">
				<div class="order-list-box">
					<div class="order-body">
					</div>
					<div class="option-btn-wrap">
						<input class="current-product-idx" type="hidden" data-product_idx="">
						<span class="noti-title" data-i18n="o_reason_return">Reason for return</span>
							<div class="order-select-box">
								<div class="tui_select reason_depth1_ORF" data-order_status="ORF"></div>
								<div class="tui_select reason_depth2_ORF"></div>
							</div>
							<div class="order-textarea-box">
								<textarea class="reason_memo" id="order-return-reason" data-i18n-placeholder="o_detail_reason" placeholder="Please enter a detailed reason. (At least 5 characters)" cols="30" rows="10"></textarea>
							</div>
					</div>
				</div>
			</div>
		</section>
		
		<section class="order-detail-section order-input-box">
			<div class="order-detail-btn wh btn_tmp_order" data-param_status="ORF">
				<span class="header-tilte" data-i18n="o_save">Save</span>
			</div>
		</section>
	</div>
</main>

<link rel="stylesheet" href="/scripts/static/toast-selectbox/toastui-select-box.min.css"/>
<script src="/scripts/static/toast-selectbox/toastui-select-box.min.js"></script>
<script src="https://js.tosspayments.com/v1/payment-widget"></script>