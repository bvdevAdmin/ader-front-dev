<main class="my">
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/as">A/S</a></li>
			<li><a href="/en/my/as/status">History</a></li>
			<li><a>Status</a></li>
		</ul>
	</nav>
	<section class="as status wrap-720">
        <article>
			<h2>A/S status</h2>
			<div class="infobox">
				<small class="info-text">
					A/S number <span id="as_code" data-no=""></span><br>
					Regist date <span id="as_date"></span><br><br>
					
					A/S status <span id="as_status"></span>
				</small>
			</div>

            <div><h2 class="border"></h2></div>
            
			<div class="goods as_product">
				
            </div>
            
            <div class="buy">
                <dl><dt>Mall</dt><dd id="purchase-mall"></dd></dl>
                <dl><dt>Bluemark serial number</dt><dd id="serial-code"></dd></dl>
                <dl><dt>Bluemark regist date</dt><dd id="reg-date"></dd></dl>
            </div>

            <div class="contents div_progress hidden" data-status="1">
                <dl>
                    <dt class="m_bold">A/S Apply</dt>
                    <dd>
                        <div id="as-contents"></div>
                    </dd>

                    <dt class="div_img_P">Attach file product</dt>
                    <dd id="attach-file_P" class="div_img_P"></dd>
					
					<dt class="div_img_R">Attach file evidence</dt>
                    <dd id="attach-file_R" class="div_img_R"></dd>
				</dl>
			</div>
			
			<div class="contents div_progress hidden" data-status="2">
				<dl>
					<dt class="m_bold">Return location</dt>
					<dd>
						<input type="hidden" name="to_idx" value="0">
						
						<div id="delivery-info">
                            Please select the delivery address to receive
						</div>
						
						<div class="wrap__select">
							<div class="btn_delivery">Search address</div>
							<div class="form-inline height45">
								<select class="as_memo" name="as_memo">
									<option value="0">Please select a request for delivery.</optoin>
								</select>
								
								<input class="as_message hidden" type="text" name="as_message" value="">
								<span class="control-label"></span>
							</div>
						</div>
						
						<div class="btn_address">Select address</div>
					</dd>
				</dl>
			</div>
			
			<div class="contents div_progress housing_F hidden" data-status="2">
				<dl>
					<dt class="m_bold">Return</dt>
					<dd>
						<div class="order-noti-wrap">
							<div class="order-exchange-box">
								<input type="hidden" class="delivery_type" value="">
								<div class="order-detail-btn btn_type direct wh" data-housing_type="DRC">
									<span class="header-tilte" data-i18n="o_ship_directly">Direct return</span>
								</div>
							</div>

							<div class="order-description-direct hidden">
								<ul>
									<li data-i18n="o_direct_info_01">If you select 'Direct return', you can selet delivery company to return.</li>
									<li data-i18n="o_direct_info_02">Please enter the contents below after return product.</li>
								</ul>
								<div class="order-header">
									<span class="header-title" data-i18n="o_return_address">Return address</span>
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
								</div>
							</div>
							
							<div>
								<div class="order-detail-btn btn_housing">
									<span class="header-tilte" data-i18n="o_application_completed">A/S Return</span>
								</div>
							</div>
						</div>
					</dd>
				</dl>
			</div>
			
			<div class="contents div_progress housing_T hidden" data-status="3">
				<dl>
					<dt class="m_bold">Return information</dt>
					<dd>
						<div id="return-info">
							<dl>
								<dt>Return company</dt>
								<dd><font class="housing_company"></font></dd>
								
								<dt>Retrun number</dt>
								<dd><font class="housing_num"></font></dd>
								
								<dt>Return start date</dt>
								<dd><font class="housing_start_date"></font></dd>
								
								<dt>Return end date</dt>
								<dd><font class="housing_end_date"></font></dd>
							</dl>
						</div>
					</dd>
				</dl>
			</div>
			
			<div class="contents div_progress repair_F hidden" data-status="4">
				<dl>
                    <dt class="m_bold">A/S Completion date</dt>
                    <dd id="completion-date"></dd>
				</dl>
			</div>
			
			<div class="contents div_progress repair_F hidden" data-status="4">
				<dl>
                    <dt class="m_bold">Repair</dt>
                    <dd id="repair-desc"></dd>
				</dl>
			</div>
			
            <div class="contents div_progress repair_T hidden" data-status="5">
				<dl>
                    <dt class="m_bold">A/S Repair price</dt>
                    <dd id="as-price"></dd>
				</dl>
			</div>
			
			<div class="contents div_progress hidden div_payment" data-status="6">
				<dl>
					<dt class="m_bold">Repair price payment</dt>
					<dd>
						<div class="btn_payment">Payment</div>
					</dd>
				</dl>
			</div>
			
			<div class="contents div_progress hidden" data-status="7">
				<dl>
                    <dt class="m_bold">Payment method</dt>
                    <dd id="payment-info"></dd>
                </dl>
            </div>
			
			<div class="contents div_progress div_delivery hidden" data-status="8">
				<dl>
                    <dt class="m_bold">Delivery</dt>
					<dd>
						<div id="return-info">
							<dl>
								<dt>Delivery company</dt>
								<dd><font class="delivery_company"></font></dd>
								
								<dt>Delivery number</dt>
								<dd><font class="delivery_num"></font></dd>
									
								<dt>Delivery start date</dt>
								<dd><font class="delivery_start_date"></font></dd>
								
								<dt>Delivery end date</dt>
								<dd><font class="delivery_end_date"></font></dd>
							</dl>
						</div>
					</dd>
                </dl>
            </div>
			
			<div class="contents div_progress hidden" data-status="9">
				<dl>
                    <dt class="m_bold">A/S Complete</dt>
                    <dd class="complete_date"></dd>
                </dl>
            </div>
			
            <div class="list">
                <a href="/en/my/as/status" id="btn-list" class="btn">List</a>
            </div>
        </article>
	</section>
</main>
