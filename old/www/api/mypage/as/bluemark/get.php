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

if (isset($country) && $member_idx > 0 && isset($bluemark_idx)) {
	$select_bluemark_product_sql = "
		SELECT
			BI.SERIAL_CODE					AS SERIAL_CODE,
			PR.IDX							AS PRODUCT_IDX,
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
			PR.PRODUCT_NAME					AS PRODUCT_NAME,
			PR.COLOR						AS COLOR,
			PR.COLOR_RGB					AS COLOR_RGB,
			BI.OPTION_IDX					AS OPTION_IDX,
			BI.OPTION_NAME					AS OPTION_NAME,
			BI.BARCODE						AS BARCODE,
			DATE_FORMAT(
				BL.REG_DATE,
				'%Y.%m.%d'
			)								AS REG_DATE,
			PR.PRICE_".$country."			AS PRICE,
			PR.DISCOUNT_".$country."		AS DISCOUNT_PRICE,
			PR.SALES_PRICE_".$country."		AS SALES_PRICE
		FROM
			BLUEMARK_LOG BL
			LEFT JOIN BLUEMARK_INFO BI ON
			BL.BLUEMARK_IDX = BI.IDX
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
		WHERE
			BL.IDX = ".$bluemark_idx." AND
			BL.COUNTRY = '".$country."' AND
			BL.MEMBER_IDX = ".$member_idx."
	";
	
	$db->query($select_bluemark_product_sql);
	
	foreach($db->fetch() as $bluemark_data) {
		$json_result['data'] = array(
			'serial_code'		=>strtoupper($bluemark_data['SERIAL_CODE']),
			'product_idx'		=>$bluemark_data['PRODUCT_IDX'],
			'img_location'		=>$bluemark_data['IMG_LOCATION'],
			'product_name'		=>$bluemark_data['PRODUCT_NAME'],
			'color'				=>$bluemark_data['COLOR'],
			'color_rgb'			=>$bluemark_data['COLOR_RGB'],
			'option_idx'		=>$bluemark_data['OPTION_IDX'],
			'option_name'		=>$bluemark_data['OPTION_NAME'],
			'barcode'			=>$bluemark_data['BARCODE'],
			'reg_date'			=>$bluemark_data['REG_DATE'],
			'price'				=>number_format($bluemark_data['PRICE']),
			'discount_price'	=>number_format($bluemark_data['DISCOUNT_PRICE']),
			'sales_price'		=>number_format($bluemark_data['SALES_PRICE'])
		);
	}
}

?>