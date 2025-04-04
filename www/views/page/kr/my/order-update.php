<style>
.deli-company-list .tui-select-box-dropdown {height:300px;overflow-y:auto;}
</style>

<main>
	<div class="order-main-title" data-i18n ="o_title_exchange_return">교환 / 반품 신청</div>
	<section class="order-detail-section">
		<div class="order-detail-body">
			<div class="order-noti-wrap order-exchange-noti">
				<ul>
					<li data-i18n="o_exchange_return_info_01">· 제품의 사이즈 변경을 원하시는 경우, '교환 신청' 버튼을 선택하시면 변경이 가능합니다.</li>
					<li data-i18n="o_exchange_return_info_02">· 반품 신청 버튼을 통하여 제품별 반품 신청이 가능합니다.</li>
					<li data-i18n="o_exchange_return_info_03">· 교환 및 반품 사유에 따라 배송비가 부과될 수 있습니다.</li>
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
				</div>
				
				<div class="order-body"></div>
			</div>
		</div>
	</section>
	
	<section class="order-detail-section order-exchange">
		<div class="order-detail-body">
			<div class="order-noti-wrap">
				<span class="noti-title" data-i18n="o_how_return">제품 반송 방법</span>
				
				<div class="order-exchange-box">
					<input type="hidden" class="delivery_type" value="">
					<div class="order-detail-btn btn_delivery APL wh" data-delivery_type="APL">
						<span class="header-tilte" data-i18n="o_ship_pickup">수거 신청</span>
					</div>
					
					<div class="order-detail-btn btn_delivery DRC wh" data-delivery_type="DRC">
						<span class="header-tilte" data-i18n="o_ship_directly">직접 발송</span>
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
							<span class="header-title" data-i18n="o_exchange_shippingfee">교환 배송비</span>
						</div>
						<div class="order-body">
							<div class="charge_description_APL"></div>
						</div>
					</div>
				</div>

				<div class="order-description-direct hidden">
					<ul>
						<li data-i18n="o_direct_info_01">교환할 제품에 대해 '직접 발송'을 하시는 경우, 원하시는 배송사를 선택하시어 발송하실 수 있습니다.</li>
						<li data-i18n="o_direct_info_02">제품 선불 발송 이후 아래에 내용을 등록해 주세요.</li>
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
								<span class="header-title" data-i18n="o_delivery_input">배송정보 입력</span>
							</div>
							<span class="noti-title" data-i18n="o_delivery_company">배송 업체</span>
							<div class="deli-company-list"></div>
							<span class="noti-title" data-i18n="as_tracking_number">운송장 번호</span>
							<div class="deli-number">
								<input class="housing_num" type="text" data-i18n-placeholder="j_num_only" placeholder="( - ) 없이 숫자만 입력">
							</div>
						</div>
						<div class="charge-description">
							<div class="order-header">
								<span class="header-title" data-i18n="o_exchange_shippingfee">교환 배송비</span>
							</div>
							<div class="order-body">
								<div class="charge_description_DRC" data-i18n="o_buyer_prepaid">구매자 책임 사유에 의한 교환이므로 구매자의 선불 발송이 필요합니다.</div>
							</div>
						</div>
					</div>
				</div>
				

				<div>
					<div class="order-detail-btn btn_put_order_product">
						<span class="header-tilte" data-i18n="o_application_completed">교환 및 반품 신청 완료</span>
					</div>
					<div class="order-detail-btn" onClick="location.href='/mypage?mypage_type=orderlist'">
						<span class="header-tilte" data-i18n="o_previous_page">이전 페이지</span>
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
							<div class="order-detail-btn same-size-btn wh"><span class="header-tilte" data-i18n="o_same_size">동일 사이즈로 선택</span></div>
							<div class="order-detail-btn check-size-btn wh"><span class="header-tilte" data-i18n="o_select_completed">선택완료</span></div>
						</div>
						<span class="noti-title" data-i18n="o_reason_exchange">교환 사유</span>
							<div class="order-select-box">
								<div class="tui_select reason_depth1_OEX" data-order_status="OEX"></div>
								<div class="tui_select reason_depth2_OEX"></div>
							</div>
							<div class="order-textarea-box">
								<textarea class="reason_memo" id="order-exchange-reason" data-i18n-placeholder="o_detail_reason" placeholder="상세 사유를 입력하세요. (5글자 이상)" cols="30" rows="10"></textarea>
							</div>
					</div>
				</div>
			</div>
		</section>
		
		<section class="order-detail-section order-input-box">
			<div class="order-detail-btn wh btn_tmp_order" data-param_status="OEX">
				<span class="header-tilte" data-i18n="o_save">저장</span>
			</div>
		</section>
	</div>
	
	<!-- 반품신청 팝업 -->
	<div class="order-popup-container-ORF hidden">
		<div class="order-main-title-wrap">
			<div class="order-main-title" data-i18n="o_return_request">반품 신청 제품</div>
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
						<span class="noti-title" data-i18n="o_reason_return">반품 사유</span>
							<div class="order-select-box">
								<div class="tui_select reason_depth1_ORF" data-order_status="ORF"></div>
								<div class="tui_select reason_depth2_ORF"></div>
							</div>
							<div class="order-textarea-box">
								<textarea class="reason_memo" id="order-return-reason" data-i18n-placeholder="o_detail_reason" placeholder="상세 사유를 입력하세요. (5글자 이상)" cols="30" rows="10"></textarea>
							</div>
					</div>
				</div>
			</div>
		</section>
		
		<section class="order-detail-section order-input-box">
			<div class="order-detail-btn wh btn_tmp_order" data-param_status="ORF">
				<span class="header-tilte" data-i18n="o_save">저장</span>
			</div>
		</section>
	</div>
</main>

<link rel="stylesheet" href="/scripts/static/toast-selectbox/toastui-select-box.min.css"/>
<script src="/scripts/static/toast-selectbox/toastui-select-box.min.js"></script>
<script src="https://js.tosspayments.com/v1/payment-widget"></script>