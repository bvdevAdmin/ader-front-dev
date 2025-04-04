
<div class="profile__tab profile__delivery__wrap">
    <div class="list__delivery__wrap">
        <div class="default_list_wrap">
            <div class="title">
                <p data-i18n="p_default_address">기본 배송지</p>
            </div>
            <table>
                <tbody class="default__list delivery_table_wrap">
                </tbody>
            </table>
        </div>
        <div class="other_list_wrap">
            <div class="title">
                <p data-i18n="p_other_address">다른 배송지</p>
            </div>
            <table>
                <tbody class="other__list delivery_table_wrap">
                </tbody>
            </table>
        </div>
    </div>
    <div class="black__full__width__btn add_order_to" data-i18n="p_add_address">새로운 배송지 추가</div>
</div>

<div class="profile__tab input__form__wrap order__to__update__wrap">
    <div class="update_wrap_header">
      <div class="close close_order_to_update">
          <img src="/images/mypage/tmp_img/X-12.svg" />
      </div>
    </div>
    <div class="input__form__rows">
        <input class="hidden_order_to_idx" type="hidden" value="0">
        <div class="delivery_regist_error_wrap">
            <div class="rows__title" data-i18n="p_place">배송지명</div>
            <div class="description delivery_regist_error">
                <p>&nbsp;</p>
            </div>
        </div>
        <input class="order_to_place" data-i18n-placeholder="s_place_placeholder"></input>
    </div>
    <div class="input__form__rows">
        <div data-i18n="p_full_name" class="rows__title">이름</div>
        <input class="order_to_name"></input>
    </div>
    <div class="input__form__rows">
        <div data-i18n="p_mobile" class="rows__title">전화번호</div>
        <input class="order_to_mobile" data-i18n-placeholder="s_mobile_placeholder"></input>
    </div>
    <div class="input__form__rows">
        <div class="profile_addr_KR">
            <div class="rows__title">주소</div>
            <div id="postcodify" class="input_row"></div>
            <div class="input_row post_result_rows">
                <div class="post_change_result"></div>
            </div>
            <div class="rows__contnets">
                <input type="hidden" class="order_to_zipcode" name="zipcode">
                <input type="hidden" class="order_to_lot_addr" name="lot_addr">
                <input type="hidden" class="order_to_road_addr" name="road_addr">
                <input type="text" class="order_to_detail_addr" placeholder="상세주소">
            </div>
        </div>
        <div class="profile_addr_EN">
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
                <input type="text" name="city">
                <input type="text" name="zipcode">
            </div>
            <div class="content__title">
                <p>Address</p>
            </div>
            <div class="content__wrap">
                <input type="text" name="address">
            </div>
        </div>
        <div class="profile_addr_CN">
            <input type="hidden" name="country_code">
            <input type="hidden" name="province_idx">
            <div class="content__title grid_half">
                <div>
                    <p>国家</p>
                </div>
                <div>
                    <p>省份</p>
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
                    <p>城市</p>
                </div>
                <div>
                    <p>邮政编码</p>
                </div>
            </div>
            <div class="content__wrap grid_half">
                <input type="text" name="city">
                <input type="text" name="zipcode">
            </div>
            <div class="content__title">
                <p>地址</p>
            </div>
            <div class="content__wrap">
                <input type="text" name="address">
            </div>
        </div>
    </div>
    <div class="input__form__rows">
        <label>
            <input class="order_to_default_flg" type="checkbox">
            <span data-i18n="p_set_default_address"></span>
        </label>
    </div>
    <div class="black__full__width__btn change_order_to_btn" data-i18n="p_save"></div>
</div>
<script src="/scripts/mypage/profile/profile-delivery.js"></script>