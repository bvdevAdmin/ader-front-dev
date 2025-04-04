<?php
/*
 +=============================================================================
 | 
 | [액션]컬러칩 클릭 - 해당 색상 상품의 상품정보 재취득
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.18
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$product_idx	= $_POST['product_idx'];
$country		= $_POST['country'];

if ($product_idx != null && $country != null) {	
	$sql = "SELECT
				PR.IDX						AS PRODUCT_IDX,
				OM.COLOR					AS COLOR,
				PR.PRICE_".$country."		AS PRICE,
				PR.DISCOUNT_".$country."	AS DISCOUNT,
				PR.SALES_PRICE_".$country."	AS SALES_PRICE
			FROM
				SHOP_PRODUCT PR
				LEFT JOIN ORDERSHEET_MST OM ON
				PR.ORDERSHEET_IDX = OM.IDX
			WHERE
				PR.IDX = ".$product_idx;
	
	$db->query($sql);
	
	foreach($db->fetch() as $data) {
		$json_result['data'][] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'color'				=>$data['COLOR'],
			'price'				=>$data['PRICE'],
			'discount'			=>$data['DISCOUNT'],
			'sales_price'		=>$data['SALES_PRICE']
		);
	}
}
?>