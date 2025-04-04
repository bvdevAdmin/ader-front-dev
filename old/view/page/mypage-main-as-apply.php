<div class="tab two">
    <div class="as__wrap__content__container">
        <div style="font-size: 13px;" data-i18n="as_request_repair">A/S 서비스 신청</div>
        <ul class="as__service__btn">
            <li class="as_apply_tab_btn_bluemark as_apply_tab selected" data-apply_tab_num="one_one" data-i18n="as_bluemark_history">Bluemark 인증 내역</li>
            <li class="as_apply_tab_btn_no_verify as_apply_tab" data-apply_tab_num="one_two" data-i18n="as_product_lost">인증 불가 제품</li>
        </ul>
    </div>
    <div class="as_buying_wrap one_one">
        <div class="as_bluemark_tab">
            <div class="bluemark_mini_title">
                <ul data-i18n="as_bluemark_history">Bluemark 인증 내역</ul>
            </div>
            <div class="as__table__container">
                <div class="bluemark_mini_description">
                    <span class="flex_text">
                        <p data-i18n="as_bluemark_info_01"></p>
                    </span>
                    <span class="flex_text">
                        <p data-i18n="as_bluemark_info_02"></p>
                    </span>
                    <span class="flex_text">
                        <p data-i18n="as_bluemark_info_03"></p>
                    </span>
                </div>
                <div class="as__contents__table as_bluemark_history_list">
                    
                </div>
            </div>
        </div>
        <div class="as__contents__table as_apply_tab_bluemark">
            <div class="as_apply_item"></div>
            <form id="frm-as-bluemark" action="mypage/as/add">
				<input class="serial_code" type="hidden" name="serial_code" value="">
				<input class="as_category_idx" type="hidden" name="as_category_idx" value="">
				<input class="barcode" type="hidden" name="barcode" value="">
				
                <div class="as__info as__contents">
                    <textarea class="as__contents__text asTextBox" id="asTextBox" name="as_contents" data-i18n-placeholder="s_textarea_placeholder_more"></textarea>
                </div>
				
                <div class="as__info as__photo__unconfirm">
                    <div class="as__photo__wrap">
                        <p class="description" data-i18n="inquiry_attachment_photo">사진 첨부</p>
                        <div class="as__photo__container">
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
								<div class="as_preview"></div>
								
								<input class="as_img product_img" type="file" name="product_img[]" value="">
                            </div>
							
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
								<div class="as_preview"></div>
								
								<input class="as_img product_img" type="file" name="product_img[]" value="">
                            </div>
							
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
								<div class="as_preview"></div>
								
								<input class="as_img product_img" type="file" name="product_img[]" value="">
                            </div>
							
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
								<div class="as_preview"></div>
								
								<input class="as_img product_img" type="file" name="product_img[]" value="">	
                            </div>
							
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
								<div class="as_preview"></div>
								
								<input class="as_img product_img" type="file" name="product_img[]" value="">	
                            </div>
                        </div>
                    </div>
                </div>
                <div class="description" data-i18n="as_request_img_info_01"></div>
                <div style="margin: 10px 0 20px;" data-i18n="as_request_img_info_02"></div>
                <div style="border-top:1px solid #dcdcdc;padding-top:20px;"></div>
                <button type="button" class="as__black__btn bluemark add_as_apply" data-apply_type="bluemark" data-i18n="as_request_service">A/S 신청</button>
                <button type="button" class="as__white__btn cancel_as_apply" data-cancel_type="as-bluemark" data-i18n="o_cancel">취소</button>
            </form>
			
            <div class="footer"></div>
        </div>
    </div>
    <div class="as_buying_wrap one_two">
        <div data-i18n="as_repair_notice_08"></div>
        <div class="as__contents__table as_apply_tab_no_verify">
            <form id="frm-as" action="mypage/as/add">
				<input class="serial_code" type="hidden" name="serial_code" value="">
				<input class="as_category_idx" type="hidden" name="as_category_idx" value="">
				<input class="barcode" type="hidden" name="barcode" value="">
				
                <div class="as__info as__title">
                    <div class="as_category_select_box"></div>
                    <input class="as_barcode_input" type="text" data-i18n-placeholder="as_product_code"></input>
                </div>
                <div class="as__info as__contents">
                    <textarea class="as__contents__text" data-i18n-placeholder="s_textarea_placeholder_more" id="asTextBox" class="asTextBox" name="as_contents"></textarea>
                </div>
                <div class="as__info as__photo__unconfirm">
                    <div class="as__photo__wrap">
                        <p class="description" data-i18n="inquiry_attachment_photo">사진 첨부</p>
                        <div class="as__photo__container">
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
								<div class="as_preview"></div>
								
								<input class="as_img product_img" type="file" name="product_img[]" value="">
                            </div>
							
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
								<div class="as_preview"></div>
								
								<input class="as_img product_img" type="file" name="product_img[]" value="">
                            </div>
							
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
								<div class="as_preview"></div>
								
								<input class="as_img product_img" type="file" name="product_img[]" value="">
                            </div>
							
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
								<div class="as_preview"></div>
								
								<input class="as_img product_img" type="file" name="product_img[]" value="">	
                            </div>
							
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
								<div class="as_preview"></div>
								
								<input class="as_img product_img" type="file" name="product_img[]" value="">	
                            </div>
                        </div>
                    </div>
                    <div class="as__photo__wrap">
                        <p class="description" data-i18n="inquiry_purchase_img"></p>
                        <div class="as__photo__container">
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
                                <div class="as_preview"></div>
                                
                                <input class="as_img receipt_img" type="file" name="receipt_img[]" value="">
                            </div>
                            
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
                                <div class="as_preview"></div>
                                
                                <input class="as_img receipt_img" type="file" name="receipt_img[]" value="">
                            </div>
                            
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
                                <div class="as_preview"></div>
                                
                                <input class="as_img receipt_img" type="file" name="receipt_img[]" value="">
                            </div>
                            
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
                                <div class="as_preview"></div>
                                
                                <input class="as_img receipt_img" type="file" name="receipt_img[]" value="">	
                            </div>
                            
                            <div class="as__photo__item">
                                <img class="as__img__item" src="/images/mypage/mypage_photo_btn.svg">
                                <div class="as_preview"></div>
                                
                                <input class="as_img receipt_img" type="file" name="receipt_img[]" value="">	
                            </div>
                        </div>
                    </div>
                </div>
                <div class="description" data-i18n="as_request_img_info_01"></div>
                <div style="margin: 10px 0 20px;" data-i18n="as_request_img_info_02"></div>
                <div style="border-top:1px solid #dcdcdc;padding-top:20px;"></div>
                <button type="button" class="as__black__btn no_verify add_as_apply" data-apply_type="no_verify" data-i18n="as_request_service">A/S 신청</button>
                <button type="button" class="as__white__btn cancel_as_apply" data-cancel_type="as" data-i18n="o_cancel">취소</button>
            </form>
            <div class="footer"></div>
        </div>
    </div>
</div>

<script src="/scripts/mypage/as/as-apply.js"></script>