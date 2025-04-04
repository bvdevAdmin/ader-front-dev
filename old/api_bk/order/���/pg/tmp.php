<?php
/*
 +=============================================================================
 | 
 | 결제정보 입력화면 - 임시 주문정보 등록
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

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
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

$basket_idx = null;
if (isset($_POST['basket_idx'])) {
	$basket_idx = explode(",",$_POST['basket_idx']);
}

$to_place = null;
if (isset($_POST['to_place'])) {
	$to_place = $_POST['to_place'];
}

$to_name = null;
if (isset($_POST['to_name'])) {
	$to_name = $_POST['to_name'];
}

$to_mobile = null;
if (isset($_POST['to_mobile'])) {
	$to_mobile = $_POST['to_mobile'];
}

$to_zipcode = null;
if (isset($_POST['to_zipcode'])) {
	$to_zipcode = $_POST['to_zipcode'];
}

$to_lot_addr = null;
if (isset($_POST['to_lot_addr'])) {
	$to_lot_addr = $_POST['to_lot_addr'];
}

$to_road_addr = null;
if (isset($_POST['to_road_addr'])) {
	$to_road_addr = $_POST['to_road_addr'];
}

$to_detail_addr = null;
if (isset($_POST['to_detail_addr'])) {
	$to_detail_addr = $_POST['to_detail_addr'];
}

$to_country_code = null;
if (isset($_POST['to_country_code'])) {
	$to_country_code = $_POST['to_country_code'];
}

$to_province_idx = null;
if (isset($_POST['to_province_idx'])) {
	$to_province_idx = $_POST['to_province_idx'];
}

$to_city = null;
if (isset($_POST['to_city'])) {
	$to_city = $_POST['to_city'];
}

$to_address = null;
if (isset($_POST['to_address'])) {
	$to_address = $_POST['to_address'];
}

$order_memo = null;
if (isset($_POST['order_memo'])) {
	$order_memo = $_POST['order_memo'];
}

$voucher_idx = 0;
if (isset($_POST['voucher_idx'])) {
	$voucher_idx = $_POST['voucher_idx'];
}

$price_mileage_point = 0;
if (isset($_POST['price_mileage_point']) && strlen($_POST['price_mileage_point']) > 0) {
	$price_mileage_point = str_replace(",","",$_POST['price_mileage_point']);
}

$price_charge_point = 0;
if (isset($_POST['price_charge_point'])) {
	$price_charge_point = $_POST['price_charge_point'];
}

if ($member_idx == 0 || $country == null) {
	$json_result['code'] = 401;
	$json_result['msg'] = "로그인 후 다시 시도해 주세요.";
	exit;
}

//쇼핑백 선택 상품 예외처리
if ($basket_idx != null) {
	$basket_cnt = $db->count("BASKET_INFO","IDX IN (".implode(",",$basket_idx).") AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
	if (count($basket_idx) != $basket_cnt) {
		$json_result['code'] = 402;
		$json_result['msg'] = "결제하려는 상품이 존재하지 않습니다. 쇼핑백에서 결제하려는 상품 정보를 확인해주세요.";
		
		return $json_result;
	}
}

//보유 바우처 예외처리
$voucher_info = array();
if ($voucher_idx > 0) {
	$voucher_cnt = $db->count("VOUCHER_ISSUE","IDX = ".$voucher_idx." AND COUNTRY='".$country."' AND MEMBER_IDX = ".$member_idx." AND (NOW() BETWEEN USABLE_START_DATE AND USABLE_END_DATE)");
	
	if ($voucher_cnt == 0) {
		$json_result['code'] = 402;
		$json_result['msg'] = "선택한 바우처 정보가 존재하지 않습니다. 선택하려는 바우처 정보를 확인해주세요.";
		
		return $json_result;
	}
	
	$select_voucher_sql = "
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
			VI.MEMBER_IDX = ".$member_idx."
	";
	
	$db->query($select_voucher_sql);
	
	foreach($db->fetch() as $voucher_data) {
		$voucher_info = array(
			'voucher_idx'		=>$voucher_data['VOUCHER_IDX'],
			'voucher_name'		=>$voucher_data['VOUCHER_NAME'],
			'sale_type'			=>$voucher_data['SALE_TYPE'],
			'sale_price'		=>$voucher_data['SALE_PRICE']
		);
	}
}

if ($price_mileage_point > 0 || $price_charge_point > 0) {
	$select_point_sql = "
		SELECT
			(
				SELECT 
					IFNULL(S_MI.MILEAGE_BALANCE,0)
				FROM 
					MILEAGE_INFO S_MI
				WHERE
					S_MI.COUNTRY = '".$country."' AND
					S_MI.MEMBER_IDX = MB.IDX
				ORDER BY 
					S_MI.IDX DESC 
				LIMIT
					0,1
			)					AS MEMBER_MILEAGE
		FROM
			MEMBER_".$country." MB
		WHERE
			MB.IDX = ".$member_idx."
	";

	$db->query($select_point_sql);

	$member_mileage = 0;
	foreach ($db->fetch() as $point_data) {
		$member_mileage = $point_data['MEMBER_MILEAGE'];
		//$member_charge = $point_data['MEMBER_CHARGE'];
	}

	//보유 적립 포인트 예외처리
	if ($price_mileage_point > $member_mileage) {
		$json_result['code'] = 402;
		$json_result['msg'] = "보유하고 있는 적립 포인트 이상의 금액은 사용할 수 없습니다.";
		
		return $json_result;
	}
	
	/*보유 충전 포인트 예외처리
	if ($price_charge_point > 0 && $member_charge) {
		$json_result['code'] = 402;
		$json_result['msg'] = "보유하고 있는 충전 포인트 이상의 금액은 사용할 수 없습니다.";
		return $json_rsult;
	}*/
}

