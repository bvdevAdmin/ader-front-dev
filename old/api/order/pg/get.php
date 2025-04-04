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

include_once(dir_f_api."/common/check.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_level = 0;
if (isset($_SESSION['LEVEL_IDX'])) {
	$member_level = $_SESSION['LEVEL_IDX'];
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = "로그인 후 다시 시도해 주세요.";
	
	echo json_encode($json_result);
	exit;
}

if ($member_idx > 0 && isset($basket_idx)) {
	/* 임시 주문정보 삭제처리 */
	deleteTmpOrder($db,$country,$member_idx);
	
	$basket_cnt = $db->count("BASKET_INFO","IDX IN (".implode(",",$basket_idx).") AND MEMBER_IDX = ".$member_idx);

	if (count($basket_idx) != $basket_cnt) {
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_F_ERR_0115', array());
		exit;
	}
	
	/* 결제하려는 쇼핑백 상품 정보 조회 전 체크처리 */
	$check_result = checkBasketInfo($db,$country,$member_idx,$member_level,$basket_idx);
	if ($check_result == true) {
		/* 1. 결제하려는 쇼핑백 상품 정보 조회처리 */
		$product_info = getOrderPgProductInfo($db,$country,$member_idx,$basket_idx);
		
		/* 2. 결제하려는 쇼핑백 상품의 총 금액 계산 조회처리 */
		$total_price = calcTotalProductPrice($product_info);
		
		/* 3. 결제 회원정보 조회처리 */
		$member_info = getOrderPgMemberInfo($db,$country,$member_idx);
		
		/* 4. 배송지 정보 조회처리 */
		$order_to_info = getOrderPgOrderToInfo($db,$country,$member_idx);
		
		/* 5. 현재 사용 가능 한 보유 바우처 정보 조회처리 */
		$voucher_info = getOrderPgVoucherInfo($db,$country,$member_idx,$basket_idx,$total_price);
		
		/* 6. 현재 사용 가능 한 보유 바우처 수량 조회처리 */
		$cnt_voucher_total	= count($voucher_info);
		$cnt_voucher_usable	= calcCntVoucherUsable($voucher_info);
		
		/* 7. 선택 가능 한 기본 주문 메모 조회처리 */
		$order_memo_info = getOrderPgOrderMemoInfo($db,$country);
		
		$json_result['data'] = array(
			'product_info'			=>$product_info,
			'member_info'			=>$member_info,
			'order_to_info'			=>$order_to_info,
			'cnt_voucher_total'		=>$cnt_voucher_total,
			'cnt_voucher_usable'	=>$cnt_voucher_usable,
			'voucher_info'			=>$voucher_info,
			'order_memo_info'		=>$order_memo_info
		);
	}
}

/* 임시 주문정보 삭제처리 */
function deleteTmpOrder($db,$country,$member_idx) {
	$delete_tmp_order_product_sql = "
		DELETE FROM
			TMP_ORDER_PRODUCT
		WHERE
			ORDER_IDX IN (
				SELECT
					IDX
				FROM
					TMP_ORDER_INFO
				WHERE
					COUNTRY = '".$country."' AND
					MEMBER_IDX = ".$member_idx."
			)
	";
	
	$db->query($delete_tmp_order_product_sql);
	
	$db->delete(
		"TMP_ORDER_INFO",
		"
			COUNTRY = ? AND
			MEMBER_IDX = ?
		",
		array(
			$country,
			$member_idx
		)
	);
}

/* 결제하려는 쇼핑백 상품 정보 조회 전 체크처리 */
function checkBasketInfo($db,$country,$member_idx,$member_level,$basket_idx) {
	$check_result = true;
	
	$select_basket_info_sql = "
		SELECT
			BI.PRODUCT_IDX		AS PRODUCT_IDX,
			BI.OPTION_IDX		AS OPTION_IDX,
			BI.PRODUCT_TYPE		AS PRODUCT_TYPE,
			BI.PRODUCT_QTY		AS BASKET_QTY
		FROM
			BASKET_INFO BI
		WHERE
			BI.IDX IN (".implode(",",$basket_idx).") AND
			BI.COUNTRY = '".$country."' AND
			BI.MEMBER_IDX = ".$member_idx."
	";
	
	$db->query($select_basket_info_sql);
	
	foreach($db->fetch() as $data) {
		$product_idx = $data['PRODUCT_IDX'];
		$option_idx = $data['OPTION_IDX'];
		$product_type = $data['PRODUCT_TYPE'];
		$basket_qty = $data['BASKET_QTY'];
		
		/* 1. 결제하려는 상품의 구매 회원 등급 제한 체크 */
		$check_result_level = checkProductLevel($db,$member_level,"PRD",$product_idx);
		if ($check_result_level['result'] != true) {
			$json_result['code'] = 403;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0098', array());
			
			echo json_encode($json_result);
			exit;
		}
		
		/* 2. 결제하려는 상품의 옵션별 구매 제한 수량 체크 */
		if ($product_type == "B") {
			$check_result_qty = checkQtyLimit($db,$country,$member_idx,"PRD",$product_idx,$option_idx,1);
			if ($check_result_qty['result'] != true) {
				$json_result['code'] = 404;
				$json_result['msg'] = $check_result_qty['msg'];
				
				echo json_encode($json_result);
				exit;
			}
		}
		
		/* 3. 결제하려는 상품의 ID당 구매제한 체크 */
		$check_result_id = checkIdReorder($db,$country,$member_idx,"PRD",$product_idx);
		if ($check_result_id['result'] != true) {
			$json_result['code'] = 405;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0004', array());
			
			echo json_encode($json_result);
			exit;
		}
		
		/* 4. 결제하려는 상품의 잔여재고 체크 */
		$stock_result = checkPurchaseableQty($db,$product_idx,$option_idx,$basket_qty);
		if ($stock_result == false) {
			$json_result['code'] = 406;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_F_ERR_0116', array());
			
			echo json_encode($json_result);
			exit;
		}
	}
	
	return $check_result;
}

/* 1. 결제하려는 쇼핑백 상품 정보 조회처리 */
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

/* 2. 결제하려는 쇼핑백 상품의 총 금액 계산 조회처리 */
function calcTotalProductPrice($product_info) {
	$total_price = 0;
	
	for ($i=0; $i<count($product_info); $i++) {
		$total_price += intval($product_info[$i]['product_price']);
	}
	
	return $total_price;
}

/* 3. 결제 회원정보 조회처리 */
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

/* 4. 배송지 정보 조회처리 */
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

/* 5-1. 현재 사용 가능 한 보유 바우처 정보 조회처리 */
function getOrderPgVoucherInfo($db,$country,$member_idx,$basket_idx,$total_price) {
	$voucher_info = array();
	
	$select_voucher_info_sql = "
		SELECT
			VM.IDX				AS VOUCHER_MST_IDX,
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
			
			VI.USED_FLG			AS USED_FLG,
			VM.MIN_PRICE		AS MIN_PRICE
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
	
	foreach($db->fetch() as $data) {
		$voucher_idx	= $data['VOUCHER_MST_IDX'];
		$used_flg		= $data['USED_FLG'];
		$min_price		= $data['MIN_PRICE'];
		
		$voucher_status = false;
		$check_result = checkVoucherProduct($db,$voucher_idx,$basket_idx);
		if ($check_result == true) {
			if ($used_flg == false && $min_price <= $total_price) {
				$voucher_status = true;
			}
		}
		
		$voucher_info[] = array(
			'voucher_idx'			=>$data['VOUCHER_IDX'],
			'voucher_name'			=>$data['VOUCHER_NAME'],
			'sale_type'				=>$data['SALE_TYPE'],
			'sale_price'			=>$data['SALE_PRICE'],
			'mileage_flg'			=>$data['MILEAGE_FLG'],
			'usable_start_date'		=>$data['USABLE_START_DATE'],
			'usable_end_date'		=>$data['USABLE_END_DATE'],
			'voucher_status'		=>$voucher_status
		);
	}
	
	return $voucher_info;
}

/* 5-2. 바우처 적용 제외상품 체크처리 */
function checkVoucherProduct($db,$voucher_idx,$basket_idx) {
	$check_result = true;
	
	$select_voucher_product_sql = "
		SELECT
			GROUP_CONCAT(
				VP.PRODUCT_IDX
			)		AS PRODUCT_IDX
		FROM
			VOUCHER_PRODUCT VP
		WHERE
			VP.VOUCHER_IDX = ?
	";
	
	$db->query($select_voucher_product_sql,array($voucher_idx));
	
	$param_voucher = null;
	foreach($db->fetch() as $data) {
		$voucher_product = $data['PRODUCT_IDX'];
	}
	
	if ($voucher_product != null) {
		$param_voucher	= explode(",",$voucher_product);
	}
	
	$select_basket_product_sql = "
		SELECT
			BI.PRODUCT_IDX		AS PRODUCT_IDX
		FROM
			BASKET_INFO BI
		WHERE
			IDX IN (?)
	";
	
	$db->query($select_basket_product_sql,array(implode(",",$basket_idx)));
	
	$param_basket = array();
	foreach($db->fetch() as $data) {
		array_push($param_basket,$data['PRODUCT_IDX']);
	}
	
	if ($param_voucher != null && count($param_basket) > 0) {
		for ($i=0; $i<count($param_basket); $i++) {
			$basket		= $param_basket[$i];
			
			if (in_array($basket,$param_voucher)) {
				$check_result = false;
				break;
			}
		}
	}
	
	return $check_result;
}

/* 6. 현재 사용 가능 한 보유 바우처 수량 조회처리 */
function calcCntVoucherUsable($voucher_info) {
	$cnt_voucher_usable = 0;
	
	for ($i=0; $i<count($voucher_info); $i++) {
		if ($voucher_info[$i]['voucher_status'] == true) {
			$cnt_voucher_usable++;
		}
	}
	
	return $cnt_voucher_usable;
}

/* 7. 선택 가능 한 기본 주문 메모 조회처리 */
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