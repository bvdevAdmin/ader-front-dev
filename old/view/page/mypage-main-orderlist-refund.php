<link rel="stylesheet" href="/css/mypage/orderlist.css">

<main>
	<div class="order-main-title" data-i18n ="o_title_exchange_return"></div>
	<section class="order-detail-section">
		<div class="order-detail-body">
			<div class="order-noti-wrap order-exchange-noti">
				<ul>
					<li data-i18n="o_exchange_return_info_01"></li>
					<li data-i18n="o_exchange_return_info_02"></li>
					<li data-i18n="o_exchange_return_info_03"></li>
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
				</div>
				
				<div class="order-body"></div>
			</div>
		</div>
	</section>
	
	<section class="order-detail-section order-exchange">
		<div class="order-detail-body">
			<div class="order-noti-wrap">
				<span class="noti-title" data-i18n="o_how_return"></span>
				
				<div class="order-exchange-box">
					<input type="hidden" class="delivery_type" value="">
					<div class="order-detail-btn delivery_type_APL_btn wh">
						<span class="header-tilte" data-i18n="o_ship_pickup"></span>
					</div>
					
					<div class="order-detail-btn delivery_type_DRC_btn wh">
						<span class="header-tilte" data-i18n="o_ship_directly"></span>
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
							<span class="header-title" data-i18n="o_exchange_shippingfee"></span>
						</div>
						<div class="order-body">
							<div class="charge_description_APL"></div>
						</div>
					</div>
				</div>

				<div class="order-description-direct hidden">
					<ul>
						<li data-i18n="o_direct_info_01"></li>
						<li data-i18n="o_direct_info_02"></li>
					</ul>
					<div class="order-header">
						<span class="header-title" data-i18n="o_return_address">반송 주소</span>
					</div>
					<div class="order-body">
						<div class="order-detail-box delivery-info">
							<div class="info-wrap"><span class="info-title" data-i18n="p_recipient">수령인</span><span>ADER</span></div>
							<div class="info-wrap"><span class="info-title" data-i18n="p_contact">연락처</span><span>02-792-2232</span></div>
							<div class="info-wrap"><span class="info-title" data-i18n="join_addr">주소</span><span data-i18n="o_return_to_ader">(17135) 경기도 용인시 처인구 이동읍 백옥대로 84-37</span></div>
							<div class="info-wrap"><span class="info-title">&nbsp</span><span class="noti_red" data-i18n="o_return_info">반송 시 주소지를 잘못 기입하거나 정확한 배송 정보가 등록되지 않을 경우 <br>입고 및 검수 처리가 늦어질 수 있습니다.</span></div>
						</div>
						<div class="deli-info">
							<div class="order-header">
								<span class="header-title" data-i18n="o_delivery_input"></span>
							</div>
							<span class="noti-title" data-i18n="o_delivery_company"></span>
							<div class="deli-company-list"></div>
							<span class="noti-title" data-i18n="as_tracking_number"></span>
							<div class="deli-number">
								<input class="housing_num" type="text" data-i18n-placeholder="j_num_only">
							</div>
						</div>
						<div class="charge-description">
							<div class="order-header">
								<span class="header-title" data-i18n="o_exchange_shippingfee"></span>
							</div>
							<div class="order-body">
								<div class="charge_description_DRC" data-i18n="o_buyer_prepaid"></div>
							</div>
						</div>
					</div>
				</div>
				

				<div>
					<div class="order-detail-btn btn_put_order_product">
						<span class="header-tilte" data-i18n="o_application_completed"></span>
					</div>
					<div class="order-detail-btn" onClick="location.href='/mypage?mypage_type=orderlist'">
						<span class="header-tilte" data-i18n="o_previous_page"></span>
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
				<img src="/images/mypage/tmp_img/X-12.svg">
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
							<div class="order-detail-btn same-size-btn wh"><span class="header-tilte" data-i18n="o_same_size"></span></div>
							<div class="order-detail-btn check-size-btn wh"><span class="header-tilte" data-i18n="o_select_completed"></span></div>
						</div>
						<span class="noti-title" data-i18n="o_reason_exchange"></span>
							<div class="order-select-box">
								<div class="tui_select reason_depth1_OEX" data-order_status="OEX"></div>
								<div class="tui_select reason_depth2_OEX"></div>
							</div>
							<div class="order-textarea-box">
								<textarea class="reason_memo" id="order-exchange-reason" data-i18n-placeholder="o_detail_reason" cols="30" rows="10"></textarea>
							</div>
					</div>
				</div>
			</div>
		</section>
		
		<section class="order-detail-section order-input-box">
			<div class="order-detail-btn wh btn_tmp_order" data-param_status="OEX">
				<span class="header-tilte" data-i18n="o_save"></span>
			</div>
		</section>
	</div>
	
	<!-- 반품신청 팝업 -->
	<div class="order-popup-container-ORF hidden">
		<div class="order-main-title-wrap">
			<div class="order-main-title" data-i18n="o_return_request">반품 신청 제품</div>
			<div class="order-close-btn btn_init_order_popup" data-param_status="ORF">
				<img src="/images/mypage/tmp_img/X-12.svg">
			</div>
		</div>
		<section class="order-detail-section order-popup">
			<div class="order-list-container">
				<div class="order-list-box">
					<div class="order-body">
					</div>
					<div class="option-btn-wrap">
						<input class="current-product-idx" type="hidden" data-product_idx="">
						<span class="noti-title" data-i18n="o_reason_return">반품 사유</span>
							<div class="order-select-box">
								<div class="tui_select reason_depth1_ORF" data-order_status="ORF"></div>
								<div class="tui_select reason_depth2_ORF"></div>
							</div>
							<div class="order-textarea-box">
								<textarea class="reason_memo" id="order-return-reason" data-i18n-placeholder="o_detail_reason" cols="30" rows="10"></textarea>
							</div>
					</div>
				</div>
			</div>
		</section>
		
		<section class="order-detail-section order-input-box">
			<div class="order-detail-btn wh btn_tmp_order" data-param_status="ORF">
				<span class="header-tilte" data-i18n="o_save"></span>
			</div>
		</section>
	</div>
</main>

<script src="/scripts/mypage/order/order-common.js"></script>
<script src="/scripts/mypage/order/order-refund.js"></script>
<script src="https://js.tosspayments.com/v1/payment-widget"></script>

<script>
	const clientKey = "test_ck_YZ1aOwX7K8meL9vyEe98yQxzvNPG";
	let tossPayments = TossPayments(clientKey);
</script>