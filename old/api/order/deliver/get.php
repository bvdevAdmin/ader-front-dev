<?php
/*
 +=============================================================================
 | 
 | 영문/중문몰 배송비 취득
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.17
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if ($country != null && $member_idx > 0) {
	$select_delivery_price_sql = "
		SELECT
			DZ.COST			AS PRICE_DELIVERY,
			(
				SELECT
					S_PC.CURRENCY
				FROM
					PRODUCT_CURRENCY S_PC
				WHERE
					S_PC.COUNTRY = '".$country."'
			)				AS CURRENCY
		FROM
			DHL_ZONES DZ
		WHERE
			DZ.ZONE_NUM = (
				SELECT
					ZONE_NUM
				FROM
					MEMBER_".$country." S_MB
					LEFT JOIN COUNTRY_INFO S_CI ON
					S_MB.COUNTRY_CODE = S_CI.COUNTRY_CODE
				WHERE
					S_MB.IDX = ".$member_idx." AND
					S_MB.COUNTRY = '".$country."'
			)
	";
	
	$db->query($select_delivery_price_sql);
	
	foreach($db->fetch() as $price_data) {
		$currency = $price_data['CURRENCY'];
		$price_delivery = intval($price_data['PRICE_DELIVERY']) * $currency;
		
		$json_result['data'] = array(
			'price_delivery'		=>$price_delivery,
			'txt_price_delivery'	=>number_format($price_delivery)
		);
	}
}

?>