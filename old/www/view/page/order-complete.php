<?php

include_once(dir_f_api."/send/send-common.php");
include_once(dir_f_api."/send/send-mail.php");
include_once(dir_f_api."/send/send-kakao.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$order_idx = 0;
if (isset($_GET['order_idx'])) {
	$order_idx = $_GET['order_idx'];
}

if ($member_idx > 0 && $order_idx > 0) {
	$order_cnt = $db->count("ORDER_INFO", "IDX = " . $order_idx . " AND MEMBER_IDX = " . $member_idx);

	if ($order_cnt > 0) {
		$select_order_info_sql = "
			SELECT
				OI.IDX						AS ORDER_IDX,
				OI.COUNTRY					AS COUNTRY,
				OI.ORDER_CODE				AS ORDER_CODE,
				OI.TO_PLACE					AS TO_PLACE,
				OI.TO_NAME					AS TO_NAME,
				OI.TO_MOBILE				AS TO_MOBILE,
				OI.TO_ZIPCODE				AS TO_ZIPCODE,
				IFNULL(
					OI.TO_ROAD_ADDR,
					OI.TO_LOT_ADDR
				)							AS TO_ADDR,
				OI.TO_DETAIL_ADDR			AS TO_DETAIL_ADDR,
				OI.ORDER_MEMO				AS ORDER_MEMO,
				OI.PG_PAYMENT				AS PG_PAYMENT,
				OI.PG_PRICE					AS PG_PRICE,
				OI.PG_RECEIPT_URL   AS PG_RECEIPT_URL,
				OI.PRICE_PRODUCT			AS PRICE_PRODUCT,
				OI.PRICE_DISCOUNT			AS PRICE_DISCOUNT,
				OI.PRICE_MILEAGE_POINT		AS PRICE_MILEAGE_POINT,
				OI.PRICE_CHARGE_POINT		AS PRICE_CHARGE_POINT,
				OI.PRICE_DELIVERY			AS PRICE_DELIVERY,
				OI.PRICE_TOTAL				AS PRICE_TOTAL
			FROM
				ORDER_INFO OI
			WHERE
				OI.IDX = " . $order_idx . ";
		";

		$db->query($select_order_info_sql);

		$order_info = array();
		$order_product = array();

		foreach ($db->fetch() as $order_data) {
			$order_idx = $order_data['ORDER_IDX'];
			$country = $order_data['COUNTRY'];

			if (!empty($order_idx)) {
				$select_order_product_sql = "
					SELECT
						OP.IDX							AS ORDER_PRODUCT_IDX,
						OP.PRODUCT_TYPE					AS PRODUCT_TYPE,
						(
							SELECT
								S_PI.IMG_LOCATION
							FROM
								PRODUCT_IMG S_PI
							WHERE
								S_PI.PRODUCT_IDX = OP.PRODUCT_IDX AND
								S_PI.IMG_TYPE = 'P' AND
								S_PI.IMG_SIZE = 'S'
							ORDER BY
								S_PI.IDX ASC
							LIMIT
								0,1
						)								AS IMG_LOCATION,
						OP.PRODUCT_NAME					AS PRODUCT_NAME,
						PR.COLOR						AS COLOR,
						PR.COLOR_RGB					AS COLOR_RGB,
						OP.OPTION_NAME					AS OPTION_NAME,
						PR.SALES_PRICE_" . $country . "	AS SALES_PRICE,
						OP.PRODUCT_QTY					AS PRODUCT_QTY,
						OP.PRODUCT_PRICE				AS PRODUCT_PRICE,
						(
							OP.PRODUCT_QTY * OP.PRODUCT_PRICE
						)								AS PRICE_TOTAL
					FROM
						ORDER_PRODUCT OP
						LEFT JOIN SHOP_PRODUCT PR ON
						OP.PRODUCT_IDX = PR.IDX
					WHERE
						OP.ORDER_IDX = " . $order_idx . " AND
						OP.PRODUCT_TYPE NOT IN ('D','V') AND
						OP.PARENT_IDX = 0
				";

				$db->query($select_order_product_sql);

				foreach ($db->fetch() as $product_data) {
					$order_product_idx = $product_data['ORDER_PRODUCT_IDX'];
					$product_type = $product_data['PRODUCT_TYPE'];

					$set_product_info = array();
					if (!empty($order_product_idx) && $product_type == "S") {
						$select_set_product_sql = "
							SELECT
								OP.PARENT_IDX					AS PARENT_IDX,
								(
									SELECT
										S_PI.IMG_LOCATION
									FROM
										PRODUCT_IMG S_PI
									WHERE
										S_PI.PRODUCT_IDX = OP.PRODUCT_IDX AND
										S_PI.IMG_TYPE = 'P' AND
										S_PI.IMG_SIZE = 'S'
									ORDER BY
										S_PI.IDX ASC
									LIMIT
										0,1
								)								AS IMG_LOCATION,
								OP.PRODUCT_NAME					AS PRODUCT_NAME,
								PR.COLOR						AS COLOR,
								PR.COLOR_RGB					AS COLOR_RGB,
								OP.OPTION_NAME					AS OPTION_NAME
							FROM
								ORDER_PRODUCT OP
								LEFT JOIN SHOP_PRODUCT PR ON
								OP.PRODUCT_IDX = PR.IDX
							WHERE
								OP.PARENT_IDX = " . $order_product_idx . "
						";
						
						$db->query($select_set_product_sql);

						foreach ($db->fetch() as $set_data) {
							$set_product_info[] = array(
								'parent_idx' => $set_data['PARENT_IDX'],
								'img_location' => $set_data['IMG_LOCATION'],
								'product_name' => $set_data['PRODUCT_NAME'],
								'color' => $set_data['COLOR'],
								'color_rgb' => $set_data['COLOR_RGB'],
								'option_name' => $set_data['OPTION_NAME']
							);
						}
					}

					$order_product[] = array(
						'order_product_idx' => $order_product_idx,
						'product_type' => $product_type,
						'img_location' => $product_data['IMG_LOCATION'],
						'product_name' => $product_data['PRODUCT_NAME'],
						'color' => $product_data['COLOR'],
						'color_rgb' => $product_data['COLOR_RGB'],
						'option_name' => $product_data['OPTION_NAME'],
						'sales_price' => number_format($product_data['SALES_PRICE']),
						'product_qty' => $product_data['PRODUCT_QTY'],
						'product_price' => number_format($product_data['PRODUCT_PRICE']),
						'price_total' => number_format($product_data['PRICE_TOTAL']),

						'set_product_info' => $set_product_info
					);
				}

				$order_info = array(
					'order_code'			=>$order_data['ORDER_CODE'],
					'to_place'				=>$order_data['TO_PLACE'],
					'to_name'				=>$order_data['TO_NAME'],
					'to_mobile'				=>$order_data['TO_MOBILE'],
					'to_zipcode'			=>$order_data['TO_ZIPCODE'],
					'to_addr'				=>$order_data['TO_ADDR'],
					'to_detail_addr'		=>$order_data['TO_DETAIL_ADDR'],
					'order_memo'			=>$order_data['ORDER_MEMO'],
					'pg_payment'			=>$order_data['PG_PAYMENT'],
					'pg_price'				=>$order_data['PG_PRICE'],
					'pg_receipt_url'		=>$order_data['PG_RECEIPT_URL'],
					'price_product'			=>number_format($order_data['PRICE_PRODUCT']),
					'price_discount'		=>number_format($order_data['PRICE_DISCOUNT']),
					'price_mileage_point'	=>number_format($order_data['PRICE_MILEAGE_POINT']),
					'price_charge_point'	=>number_format($order_data['PRICE_CHARGE_POINT']),
					'price_delivery'		=>number_format($order_data['PRICE_DELIVERY']),
					'price_total'			=>number_format($order_data['PRICE_TOTAL'])
				);
			}
		}
	} else {
		echo "
			<script>
				location.href='/main';
			</script>
		";
	}
}
?>

<link rel=stylesheet href='/css/order/complete.css' type='text/css'>
<main>
	<div class="banner-wrap">
		<div class="banner-box">
			<span data-i18n="oc_order_complete_title">주문이 완료되었습니다.</span>
		</div>
	</div>
	<section class="order-section">
		<div class="content">
			<div class="wrapper number-info">
				<div class="header-wrap">
					<div class="header-box">
						<span class="hd-title" data-i18n="m_order_number">주문번호</span>
						<span>
							<?= $order_info['order_code'] ?>
						</span>
					</div>
				</div>
			</div>
			<div class="wrapper address-info" data-group="3">
				<div class="header-wrap">
					<div class="header-box">
						<span class="hd-title" data-i18n="s_shipping_address"></span>
					</div>
				</div>
				<div class="body-wrap">
					<div class="save-box">
						<div class="to-place">
							<?= $order_info['to_place'] ?>
						</div>
						<div class="cn-box">
							<p class="to-name">
								<?= $order_info['to_name'] ?>
							</p>
							<p class="to-phone">
								<?= $order_info['to_mobile'] ?>
							</p>
							<p class="to-zipcode">
								<?= $order_info['to_zipcode'] ?>
							</p>
							<p class="to-addr">
								<?= $order_info['to_addr'] ?>
							</p>
							<p class="to-detail">
								<?= $order_info['to_detail_addr'] ?>
							</p>
						</div>
						<div class="message-box">
							<span class="hd-title" data-i18n="s_addr_memo"></span>
							<span class="message-content">
								<?= $order_info['order_memo'] ?>
							</span>
						</div>
					</div>
				</div>
			</div>
			<div class="wrapper option-info" data-group="3">
				<div class="header-wrap">
					<div class="header-box">
						<span class="hd-title" data-i18n="p_payment_method">결제수단</span>
					</div>
					<div class="header-under">
						<span class="view_receipts_btn">
							<a href="<?= $order_data['PG_RECEIPT_URL'] ?>" target="_blank" rel="noopener noreferrer" data-i18n="oc_view_receipts">영수증 보기</a>
						</span>
					</div>
				</div>
				<div class="body-wrap padding">
					<div>
						<?= $order_data['PG_PAYMENT'] ?>
					</div>
				</div>
			</div>
			<div class="wrapper order-product" style="">
				<div class="header-wrap">
					<div class="header-box">
						<span class="hd-title" data-i18n="m_order_history">주문내역</span>
					</div>
				</div>
				<div class="header-list">
					<div class="header-col prd-col"><span data-i18n="s_product">제품</span></div>
					<div class="header-col price-col"><span data-i18n="s_price">가격</span></div>
					<div class="header-col qty-col"><span data-i18n="s_quantity">수량</span></div>
					<div class="header-col sum-col"><span data-i18n="s_sum">합계</span></div>
				</div>
				<div class="body-wrap order-complete-list">
					<?php
					foreach ($order_product as $product_data) {
						?>
						<div class="body-list product" data-product_type="<?= $product_data['product_type'] ?>" data-order_product_idx="<?= $product_data['order_product_idx'] ?>">
							<div class="product-info">
								<img class="prd-img" cnt="1" src="https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user<?=$product_data['img_location']?>" alt="">
								<div class="info-box">
									<div class="info-row">
										<div class="name" data-soldout=""><span>
												<?= $product_data['product_name'] ?>
											</span></div>
									</div>
									<div class="info-row mobile-saleprice">
										<div class="product-price">
											<?= $product_data['product_price'] ?>
										</div>
									</div>
									<div class="info-row">
										<div class="color-title"><span>
												<?= $product_data['color'] ?>
											</span></div>
											<?php
													$color_data = explode(";", $product_data['color_rgb']);
													$color_html = null;
													
													if(count($color_data) > 1) {
														$color_html = "linear-gradient(90deg, ".$color_data[0]." 50%, ".$color_data[1]." 50%);";
													} else {
														$color_html = $color_data[0];
													}
											?>
										<div class="color__box" data-maxcount="" data-colorcount="1" style="--background-color:<?= $color_html ?>">
											<div class="color" data-color="<?= $product_data['color_rgb'] ?>"data-soldout="STIN"></div>
										</div>
									</div>
									<div class="info-row">
										<div class="size__box">
											<li class="size" data-soldout="STIN">
												<?= $product_data['option_name'] ?>
											</li>
										</div>
									</div>
								</div>
							</div>

							<div class="list-row web-saleprice">
								<span class="product-price">
									<?= $product_data['sales_price'] ?>
								</span>
							</div>
							<div class="list-row">
								<span class="product-count">
									<?= $product_data['product_qty'] ?>
								</span>
							</div>
							<div class="list-row">
								<span class="total-price">
									<?= $product_data['product_price'] ?>
								</span>
								<?php
								if ($product_data['product_type'] == "S") {
								?>
									<img class="set_toggle" data-order_product_idx="<?=$product_data['order_product_idx']?>" data-action_type="show" src="/images/mypage/mypage_down_tab_btn.svg">
								<?php
								}
								?>
							</div>
						</div>

						<?php
						$set_product_info = $product_data['set_product_info'];
						if (count($set_product_info) > 0) {
							foreach ($set_product_info as $set_data) {
						?>
							<div class="body-list product set_product hidden" data-parent_idx=<?= $set_data['parent_idx'] ?>>
								<div class="product-info">
									<img class="prd-img" cnt="1" src="https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user<?= $set_data['img_location'] ?>" alt="">
									<div class="info-box">
										<div class="info-row">
											<div class="name" data-soldout=""><span>
													<?= $set_data['product_name'] ?>
												</span></div>
										</div>
										<div class="info-row mobile-saleprice">

										</div>
										<div class="info-row">
											<div class="color-title"><span>
													<?= $set_data['color'] ?>
												</span></div>
											<div class="color__box" data-maxcount="" data-colorcount="1">
												<?php
													$color_data = explode(";", $set_data['color_rgb']);
													$set_color_html = null;

													if(count($color_data) > 1) {
														$set_color_html = "linear-gradient(90deg, ".$color_data[0]." 50%, ".$color_data[1]." 50%);";
													} else {
														$set_color_html = $color_data[0];
													}
												?>
												<div class="color" data-color="<?= $set_data['color_rgb'] ?>"
													data-soldout="STIN" style="background-color:<?= $set_color_html ?>">
												</div>
											</div>
										</div>
										<div class="info-row">
											<div class="size__box">
												<li class="size" data-soldout="STIN">
													<?= $set_data['option_name'] ?>
												</li>
											</div>
										</div>
									</div>
								</div>

								<div class="list-row web-saleprice">

								</div>
								<div class="list-row">

								</div>
								<div class="list-row">

								</div>
							</div>
					<?php
							}
						}
					}
					?>
					<div class="calculation-wrap">
						<div class="calculation-box">
							<div class="product-sum calculation-row">
								<span data-i18n="o_subtotal">제품 합계</span>
								<span class="cal-price">
									<?= $order_info['price_product'] ?>
								</span>
							</div>
							<div class="point-box">
								<div class="calculation-row">
									<span data-i18n="v_used">바우처 사용</span>
									<span class="voucher-point-use" data-voucher="0">
										<?= $order_info['price_discount'] ?>
									</span>
								</div>
								<div class="calculation-row">
									<span data-i18n="membership_used">적립금 사용</span>
									<span class="accumulate-point-use" data-accumulate="0">
										<?= $order_info['price_mileage_point'] ?>
									</span>
								</div>
								<!--
								<div class="calculation-row">
									<span>충전 포인트 사용</span>
									<span class="charge-point-use" data-charge="0">
										<?= $order_info['price_charge_point'] ?>
									</span>
								</div>
								-->
							</div>
							<div class="calculation-row">
								<span data-i18n="o_shipping_total">배송비</span>
								<span data-delprice="5000" class="del-price">
									<?= $order_info['price_delivery'] ?>
								</span>
							</div>
						</div>
						<div class="total-price-wrap">
							<div class="total-box">
								<span data-i18n="s_order_total_price">최종 결제금액</span>
								<span class="product-qty hidden"></span>
							</div>
							<span class="total-price">
								<?= $order_info['price_total'] ?>
							</span>
						</div>
					</div>
				</div>
			</div>

			<div class="terms-service " data-group="4">
				<div class="header-title" data-i18n="o_order_cancel_msg">주문 취소 안내</div>
				<div class="terms-info-list">
					<p data-i18n="o_order_cancel_msg_01"></p>
					<p data-i18n="o_order_cancel_msg_02"></p>
				</div>
				<div class="header-title" data-i18n="o_return_exchange">교환 및 반품 안내</div>
				<div class="terms-info-list">
					<p data-i18n="o_order_cancel_msg_03"></p>
					<p data-i18n="o_order_cancel_msg_04"></p>
					<p data-i18n="o_order_list_info_03"></p>
				</div>
			</div>
			<div class="step-btn-wrap">
				<div class="step-btn pre" onClick="location.href='/main'"><span data-i18n="oc_keep_shopping">계속 쇼핑하기</span></div>
				<div class="step-btn next" data-step="1" onClick="location.href='/mypage/main?mypage_type=orderlist'">
					<span data-i18n="oc_to_orderlist">주문내역 보러 가기</span>
				</div>
			</div>
		</div>
	</section>
</main>
<script>
window.addEventListener('DOMContentLoaded', function () {
	clickSetToggle();
});

window.addEventListener("resize", function () {
	resizeEvent();
});

function resizeEvent() {
	const bodyWidth = document.querySelector("body").offsetWidth;
	if (1024 <= bodyWidth) {
		document.querySelector(".order-product").querySelector(".header-list").classList.remove("hidden");
	} else if (1024 >= bodyWidth) {
		document.querySelector(".order-product").querySelector(".header-list").classList.add("hidden");
	}
}

function clickSetToggle() {
	let set_toggle = document.querySelectorAll('.set_toggle');
	set_toggle.forEach(toggle => {
		toggle.addEventListener('click', function (e) {
			let toggle_btn = e.currentTarget;
			
			let order_product_idx = toggle_btn.dataset.order_product_idx;
			let action_type = toggle_btn.dataset.action_type;
			
			let set_product = document.querySelectorAll('.set_product');
			set_product.forEach(set => {
				if (set.dataset.parent_idx == order_product_idx) {
					set.classList.toggle('hidden');
				}
			});
			
			if (action_type == "show") {
				toggle_btn.dataset.action_type = "hide";
				toggle_btn.src = "/images/mypage/mypage_up_tab_btn.svg";
			} else if (action_type == "hide") {
				toggle_btn.dataset.action_type = "show";
				toggle_btn.src = "/images/mypage/mypage_down_tab_btn.svg";
			}
		});
	});
}
</script>