<?php
/*
 +=============================================================================
 | 
 | 찜한 상품 리스트 - 상품 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.13
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common/common.php");

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
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	return $json_result;
}

if ($member_idx > 0 && $country != null) {
	$select_whish_sql = "
		SELECT
			WL.IDX						AS WHISH_IDX,
			WL.PRODUCT_IDX				AS PRODUCT_IDX,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			IFNULL(
				PR.SET_TYPE,'BS'
			)							AS SET_TYPE,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = WL.PRODUCT_IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'M'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)							AS PRODUCT_IMG,
			WL.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.PRICE_".$country."		AS PRICE,
			PR.DISCOUNT_".$country."	AS DISCOUNT,
			PR.SALES_PRICE_".$country."	AS SALES_PRICE,
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			WL.OPTION_IDX				AS OPTION_IDX,
			WL.OPTION_NAME				AS OPTION_NAME,
			WL.PRODUCT_QTY				AS PRODUCT_QTY
		FROM
			WHISH_LIST WL
			LEFT JOIN SHOP_PRODUCT PR ON
			WL.PRODUCT_IDX = PR.IDX
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
		WHERE
			WL.COUNTRY = '".$country."' AND
			WL.MEMBER_IDX = ".$member_idx." AND
			WL.DEL_FLG = FALSE
		ORDER BY
			WL.IDX DESC
	";
	
	$db->query($select_whish_sql);
	
	foreach($db->fetch() as $data) {
		$product_color = getProductColor($db,$data['PRODUCT_IDX']);
		$product_size = getProductSize($db,$data['PRODUCT_TYPE'],$data['SET_TYPE'],$data['PRODUCT_IDX']);
		
		$json_result['data'][] = array(
			'wish_idx'			=>$data['WHISH_IDX'],
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'set_type'			=>$data['SET_TYPE'],
			'product_img'		=>$data['PRODUCT_IMG'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'price'				=>number_format($data['PRICE']),
			'discount'			=>$data['DISCOUNT'],
			'sales_price'		=>number_format($data['SALES_PRICE']),
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'option_idx'		=>$data['OPTION_IDX'],
			'option_name'		=>$data['OPTION_NAME'],
			'product_qty'		=>$data['PRODUCT_QTY'],
			'product_color'		=>$product_color,
			
			'product_size'		=>$product_size,
			'set_type'			=>$data['SET_TYPE']
		);
	}
}
?>