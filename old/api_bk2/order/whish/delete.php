<?php
/*
 +=============================================================================
 | 
 | 위시 리스트 - 위시 리스트 상품 정보 삭제
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.13
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");
$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$product_idx = 0;
if (isset($_POST['product_idx'])) {
	$product_idx = $_POST['product_idx'];
}

$whish_idx = 0;
if (isset($_POST['whish_idx'])) {
	$whish_idx = $_POST['whish_idx'];
}

if(isset($_POST['country'])){
	$country = $_POST['country'];
}

if ($member_idx == 0 || $member_id == null) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if ($member_idx > 0 && ($product_idx > 0 || $whish_idx > 0)) {
	$whish_cnt = 0;
	$where_sql = "";
	if ($product_idx > 0) {
		$whish_cnt = $db->count("WHISH_LIST","PRODUCT_IDX = ".$product_idx." AND MEMBER_IDX = ".$member_idx." AND DEL_FLG = FALSE ");
		$where_sql = " PRODUCT_IDX = ".$product_idx." ";
	} else if ($whish_idx > 0) {
		$whish_cnt = $db->count("WHISH_LIST","IDX = ".$whish_idx." AND MEMBER_IDX = ".$member_idx." AND DEL_FLG = FALSE ");
		$where_sql = " IDX = ".$whish_idx." ";
		$product_idx = $db->get('WHISH_LIST', "IDX = ? AND MEMBER_IDX = ? AND DEL_FLG = FALSE ", array($whish_idx,$member_idx))[0]['PRODUCT_IDX'];
	}
	
	if ($whish_cnt == 0) {
		$json_result['code'] = 401;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0079', array());
		
		echo json_encode($json_result);
		exit;
	}

	$delete_whish_sql = "
		UPDATE
			WHISH_LIST
		SET
			DEL_FLG = TRUE,
			UPDATE_DATE = NOW(),
			UPDATER = '".$member_id."'
		WHERE
			".$where_sql." AND
			MEMBER_IDX = ".$member_idx."
	";

	$db->query($delete_whish_sql);
	
	$whish_cnt = $db->count("WHISH_LIST","MEMBER_IDX = ".$member_idx." AND DEL_FLG = FALSE");
	
	$json_result['data'] = $whish_cnt;
	$json_result['result_prod_idx'] = $product_idx;
}
?>