<?php
/*
 +=============================================================================
 | 
 | 리오더 정보 변경
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2022.01.10
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common.php");

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

$reorder_idx = 0;
if (isset($_POST['no'])) {
	$reorder_idx = $_POST['no'];
}

if(!isset($country) || $member_idx == 0){
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if ($country != null && $reorder_idx > 0 && isset($action_type)) {
	$reorder_cnt = $db->count("PRODUCT_REORDER","IDX = ".$reorder_idx." AND MEMBER_IDX = ".$member_idx);
	
	if ($reorder_cnt > 0) {
		$bool_del_flg = 0;
		if ($action_type == "cancel") {
			$bool_del_flg = 1;
		} else if($action_type == 're_apply'){
			$bool_del_flg = 0;
		}
		
		$db->update(
			"PRODUCT_REORDER",
			array(
				'DEL_FLG'		=>$bool_del_flg,
				'UPDATE_DATE'	=>NOW(),
				'UPDATER'		=>$member_id
			),
			"
				IDX = ".$reorder_idx." AND
				MEMBER_IDX = ".$member_idx."
			"
		);
	} else {
		$json_result['code'] = 304;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0024', array());
		
		echo json_encode($json_result);
		exit;
	}
}
	
?>