<?php
/*
 +=============================================================================
 | 
 | 결제하기 화면 - PG사 결제처리용 임시 주문정보 등록
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

include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/common/check.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$level_idx = 0;
if (isset($_SESSION['LEVEL_IDX'])) {
	$level_idx = $_SESSION['LEVEL_IDX'];
}

$member_name = null;
if (isset($_SESSION['MEMBER_NAME'])) {
	$member_name = $_SESSION['MEMBER_NAME'];
}

$tel_mobile = null;
if (isset($_SESSION['TEL_MOBILE'])) {
	$tel_mobile = $_SESSION['TEL_MOBILE'];
}

$member_email = null;
if (isset($_SESSION['MEMBER_EMAIL'])) {
	$member_email = $_SESSION['MEMBER_EMAIL'];
}

$price_mileage_point = 0;
if (isset($_POST['price_mileage_point']) && strlen($_POST['price_mileage_point']) > 0) {
	$price_mileage_point = str_replace(",","",$_POST['price_mileage_point']);
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

/* 임시 주문정보 삭제처리 */
deleteTmpOrder($db,$country,$member_idx);

/* 1-1. 쇼핑백 선택 상품 예외처리 */
if (isset($basket_idx)) {
	$check_result = checkBasketInfo($db,$country,$member_idx,$basket_idx);
	if ($check_result != true) {
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_F_ERR_0115', array());
		
		echo json_encode($json_result);
		exit;
	}
}

/* 1-2. 쇼핑백 선택 상품 재고 예외처리 */
if (isset($basket_idx)) {
	$check_result = checkBasketQty($db,$country,$basket_idx);
	if ($check_result != true) {
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_F_ERR_0116', array());
		
		echo json_encode($json_result);
		exit;
	}
}

