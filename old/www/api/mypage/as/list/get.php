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

include_once(dir_f_api."/mypage/as/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($country) && $member_idx > 0) {
	$where = "";
	if (isset($as_status)) {
		if ($as_status == "ACP") {
			$where .= " MA.AS_STATUS = '".$as_status."' ";
		} else {
			$where .= " MA.AS_STATUS != 'ACP' ";
		}
	}
	
	$select_bluemark_product_list_sql = "
		SELECT
			MA.IDX							AS AS_IDX,
			MA.AS_CODE						AS AS_CODE,
			MA.AS_STATUS					AS AS_STATUS,
			MA.SERIAL_CODE					AS SERIAL_CODE,
			MA.BLUEMARK_FLG					AS BLUEMARK_FLG,
			AC.TXT_CATEGORY					AS TXT_CATEGORY,
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
			MA.OPTION_IDX					AS OPTION_IDX,
			MA.OPTION_NAME					AS OPTION_NAME,
			IFNULL(
				MA.BARCODE,'-'
			)								AS BARCODE,
			PR.PRICE_".$country."			AS PRICE,
			PR.DISCOUNT_".$country."		AS DISCOUNT_PRICE,
			PR.SALES_PRICE_".$country."		AS SALES_PRICE,
			DATE_FORMAT(
				MA.CREATE_DATE,
				'%Y.%m.%d'
			)								AS CREATE_DATE,
			IF(MA.AS_STATUS = 'ACP', DATE_FORMAT(
				MA.UPDATE_DATE,
				'%Y.%m.%d'
			),NULL)							AS AS_COMPLETE_DATE
		FROM
			MEMBER_AS MA
			LEFT JOIN SHOP_PRODUCT PR ON
			MA.PRODUCT_IDX = PR.IDX
			LEFT JOIN AS_CATEGORY AC ON
			MA.AS_CATEGORY_IDX = AC.IDX
		WHERE
			MA.COUNTRY = '".$country."' AND
			MA.MEMBER_IDX = ".$member_idx." AND
			".$where."
		ORDER BY
			MA.IDX DESC
	";
	
	$db->query($select_bluemark_product_list_sql);
	
	foreach($db->fetch() as $as_data) {
		$serial_code = $as_data['SERIAL_CODE'];
		$bluemark_flg = $as_data['BLUEMARK_FLG'];
		
		$bluemark_info = array();
		if (!empty($serial_code) && $bluemark_flg == true) {
			$bluemark_info = getBluemarkInfo($db,$serial_code,$member_idx);
		}
		
		$reg_date = null;
		$purchase_mall = null;
		
		if ($bluemark_info != null && $bluemark_info > 0) {
			$serial_code = $bluemark_info['serial_code'];
			$purchase_mall = $bluemark_info['purchase_mall'];
			$reg_date = $bluemark_info['reg_date'];
		}
		
		$json_result['data'][] = array(
			'as_idx'			=>$as_data['AS_IDX'],
			'as_code'			=>$as_data['AS_CODE'],
			'as_status'			=>setTxtParam($as_data['AS_STATUS'], $country),
			'bluemark_flg'		=>$as_data['BLUEMARK_FLG'],
			'txt_category'		=>$as_data['TXT_CATEGORY'],
			'img_location'		=>$as_data['IMG_LOCATION'],
			'product_name'		=>$as_data['PRODUCT_NAME'],
			'color'				=>$as_data['COLOR'],
			'color_rgb'			=>$as_data['COLOR_RGB'],
			'option_idx'		=>$as_data['OPTION_IDX'],
			'option_name'		=>$as_data['OPTION_NAME'],
			'barcode'			=>$as_data['BARCODE'],
			'price'				=>number_format($as_data['PRICE']),
			'discount_price'	=>number_format($as_data['DISCOUNT_PRICE']),
			'sales_price'		=>number_format($as_data['SALES_PRICE']),
			'create_date'  		=>$as_data['CREATE_DATE'],
			'as_complete_date'  =>$as_data['AS_COMPLETE_DATE'],
			
			'serial_code'		=>$serial_code,
			'reg_date'			=>$reg_date,
			'purchase_mall'		=>$purchase_mall
		);
	}
}

?>