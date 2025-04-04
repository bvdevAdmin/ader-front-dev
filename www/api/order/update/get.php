<?php
/*
 +=============================================================================
 | 
 | 주문 교환/반품 접수 화면
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2024.12.05
 | 최종 수정	: 
 | 최종 수정일	: 
 | 버전		: 
 | 설명		: 
 | 
 +=============================================================================
*/	

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if ($op_idx != null && $param_status != null) {
		$select_order_product_sql = "
			SELECT
				OP.IDX						AS OP_IDX,
				OP.ORDER_PRODUCT_CODE		AS ORDER_PRODCT_CODE,
				OP.PRODUCT_IDX				AS PRODUCT_IDX,
				PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
				OP.PRODUCT_NAME				AS PRODUCT_NAME,
				PR.COLOR					AS COLOR,
				PR.COLOR_RGB				AS COLOR_RGB,
				J_PI.IMG_LOCATION			AS IMG_LOCATION,
				OP.OPTION_IDX				AS OPTION_IDX,
				OP.OPTION_NAME				AS OPTION_NAME,
				OP.REMAIN_QTY				AS REMAIN_QTY,
				OP.REMAIN_PRICE				AS REMAIN_PRICE 
			FROM
				ORDER_PRODUCT OP
				
				LEFT JOIN SHOP_PRODUCT PR ON
				OP.PRODUCT_IDX = PR.IDX
				
				LEFT JOIN (
					SELECT
						S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
						S_PI.IMG_LOCATION	AS IMG_LOCATION
					FROM
						PRODUCT_IMG S_PI
					WHERE
						S_PI.IMG_TYPE = 'P' AND
						S_PI.IMG_SIZE = 'S' AND
						S_PI.DEL_FLG = FALSE
					GROUP BY
						S_PI.PRODUCT_IDX
				) AS J_PI ON
				OP.PRODUCT_IDX = J_PI.PRODUCT_IDX
			WHERE
				OP.IDX = ? AND
				OP.ORDER_STATUS = 'DCP' AND
				OP.PRODUCT_TYPE NOT REGEXP 'V|D' AND
				OP.PARENT_IDX = 0 AND
				OP.REMAIN_QTY > 0
		";
		
		$db->query($select_order_product_sql,array($op_idx));
		
		$param_idx = array();
		
		$order_product = array();
		
		foreach($db->fetch() as $data) {
			$product_price = $data['REMAIN_PRICE'] / $data['REMAIN_QTY'];
			$t_product_price = number_format($product_price);
			if ($_SERVER['HTTP_COUNTRY'] != "KR") {
				$product_price = number_format($product_price,1);
			}
			
			$exchange_size = array();
			if ($param_status == "OEX") {
				$exchange_size = getProduct_size($db,$data['PRODUCT_TYPE'],$data['PRODUCT_IDX']);
			}
			
			$order_product = array(
				'op_idx'				=>$data['OP_IDX'],
				'order_product_code'	=>$data['ORDER_PRODCT_CODE'],
				'product_type'			=>$data['PRODUCT_TYPE'],
				'product_name'			=>$data['PRODUCT_NAME'],
				'color'					=>$data['COLOR'],
				'color_rgb'				=>$data['COLOR_RGB'],
				'img_location'			=>$data['IMG_LOCATION'],
				'option_idx'			=>$data['OPTION_IDX'],
				'option_name'			=>$data['OPTION_NAME'],
				
				'product_qty'			=>1,
				
				'product_price'			=>$product_price,
				't_product_price'		=>$t_product_price,
				
				'exchange_size'			=>$exchange_size
			);
		}
		
		$json_result['data'] = $order_product;
	}
} else {
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());

    echo json_encode($json_result);
    exit;
}

function getProduct_size ($db,$product_type,$product_idx) {
	$exchange_size = null;
	
	if ($product_type != "S") {
		$select_shop_option_sql = "
			SELECT
				OO.IDX				AS OPTION_IDX,
				OO.OPTION_NAME		AS OPTION_NAME
			FROM
				SHOP_OPTION OO
			WHERE
				OO.PRODUCT_IDX = ?
			ORDER BY
				OO.IDX ASC
		";
		
		$db->query($select_shop_option_sql,array($product_idx));
		
		foreach($db->fetch() as $data) {
			$exchange_size[] = array(
				'option_idx'		=>$data['OPTION_IDX'],	
				'option_name'		=>$data['OPTION_NAME']
			);
		}
	} else {
		$exchange_size = "Set";
	}
	
	return $exchange_size;
}

?>