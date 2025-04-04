<link rel="stylesheet" href="/css/user/main/login-join.css">

<script src="https://scert.mobile-ok.com/resources/js/index.js"></script>

<main>
    <div class="join__card">
        <div class="card__header">
            <p class="font__large" data-i18n="lm_create_account"></p>
        </div>
        <form id="frm-regist" method="post">
            <input type="hidden" name="country">
            <div class="card__body">
                <div class="content__wrap">
                    <div class="content__title warm__msg__area">
                        <p class="font__small" data-i18n="p_email"></p>
						<div>
							<p class="font__underline warn__msg member_id" data-i18n="j_email_msg"></p>
						</div>
                    </div>
                    <div class="contnet__row warm__msg__area">
                        <input class="user_id_input" type="text" name="member_id">
                    </div>
                </div>
                <div class="content__wrap">
                    <div class="content__title warm__msg__area">
                        <p class="font__small" data-i18n="p_password"></p>
						<div>
							<p class="font__underline warn__msg member_pw" data-i18n="j_password_msg"></p>
						</div>
                    </div>
                    <div class="contnet__row">
                        <input type="password" name="member_pw">
                        <div id="pw_desciption"
                            style="height:154px;border: solid 1px #808080;font-size: 11px;padding-top:10px;padding-left:10px">
                            <div class="pw__desc__title" style="margin-bottom:10px;">
                                <span data-i18n="p_password_requirements"></span>
                            </div>
                            <div class="pw__desc__content" style="margin-bottom:10px;">
                                <span data-i18n="p_password_msg_01"></span>
                            </div>
                            <div class="pw__desc__content" style="margin-bottom:10px;">
                                <span data-i18n="p_special_character_msg"></span>
                                <p>&nbsp;&nbsp;!@#$%^()_-={}[]|;:<>,.?/</p>
                            </div>
                            <div class="pw__desc__content" style="margin-bottom:10px;">
                                <span data-i18n="p_blank_character_msg"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="hide_area">
                    <div class="content__wrap">
                        <div class="content__title warm__msg__area">
                            <p class="font__small" data-i18n="j_password_confirm"></p>
                            <div>
								<p class="font__underline warn__msg member_pw_confirm" data-i18n="j_password_match_msg"></p>
							</div>
                        </div>
                        <div class="contnet__row warm__msg__area">
                            <input class="user_pw_confirm_input" type="password" name="member_pw_confirm">
                        </div>
                    </div>
                    <div class="content__wrap">
                        <div class="content__title warm__msg__area">
                            <p class="font__small" data-i18n="p_full_name"></p>
                            <div>
								<p class="font__underline warn__msg member_name" data-i18n="j_name_msg"></p>
							</div>
                        </div>
                        <div class="contnet__row warm__msg__area">
                            <input type="text" name="member_name">
                        </div>
                    </div>
                </div>

                <div class="addr-KR">
                    <div class="content__wrap">
                        <div class="content__title" data-i18n="join_addr"></div>
                        <div id="postcodify" class="input-row"></div>
                        <div class="input-row" style="clear:both;">
                            <div class="post-change-result"></div>
                        </div>
                    </div>
                    <div class="content__wrap">
                        <div class="content__row">
                            <input type="hidden" id="zipcode" name="zipcode">
                            <input type="hidden" id="lot_addr" name="lot_addr">
                            <input type="hidden" id="road_addr" name="road_addr">
                            <input type="text" id="addr_detail" name="addr_detail" placeholder="상세주소">
                        </div>
                    </div>
                    <div class="content__wrap">
                        <div class="content__row">
                            <span class="font__small" name="default_addr_flg" style="float:right"
                                data-i18n="j_add_def_addr"></span>
                            <div style="float:right">
                                <input type="checkbox" id="default_addr_flg_KR">
                                <label for="default_addr_flg_KR"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="addr-EN">
                    <div class="content__title flex-container">
                        <div style="width:50%">
                            <p>Country</p>
                        </div>
                        <div style="width:50%">
                            <p>Province</p>
                        </div>
                    </div>
                    <div class="content__wrap flex-container">
                        <input type="hidden" name="country_code">
                        <input type="hidden" name="province_idx">
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
                    <div class="content__title flex-container">
                        <div class="flex-two-item">
                            <p>City</p>
                        </div>
                        <div class="flex-two-item">
                            <p>Zipcode</p>
                        </div>
                    </div>
                    <div class="content__wrap flex-container">
                        <input type="text" style="" name="city">
                        <input type="text" style="" name="zipcode">
                    </div>
                    <div class="content__title">
                        <p>Address</p>
                    </div>
                    <div class="content__wrap">
                        <input type="text" name="address">
                    </div>
                    <div class="content__wrap">
                        <div class="content__row">
                            <span class="font__small" name="default_addr_flg" style="float:right"
                                data-i18n="j_add_def_addr"></span>
                            <div style="float:right">
                                <input type="checkbox" id="default_addr_flg_EN">
                                <label for="default_addr_flg_EN"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="addr-CN">
                    <input type="hidden" name="country_code">
                    <input type="hidden" name="province_idx">
                    <div class="content__title flex-container">
                        <div style="width:50%">
                            <p>国家</p>
                        </div>
                        <div style="width:50%">
                            <p>省份</p>
                        </div>
                    </div>
                    <div class="content__wrap flex-container">
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
                    <div class="content__title flex-container">
                        <div class="flex-two-item">
                            <p>城市</p>
                        </div>
                        <div class="flex-two-item">
                            <p>邮政编码 </p>
                        </div>
                    </div>
                    <div class="content__wrap flex-container">
                        <input type="text" style="" name="city">
                        <input type="text" style="" name="zipcode">
                    </div>
                    <div class="content__title">
                        <p>地址</p>
                    </div>
                    <div class="content__wrap">
                        <input type="text" name="address">
                    </div>
                    <div class="content__wrap">
                        <div class="content__row">
                            <span class="font__small" name="default_addr_flg" style="float:right"
                                data-i18n="j_add_def_addr"></span>
                            <div style="float:right">
                                <input type="checkbox" id="default_addr_flg_CN">
                                <label for="default_addr_flg_CN"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content__wrap content__wrap__tel">
                    <div class="content__title warm__msg__area">
                        <p class="font__small" data-i18n="p_mobile"></p>
						<div>
							<p class="font__underline warn__msg tel_confirm authenfication" data-i18n="j_phone_veri_msg"></p>
						</div>
						<div>
							<p class="font__underline warn__msg tel_confirm format" data-i18n="j_phone_include_msg"></p>
						</div>
                    </div>
                    <div class="content__wrap grid__two tel_mobile" style="margin-top:0px">
                        <div class="left__area__wrap">
                            <input type="hidden" id="mobile_authenfication_flg" value="true">
                            <input class="join_tel_mobile" type="text" name="tel_mobile" value="" data-i18n-placeholder="j_num_only">
                        </div>
                        <!-- <div class="left__area__wrap">
                            <input type="hidden" id="mobile_authenfication_flg" value="false">
                            <input class="join_tel_mobile" type="text" name="tel_mobile" value="" data-i18n-placeholder="j_num_only" readonly>
                        </div> -->
                        <div class="right__area__wrap">
                            <button class="black__small__btn mobile_auth_btn" type="button" data-i18n="b_verify"></button>
                        </div>
                    </div>
                </div>
                <div class="content__wrap">
                    <div class="content__title warm__msg__area">
                        <p class="font__small" data-i18n="p_birth"></p>
                        <div>
							<p class="font__underline warn__msg birth" data-i18n="j_birth_msg"></p>
						</div>
                    </div>
                    <div class="contnet__row warm__msg__area">
                        <div class="grid__three">
                            <div class="left__area__wrap">
                                <input class="short__input address__input birth_y" type="number" step="1" min="1900" max="9999" name="birth_year" value="" data-i18n-placeholder="j_year">
                            </div>
                            <div class="middle__area__wrap">
                                <input class="short__input address__input birth_m" type="number" step="1" min="1" max="12" name="birth_month" value="" data-i18n-placeholder="j_month">
                            </div>
                            <div class="right__area__wrap">
                                <input class="short__input address__input birth_d" type="number" step="1" min="1" max="31" name="birth_day" value="" data-i18n-placeholder="j_day">
                            </div>
                        </div>
                        <!-- <div class="grid__three">
                            <div class="left__area__wrap">
                                <input class="short__input address__input birth_y" type="number" step="1" min="1900" max="9999" name="birth_year" value="" data-i18n-placeholder="j_year" readonly>
                            </div>
                            <div class="middle__area__wrap">
                                <input class="short__input address__input birth_m" type="number" step="1" min="1" max="12" name="birth_month" value="" data-i18n-placeholder="j_month" readonly>
                            </div>
                            <div class="right__area__wrap">
                                <input class="short__input address__input birth_d" type="number" step="1" min="1" max="31" name="birth_day" value="" data-i18n-placeholder="j_day" readonly>
                            </div>
                        </div> -->
                    </div>
                </div>
                <div class="content__wrap" style="margin-bottom:40px;">
                    <div class="content__title warm__msg__area">
                        <p class="font__small" data-i18n="p_gender"></p>
                    </div>
                    <div class="contnet__row warm__msg__area">
                        <div style="float:left">
                            <input type="radio" id="gender_female" name="gender" value="F" checked>
                            <label for="gender_female"></label>
                        </div>
                        <span class="font__small" style="margin-right:20px" data-i18n="p_woman"></span>
                        <div style="float:left">
                            <input type="radio" id="gender_male" name="gender" value="M">
                            <label for="gender_male"></label>
                        </div>
                        <span class="font__small" data-i18n="p_man"></span>
                    </div>
                </div>
                <div class="addr-KR">
                    <div class="content__wrap checkbox__area">
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" id="select_all" class="login__check__option select__all" value="true">
                                <label for="select_all"></label>
                            </div>
                            <span class="font__small" data-i18n="p_select_all"></span>
                        </div>
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" name="accept_terms_flg" id="accept_terms_flg" class="login__check__option component" value="true">
                                <label for="accept_terms_flg"></label>
                            </div>
                            <span class="font__underline to_terms_of_use" style="cursor:pointer">이용약관,</span>
                            <span class="font__small"></span>
                            <span class="font__underline to_privacy_policy" style="cursor:pointer">개인정보수집 및 이용</span>
                            <span class="font__small">에 동의합니다. (필수)</span>
                        </div>
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" name="receive_sms_flg" id="receive_sms_flg" class="login__check__option component" value="true">
                                <label for="receive_sms_flg"></label>
                            </div>
                            <span class="font__small">SMS 마케팅정보 수신을 동의합니다. (선택)</span>
                        </div>
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" name="receive_email_flg" id="receive_email_flg" class="login__check__option component" value="true">
                                <label for="receive_email_flg"></label>
                            </div>
                            <span class="font__small">이메일 마케팅정보 수신을 동의합니다. (선택)</span>
                        </div>
                        <div>
							<p class="font__underline warn__msg essential">필수항목을 선택해주세요.</p>
						</div>
                    </div>
                </div>
                <div class="addr-EN">
                    <div class="content__wrap checkbox__area">
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" id="select_all" class="login__check__option select__all">
                                <label for="select_all"></label>
                            </div>
                            <span class="font__small" data-i18n="p_select_all"></span>
                        </div>
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" name="accept_terms_flg" id="accept_terms_flg" class="login__check__option component" value="true">
                                <label for="accept_terms_flg"></label>
                            </div>
                            <span class="font__small">agree to the</span>
                            <span class="font__underline to_terms_of_use" style="cursor:pointer">Terms of Use,</span>
                            <span class="font__underline to_privacy_policy" style="cursor:pointer">collection and use of personal information</span>
                            <span class="font__small">(Required)</span>
                        </div>
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" name="receive_sms_flg" id="receive_sms_flg" class="login__check__option component" value="true">
                                <label for="receive_sms_flg"></label>
                            </div>
                            <span class="font__small">agree to receive SMS marketing information. (Optional)</span>
                        </div>
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" name="receive_email_flg" id="receive_email_flg" class="login__check__option component" value="true">
                                <label for="receive_email_flg"></label>
                            </div>
                            <span class="font__small">agree to receive email marketing information. (Optional)</span>
                        </div>
                        <div>
							<p class="font__underline warn__msg essential">Please select the required fields.</p>
						</div>
                    </div>
                </div>
                <div class="addr-CN">
                    <div class="content__wrap checkbox__area">
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" id="select_all" class="login__check__option select__all">
                                <label for="select_all"></label>
                            </div>
                            <span class="font__small" data-i18n="p_select_all"></span>
                        </div>
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" name="accept_terms_flg" id="accept_terms_flg" class="login__check__option component" value="true">
                                <label for="accept_terms_flg"></label>
                            </div>
                            <span class="font__small">(必选) </span>
                            <span class="font__small">同意使用</span>
                            <span class="font__underline to_terms_of_use" style="cursor:pointer">条款,</span>
                            <span class="font__underline to_privacy_policy" style="cursor:pointer">以及收集个人隐私。</span>
                        </div>
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" name="receive_sms_flg" id="receive_sms_flg" class="login__check__option component" value="true">
                                <label for="receive_sms_flg"></label>
                            </div>
                            <span class="font__small">（选择）同意收到手机短信推广信息。</span>
                        </div>
                        <div class="content__row">
                            <div style="float:left">
                                <input type="checkbox" name="receive_email_flg" id="receive_email_flg" class="login__check__option component" value="true">
                                <label for="receive_email_flg"></label>
                            </div>
                            <span class="font__small">（选择）同意收到邮箱推广信息</span>
                        </div>
                        <div>
							<p class="font__underline warn__msg essential">请选择必填项。</p>
						</div>
                    </div>
                </div>
            </div>
            <div class="card__footer">
                <div>
                    <button class="black__btn join_button" type="button" data-i18n="j_signup"></button>
                </div>
            </div>
        </form>
    </div>
</main>

<script src="/scripts/member/login.js"></script>
<script src="/scripts/member/login-join.js"></script>