$select_product_sql = "
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
		BI.IDX IN (".implode(",",$basket_idx).")
	ORDER BY
		BI.IDX DESC
";

$db->query($select_product_sql);

$product_info = array();

$price_total = 0;
$price_product = 0;
$price_delivery = 0;

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

$price_discount = 0;
if (count($voucher_info) > 0) {
	$sale_type = $voucher_info['sale_type'];
	$sale_price = $voucher_info['sale_price'];
	
	if ($sale_type == "PRC") {
		$price_discount = $sale_price;
	} else if ($sale_type = "PER") {
		$price_discount = ($price_product * ($sale_price / 100));
	}
}

$price_total = intval($price_product) - intval($price_charge_point) - intval($price_discount);

if ($country == "KR") {
	if ($price_total > 0 && $price_total < 80000) {
		$price_delivery = 2500;
	}
} else {
	if ($to_country_code != null) {
		$select_dhl_zones_sql = "
			SELECT
				DZ.COST		AS COST,
				(
					SELECT
						CURRENCY
					FROM
						PRODUCT_CURRENCY
					WHERE
						COUNTRY = '".$country."'
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
	} else {
		$json_result['code'] = 303;
		$json_result['msg'] = "해외 배송비 조회처리중 오류가 발생했습니다.";
	}
}

$price_total += ($price_delivery - intval($price_mileage_point));

$order_code = date("Ymd-His").substr(microtime(),2,1);
$order_title = null;

$product_cnt = count($product_info);

if ($product_cnt > 1) {
	$order_title = $product_info[0]['product_name']." 외 ".($product_cnt - 1)."건";
} else {
	$order_title = $product_info[0]['product_name'];
}

try {
	if($country == 'KR'){
		$road_addr_str = $to_road_addr;
		$lot_addr_str = $to_lot_addr;
		$detail_addr_str = $to_detail_addr;		
	} else if($country == 'EN' || $country == 'CN' ){
		$to_country_name = $db->get('COUNTRY_INFO','COUNTRY_CODE = ?',array($to_country_code))[0]['COUNTRY_NAME'];
		$to_province_name = $db->get('PROVINCE_INFO', 'IDX = ?', array($to_province_idx))[0]['PROVINCE_NAME'];
		
		$road_addr_str = $to_city.' '.$to_province_name.' '.$to_country_name;
		$lot_addr_str = $road_addr_str;
		$detail_addr_str = $to_address;
	}

	$insert_tmp_order_info_sql = "
		INSERT INTO
			TMP_ORDER_INFO
		(
			COUNTRY,
			ORDER_CODE,
			ORDER_TITLE,
			ORDER_STATUS,
			ORDER_DATE,
			MEMBER_IDX,
			MEMBER_LEVEL,
			MEMBER_ID,
			MEMBER_NAME,
			MEMBER_MOBILE,
			MEMBER_EMAIL,
			PRICE_PRODUCT,
			PRICE_MILEAGE_POINT,
			PRICE_CHARGE_POINT,
			PRICE_DISCOUNT,
			PRICE_DELIVERY,
			PRICE_TOTAL,
			TO_PLACE,
			TO_NAME,
			TO_MOBILE,
			TO_ZIPCODE,
			TO_LOT_ADDR,
			TO_ROAD_ADDR,
			TO_DETAIL_ADDR,
			ORDER_MEMO,
			BASKET_IDX,
			CREATE_DATE,
			CREATER,
			UPDATE_DATE,
			UPDATER
		) VALUES (
			'".$country."',
			'".$order_code."',
			'".$order_title."',
			'PCP',
			NOW(),
			".$member_idx.",
			".$level_idx.",
			'".$member_id."',
			'".$member_name."',
			'".$tel_mobile."',
			'".$member_email."',
			".$price_product.",
			".$price_mileage_point.",
			".$price_charge_point.",
			".$price_discount.",
			".$price_delivery.",
			".$price_total.",
			'".$to_place."',
			'".$to_name."',
			'".$to_mobile."',
			'".$to_zipcode."',
			'".$lot_addr_str."',
			'".$road_addr_str."',
			'".$detail_addr_str."',
			'".$order_memo."',
			'".implode(",",$basket_idx)."',
			NOW(),
			'".$member_id."',
			NOW(),
			'".$member_id."'
		)
	";
	
	$db->query($insert_tmp_order_info_sql);
	
	$order_idx = $db->last_id();

	if (!empty($order_idx)) {
		$product_num = 1;
		for ($i=0; $i<count($product_info); $i++) {
			$insert_tmp_order_product_sql = "
				INSERT INTO
					TMP_ORDER_PRODUCT
				(
					ORDER_IDX,
					ORDER_CODE,
					ORDER_PRODUCT_CODE,
					ORDER_STATUS,
					
					PRODUCT_IDX,
					PRODUCT_TYPE,
					REORDER_CNT,
					PREORDER_FLG,
					PRODUCT_CODE,
					PRODUCT_NAME,
					
					OPTION_IDX,
					BARCODE,
					OPTION_NAME,
					
					PRODUCT_QTY,
					PRODUCT_PRICE,
					
					CREATE_DATE,
					CREATER,
					UPDATE_DATE,
					UPDATER
				) VALUES (
					".$order_idx.",
					'".$order_code."',
					'".$order_code."_".$product_num."',
					'PCP',
					
					".$product_info[$i]['product_idx'].",
					'".$product_info[$i]['product_type']."',
					".$product_info[$i]['reorder_cnt'].",
					".$product_info[$i]['preorder_flg'].",
					'".$product_info[$i]['product_code']."',
					'".$product_info[$i]['product_name']."',
					
					".$product_info[$i]['option_idx'].",
					'".$product_info[$i]['barcode']."',
					'".$product_info[$i]['option_name']."',
					
					".$product_info[$i]['product_qty'].",
					".intval($product_info[$i]['product_qty']) * intval($product_info[$i]['sales_price']).",
					
					NOW(),
					'".$member_id."',
					NOW(),
					'".$member_id."'
				)
			";
			
			$db->query($insert_tmp_order_product_sql);
			
			if ($product_info[$i]['product_type'] == "S") {
				$parent_idx = $db->last_id();
				
				if (!empty($parent_idx)) {
					$select_set_product_sql = "
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
							PARENT_IDX = ".$product_info[$i]['basket_idx']."
						ORDER BY
							BI.IDX DESC
					";

					$db->query($select_set_product_sql);

					foreach($db->fetch() as $set_data) {
						$insert_set_product_sql = "
							INSERT INTO
								TMP_ORDER_PRODUCT
							(
								ORDER_IDX,
								ORDER_CODE,
								ORDER_PRODUCT_CODE,
								ORDER_STATUS,
								
								PRODUCT_IDX,
								PRODUCT_TYPE,
								PARENT_IDX,
								REORDER_CNT,
								PREORDER_FLG,
								PRODUCT_CODE,
								PRODUCT_NAME,
								
								OPTION_IDX,
								BARCODE,
								OPTION_NAME,
								
								PRODUCT_QTY,
								PRODUCT_PRICE,
								
								CREATE_DATE,
								CREATER,
								UPDATE_DATE,
								UPDATER
							) VALUES (
								".$order_idx.",
								'".$order_code."',
								'".$order_code."_".$product_num."',
								'PCP',
								
								".$set_data['PRODUCT_IDX'].",
								'".$set_data['PRODUCT_TYPE']."',
								".$parent_idx.",
								".$set_data['REORDER_CNT'].",
								".$set_data['PREORDER_FLG'].",
								'".$set_data['PRODUCT_CODE']."',
								'".$set_data['PRODUCT_NAME']."',
								
								".$set_data['OPTION_IDX'].",
								'".$set_data['BARCODE']."',
								'".$set_data['OPTION_NAME']."',
								
								".$set_data['PRODUCT_QTY'].",
								".intval($set_data['PRODUCT_QTY']) * intval($set_data['SALES_PRICE']).",
								
								NOW(),
								'".$member_id."',
								NOW(),
								'".$member_id."'
							)
						";
						
						$db->query($insert_set_product_sql);
					}
				}
			}
			
			$product_num++;
		}
		
		if (count($voucher_info) > 0) {
			$insert_tmp_voucher_sql = "
				INSERT INTO
					TMP_ORDER_PRODUCT
				(
					ORDER_IDX,
					ORDER_CODE,
					ORDER_PRODUCT_CODE,
					ORDER_STATUS,
					
					PRODUCT_IDX,
					PRODUCT_TYPE,
					PRODUCT_CODE,
					PRODUCT_NAME,
					
					PRODUCT_QTY,
					PRODUCT_PRICE,
					
					CREATE_DATE,
					CREATER,
					UPDATE_DATE,
					UPDATER
				) VALUES (
					".$order_idx.",
					'".$order_code."',
					'".$order_code."_".$product_num."',
					'PCP',
					
					".$voucher_info['voucher_idx'].",
					'V',
					'VOUXXXXXXXXXXXX',
					'".$voucher_info['voucher_name']."',
					
					1,
					'".$price_discount."',
					
					NOW(),
					'".$member_id."',
					NOW(),
					'".$member_id."'
				)
			";
			
			$db->query($insert_tmp_voucher_sql);
			
			$product_num++;
		}
		
		if ($price_delivery > 0) {
			$insert_tmp_delivery_sql = "
				INSERT INTO
					TMP_ORDER_PRODUCT
				(
					ORDER_IDX,
					ORDER_CODE,
					ORDER_PRODUCT_CODE,
					ORDER_STATUS,
					
					PRODUCT_IDX,
					PRODUCT_TYPE,
					PRODUCT_CODE,
					PRODUCT_NAME,
					
					PRODUCT_QTY,
					PRODUCT_PRICE,
					
					CREATE_DATE,
					CREATER,
					UPDATE_DATE,
					UPDATER
				) VALUES (
					".$order_idx.",
					'".$order_code."',
					'".$order_code."_".$product_num."',
					'PCP',
					
					0,
					'D',
					'DLVXXXXXXXXXXXX',
					'주문 배송비',
					
					1,
					".$price_delivery.",
					
					NOW(),
					'".$member_id."',
					NOW(),
					'".$member_id."'
				)
			";
			
			$db->query($insert_tmp_delivery_sql);
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
?>