/* 2. 보유 바우처 예외처리 */
$voucher_info = null;
if ($voucher_idx > 0) {
	$check_result = checkVoucherInfo($db,$country,$member_idx,$voucher_idx);
	if ($check_result != true) {
		$json_result['code'] = 403;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_F_ERR_0117', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	$voucher_info = getVoucherInfo($db,$country,$member_idx,$voucher_idx);
}

/* 3. 회원 적립금 예외처리 */
if ($price_mileage_point > 0) {
	$check_result = checkMemberMileagePoint($db,$country,$member_idx,$price_mileage_point);
	if ($check_result != true) {
		$json_result['code'] = 404;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_F_ERR_0118', array());
		
		echo json_encode($json_result);
		exit;
	}
}

/* 4. 회원 예치금 예외처리 */
if ($price_charge_point > 0) {
	$check_result = checkMemberChargePoint($db,$country,$member_idx,$price_mileage_point,$price_charge_point);
	if ($check_result != true) {
		$json_result['code'] = 405;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_F_ERR_0119', array());
		
		echo json_encode($json_result);
		exit;
	}
}

/* 5. 주문 상품 정보 조회 */
$select_basket_product_sql = "
	SELECT
		BI.IDX							AS BASKET_IDX,
		BI.PRODUCT_IDX					AS PRODUCT_IDX,
		PR.PRODUCT_TYPE					AS PRODUCT_TYPE,
		PR.PRODUCT_CODE					AS PRODUCT_CODE,
		PR.REORDER_CNT					AS REORDER_CNT,
		OM.PREORDER_FLG					AS PREORDER_FLG,
		PR.PRODUCT_NAME					AS PRODUCT_NAME,
		BI.OPTION_IDX					AS OPTION_IDX,
		BI.BARCODE						AS BARCODE,
		BI.OPTION_NAME					AS OPTION_NAME,
		BI.PRODUCT_QTY					AS PRODUCT_QTY,
		PR.SALES_PRICE_".$country."		AS SALES_PRICE
	FROM
		BASKET_INFO BI
		LEFT JOIN SHOP_PRODUCT PR ON
		BI.PRODUCT_IDX = PR.IDX
		LEFT JOIN ORDERSHEET_MST OM ON
		PR.ORDERSHEET_IDX = OM.IDX
	WHERE
		BI.IDX IN (".$basket_idx.")
	ORDER BY
		BI.IDX DESC
";

$db->query($select_basket_product_sql);

$product_info = array();

$price_total = 0;
$price_product = 0;

foreach($db->fetch() as $product_data) {
	$product_qty = $product_data['PRODUCT_QTY'];
	$sales_price = $product_data['SALES_PRICE'];
	
	$product_info[] = array(
		'basket_idx'		=>$product_data['BASKET_IDX'],
		'product_idx'		=>$product_data['PRODUCT_IDX'],
		'product_code'		=>$product_data['PRODUCT_CODE'],
		'product_type'		=>$product_data['PRODUCT_TYPE'],
		'reorder_cnt'		=>$product_data['REORDER_CNT'],
		'preorder_flg'		=>$product_data['PREORDER_FLG'],
		'product_name'		=>$product_data['PRODUCT_NAME'],
		'option_idx'		=>$product_data['OPTION_IDX'],
		'barcode'			=>$product_data['BARCODE'],
		'option_name'		=>$product_data['OPTION_NAME'],
		'product_qty'		=>$product_qty,
		'sales_price'		=>$sales_price
	);
	
	$price_product += intval($product_qty) * intval($sales_price);
}

/* 6. 바우처 할인금액 계산 */
$price_discount = 0;
if ($voucher_info != null && count($voucher_info) > 0) {
	$sale_type = $voucher_info['sale_type'];
	$sale_price = $voucher_info['sale_price'];
	
	if ($sale_type == "PRC") {
		$price_discount = $sale_price;
	} else if ($sale_type = "PER") {
		$price_discount = ($price_product * ($sale_price / 100));
	}
}

/* 7. 주문 배송비 계산 */
$price_delivery = 0;
if ($country == "KR") {
	/* 한국몰 배송비 계산처리 */
	if ($price_total > 0 && $price_total < 80000) {
		$price_delivery = 2500;
	}
} else {
	/* 영문몰/중문몰 배송비 계산처리 */
	if ($to_country_code != null) {
		$price_delivery = calcPriceDelivery($db,$country,$to_country_code);
	} else {
		$json_result['code'] = 303;
		$json_result['msg'] = "해외 배송비 조회처리중 오류가 발생했습니다.";
	}
}

/* 8. PG사 주문 결제금액 계산 */
$price_total = intval($price_product) - intval($price_discount) - floatval($price_mileage_point) - intval($price_charge_point) - $price_delivery;

/* 9. 주문 코드 생성 */
$order_code = date("Ymd-His").substr(microtime(),2,1);

/* 10. 주문 타이틀 생성 */
$order_title = null;

$product_cnt = count($product_info);

$title_unit = "";
if ($country == "KR") {
	$title_unit = "외";
} else {
	$title_unit = "and";
}

if ($product_cnt > 1) {
	$order_title = $product_info[0]['product_name']." ".$title_unit." ".($product_cnt - 1)."건";
} else {
	$order_title = $product_info[0]['product_name'];
}

/* 11. [임시 주문 정보],[임시 주문 상품 정보] 테이블 등록처리 */
try {
	$txt_road_addr		= null;
	$txt_lot_addr		= null;
	$txt_detail_addr	= null;
	
	if($country == 'KR'){
		$txt_road_addr		= $to_road_addr;
		$txt_lot_addr		= $to_lot_addr;
		$txt_detail_addr	= $to_detail_addr;
	} else {
		$to_country_name	= $db->get('COUNTRY_INFO','COUNTRY_CODE = ?',array($to_country_code))[0]['COUNTRY_NAME'];
		$to_province_name	= $db->get('PROVINCE_INFO', 'IDX = ?', array($to_province_idx))[0]['PROVINCE_NAME'];
		
		$txt_road_addr = $to_city.' '.$to_province_name.' '.$to_country_name;
		$txt_lot_addr = $txt_road_addr;
		$txt_detail_addr = $to_address;
	}
	
	/* [임시 주문 정보] 테이블 등록 PARAM */
	$param_order_info = array(
		'country'				=>$country,
		'order_code'			=>$order_code,
		'order_title'			=>$order_title,
		
		'member_idx'			=>$member_idx,
		'member_id'				=>$member_id,
		'member_name'			=>$member_name,
		'tel_mobile'			=>$tel_mobile,
		'level_idx'				=>$level_idx,
		
		'price_product'			=>$price_product,
		'price_discount'		=>$price_discount,
		'price_mileage_point'	=>$price_mileage_point,
		'price_charge_point'	=>$price_charge_point,
		'price_delivery'		=>$price_delivery,
		'price_total'			=>$price_total,
		
		'to_place'				=>$to_place,
		'to_name'				=>$to_name,
		'to_mobile'				=>$to_mobile,
		'to_zipcode'			=>$to_zipcode,
		'txt_lot_addr'			=>$txt_lot_addr,
		'txt_road_addr'			=>$txt_road_addr,
		'txt_detail_addr'		=>$txt_detail_addr,
		
		'order_memo'			=>$order_memo,
		
		'basket_idx'			=>$basket_idx
	);
	
	$order_idx = addTmpOrderInfo($db,$param_order_info);
	
	if (!empty($order_idx)) {
		/* [임시 주문 상품 정보] 테이블 등록 PARAM */
		$param_order_product = array(
			'country'			=>$country,
			'order_idx'			=>$order_idx,
			'order_code'		=>$order_code,
			'product_info'		=>$product_info,	
			'member_id'			=>$member_id
		);
		
		/* [임시 주문 상품 정보] 테이블 등록처리 */
		$product_num = addTmpOrderProduct($db,$country,$param_order_product);
		
		/* [주문 상품 정보] 테이블 바우처 결제정보 등록 */
		if ($voucher_info != null && count($voucher_info) > 0) {
			$param_order_voucher = array(
				'order_idx'			=>$order_idx,
				'order_code'		=>$order_code,
				'product_num'		=>$product_num,
				'price_discount'	=>$price_discount,
				'voucher_info'		=>$voucher_info,
				'member_id'			=>$member_id
			);
			
			addTmpOrderProductVoucher($db,$param_order_voucher);
			
			$product_num++;
		}
		
		/* [주문 상품 정보] 테이블 배송비 등록처리 */
		if ($price_delivery > 0) {
			$param_order_delivery = array(
				'order_idx'			=>$order_idx,
				'order_code'		=>$order_code,
				'product_num'		=>$product_num,
				'price_delivery'	=>$price_delivery
			);
			
			addTmpOrderProductDelivery($db,$param_order_delivery,$member_id,$price_delivery);
		}
	}
	
	$db->commit();
	
	$json_result['data'] = array(
		'country'				=>$country,
		'order_code'			=>$order_code,
		'order_title'			=>$order_title,
		'price_product'			=>$price_product,
		'price_mileage_point'	=>$price_mileage_point,
		'price_charge_point'	=>$price_charge_point,
		'price_discount'		=>$price_discount,
		'price_delivery'		=>$price_delivery,
		'price_total'			=>$price_total,
		
		'member_name'			=>$member_name
	);
} catch (mysqli_sql_exception $exception) {
	$db->rollback();
	print_r($exception);
	
	$json_result['code'] = 301;
	$json_result['msg'] = "주문정보 등록처리중 오류가 발생했습니다.";
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

/* 1-1. 쇼핑백 선택 상품 예외처리 */
function checkBasketInfo($db,$country,$member_idx,$basket_idx) {
	$check_result = false;
	
	$param_cnt = explode(",",$basket_idx);
	
	$basket_cnt = $db->count("BASKET_INFO","IDX IN (".$basket_idx.") AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
	if (count($param_cnt) == $basket_cnt) {
		$check_result = true;
	}
	
	return $check_result;
}

/* 1-2. 쇼핑백 선택 상품 재고 예외처리 */
function checkBasketQty($db,$country,$basket_idx) {
	$check_result = false;
	
	$select_basket_info_sql = "
		SELECT
			PRODUCT_IDX		AS PRODUCT_IDX,
			OPTION_IDX		AS OPTION_IDX,
			PRODUCT_QTY		AS PRODUCT_QTY
		FROM
			BASKET_INFO BI
		WHERE
			BI.IDX IN (".$basket_idx.")
	";
	
	$db->query($select_basket_info_sql);
	
	$err_cnt = 0;
	
	foreach($db->fetch() as $data) {
		$product_idx = $data['PRODUCT_IDX'];
		$option_idx = $data['OPTION_IDX'];
		$product_qty = $data['PRODUCT_QTY'];
		
		$result = checkPurchaseableQty($db,$product_idx,$option_idx,$product_qty);
		if ($result != true) {
			$err_cnt++;
		}
	}
	
	if ($err_cnt == 0) {
		$check_result = true;
	}
	
	return $check_result;
}

/* 2. 보유 바우처 예외처리 */
function checkVoucherInfo($db,$country,$member_idx,$voucher_idx) {
	$check_result = false;
	
	$voucher_cnt = $db->count("VOUCHER_ISSUE","IDX = ".$voucher_idx." AND COUNTRY='".$country."' AND MEMBER_IDX = ".$member_idx." AND (NOW() BETWEEN USABLE_START_DATE AND USABLE_END_DATE) AND USED_FLG = FALSE");
	
	if ($voucher_cnt > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

function getVoucherInfo($db,$country,$member_idx,$voucher_idx) {
	$voucher_info = null;
	
	$select_voucher_issue_sql = "
		SELECT
			VI.IDX				AS VOUCHER_IDX,
			VM.VOUCHER_NAME		AS VOUCHER_NAME,
			VM.SALE_TYPE		AS SALE_TYPE,
			VM.SALE_PRICE		AS SALE_PRICE
		FROM
			VOUCHER_ISSUE VI
			LEFT JOIN VOUCHER_MST VM ON
			VI.VOUCHER_IDX = VM.IDX
		WHERE
			VI.IDX = ".$voucher_idx." AND
			VI.COUNTRY = '".$country."' AND
			VI.MEMBER_IDX = ".$member_idx."
	";
	
	$db->query($select_voucher_issue_sql);
	
	foreach($db->fetch() as $voucher_data) {
		$voucher_info = array(
			'voucher_idx'		=>$voucher_data['VOUCHER_IDX'],
			'voucher_name'		=>$voucher_data['VOUCHER_NAME'],
			'sale_type'			=>$voucher_data['SALE_TYPE'],
			'sale_price'		=>$voucher_data['SALE_PRICE']
		);
	}
	
	return $voucher_info;
}

/* 3. 회원 적립금 예외처리 */
function checkMemberMileagePoint($db,$country,$member_idx,$price_mileage_point) {
	$check_result = false;
	
	$member_mileage = 0;
	
	$select_member_mileage_sql = "
		SELECT
			(
				SELECT
					MI.MILEAGE_BALANCE
				FROM
					MILEAGE_INFO MI
				WHERE
					MI.COUNTRY = '".$country."' AND
					MI.MEMBER_IDX = ".$member_idx."
				ORDER BY
					MI.IDX DESC
				LIMIT
					0,1
			)		AS MEMBER_MILEAGE
		FROM
			DUAL
	";
	
	$db->query($select_member_mileage_sql);

	foreach ($db->fetch() as $point_data) {
		$member_mileage = $point_data['MEMBER_MILEAGE'];
	}
	
	if ($member_mileage > 0 && $member_mileage > $price_mileage_point) {
		$check_result = true;
	} else {
		$check_result = false;
	}
	
	return $check_result;
}

/* 4. 회원 예치금 예외처리 */
function checkMemberChargePoint($db,$country,$member_idx,$price_charge_point) {
	$check_result = false;
	
	$member_charge = 0;
	
	$select_member_charge_sql = "
		SELECT
			0		AS MEMBER_CHARGE
		FROM
			DUAL
	";
	
	$db->query($select_member_charge_sql);

	foreach ($db->fetch() as $point_data) {
		$member_charge = $point_data['MEMBER_CHARGE'];
	}
	
	if ($member_charge > 0 && $price_charge_point > $member_charge) {
		$check_result = true;
	} else {
		$check_result = false;
	}
	
	return $check_result;
}

/* 7. 주문 배송비 계산 */
function calcPriceDelivery($db,$country,$to_country_code) {
	$price_delivery = 0;
	
	$select_dhl_zones_sql = "
		SELECT
			DZ.COST		AS COST,
			(
				SELECT
					S_PC.CURRENCY
				FROM
					PRODUCT_CURRENCY S_PC
				WHERE
					S_PC.COUNTRY = '".$country."'
			)			AS CURRENCY
		FROM
			DHL_ZONES DZ
		WHERE
			DZ.ZONE_NUM = (
				SELECT
					S_CI.ZONE_NUM
				FROM
					COUNTRY_INFO S_CI
				WHERE
					S_CI.COUNTRY_CODE = '".$to_country_code."'
			)
	";
	
	$db->query($select_dhl_zones_sql);
	
	foreach($db->fetch() as $zone_data) {
		$price_delivery = intval(intval($zone_data['COST']) * floatval($zone_data['CURRENCY']));
	}
	
	return $price_delivery;
}

/* [임시 주문 정보] 테이블 등록처리 */
function addTmpOrderInfo($db,$data) {
	$db->insert(
		"TMP_ORDER_INFO",
		array(
			'COUNTRY'				=>$data['country'],
			'ORDER_CODE'			=>$data['order_code'],
			'ORDER_TITLE'			=>$data['order_title'],
			'ORDER_STATUS'			=>'PCP',
			
			'MEMBER_IDX'			=>$data['member_idx'],
			'MEMBER_ID'				=>$data['member_id'],
			'MEMBER_NAME'			=>$data['member_name'],
			'MEMBER_MOBILE'			=>$data['tel_mobile'],
			'MEMBER_LEVEL'			=>$data['level_idx'],
			
			'PRICE_PRODUCT'			=>$data['price_product'],
			'PRICE_MILEAGE_POINT'	=>$data['price_mileage_point'],
			'PRICE_CHARGE_POINT'	=>$data['price_charge_point'],
			'PRICE_DISCOUNT'		=>$data['price_discount'],
			'PRICE_DELIVERY'		=>$data['price_delivery'],
			'PRICE_TOTAL'			=>$data['price_total'],
			
			'TO_PLACE'				=>$data['to_place'],
			'TO_NAME'				=>$data['to_name'],
			'TO_MOBILE'				=>$data['to_mobile'],
			'TO_ZIPCODE'			=>$data['to_zipcode'],
			'TO_LOT_ADDR'			=>$data['txt_lot_addr'],
			'TO_ROAD_ADDR'			=>$data['txt_road_addr'],
			'TO_DETAIL_ADDR'		=>$data['txt_detail_addr'],
			
			'ORDER_MEMO'			=>$data['order_memo'],
			
			'BASKET_IDX'			=>$data['basket_idx'],
			
			'CREATER'				=>$data['member_id'],
			'UPDATER'				=>$data['member_id']
		)
	);
	
	$order_idx = $db->last_id();
	
	return $order_idx;
}

/* [임시 주문 상품 정보] 테이블 등록처리 */
function addTmpOrderProduct($db,$country,$param_order_product) {
	$order_idx = $param_order_product['order_idx'];
	$order_code = $param_order_product['order_code'];
	$member_id = $param_order_product['member_id'];
	
	$product_info = $param_order_product['product_info'];
	
	$product_num = 1;
	
	for ($i=0; $i<count($product_info); $i++) {
		$data = $product_info[$i];
		
		$db->insert(
			"TMP_ORDER_PRODUCT",
			array(
				"ORDER_IDX"				=>$order_idx,
				"ORDER_CODE"			=>$order_code,
				"ORDER_PRODUCT_CODE"	=>$order_code."-".$product_num,
				"ORDER_STATUS"			=>'PCP',
				
				"PRODUCT_IDX"			=>$data['product_idx'],
				"PRODUCT_TYPE"			=>$data['product_type'],
				"REORDER_CNT"			=>$data['reorder_cnt'],
				"PREORDER_FLG"			=>$data['preorder_flg'],
				"PRODUCT_CODE"			=>$data['product_code'],
				"PRODUCT_NAME"			=>$data['product_name'],
				
				"OPTION_IDX"			=>$data['option_idx'],
				"BARCODE"				=>$data['barcode'],
				"OPTION_NAME"			=>$data['option_name'],
				
				"PRODUCT_QTY"			=>$data['product_qty'],
				"PRODUCT_PRICE"			=>intval($data['product_qty']) * floatval($data['sales_price']),
				
				"CREATER"				=>$member_id,
				"UPDATER"				=>$member_id
			)
		);
		
		$product_num++;
		
		$parent_idx = $db->last_id();
		
		if ($data['product_type'] == "S" && !empty($parent_idx)) {
			$select_basket_set_product_sql = "
				SELECT
					BI.PRODUCT_IDX					AS PRODUCT_IDX,
					PR.PRODUCT_TYPE					AS PRODUCT_TYPE,
					PR.PRODUCT_CODE					AS PRODUCT_CODE,
					PR.REORDER_CNT					AS REORDER_CNT,
					OM.PREORDER_FLG					AS PREORDER_FLG,
					PR.PRODUCT_NAME					AS PRODUCT_NAME,
					BI.OPTION_IDX					AS OPTION_IDX,
					BI.BARCODE						AS BARCODE,
					BI.OPTION_NAME					AS OPTION_NAME,
					BI.PRODUCT_QTY					AS PRODUCT_QTY,
					PR.SALES_PRICE_".$country."		AS SALES_PRICE
				FROM
					BASKET_INFO BI
					LEFT JOIN SHOP_PRODUCT PR ON
					BI.PRODUCT_IDX = PR.IDX
					LEFT JOIN ORDERSHEET_MST OM ON
					PR.ORDERSHEET_IDX = OM.IDX
				WHERE
					PARENT_IDX = ".$data['basket_idx']."
				ORDER BY
					BI.IDX DESC
			";
			
			$db->query($select_basket_set_product_sql);

			foreach($db->fetch() as $set_data) {
				$db->insert(
					"TMP_ORDER_PRODUCT",
					array(
						'ORDER_IDX'				=>$order_idx,
						'ORDER_CODE'			=>$order_code,
						'ORDER_PRODUCT_CODE'	=>$order_code."-".$product_num,
						'ORDER_STATUS'			=>'PCP',
						
						'PRODUCT_IDX'			=>$set_data['PRODUCT_IDX'],
						'PRODUCT_TYPE'			=>$set_data['PRODUCT_TYPE'],
						'PARENT_IDX'			=>$parent_idx,
						'REORDER_CNT'			=>$set_data['REORDER_CNT'],
						'PREORDER_FLG'			=>$set_data['PREORDER_FLG'],
						'PRODUCT_CODE'			=>$set_data['PRODUCT_CODE'],
						'PRODUCT_NAME'			=>$set_data['PRODUCT_NAME'],
						
						'OPTION_IDX'			=>$set_data['OPTION_IDX'],
						'BARCODE'				=>$set_data['BARCODE'],
						'OPTION_NAME'			=>$set_data['OPTION_NAME'],
						
						'PRODUCT_QTY'			=>$set_data['PRODUCT_QTY'],
						'PRODUCT_PRICE'			=>intval($set_data['PRODUCT_QTY']) * intval($set_data['SALES_PRICE']),
						
						'CREATER'				=>$member_id,
						'UPDATER'				=>$member_id
					)
				);
				
				$product_num++;
			}
		}
	}
	
	return $product_num;
}

/* [임시 주문 상품 정보] 테이블 바우처 정보 등록처리 */
function addTmpOrderProductVoucher($db,$data) {
	$voucher_info = $data['voucher_info'];
	
	$db->insert(
		"TMP_ORDER_PRODUCT",
		array(
			'ORDER_IDX'				=>$data['order_idx'],
			'ORDER_CODE'			=>$data['order_code'],
			'ORDER_PRODUCT_CODE'	=>$data['order_code']."-".$data['product_num'],
			'ORDER_STATUS'			=>'PCP',
			
			'PRODUCT_IDX'			=>$voucher_info['voucher_idx'],
			'PRODUCT_TYPE'			=>'V',
			'PRODUCT_CODE'			=>'VOU-P-XXXXXXXXXX',
			'PRODUCT_NAME'			=>"주문 바우처 할인정보",
			
			'OPTION_IDX'			=>0,
			'BARCODE'				=>'VOU-P-XXXXXXXXXX',
			'OPTION_NAME'			=>$voucher_info['voucher_name'],
			
			'PRODUCT_QTY'			=>1,
			'PRODUCT_PRICE'			=>$data['price_discount'],
			
			'CREATER'				=>$data['member_id'],
			'UPDATER'				=>$data['member_id']
		)
	);
}

/* [임시 주문 상품 정보] 테이블 배송비 정보 등록처리 */
function addTmpOrderProductDelivery($db,$data,$member_id,$price_delivery) {
	$db->insert(
		"TMP_ORDER_PRODUCT",
		array(
			'ORDER_IDX'				=>$data['order_idx'],
			'ORDER_CODE'			=>$data['order_code'],
			'ORDER_PRODUCT_CODE'	=>$data['order_code']."-".$data['product_num'],
			'ORDER_STATUS'			=>'PCP',
			
			'PRODUCT_IDX'			=>0,
			'PRODUCT_TYPE'			=>'D',
			'PRODUCT_CODE'			=>'DLVXXXXXXXXXXXXX',
			'PRODUCT_NAME'			=>"주문 배송비",
			
			'OPTION_IDX'			=>0,
			'BARCODE'				=>'DLV-PC-XXXXXXXXX',
			'OPTION_NAME'			=>"주문 결제 배송비",
			
			'PRODUCT_QTY'			=>1,
			'PRODUCT_PRICE'			=>$price_delivery,
			
			'CREATE_DATE'			=>NOW(),
			'CREATER'				=>$member_id,
			'UPDATE_DATE'			=>NOW(),
			'UPDATER'				=>$member_id
		)
	);
}

?>