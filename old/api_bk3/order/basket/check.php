<?php
/*
 +=============================================================================
 | 
 | 장바구니 화면 - 상품 정보 삭제
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.14
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/common/check.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($basket_idx) != null && count($basket_idx) == 1) {
	$exclusive_cnt = 0;
	
	for ($i=0; $i<count($basket_idx); $i++) {
		$exclusive_flg = checkProductExclusiveFlg($db,"BSK",$basket_idx[$i]);
		if ($exclusive_flg == true) {
			$exclusive_cnt++;
		}
	}
	
	if ($exclusive_cnt > 0) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0014', array());
	}
}
	
?>