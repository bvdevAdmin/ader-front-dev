<?php
/*
 +=============================================================================
 | 
 | 마이페이지_취소/교환/환불 상품 리스트 조회
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

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 301;
	$json_result['msg'] = "부적절한 접근이 감지되었습니다. 로그인 후 다시 시도해주세요.";
}

$order_idx = 0;
if (isset($_POST['order_idx'])) {
	$order_idx = $_POST['order_idx'];
}

$order_cnt = $db->count("ORDER_INFO","IDX = ".$order_idx." AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
if ($order_cnt > 0) {
	$select_order_product_sql = "
		SELECT
			OP.IDX					AS ORDER_PRODUCT_IDX,
			OP.ORDER_IDX			AS ORDER_IDX,
			OP.ORDER_CODE			AS ORDER_CODE,
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
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
				LIMIT 0,1
			)						AS IMG_LOCATION,
			OP.PRODUCT_NAME			AS PRODUCT_NAME,
			OM.COLOR				AS COLOR,
			PR.COLOR_RGB			AS COLOR_RGB,
			OP.OPTION_NAME			AS OPTION_NAME,
			OP.OPTION_IDX			AS OPTION_IDX,
			OP.PRODUCT_QTY			AS PRODUCT_QTY,
			OP.PRODUCT_PRICE		AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT OP
			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
		WHERE
			OP.ORDER_IDX = ".$order_idx." AND
			OP.PRODUCT_CODE NOT LIKE 'VOUXXX%'
	";
	
	$db->query($select_order_product_sql);
	
	foreach($db->fetch() as $product_data) {
		$product_qty = $product_data['product_qty'];
		
		for ($i=0; $i<$product_qty; $i++) {
			$json_result['data'][] = array(
				'order_product_idx'		=>$product_data['ORDER_PRODUCT_IDX'],
				'order_idx'				=>$product_data['ORDER_IDX'],
				'order_code'			=>$product_data['ORDER_CODE'],
				'order_product_code'	=>$product_data['ORDER_PRODUCT_CODE'],
				'img_location'			=>$product_data['IMG_LOCATION'],
				'product_name'			=>$product_data['PRODUCT_NAME'],
				'color'					=>$product_data['COLOR'],
				'color_rgb'				=>$product_data['COLOR_RGB'],
				'option_idx'			=>$product_data['OPTION_IDX'],
				'option_name'			=>$product_data['OPTION_NAME'],
				'product_qty'			=>1,
				'product_price'			=>number_format($product_data['PRODUCT_PRICE'] / $product_data['PRODUCT_QTY'])
			);
		}
	}
} else {
	$json_result['code'] = 302;
	$json_result['msg'] = "선택한 주문정보가 존재하지 않습니다. 상세내역을 조회하려는 주문정보를 다시 선택해주세요.";
}

?>