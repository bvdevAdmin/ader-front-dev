<?php
/*
 +=============================================================================
 | 
 | 공통 - 관련 상품 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.25
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common.php");

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$relevant_idx = null;
if (is_array($_POST['relevant_idx'])) {
	$relevant_idx = implode(",",$_POST['relevant_idx']);
} else {
	$relevant_idx = $_POST['relevant_idx'];
}

$country		= $_POST['country'];

if ($relevant_idx != null && $country != null) {
	$sql = "SELECT
				PR.IDX						AS PRODUCT_IDX,
				PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
				PR.SET_TYPE					AS SET_TYPE,
				(
					SELECT
						S_PI.IMG_LOCATION
					FROM
						PRODUCT_IMG S_PI
					WHERE
						S_PI.PRODUCT_IDX = PR.IDX AND
						S_PI.IMG_TYPE = 'P' AND
						S_PI.IMG_SIZE = 'M'
					ORDER BY
						S_PI.IDX ASC
					LIMIT
						0,1
				)							AS PRODUCT_IMG,
				PR.PRODUCT_NAME				AS PRODUCT_NAME,
				OM.COLOR					AS COLOR,
				PR.PRICE_".$country."		AS PRICE,
				PR.DISCOUNT_".$country."	AS DISCOUNT,
				PR.SALES_PRICE_".$country."	AS SALES_PRICE,
				OM.COLOR					AS COLOR
			FROM
				SHOP_PRODUCT PR
				LEFT JOIN ORDERSHEET_MST OM ON
				PR.ORDERSHEET_IDX = OM.IDX
			WHERE
				PR.IDX IN (".$relevant_idx.")";
	
	$db->query($sql);
	
	foreach($db->fetch() as $data) {		
		$product_idx = $data['PRODUCT_IDX'];
		
		if ($product_idx != null) {
			$whish_flg = false;
			
			if ($member_idx > 0) {
				$whish_cnt = $db->count("WHISH_LIST"," MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx." AND DEL_FLG = FALSE");
				
				if ($whish_cnt > 0) {
					$whish_flg = true;
				}
			}
			
			$product_color = getProductColor($db,$product_idx);
			
			$product_size = getProductSize($db,$data['PRODUCT_TYPE'],$data['SET_TYPE'],$product_idx);
			
			$json_result['data'][] = array(
				'product_idx'		=>$product_idx,
				'product_type'		=>$data['PRODUCT_TYPE'],
				'product_img'		=>$data['PRODUCT_IMG'],
				'product_name'		=>$data['PRODUCT_NAME'],
				'color'				=>$data['COLOR'],
				'price'				=>number_format($data['PRICE']),
				'discount'			=>$data['DISCOUNT'],
				'sales_price'		=>number_format($data['SALES_PRICE']),
				'product_color'		=>$product_color,
				'product_size'		=>$product_size,
				'whish_flg'			=>$whish_flg
			);
		}
	}
}
?>