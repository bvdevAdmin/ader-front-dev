<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 배송지 삭제
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.11
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$order_to_idx = null;
if (isset($_POST['order_to_idx'])) {
	$order_to_idx = $_POST['order_to_idx'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	exit;
}

if ($member_idx > 0 && $order_to_idx > 0) {
	$delete_order_to_sql = "
		DELETE FROM
			ORDER_TO
		WHERE
			IDX = ".$order_to_idx." AND
			COUNTRY = '".$country."' AND
			MEMBER_IDX = ".$member_idx."
	";

	$db->query($delete_order_to_sql);
}
else{
	$json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0023', array());
	exit;
}
?>