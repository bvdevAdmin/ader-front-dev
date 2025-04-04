<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문취소,주문교환/반품 - [주문취소][주문교환][주문반품] 대상 [주문 상품 테이블] 개별 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.04.10
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common/common.php");
include_once("/var/www/www/api/mypage/order/order-common.php");
include_once("/var/www/www/api/mypage/order/order-pg.php");

$order_product_code = 0;
if (isset($_POST['order_product_code'])) {
	$order_product_code = $_POST['order_product_code'];
}

if ($order_product_code > 0) {
	$select_order_product_sql = "
		SELECT
			OP.IDX					AS PARAM_IDX,
			OP.ORDER_IDX			AS ORDER_IDX,
			OP.ORDER_CODE			AS ORDER_CODE,
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			
			OP.PRODUCT_IDX			AS PRODUCT_IDX,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = OP.PRODUCT_IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)						AS IMG_LOCATION,
			PR.product_type			AS PRODUCT_TYPE,
			PR.set_type				AS SET_TYPE,
			OP.PRODUCT_NAME			AS PRODUCT_NAME,
			PR.COLOR				AS COLOR,
			PR.COLOR_RGB			AS COLOR_RGB,
			
			OP.OPTION_IDX			AS OPTION_IDX,
			OP.OPTION_NAME			AS OPTION_NAME,
			
			OP.PRODUCT_QTY			AS PRODUCT_QTY,
			OP.PRODUCT_PRICE		AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT OP
			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
		WHERE
			OP.ORDER_PRODUCT_CODE = '".$order_product_code."'
	";
	
	$db->query($select_order_product_sql);
	
	foreach($db->fetch() as $product_data) {
		$product_idx = $product_data['PRODUCT_IDX'];
		$product_type = $product_data['PRODUCT_TYPE'];
		$set_type = $product_data['SET_TYPE'];
		
		$size_info = array();
		if (!empty($product_idx)) {
			$size_info = getProductSize($db,$product_type,$set_type,$product_idx);
		}
		
		$json_result['data'] = array(
			'param_idx'				=>$product_data['PARAM_IDX'],
			'order_code'			=>$product_data['ORDER_CODE'],
			'order_product_code'	=>$product_data['ORDER_PRODUCT_CODE'],
			
			'img_location'			=>$product_data['IMG_LOCATION'],
			'product_name'			=>$product_data['PRODUCT_NAME'],
			'color'					=>$product_data['COLOR'],
			'color_rgb'				=>$product_data['COLOR_RGB'],
			'option_idx'			=>$product_data['OPTION_IDX'],
			'option_name'			=>$product_data['OPTION_NAME'],
			
			'product_qty'			=>1,
			'product_price'			=>number_format($product_data['PRODUCT_PRICE'] / $product_data['PRODUCT_QTY']),
			
			'size_info'				=>$size_info
		);
	}
}

?>