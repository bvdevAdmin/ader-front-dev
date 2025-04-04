<main class="my">
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/as">A/S</a></li>
			<li><a href="/kr/my/as/status">내역</a></li>
			<li><a>현황</a></li>
		</ul>
	</nav>
	<section class="as status wrap-720">
        <article>
			<h2>A/S 현황</h2>
			<div class="infobox">
				<small class="info-text">
					A/S번호 <span id="as_code" data-no=""></span><br>
					신청 날짜 <span id="as_date"></span><br><br>
					
					A/S 진행현황 <span id="as_status"></span>
				</small>
			</div>

            <div><h2 class="border"></h2></div>
            
			<div class="goods as_product">
				
            </div>
            
            <div class="buy">
                <dl><dt>구매처</dt><dd id="purchase-mall"></dd></dl>
                <dl><dt>Bluemark 시리얼코드</dt><dd id="serial-code"></dd></dl>
                <dl><dt>Bluemark 인증 날짜</dt><dd id="reg-date"></dd></dl>
            </div>

            <div class="contents div_progress hidden" data-status="1">
                <dl>
                    <dt class="m_bold">A/S 신청내용</dt>
                    <dd>
                        <div id="as-contents"></div>
                    </dd>

                    <dt class="div_img_P">상품 첨부파일</dt>
                    <dd id="attach-file_P" class="div_img_P"></dd>
					
					<dt class="div_img_R">구매내역 첨부파일</dt>
                    <dd id="attach-file_R" class="div_img_R"></dd>
				</dl>
			</div>
			
			<div class="contents div_progress hidden" data-status="2">
				<dl>
					<dt class="m_bold">반환 배송지</dt>
					<dd>
						<input type="hidden" name="to_idx" value="0">
						
						<div id="delivery-info">
                            A/S가 완료되면 제품을 반환받을 배송지를 선택해주세요.
						</div>
						
						<div class="wrap__select">
							<div class="btn_delivery">배송지 검색</div>
							<div class="form-inline height45">
								<select class="as_memo" name="as_memo">
									<option value="0">배송시 요청사항을 선택해주세요.</optoin>
								</select>
								
								<input class="as_message hidden" type="text" name="as_message" value="">
								<span class="control-label"></span>
							</div>
						</div>
						
						<div class="btn_address">배송지 선택</div>
					</dd>
				</dl>
			</div>
			
			<div class="contents div_progress housing_F hidden" data-status="2">
				<dl>
					<dt class="m_bold">반송방법</dt>
					<dd>
						<div class="order-noti-wrap">
							<div class="order-exchange-box">
								<input type="hidden" class="delivery_type" value="">
								<div class="order-detail-btn btn_type pickup wh" data-housing_type="APL">
									<span class="header-tilte" data-i18n="o_ship_pickup">수거 신청</span>
								</div>
								
								<div class="order-detail-btn btn_type direct wh" data-housing_type="DRC">
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
										<div class="charge_description_APL">
											구매자 책임 사유에 의한 교환이므로 구매자의 선불 발송이 필요합니다.
										</div>
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
								</div>
							</div>
							
							<div>
								<div class="order-detail-btn btn_housing">
									<span class="header-tilte" data-i18n="o_application_completed">A/S 반송</span>
								</div>
							</div>
						</div>
					</dd>
				</dl>
			</div>
			
			<div class="contents div_progress housing_T hidden" data-status="3">
				<dl>
					<dt class="m_bold">반송정보</dt>
					<dd>
						<div id="return-info">
							<dl>
								<dt>반송회사</dt>
								<dd><font class="housing_company">우체국택배</font></dd>
								
								<dt>운송장 번호</dt>
								<dd><font class="housing_num">11111111</font></dd>
								
								<dt>반송 시작일</dt>
								<dd><font class="housing_start_date">2025.01.08</font></dd>
								
								<dt>반송 완료일</dt>
								<dd><font class="housing_end_date">2025.01.08</font></dd>
							</dl>
						</div>
					</dd>
				</dl>
			</div>
			
			<div class="contents div_progress repair_F hidden" data-status="4">
				<dl>
                    <dt class="m_bold">A/S 완료 예정일</dt>
                    <dd id="completion-date"></dd>
				</dl>
			</div>
			
			<div class="contents div_progress repair_F hidden" data-status="4">
				<dl>
                    <dt class="m_bold">수선내용</dt>
                    <dd id="repair-desc"></dd>
				</dl>
			</div>
			
            <div class="contents div_progress repair_T hidden" data-status="5">
				<dl>
                    <dt class="m_bold">A/S 수선비용</dt>
                    <dd id="as-price"></dd>
				</dl>
			</div>
			
			<div class="contents div_progress hidden div_payment" data-status="6">
				<dl>
					<dt class="m_bold">수선비용 결제</dt>
					<dd>
						<div class="btn_payment">결제하기</div>
					</dd>
				</dl>
			</div>
			
			<div class="contents div_progress hidden" data-status="7">
				<dl>
                    <dt class="m_bold">결제수단</dt>
                    <dd id="payment-info"></dd>
                </dl>
            </div>
			
			<div class="contents div_progress div_delivery hidden" data-status="8">
				<dl>
                    <dt class="m_bold">배송정보</dt>
					<dd>
						<div id="return-info">
							<dl>
								<dt>배송회사</dt>
								<dd><font class="delivery_company"></font></dd>
								
								<dt>운송장 번호</dt>
								<dd><font class="delivery_num"></font></dd>
									
								<dt>배송 시작일</dt>
								<dd><font class="delivery_start_date"></font></dd>
								
								<dt>배송 완료일</dt>
								<dd><font class="delivery_end_date"></font></dd>
							</dl>
						</div>
					</dd>
                </dl>
            </div>
			
			<div class="contents div_progress hidden" data-status="9">
				<dl>
                    <dt class="m_bold">A/S 완료</dt>
                    <dd class="complete_date"></dd>
                </dl>
            </div>
			
            <div class="list">
                <a href="/kr/my/as/status" id="btn-list" class="btn">목록 보기</a>
            </div>
        </article>
	</section>
</main>
