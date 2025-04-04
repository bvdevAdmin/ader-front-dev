<?php
$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_idx = 19;
if ($member_idx == 0) {
	echo "
			<script>
				location.href='/main';
			</script>
		";
}
?>

<link rel="stylesheet" href="/css/order/confirm.css">
<link rel=stylesheet href='/scripts/static/postcodify-master/api/search.css' type='text/css'>
<style>
	.edit-box .content__title.address {
		margin: 10px 0;
	}

	.edit-box .content__wrap.address input {
		width: 100%;
		height: 40px;
		padding: 5px;
	}

	.flex-container {
		display: flex;
		gap: 15px;
	}

	.tui-select-box-highlight {
		background: #f8f8f8 !important;
	}

	.tui-select-box-placeholder {
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		letter-spacing: normal;
		padding: 5px 0;
	}

	.tui-selected {
		color: #343434;
	}

	.tui-select-box-input:focus {
		outline: 0px;
	}

	.tui-select-box-input {
		border: 1px solid #808080;
		height: 39px;
	}

	.tui-select-box-item {
		color: var(--bk);
		font-size: 11px;
		padding: 3px 10px;
		border-bottom: 1px solid #eeeeee;
		height: 35px;
	}
</style>
<main data-basketStr="<?= $basket_idx ?>">
	<div id="payment-method"></div>

	<div class="banner-wrap">
		<div class="banner-box">
			<span data-i18n="s_checkout"></span>
		</div>
	</div>

	<section class="order-section">
		<div class="content left web">
			<div class="wrapper order-product">
				<div class="header-wrap order-list">
					<div class="header-box-top">
						<span class="hd-title" data-i18n="m_order_history"></span>
						<div class="product-toggle-btn">
							<span data-i18n="o_view_details"></span>
						</div>
					</div>
				</div>
				<div class="header-list">
					<div class="header-col prd-col"><span data-i18n="s_product"></span></div>
					<div class="header-col price-col"><span data-i18n="s_price"></span></div>
					<div class="header-col qty-col"><span data-i18n="s_quantity"></span></div>
					<div class="header-col sum-col"><span data-i18n="s_sum"></span></div>
				</div>
				<div class="body-wrap">

					<div class="calculation-wrap">
						<div class="calculation-box">
							<div class="price_product_wrap calculation-row">
								<span data-i18n="s_subtotal"></span>
								<span class="price_product" data-price_product="0">0</span>
							</div>

							<div class="point-box hidden">
								<div class="calculation-row">
									<div>
										<span data-i18n="v_used"></span>
										<span class="voucher_type"></span>
									</div>
									<div class="voucher_box">
										<div class="price_discount" data-price_discount="0">0</div>
										<div class="voucher_name"></div>
									</div>
								</div>
								<div class="calculation-row">
									<span data-i18n="membership_used"></span>
									<span class="price_mileage_point" data-price_mileage_point="0">0</span>
								</div>
								<!-- <div class="calculation-row">
									<span>충전 포인트 사용</span>
									<span class="price_charge_point" data-price_charge_point="0">0</span>
								</div> -->
							</div>

							<div class="calculation-row">
								<span data-i18n="s_shipping_total"></span>
								<span class="price_delivery" data-price_delivery="5000">5,000</span>
							</div>
						</div>
						<div class="total-price-wrap">
							<div class="total-box">
								<span data-i18n="s_order_total"></span>
								(<span data-i18n="s_prd_quantity"></span>:<span class="product-qty"></span>)
							</div>
							<span class="price_total">0</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="content right">
			<div class="wrapper voucher-info" data-group="1">
				<div class="header-wrap">
					<div class="header-box-top">
						<span class="hd-title" data-i18n="m_voucher"></span>
					</div>
				</div>
				<div class="body-wrap">
					<div class="voucher-select-box"></div>
					<div class="voucher-info-list">
						<p data-i18n="s_voucher_msg01"></p>
						<p data-i18n="s_voucher_msg02"></p>
					</div>
				</div>
			</div>
			<div class="wrapper reserves-info" data-group="1">
				<div class="header-wrap">
					<div class="header-box">
						<span class="hd-title" data-i18n="m_mileage"></span>
					</div>
				</div>
				<div class="body-wrap mileage">
					<div class="point-row">
						<input id="use_mileage" type="text" placeholder="사용하실 보유 적립금을 입력해주세요." data-i18n-placeholder="s_mileage_placeholder" style="padding-left:10px;">
						<div class="mileage_point_btn total_mileage" data-btn_type="ALL">
							<span class="mileage_point_msg" data-i18n="s_use_all"></span>
						</div>
					</div>
					<div class="get-point reserves">
						<span data-i18n="s_available_mileage"></span>
					</div>
					<div class="reserves-info-list">
						<p data-i18n="o_milleage_info_01"></p>
						<p data-i18n="o_milleage_info_02"></p>
						<p data-i18n="o_milleage_info_03"></p>
						<p data-i18n="o_milleage_info_04"></p>
					</div>
				</div>
				<div class="body-wrap disable-mileage">
					<div class="disable">
						<span class="mileage_point_msg" data-i18n="o_voucher_info"></span>
					</div>
					<div class="get-point reserves">
						<span data-i18n="o_held_mileage"></span>
					</div>
				</div>
			</div>

			<!-- <div class="wrapper charge-info" data-group="1">
				<div class="header-wrap">
					<div class="header-box">
						<span class="hd-title">충전 포인트</span>
					</div>
				</div>
				<div class="body-wrap">
					<div class="point-row">
						<input type="text" id="use_mileage" placeholder="사용하실 보유 적립금을 입력해주세요."
							style="padding-left:10px;">
						<div class="charge_point_btn"><span>모두적용</span></div>
					</div>
					<div class="get-point charge">
						<span>보유 충전 포인트</span>
						<span id="txt_total_charge" style="margin-left:5px;">0</span>
					</div>
					<div class="charge-btn"><span>충전하기</span></div>
					<div class="reserves-info-list">
						<p>·&nbsp;충전 포인트는 현금과 동일하게 사용 가능합니다.</p>
						<p>·&nbsp;충전 포인트 사용 시 적립금 3% 추가 적립이 적용됩니다.</p>
					</div>
				</div>
			</div> -->

			<div class="wrapper member_info" data-group="2">
				<div class="header-wrap">
					<div class="header-box-top">
						<span class="hd-title" data-i18n="s_purchaser"></span>
					</div>
				</div>
				<div class="body-wrap">
					<div class="cn-box">
						<p class="member_name"></p>
						<p class="member_mobile"></p>
						<p class="member_email"></p>
					</div>
				</div>
			</div>

			<div class="wrapper address-info" data-group="3">
				<div class="header-wrap">
					<div class="header-box">
						<span class="hd-title" data-i18n="s_shipping_address"></span>
					</div>

					<div class="header-box-btn">
						<div class="header-btn edit-btn" data-i18n="p_edit"></div>
						<div class="header-btn list_addr_btn" data-i18n="s_addr_list"></div>
					</div>
				</div>
				<div class="body-wrap">
					<div class="list-box hidden">
						<div class="addrList-header">
							<div data-i18n="s_addr_list"></div>
							<div class="close close_order_to">
								<img src="/images/mypage/tmp_img/X-12.svg">
							</div>
						</div>
						<div class="addrList-body"></div>
					</div>
					<div class="edit-box hidden">
						<div class="input-row">
							<span data-content="필수 입력란 입니다." class="confirm-text" data-i18n="p_place"></span>
							<input type="text" class="tmp_to_place" name="tmp_to_place" placeholder="예) 집"
								data-i18n-placeholder="s_place_placeholder">
						</div>
						<div class="input-row">
							<span data-content="필수 입력란 입니다." class="confirm-text" data-i18n="p_recipient"></span>
							<input type="text" class="tmp_to_name" name="tmp_to_name" placeholder="이름"
								data-i18n-placeholder="s_name_placeholder">
						</div>
						<div class="input-row">
							<span data-content="전화번호를 정확하게 기입해주세요." class="confirm-text"
								data-i18n="p_mobile"></span>
							<input maxlength="13" type="text" class="tmp_to_mobile"
								name="tmp_to_mobile" placeholder="(-) 없이 숫자만 입력"
								data-i18n-placeholder="s_mobile_placeholder">
						</div>
						<div class="order-to-KR">
							<div class="input-row addr-search">
								<div class="input-text">
									<span>배송지 검색</span>
								</div>
							</div>

							<div id="postcodify" class="input-row"></div>
							<div class="input-row">
								<div class="post-change-result"></div>
								<span class="post-detail-addr-title">상세주소</span>
								<input class="tmp_to_detail_addr" value="" />
							</div>

							<input type="hidden" class="tmp_to_zipcode" value="">
							<input type="hidden" class="tmp_to_road_addr" value="">
							<input type="hidden" class="tmp_to_lot_addr" value="">
						</div>
						<div class="order-to-EN">
							<input type="hidden" class="tmp_country_code">
							<input type="hidden" class="tmp_province_idx">
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
								<input type="text" class="tmp_city">
								<input type="text" class="tmp_zipcode">
							</div>
							<div class="content__title address">
								<p>Address</p>
							</div>
							<div class="content__wrap address">
								<input type="text" class="tmp_address">
							</div>
						</div>
						<div class="order-to-CN">
							<input type="hidden" class="tmp_country_code">
							<input type="hidden" class="tmp_province_idx">
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
								<input type="text" class="tmp_city">
								<input type="text" class="tmp_zipcode">
							</div>
							<div class="content__title address">
								<p>地址</p>
							</div>
							<div class="content__wrap address">
								<input type="text" class="tmp_address">
							</div>
						</div>
						<div class="check-row">
							<div class="check-text">
								<input type="checkbox" class="add_flg">
								<span data-i18n="s_add_addr_list">배송지 목록에 추가</span>
							</div>
							<div class="save-btn"><span data-i18n="p_save">저장</span></div>
						</div>
					</div>

					<div class="save-box hidden">
						<div class="cn-box">
							<p class="to_place"></p>
							<p class="to_name"></p>
							<p class="to_mobile"></p>
							<p class="to_zipcode"></p>
							<p class="to_addr"></p>
							<p class="to_detail_addr"></p>
						</div>

						<div class="message-box">
							<span class="hd-title" data-i18n="s_addr_memo">배송메시지</span>
							<div class="edit-message-box">
								<div class="addr-message-select-box"></div>
								<!-- <input id="recent_order_msg" type="hidden"> -->
								<textarea id="tmp_order_memo" class="tmp_order_memo" type="text" data-i18n-placeholder="s_textarea_placeholder"></textarea>
							</div>
							<div class="save-message-box hidden">
								<p class="message-content"></p>
								<input class="save-message-value" type="hidden" value="" />
							</div>

						</div>
					</div>
				</div>
			</div>

			<div class="terms-service hidden" data-group="4">
				<div class="terms-info-list">
					<p data-i18n="o_confirm_info_01"></p>
					<p data-i18n="o_confirm_info_02"></p>
				</div>
				<div class="check-row">
					<div class="check-text">
						<input class="check-all check_terms" data-check_type="ALL" type="checkbox">
						<label for=""><span data-i18n="o_select_all"></span></label>
					</div>
				</div>

				<div class="check-row">
					<div class="check-text">
						<input class="check-self essential check_terms" data-check_type="ESS" type="checkbox">

						<label for="">
							<span data-i18n="o_agree_to_the"></span>
							<span style="text-decoration: underline;" data-i18n="p_privacy_policy_03"
								onclick="window.open('/notice/privacy?notice_type=terms_of_use');">이용약관</span>,&nbsp;
							<span style="text-decoration: underline;" data-i18n="p_privacy_policy_01"
								onclick="window.open('/notice/privacy?notice_type=privacy_policy');">
								개인정보처리방침</span><span data-i18n="o_agree_required">(필수)</span>
						</label>
					</div>
				</div>

				<div class="check-row">
					<div class="check-text">
						<input type="checkbox" class="check-self check_terms" data-check_type="CHO"
							style="margin-bottom: 20px;">
						<label for="">
							<p data-i18n="o_agree_optional">뉴스레터 발송, 맞춤 서비스 및 이벤트 제공, 신규 서비스 개발 등 서비스 품질 향상을 위한 마케팅 정보 수신 및 활용에 동의합니다. (선택)</p>
						</label>
					</div>
				</div>
			</div>

			<div class="content mobile"></div>
			<div class="select-box"></div>

			<div class="step-btn-wrap">
				<div class="step-btn pre" data-step="0"><a><span data-i18n="s_previous"></span></a></div>
				<div class="step-btn next" data-step="1"><span data-i18n="s_next"></span></div>
			</div>
		</div>
	</section>
