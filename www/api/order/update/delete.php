<?php
/*
 +=============================================================================
 | 
 | 주문 교환/반품 접수 상품 삭제
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
	if ($order_code != null) {
		initOrder_update($db,"OEX",$order_code);
		initOrder_update($db,"ORF",$order_code);
	}
} else {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());

    echo json_encode($json_result);
    exit;
}

function initOrder_update($db,$param_status,$order_code) {
	$table_I = array(
		'OEX'		=>"ORDER_EXCHANGE",
		'ORF'		=>"ORDER_REFUND",
	);
	
	$table_P = array(
		'OEX'		=>"ORDER_PRODUCT_EXCHANGE",
		'ORF'		=>"ORDER_PRODUCT_REFUND",
	);
	
	$status_D = array(
		'OEX'		=>"OET",
		'ORF'		=>"ORT"
	);
	
	$select_order_update_sql = "
		SELECT
			OP.IDX					AS OP_IDX,
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			OP.PRODUCT_QTY			AS PRODUCT_QTY
		FROM
			".$table_P[$param_status]." OP
		WHERE
			OP.ORDER_CODE = ? AND
			OP.ORDER_STATUS LIKE '%T' AND
			OP.PRODUCT_TYPE NOT REGEXP 'V|D'
	";
	
	$db->query($select_order_update_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		$op_idx				= $data['OP_IDX'];
		$order_product_code = $data['ORDER_PRODUCT_CODE'];
		$product_qty		= $data['PRODUCT_QTY'];
		
		$init_product_B_sql = "
			UPDATE
				ORDER_PRODUCT
			SET
				REMAIN_PRICE	= (PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY + ?),
				REMAIN_QTY		= REMAIN_QTY + ?
			WHERE
				ORDER_PRODUCT_CODE = ?
		";
		
		$db->query($init_product_B_sql,array($product_qty,$product_qty,$order_product_code));
		
		$cnt_set = $db->count($table_P[$param_status],"PARENT_IDX = ?",array($op_idx));
		if ($cnt_set > 0) {
			$init_product_S_sql = "
				UPDATE
					ORDER_PRODUCT
				SET
					REMAIN_PRICE	= (PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY + ?),
					REMAIN_QTY		= REMAIN_QTY + ?
				WHERE
					PARENT_IDX = ?
			";
			
			$db->query($init_product_B_sql,array($product_qty,$product_qty,$op_idx));
		}
	}
	
	$db->delete("ORDER_PRODUCT","ORDER_CODE = ? AND PRODUCT_TYPE = 'D' AND ORDER_STATUS = 'PWT'",array($order_code));
	$db->delete($table_I[$param_status],"ORDER_CODE = ? AND ORDER_STATUS = ?",array($order_code,$status_D[$param_status]));
	$db->delete($table_P[$param_status],"ORDER_CODE = ? AND ORDER_STATUS = ?",array($order_code,$status_D[$param_status]));
}

?>