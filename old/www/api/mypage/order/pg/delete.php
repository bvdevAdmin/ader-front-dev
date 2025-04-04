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

include_once(dir_f_api."/mypage/order/order-common.php");

if (isset($order_status) && isset($order_product_code)) {
	$order_table = getOrderTable($order_status,true);
	
	$delete_tmp_order_product_table_sql = "
		DELETE FROM
			".$order_table['product']."
		WHERE
			ORDER_PRODUCT_CODE = '".$order_product_code."'
	";
	
	$db->query($delete_tmp_order_product_table_sql);
	
	$cnt_OT = $db->count($order_table['product'],"ORDER_CODE = '".$order_code."'");
	if ($cnt_OT == 0) {
		$db->delete(
			$order_table['info'],
			"ORDER_CODE = ?",
			array($order_code)
		);
	}
}

?>