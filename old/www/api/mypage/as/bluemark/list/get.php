<?php
/*
 +=============================================================================
 | 
 | A/S신청 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.04.23
 | 최종 수정일	: 
 | 버전		: 1.1
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
	$select_bluemark_product_list_sql = "
		SELECT
			BL.IDX							AS BLUEMARK_IDX,
			BI.SERIAL_CODE					AS SERIAL_CODE,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = PR.IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)								AS IMG_LOCATION,
			PR.IDX							AS PRODUCT_IDX,
			PR.PRODUCT_NAME					AS PRODUCT_NAME,
			PR.COLOR						AS COLOR,
			PR.COLOR_RGB					AS COLOR_RGB,
			BI.OPTION_NAME					AS OPTION_NAME,
			BI.BARCODE						AS BARCODE,
			BL.PURCHASE_MALL				AS PURCHASE_MALL,
			DATE_FORMAT(
				BL.REG_DATE,
				'%Y.%m.%d'
			)								AS REG_DATE,
			PR.PRICE_".$country."			AS PRICE,
			PR.DISCOUNT_".$country."		AS DISCOUNT_PRICE,
			PR.SALES_PRICE_".$country."		AS SALES_PRICE
		FROM
			BLUEMARK_INFO BI
			LEFT JOIN BLUEMARK_LOG BL ON
			BI.IDX = BL.BLUEMARK_IDX
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
		WHERE
			BL.COUNTRY = '".$country."' AND
			BL.MEMBER_IDX = ".$member_idx." AND
			BL.ACTIVE_FLG = TRUE
	";
	
	$db->query($select_bluemark_product_list_sql);
	
	foreach($db->fetch() as $bluemark_data) {
		$serial_code = $bluemark_data['SERIAL_CODE'];
		
		$as_cnt = $db->count("MEMBER_AS","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND SERIAL_CODE = '".$serial_code."' AND COMPLETE_FLG = FALSE");
		
		$as_flg = false;
		if ($as_cnt > 0) {
			$as_flg = true;
		}
		
		$json_result['data'][] = array(
			'bluemark_idx'		=>$bluemark_data['BLUEMARK_IDX'],
			'serial_code'		=>strtoupper($bluemark_data['SERIAL_CODE']),
			'img_location'		=>$bluemark_data['IMG_LOCATION'],
			'product_idx'		=>$bluemark_data['PRODUCT_IDX'],
			'product_name'		=>$bluemark_data['PRODUCT_NAME'],
			'color'				=>$bluemark_data['COLOR'],
			'color_rgb'			=>$bluemark_data['COLOR_RGB'],
			'option_name'		=>$bluemark_data['OPTION_NAME'],
			'barcode'			=>$bluemark_data['BARCODE'],
			'purchase_mall'		=>$bluemark_data['PURCHASE_MALL'],
			'reg_date'			=>$bluemark_data['REG_DATE'],
			'price'				=>number_format($bluemark_data['PRICE']),
			'discount_price'	=>number_format($bluemark_data['DISCOUNT_PRICE']),
			'sales_price'		=>number_format($bluemark_data['SALES_PRICE']),
			'as_flg'			=>$as_flg
		);
	}
}

?>