</main>

<form id="frm-check" action="/order/check" style="display:none;" method="POST">
	<input id="basket_idx" type="hidden" name="basket_idx" value="<?= $basket_idx ?>">

	<input id="to_place" type="hidden" name="to_place" value="">
	<input id="to_name" type="hidden" name="to_name" value="">
	<input id="to_mobile" type="hidden" name="to_mobile" value="">
	<input id="to_zipcode" type="hidden" name="to_zipcode" value="">
	<input id="to_lot_addr" type="hidden" name="to_lot_addr" value="">
	<input id="to_road_addr" type="hidden" name="to_road_addr" value="">
	<input id="to_detail_addr" type="hidden" name="to_detail_addr" value="">
	<input id="order_memo" type="hidden" name="order_memo" value="">

	<input id="to_country_code" type="hidden" name="to_country_code" value="">
	<input id="to_province_idx" type="hidden" name="to_province_idx" value="">
	<input id="to_city" type="hidden" name="to_city" value="">
	<input id="to_address" type="hidden" name="to_address" value="">

	<input id="voucher_idx" type="hidden" name="voucher_idx" value="0">

	<input id="price_mileage_point" type="hidden" name="price_mileage_point" value="0">
	<input id="price_charge_point" type="hidden" name="price_charge_point" value="0">
</form>

<script src="https://js.tosspayments.com/v1/payment-widget"></script>
<script src="/scripts/module/order-confirm.js"></script>
<script>
	const clientKey = "test_ck_YZ1aOwX7K8meL9vyEe98yQxzvNPG";
	//const clientKey = "test_ck_D5GePWvyJnrK0W0k6q8gLzN97Eoq";		//결제위젯용
	let tossPayments = TossPayments(clientKey);

	const customerKey = "<?= $_SESSION['MEMBER_ID'] ?>";
	const paymentWidget = PaymentWidget(clientKey, customerKey);  // 결제위젯 초기화
</script>