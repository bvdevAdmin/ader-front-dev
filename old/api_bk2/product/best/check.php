<?php
/*
 +=============================================================================
 | 
 | 베스트 리스트 - 상품 상세 페이지 이동 전 판매여부 체크
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.05.25
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/common/check.php");

$product_idx = 0;
if (isset($_POST['product_idx'])) {
	$product_idx = $_POST['product_idx'];
}

if ($product_idx > 0) {
	$check_sale_flg_result  = checkProductSaleFlg($db,"PRD",$product_idx);
	if ($check_sale_flg_result['result'] != true) {
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0007', array());
		
		echo json_encode($json_result);
		exit;
	}
}

?>