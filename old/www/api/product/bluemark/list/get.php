<?php
/*
 +=============================================================================
 | 
 | 마이페이지 블루마크 - 블루마크 내역 조회
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.09
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($country) && $member_idx > 0) {
	$table = "
		BLUEMARK_INFO BI
		LEFT JOIN BLUEMARK_LOG BL ON
		BI.IDX = BL.BLUEMARK_IDX
		LEFT JOIN SHOP_PRODUCT PR ON
		PR.IDX = BI.PRODUCT_IDX
	";
	
	$where = "
		BL.COUNTRY = ? AND
		BL.MEMBER_IDX = ? AND
		BI.DEL_FLG = FALSE AND
		BL.ACTIVE_FLG = TRUE
	";
	$json_result = array(
		'total'	=>$db->count($table,$where,array($country,$member_idx)),
		'page'	=>$page
	);

	$limit_start = (intval($page)-1)*$rows;
	
	$select_bluemark_sql = "
		SELECT 
			BI.IDX						AS BLUEMARK_IDX,
			BI.SERIAL_CODE    			AS SERIAL_CODE,
			IFNULL((
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE 
					S_PI.PRODUCT_IDX = PR.IDX AND 
					IMG_TYPE = 'P' AND 
					IMG_SIZE = 'S'
				LIMIT 
					0,1
			),'/product_img/default_product_img.jpg')							
										AS IMG_LOCATION,
			PR.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.SALES_PRICE_".$country."	AS SALES_PRICE,
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			BI.OPTION_NAME				AS OPTION_NAME,
			DATE_FORMAT(
				BL.REG_DATE,
				'%Y.%m.%d'
			)							AS REG_DATE,
			
			BL.PURCHASE_MALL			AS PURCHASE_MALL,
			DATE_FORMAT(
				BL.PURCHASE_DATE,
				'%Y.%m.%d'
			)				AS PURCHASE_DATE
		FROM
			".$table."
		WHERE
			".$where."
		ORDER BY
			BL.IDX DESC
		LIMIT
			".$limit_start.",".$rows."
	";
	
	$db->query($select_bluemark_sql,array($country,$member_idx));

	foreach($db->fetch() as $data) {
		$json_result['data'][] = array(
			'bluemark_idx'		=>$data['BLUEMARK_IDX'],
			'serial_code'   	=>strtoupper($data['SERIAL_CODE']),
			'img_location'  	=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'sales_price'		=>number_format($data['SALES_PRICE']),
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'option_name'		=>$data['OPTION_NAME'],
			'reg_date'			=>$data['REG_DATE'],
			
			'purchase_mall'		=>$data['PURCHASE_MALL'],
			'purchase_date'		=>$data['PURCHASE_DATE']
		);
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>