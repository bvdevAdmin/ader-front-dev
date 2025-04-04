<div class="tab three as_tab_current">
	<input class="as_idx" type="hidden" value="">
    <p class="as_status_title" data-i18n="as_status">A/S 현황</p>
    <div class="as__table__container">
        <div class="as__contents__table as_current_list" id="as_table">
		</div>

        <div class="as__contents__table as_current_status hidden">
            <div class="as_item_status">
				
            </div>
            
			<button class="step_btn_APL btn_step" data-i18n="as_request_step">
                01. 신청
            </button>
            
			<div class="as_step_contents contents_APL contents_APL_RWT">
                <div>
                    <p data-i18n="as_request_submitted">제품 A/S 신청이 완료되었습니다.</p>
                    <P data-i18n="as_request_updated_soon">신청 내역 검토 이후 다음 단계로 변경됩니다.</p>
                    <div class="as_table_align_l">
                        <div>
                            <span data-i18n="as_request_submitted_on">요청일</span>
                            <span class="create_date"></span>
                        </div>
                        <div class="div_as_contents">
                            <span class="as_apply_contents_title" data-i18n="as_request_comment">요청내용</span>
							<br/>
                            <span class="as_contents"></span>
                        </div>
                        <div class="as_img_wrap">
                            <div class="as_img_box product_img_box">
                                <div class="as_img_title" data-i18n="as_product_img">제품 이미지</div>
                                <div class="as_product_img"></div>
                            </div>
                            <div class="as_img_box receipt_img_box">
                                <div class="as_img_title" data-i18n="as_proof_img">구매 증빙 이미지</div>
                                <div class="as_receipt_img"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			
            <div class="as_step_contents contents_APL contents_APL_RPA">
                <div>
                    <p data-i18n="as_complete_collect_msg">A/S 서비스 접수가 완료되었으며 회수 준비 중입니다.</p>
                    <div class="as_table_align_l">
                        <div>
                            <span data-i18n="as_request_submitted_on">요청일</span>
                            <span class="create_date"></span>
                        </div>
                        <div class="div_as_contents">
                            <span data-i18n="as_request_comment">요청내용</span>
							<br/>
                            <span class="as_contents"></span>
                        </div>
                        <div class="as_img_wrap">
                            <div class="as_img_box product_img_box">
                                <div class="as_img_title" data-i18n="as_product_img">제품 이미지</div>
                                <div class="as_product_img"></div>
                            </div>
                            <div class="as_img_box receipt_img_box">
                                <div class="as_img_title" data-i18n="as_proof_img">구매 증빙 이미지</div>
                                <div class="as_receipt_img"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			
			<div class="as_step_contents contents_APL contents_APL_RPA">
                <div>
                    <p data-i18n="as_not_available_msg">해당 제품은 A/S 불가 제품이며 반송 예정입니다.</p>
                    <div class="as_table_align_l">
                        <div>
                            <span data-i18n="as_request_submitted_on">요청일</span>
                            <span class="create_date"></span>
                        </div>
                        <div class="div_as_contents">
                            <span data-i18n="as_request_comment">요청내용</span>
							<br/>
                            <span class="as_contents"></span>
                        </div>
                        <div class="as_img_wrap">
                            <div class="as_img_box product_img_box">
                                <div class="as_img_title" data-i18n="as_product_img">제품 이미지</div>
                                <div class="as_product_img"></div>
                            </div>
                            <div class="as_img_box receipt_img_box">
                                <div class="as_img_title" data-i18n="as_proof_img">구매 증빙 이미지</div>
                                <div class="as_receipt_img"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
			<button class="step_btn_HOS btn_step" data-i18n="as_ruturn_step">
                02. 회수
            </button>
            
			<div class="">
                <div class="as_step_contents contents_HOS contents_HOS_DPG">
                    <p data-i18n="as_ruturn_info">제품의 A/S를 위하여 발송 후 배송 정보를 입력해 주세요.</p>
                    <div class="return_address_wrap">
                        <p data-i18n="as_ruturn_address">발송 주소 서울시 성동구 연무장길 53 삼영빌딩 3층 ADER A/S</p>
                        <p>
                            <p data-i18n="as_contect_txt">연락처</p>
                            <p> 02-792-2232</p>
                        </p>
                    </div>
                    <div id="as_step_btn_two" class="as_order_status_box open_shipping" data-i18n="as_shipping_info">
                        배송정보 입력
                    </div>
                    <div class="as_img_wrap">
                        <div class="as_img_box product_img_box">
                            <div class="as_img_title" data-i18n="as_product_img">제품 이미지</div>
                            <div class="as_product_img"></div>
                        </div>
                        <div class="as_img_box receipt_img_box">
                            <div class="as_img_title" data-i18n="as_proof_img">구매 증빙 이미지</div>
                            <div class="as_receipt_img"></div>
                        </div>
                    </div>
                </div>
				
                <div class="as_shipping_form hidden">
                    <div class="as_shipping_form_title">
                        <p data-i18n="as_shipping_info">배송정보 입력</p>
                        <button class="close_shipping">
                            <img src="/images/mypage/tmp_img/X-12.svg" />
                        </button>
                    </div>
                    <div class="housing_data_wrap">
                        <div class="housing-company-list"></div>
                        <input id="trackingNumber" class="housing_num" type="text" placeholder="운송장 번호를 입력해주세요." data-i18n-placeholder="as_shipping_tracking">
                    </div>
                    <button class="black__full__width__btn put_housing" data-i18n="as_shipping_save">
                        입력 완료
                    </button>
                </div>
				
                <div class="as_step_contents contents_HOS contents_HOS_DCP">
                    <div>
                        <!-- <p data-i18n="as_waiting_shipment">A/S 신청하신 제품의 입고 대기 중입니다.</p>
                        <p data-i18n="as_receive_updated">입고 및 제품 확인 이후 다음 단계로 변경됩니다.</p> -->
                        <p data-i18n="as_shipping_info_regi_msg_01">배송정보 등록이 완료되었습니다.</p>
                        <p data-i18n="as_shipping_info_regi_msg_02">송장 번호 오입력 시 입고 지연될 수 있습니다.</p>
                        <p data-i18n="as_shipping_info_regi_msg_03">입고 및 제품 확인 이후 다음 단계로 변경됩니다.</p>
                        <div class="as_table_align_l">
                            <div>
                                <span data-i18n="as_receive_submiited">입력 일시</span>
                                <span class="housing_start_date"></span>
                            </div>
                            <div>
                                <span data-i18n="as_shipping_company_01">배송사</span>
                                <span class="housing_company"></span>
                            </div>
                            <div>
                                <span data-i18n="as_tracking_number">운송장 번호</span>
                                <span class="housing_company">
                                    <a class="housing_num" href="https://www.cjlogistics.com/ko/tool/parcel/tracking" id="sample_a" target="_blank" data-i18n="as_tracking_number"></a>
                                </span>
                            </div>
                        </div>
                        <div class="as_img_wrap">
                            <div class="as_img_box product_img_box">
                                <div class="as_img_title" data-i18n="as_product_img">제품 이미지</div>
                                <div class="as_product_img"></div>
                            </div>
                            <div class="as_img_box receipt_img_box">
                                <div class="as_img_title" data-i18n="as_proof_img">구매 증빙 이미지</div>
                                <div class="as_receipt_img"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
            <button class="step_btn_RPR btn_step" data-i18n="as_repair_step">
                03. 수선
            </button>
            
			<div class="as_step_contents contents_RPR">
                <div>
                    <p data-i18n="as_repair_3weeks">제품이 입고되어 수선 중이며 최대 3주 소요될 수 있습니다.</p>
                    <div>
                        <div class="as_table_align_l RPR">
                            <div>
                                <span class="data_title" data-i18n="as_repair_collected">입고확인</span>
                                <span class="housing_end_date"></span>
                            </div>
                            <div class="div_as_contents">
                                <span class="data_title" data-i18n="as_repair_request">요청사항</span>
								<br/>
                                <span class="as_contents"></span>
                            </div>
                            <div>
                                <span class="data_title" data-i18n="as_repair_method">수선 방법</span>
                                <span class="repair_desc"></span>
                            </div>
                            <div>
                                <span class="data_title" data-i18n="as_repair_completion">예상 완료일</span>
                                <span class="completion_date"></span>
                            </div>
                            <div class="as_img_wrap">
                                <div class="as_img_box product_img_box">
                                    <div class="as_img_title" data-i18n="as_product_img">제품 이미지</div>
                                    <div class="as_product_img"></div>
                                </div>
                                <div class="as_img_box receipt_img_box">
                                    <div class="as_img_title" data-i18n="as_proof_img">구매 증빙 이미지</div>
                                    <div class="as_receipt_img"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
            <button class="step_btn_APG btn_step" data-i18n="as_payment_step">
                04. 결제
            </button>
            <div class="as_step_contents contents_APG">
                <div class="as_step_contents_wrap">
                    <p data-i18n="as_payment_repaired">수선이 완료되었습니다.</p>
                    <div class="as_price_wrap">
                        <span data-i18n="as_repair_charge">A/S 요금</span>
                        <span class="as_price"></span>
                    </div>
                    <p data-i18n="as_payment_within">결제 이후 영업일 2일 내 제품 배송이 시작됩니다.</p>
					<p class="as_order_to_alert hidden" data-i18n="as_shipping_info_msg">배송정보를 입력해주세요.</p>
                    <div class="as_order_to_data hidden">
                        <div class="tab_title" data-i18n="s_shipping_address">배송지 정보</div>
                        <div class="as_order_to_data_wrap">
                            <input class="as_to_lot_addr" type="hidden" name="lot_addr">
                            <input class="as_to_road_addr" type="hidden" name="road_addr">
                            <input class="as_to_country_code" type="hidden" name="country_code">
                            <input class="as_to_province_idx" type="hidden" name="province_idx">
                            <input class="as_to_detail_addr" type="hidden" name="to_detail_addr">
                            <input class="as_to_city" type="hidden" name="to_city">

                            <div class="as_to_place"></div>
                            <div class="as_to_name"></div>
                            <div class="as_to_mobile"></div>
                            <div class="as_to_zipcode"></div>
                            <div class="as_to_address"></div>
                            <div class="as_order_memo"></div>
                        </div>
                    </div>
                    <div class="as_payment_complete_noti_wrap hidden">
                        <span>결제가 완료되었습니다.</span>
                        <a class="as_receipt_link" target="_blank" rel="noopener noreferrer" data-i18n="oc_view_receipts">영수증 보기</a>
                    </div>
                    <div class="as_order_btn_wrap">
                        <button class="as_order_btn add_order_to_btn" type="button" data-i18n="as_shipping_info">
                            배송 정보 입력
                        </button>
                        <button class="as_order_btn edit_order_to_btn" type="button" data-i18n="as_shipping_modify">
                            배송 정보 수정
                        </button>
                        <div class="as_order_btn" id="as_payment_btn" data-i18n="s_checkout">
                            결제하기
                        </div>
                    </div>
                </div>
            </div>
            <div class="order_to_popup hidden">
                <div class="close_btn">
                    <img src="/images/mypage/tmp_img/X-12.svg">
                </div>
                <div class="tab_header">
                    <div class="tab_title" data-i18n="s_shipping_address">배송지 정보</div>
                    <div class="tab_button_wrap">
                        <div class="order_to_list_btn" data-i18n="s_addr_list">배송지 목록</div>
                    </div>
                </div>
                <div class="tab_body">
                    <div class="as_order_to_list hidden">
                        <div class="order_to_list_header">
                            <div class="order_to_list_title" data-i18n="s_addr_list">배송지 목록</div>
                            <div class="order_to_list_close_btn">
                                <img src="/images/mypage/tmp_img/X-12.svg">
                            </div>
                        </div>
                        <div class="order_to_list_body">
                            
                        </div>
                    </div>
					
					<div class="as_order_to_input_wrap">
                        <div class="tab_input_wrap">
                            <div class="tab_input_title" data-i18n="p_place">배송지명</div>
                            <input class="to_place" type="text" placeholder="예) 집" data-i18n-placeholder="s_place_placeholder"/>
                        </div>
                        <div class="tab_input_wrap">
                            <div class="tab_input_title" data-i18n="p_recipient">수령인</div>
                            <input class="to_name" type="text" placeholder="이름" data-i18n-placeholder="s_name_placeholder"/>
                        </div>
                        <div class="tab_input_wrap">
                            <div class="tab_input_title" data-i18n="p_mobile">전화번호</div>
                            <input class="to_mobile" type="text" placeholder="(-) 없이 숫자만 입력" data-i18n-placeholder="s_mobile_placeholder"/>
                        </div>
                        
                        <div class="tab_input_wrap">
                            <div class="as_addr_KR">
                                <div class="tab_input_title" data-i18n="join_addr">주소</div>
                                <div id="postcodify_as"></div>
                                <div class="input_row">
                                    <div class="post_change_result"></div>
                                </div>
                                <div class="rows__contnets">
                                    <input class="to_zipcode" type="hidden" name="zipcode">
                                    <input class="to_lot_addr" type="hidden" name="lot_addr">
                                    <input class="to_road_addr" type="hidden" name="road_addr">
                                    <input class="to_detail_addr" type="text" name="detail_addr" placeholder="상세주소">
                                </div>
                            </div>

                            <div class="as_addr_EN">
                                <input type="hidden" name="country_code">
                                <input type="hidden" name="province_idx">
                                <div class="content__title grid_half">
                                    <div>
                                        <p>Country</p>
                                    </div>
                                    <div>
                                        <p>Province</p>
                                    </div>
                                </div>
                                <div class="content__wrap grid_half">
                                        <div class="country-box">
                                        <div class="country-join-box">
                                            <div class="country-select-box"></div>
                                        </div>
                                    </div>
                                    <div class="province-box">
                                        <div class="province-join-box">
                                            <div class="province-select-box"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="content__title grid_half">
                                    <div>
                                        <p>City</p>
                                    </div>
                                    <div>
                                        <p>Zipcode</p>
                                    </div>
                                </div>
                                <div class="content__wrap grid_half">
                                    <input class="to_city" type="text" name="city">
                                    <input class="to_zipcode" type="text" name="zipcode">
                                </div>
                                <div class="content__title">
                                    <p>Address</p>
                                </div>
                                <div class="content__wrap">
                                    <input class="to_address" type="text" name="address">
                                </div>
                            </div>

                            <div class="as_addr_CN">
                                <input type="hidden" name="country_code">
                                <input type="hidden" name="province_idx">
                                <div class="content__title grid_half">
                                    <div>
                                        <p>Country</p>
                                    </div>
                                    <div>
                                        <p>Province</p>
                                    </div>
                                </div>
                                <div class="content__wrap grid_half">
                                        <div class="country-box">
                                        <div class="country-join-box">
                                            <div class="country-select-box"></div>
                                        </div>
                                    </div>
                                    <div class="province-box">
                                        <div class="province-join-box">
                                            <div class="province-select-box"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="content__title grid_half">
                                    <div>
                                        <p>City</p>
                                    </div>
                                    <div>
                                        <p>Zipcode</p>
                                    </div>
                                </div>
                                <div class="content__wrap grid_half">
                                    <input class="to_city" type="text" name="city">
                                    <input class="to_zipcode" type="text" name="zipcode">
                                </div>
                                <div class="content__title">
                                    <p>Address</p>
                                </div>
                                <div class="content__wrap">
                                    <input class="to_address" type="text" name="address">
                                </div>
                            </div>
                            <div class="tap_defalut_order_to_check">
                                <div class="defalut_checkbox_wrap">
                                    <label class="defalut_checkbox_label">
                                        <input class="as_order_to_default_flg" type="checkbox">
                                    </label>
                                    <span data-i18n="s_add_addr_list">배송지 목록에 추가</span>
                                </div>
                            </div>
                        </div>
                    </div>
					
                    <div class="as_order_to_result hidden">
                        <input class="result_to_lot_addr" type="hidden" name="lot_addr">
                        <input class="result_to_road_addr" type="hidden" name="to_road_addr">
                        <input class="result_to_country_code" type="hidden" name="country_code">
                        <input class="result_to_province_idx" type="hidden" name="province_idx">
                        <input class="result_to_city" type="hidden" name="to_city">

                        <div class="result_to_place"></div>
                        <div class="result_to_name"></div>
                        <div class="result_to_mobile"></div>
                        <div class="result_to_zipcode"></div>

                        <div class="result_addr_KR">
                            <div class="result_to_addr"></div>
                            <div class="result_to_detail_addr"></div>
                        </div>

                        <div class="result_addr_EN">
                            <div class="result_to_addr"></div>
                        </div>

                        <div class="result_addr_CN">
                            <div class="result_to_addr"></div>
                        </div>
                    </div>
					
                    <div class="tab_input_wrap as_order_to_msg_wrap">
                        <div class="tab_input_title" data-i18n="s_addr_memo">배송 메시지</div>
                        <div class="as_order_to_msg_list"></div>
                        <input class="as_order_to_msg_direct hidden" type="text">
                    </div>
                    <div class="as_order_to_complete_btn" data-order_to_idx="0" data-i18n="p_nomore_address">배송지 입력 완료</div>
                </div>
            </div>
            <button class="step_btn_DLV btn_step" data-i18n="as_shipping_step">
                05. 배송
            </button>
            <div class="as_step_contents contents_DLV">
                <div>
                    <p data-i18n="as_shipping_started">제품 배송이 시작되었습니다.</p>
                    <p data-i18n="as_shipping_with">영업일 기준 1-3일 소요되며, 택배사 현황에 따라 변동될 수 있습니다.</p>
                    <div class="as_payment_complete_noti_wrap">
                        <span data-i18n="as_payment_complete">결제가 완료되었습니다.</span>
                        <a class="as_receipt_link" target="_blank" rel="noopener noreferrer" data-i18n="oc_view_receipts">영수증 보기</a>
                    </div>
                    <div>
                        <div class="as_table_align_l">
                            <div>
                                <span data-i18n="as_shipping_courier">택배사</span>
                                <span class="company_name"></span>
                            </div>
                            <div>
                                <span data-i18n="as_tracking_number">운송장 번호</span>
                                <div class="delivery_num_wrap"></div>
                            </div>
                            <div>
                                <span data-i18n="as_shipping_dispatched">출고일</span>
                                <span class="delivery_start_date"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="step_btn_ACP btn_step" data-i18n="as_repaired_step">
                06. 완료
            </button>
            <div class="as_step_contents contents_ACP">
                <p data-i18n="as_repaired_service">A/S가 완료되었습니다.</p>
                <div class="show_as_complete_list_btn" data-i18n="as_history_txt">A/S 내역 보러가기</div>
                <div class="as_payment_complete_noti_wrap">
                    <span data-i18n="as_payment_complete">결제가 완료되었습니다.</span>
                    <a class="as_receipt_link" target="_blank" rel="noopener noreferrer" data-i18n="oc_view_receipts">영수증 보기</a>
                </div>
            </div>
            <div class="show_as_current_list_btn" data-i18n="as_to_list_txt">목록보기</div>
        </div>
    </div>
</div>

<script src="/scripts/mypage/as/as-current.js"></script>
<script src="https://js.tosspayments.com/v1/payment-widget"></script>

<script>
	const clientKey = "test_ck_YZ1aOwX7K8meL9vyEe98yQxzvNPG";
	let tossPayments = TossPayments(clientKey);
</script>