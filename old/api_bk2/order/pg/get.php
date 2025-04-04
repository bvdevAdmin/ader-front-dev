<?php
/*
 +=============================================================================
 | 
 | 결제정보 입력화면 - 결제 상품 및 주문자 정보 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.12.12
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

if ($member_idx == 0 || $country == null) {
	$json_result['code'] = 401;
	$json_result['msg'] = "로그인 후 다시 시도해 주세요.";
	exit;
}

$basket_idx = null;
if (isset($_POST['basket_idx'])) {
	$basket_idx = $_POST['basket_idx'];
}

if ($member_idx > 0 && $basket_idx != null) {
	$basket_cnt = $db->count("BASKET_INFO","IDX IN (".implode(",",$basket_idx).") AND MEMBER_IDX = ".$member_idx);

	if (count($basket_idx) != $basket_cnt) {
		$json_result['code'] = 402;
		$json_result['msg'] = "결제하려는 상품이 존재하지 않습니다. 쇼핑백에서 결제하려는 상품 정보를 확인해주세요.";
		exit;
	}
	
	if ($member_idx != 0 && $basket_idx != null) {
		$product_info = getOrderPgProductInfo($db,$country,$member_idx,$basket_idx);
		
		$total_price = calcTotalProductPrice($product_info);
		
		$member_info = getOrderPgMemberInfo($db,$country,$member_idx);
		
		$order_to_info = getOrderPgOrderToInfo($db,$country,$member_idx);
		
		$voucher_info = getOrderPgVoucherInfo($db,$country,$member_idx,$total_price);
		
		$voucher_cnt = calcVoucherCnt($voucher_info);
		
		$order_memo_info = getOrderPgOrderMemoInfo($db,$country);
		
		$json_result['data'] = array(
			'product_info'			=>$product_info,
			'member_info'			=>$member_info,
			'order_to_info'			=>$order_to_info,
			'voucher_cnt'			=>$voucher_cnt,
			'voucher_info'			=>$voucher_info,
			'order_memo_info'		=>$order_memo_info
		);
	}
}

function getOrderPgProductInfo($db,$country,$member_idx,$basket_idx) {
	$product_info = array();
	
	$select_basket_product_sql = "
		SELECT
			BI.IDX							AS BASKET_IDX,
			BI.PRODUCT_TYPE					AS PRODUCT_TYPE,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = BI.PRODUCT_IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)								AS IMG_LOCATION,
			BI.PRODUCT_NAME					AS PRODUCT_NAME,
			OM.BRAND						AS BRAND,
			PR.COLOR						AS COLOR,
			PR.COLOR_RGB					AS COLOR_RGB,
			BI.OPTION_NAME					AS OPTION_NAME,
			PR.REFUND_FLG					AS REFUND_FLG,
			BI.PRODUCT_QTY					AS PRODUCT_QTY,
			PR.SALES_PRICE_".$country."		AS SALES_PRICE
		FROM
			BASKET_INFO BI
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
		WHERE
			BI.IDX IN (".implode(",",$basket_idx).") AND
			BI.COUNTRY = '".$country."' AND
			BI.MEMBER_IDX = ".$member_idx." AND
			BI.PARENT_IDX = 0 AND
			BI.DEL_FLG = FALSE
	";

	$db->query($select_basket_product_sql);
	
	foreach($db->fetch() as $product_data) {
		$basket_idx = $product_data['BASKET_IDX'];
		$product_type = $product_data['PRODUCT_TYPE'];
		
		$set_product_info = array();
		if (!empty($basket_idx) && $product_type == "S") {
			$select_set_product_sql = "
				SELECT
					BI.PARENT_IDX				AS PARENT_IDX,
					(
						SELECT
							S_PI.IMG_LOCATION
						FROM
							PRODUCT_IMG S_PI
						WHERE
							S_PI.PRODUCT_IDX = BI.PRODUCT_IDX AND
							S_PI.IMG_TYPE = 'P' AND
							S_PI.IMG_SIZE = 'S'
						ORDER BY
							S_PI.IDX ASC
						LIMIT
							0,1
					)							AS IMG_LOCATION,
					BI.PRODUCT_NAME				AS PRODUCT_NAME,
					PR.COLOR					AS COLOR,
					PR.COLOR_RGB				AS COLOR_RGB,
					BI.OPTION_NAME				AS OPTION_NAME
				FROM
					BASKET_INFO BI
					LEFT JOIN SHOP_PRODUCT PR ON
					BI.PRODUCT_IDX = PR.IDX
				WHERE
					BI.PARENT_IDX = ".$basket_idx." AND
					BI.DEL_FLG = FALSE
			";
			
			$db->query($select_set_product_sql);
			
			$soldout_cnt = 0;
			foreach($db->fetch() as $set_data) {					
				$set_product_info[] = array(
					'parent_idx'		=>$set_data['PARENT_IDX'],
					'img_location'		=>$set_data['IMG_LOCATION'],
					'product_name'		=>$set_data['PRODUCT_NAME'],
					'color'				=>$set_data['COLOR'],
					'color_rgb'			=>$set_data['COLOR_RGB'],
					'option_name'		=>$set_data['OPTION_NAME']
				);
			}
		}
		
		$product_price = intval($product_data['SALES_PRICE']) * intval($product_data['PRODUCT_QTY']);
		
		$product_info[] = array(
			'basket_idx'			=>$product_data['BASKET_IDX'],
			'product_type'			=>$product_data['PRODUCT_TYPE'],
			'img_location'			=>$product_data['IMG_LOCATION'],
			'product_name'			=>$product_data['PRODUCT_NAME'],
			'brand'					=>$product_data['BRAND'],
			'color'					=>$product_data['COLOR'],
			'color_rgb'				=>$product_data['COLOR_RGB'],
			'option_name'			=>$product_data['OPTION_NAME'],
			'refund_flg'			=>$product_data['REFUND_FLG'],
			'product_qty'			=>$product_data['PRODUCT_QTY'],
			'sales_price'			=>$product_data['SALES_PRICE'],
			'txt_sales_price'		=>number_format($product_data['SALES_PRICE']),
			'product_price'			=>$product_price,
			'txt_product_price'		=>number_format($product_price),
			
			'set_product_info'		=>$set_product_info
		);
	}
	
	return $product_info;
}

function calcTotalProductPrice($product_info) {
	$total_price = 0;
	
	for ($i=0; $i<count($product_info); $i++) {
		$total_price += intval($product_info[$i]['product_price']);
	}
	
	return $total_price;
}

function getOrderPgMemberInfo($db,$country,$member_idx) {
	$member_info = array();
	
	$select_member_sql = "
		SELECT
			MB.MEMBER_NAME		AS MEMBER_NAME,
			MB.TEL_MOBILE		AS MEMBER_MOBILE,
			MB.MEMBER_ID		AS MEMBER_EMAIL
		FROM
			MEMBER_".$country." MB
		WHERE
			MB.IDX = ".$member_idx."
	";
	
	$db->query($select_member_sql);
	
	foreach($db->fetch() as $member_data) {
		$member_info = array(
			'member_name'		=>$member_data['MEMBER_NAME'],
			'member_mobile'		=>$member_data['MEMBER_MOBILE'],
			'member_email'		=>$member_data['MEMBER_EMAIL']
		);
	}
	
	return $member_info;
}

function getOrderPgOrderToInfo($db,$country,$member_idx) {
	$order_to_info = array();
	
	$select_order_to_sql = "
		SELECT
			OT.TO_PLACE			AS TO_PLACE,
			OT.TO_NAME			AS TO_NAME,
			OT.TO_MOBILE		AS TO_MOBILE,
			OT.TO_ZIPCODE		AS TO_ZIPCODE,
			OT.TO_LOT_ADDR		AS TO_LOT_ADDR,
			OT.TO_ROAD_ADDR		AS TO_ROAD_ADDR,
			OT.TO_DETAIL_ADDR	AS TO_DETAIL_ADDR,
			OT.TO_COUNTRY_CODE	AS TO_COUNTRY_CODE,
			IFNULL(
				CI.COUNTRY_NAME,''
			)					AS TO_COUNTRY_NAME,
			OT.TO_PROVINCE_IDX	AS TO_PROVINCE_IDX,
			IFNULL(
				PI.PROVINCE_NAME,''
			)					AS TO_PROVINCE_NAME,
			OT.TO_CITY			AS TO_CITY,
			OT.TO_ADDRESS		AS TO_ADDRESS
		FROM
			ORDER_TO OT LEFT JOIN
			COUNTRY_INFO CI
		ON
			OT.TO_COUNTRY_CODE = CI.COUNTRY_CODE LEFT JOIN
			PROVINCE_INFO PI
		ON
			OT.TO_PROVINCE_IDX = PI.IDX
		WHERE
			OT.COUNTRY = '".$country."' AND
			OT.MEMBER_IDX = ".$member_idx." AND
			OT.DEFAULT_FLG = TRUE
	";
	
	$db->query($select_order_to_sql);
	
	foreach($db->fetch() as $order_to_data) {
		$order_to_info = array(
			'to_place'			=>$order_to_data['TO_PLACE'],
			'to_name'			=>$order_to_data['TO_NAME'],
			'to_mobile'			=>$order_to_data['TO_MOBILE'],
			'to_zipcode'		=>$order_to_data['TO_ZIPCODE'],
			'to_road_addr'		=>$order_to_data['TO_ROAD_ADDR'],
			'to_lot_addr'		=>$order_to_data['TO_LOT_ADDR'],
			'to_detail_addr'	=>$order_to_data['TO_DETAIL_ADDR'],
			'to_country_code' 	=>$order_to_data['TO_COUNTRY_CODE'],
			'to_country_name' 	=>$order_to_data['TO_COUNTRY_NAME'],
			'to_province_idx' 	=>$order_to_data['TO_PROVINCE_IDX'],
			'to_province_name' 	=>$order_to_data['TO_PROVINCE_NAME'],
			'to_city' 			=>$order_to_data['TO_CITY'],
			'to_address' 		=>$order_to_data['TO_ADDRESS'],
		);
	}
	
	return $order_to_info;
}

function getOrderPgVoucherInfo($db,$country,$member_idx,$total_price) {
	$voucher_info = array();
	
	$select_voucher_info_sql = "
		SELECT
			VI.IDX				AS VOUCHER_IDX,
			VM.VOUCHER_NAME		AS VOUCHER_NAME,
			VM.SALE_TYPE		AS SALE_TYPE,
			VM.SALE_PRICE		AS SALE_PRICE,
			VM.MILEAGE_FLG		AS MILEAGE_FLG,
			
			DATE_FORMAT(
				VI.USABLE_START_DATE,
				'%Y.%m.%d'
			)					AS USABLE_START_DATE,
			DATE_FORMAT(
				VI.USABLE_END_DATE,
				'%Y.%m.%d'
			)					AS USABLE_END_DATE,
			
			CASE
				WHEN
					VI.USED_FLG = FALSE AND
					VM.MIN_PRICE <= ".$total_price."
					THEN
						TRUE
				ELSE
					FALSE
			END					AS VOUCHER_STATUS
		FROM
			VOUCHER_ISSUE VI
			LEFT JOIN VOUCHER_MST VM ON
			VI.VOUCHER_IDX = VM.IDX
		WHERE
			VI.COUNTRY = '".$country."' AND
			VI.MEMBER_IDX = ".$member_idx." AND
			(
				NOW() BETWEEN VI.USABLE_START_DATE AND VI.USABLE_END_DATE
			) AND
			(
				NOW() BETWEEN VM.VOUCHER_START_DATE AND VM.VOUCHER_END_DATE
			) AND
			VI.DEL_FLG = FALSE AND
			VM.DEL_FLG = FALSE
	";
	
	$db->query($select_voucher_info_sql);
	
	foreach($db->fetch() as $voucher_data) {
		$voucher_info[] = array(
			'voucher_idx'			=>$voucher_data['VOUCHER_IDX'],
			'voucher_name'			=>$voucher_data['VOUCHER_NAME'],
			'sale_type'				=>$voucher_data['SALE_TYPE'],
			'sale_price'			=>$voucher_data['SALE_PRICE'],
			'mileage_flg'			=>$voucher_data['MILEAGE_FLG'],
			'usable_start_date'		=>$voucher_data['USABLE_START_DATE'],
			'usable_end_date'		=>$voucher_data['USABLE_END_DATE'],
			'voucher_status'		=>$voucher_data['VOUCHER_STATUS']
		);
	}
	
	return $voucher_info;
}

function calcVoucherCnt($voucher_info) {
	$voucher_cnt = 0;
	
	for ($i=0; $i<count($voucher_info); $i++) {
		if ($voucher_info[$i]['voucher_status'] == true) {
			$voucher_cnt++;
		}
	}
	
	return $voucher_cnt;
}

function getOrderPgOrderMemoInfo($db,$country) {
	$order_memo_info = array();
	
	$select_order_memo_sql = "
		SELECT
			OM.IDX					AS MEMO_IDX,
			OM.COUNTRY				AS COUNTRY,
			OM.PLACEHOLDER_FLG		AS PLACEHOLDER_FLG,
			OM.MEMO_TXT				AS MEMO_TXT,
			OM.DIRECT_FLG			AS DIRECT_FLG
		FROM
			ORDER_MEMO OM
		WHERE
			COUNTRY = '".$country."'
		ORDER BY
			OM.DISPLAY_NUM ASC
	";
	
	$db->query($select_order_memo_sql);
	
	foreach($db->fetch() as $memo_data) {
		$order_memo_info[] = array(
			'memo_idx'			=>$memo_data['MEMO_IDX'],
			'country'			=>$memo_data['COUNTRY'],
			'placeholder_flg'	=>$memo_data['PLACEHOLDER_FLG'],
			'memo_txt'			=>$memo_data['MEMO_TXT'],
			'direct_flg'		=>$memo_data['DIRECT_FLG']
		);
	}
	
	return $order_memo_info;
}

?>