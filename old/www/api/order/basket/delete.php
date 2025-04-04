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

include_once(dir_f_api."/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if ($member_idx > 0 && $basket_idx != null) {
	$db->update(
		"BASKET_INFO",
		array(
			'DEL_FLG'		=>1,
			'UPDATE_DATE'	=>NOW(),
			'UPDATER'		=>$member_id,
		),
		"
			IDX IN (".implode(",",$basket_idx).") AND
			MEMBER_IDX = ".$member_idx."
		"
	);
	
	$json_result['data'] = array(
		'basket_cnt'		=>getBasketCnt($db,$country,$member_idx)
	);
}
?>