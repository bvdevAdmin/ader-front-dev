<?php
/*
 +=============================================================================
 | 
 | 회원별 마일리지 정보 취득 API
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.02
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/common/check.php");

function checkBasketMileageFlg($db,$country,$member_idx,$basket_idx) {
	$check_result = false;
	
	$mileage_cnt = 0;
	for ($i=0; $i<count($basket_idx); $i++) {
		$mileage_flg = checkProductMileageFlg($db,"BSK",$basket_idx[$i]);
		if ($mileage_flg == true) {
			$mileage_cnt++;
		}
	}
	
	if (!$mileage_cnt > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

function getDeliveryPrice($db,$country,$to_country_code) {
	$delivery_price = 0;
	
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
		$delivery_price = intval($zone_data['COST']) * floatval($zone_data['CURRENCY']);
	}
	
	return $delivery_price;
}

function getTotalBasketPrice($db,$country,$member_idx,$basket_idx,$to_country_code) {
	$total_basket_price = 0;
	
	$select_basket_info_sql = "
		SELECT
			BI.PRODUCT_QTY				AS PRODUCT_QTY,
			PR.SALES_PRICE_".$country."	AS SALES_PRICE
		FROM
			BASKET_INFO BI
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
		WHERE
			PR.DEL_FLG = FALSE AND
			BI.COUNTRY = '".$country."' AND
			BI.MEMBER_IDX = ".$member_idx." AND
			BI.IDX IN (".implode(",",$basket_idx).")
	";
	
	$db->query($select_basket_info_sql);
	
	foreach($db->fetch() as $basket_data) {
		$total_basket_price += (intval($basket_data['PRODUCT_QTY']) * intval($basket_data['SALES_PRICE']));
	}
	
	if ($country == "KR") {
		if ($total_basket_price < 80000) {
			$total_basket_price += 2500;
		}
	} else {
		if ($to_country_code != null) {
			$delivery_price = getDeliveryPrice($db,$country,$to_country_code);
			$total_basket_price += $delivery_price;
		} else {
			$json_result['code'] = 303;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0097', array());
		}
	}
	
	return $total_basket_price;
}

function getTotalMileagePrice($db,$country,$member_idx) {
	$total_mileage_price = 0;
	
	$select_total_mileage_sql = "
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
	";

	$db->query($select_total_mileage_sql);

	foreach ($db->fetch() as $data) {
		$total_mileage_price = intval($data['MILEAGE_BALANCE']);
	}
	
	return $total_mileage_price;
}

?>