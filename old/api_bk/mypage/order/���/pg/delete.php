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

include_once("/var/www/www/api/common/common.php");

$order_status = null;
if (isset($_POST['order_status'])) {
	$order_status = $_POST['order_status'];
}

$order_product_idx = 0;
if (isset($_POST['order_product_idx'])) {
	$order_product_idx = $_POST['order_product_idx'];
}

if ($order_product_idx > 0) {
	$order_table = null;
	switch ($order_status) {
		case "OEX" :
			$order_table = "TMP_ORDER_PRODUCT_EXCHANGE";
			break;
		
		case "ORF" :
			$order_table = "TMP_ORDER_PRODUCT_REFUND";
			break;
	}
	
	$delete_tmp_order_table_sql = "
		DELETE FROM
			".$order_table."
		WHERE
			IDX = ".$order_product_idx."
	";
	
	$db->query($delete_tmp_order_table_sql);
}

?>