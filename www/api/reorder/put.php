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
include_once("/var/www/www/api/common/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}
else{
	if(isset($_POST['country'])){
        $country = $_POST['country'];
	}
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

$action_type = null;
if (isset($_POST['action_type'])) {
	$action_type = $_POST['action_type'];
}

if($member_idx == 0){
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	return $json_result;
}

if ($country != null && $reorder_idx > 0 && $action_type != null) {
	$reorder_cnt = $db->count("PRODUCT_REORDER","IDX = ".$reorder_idx." AND MEMBER_IDX = ".$member_idx);
	
	if ($reorder_cnt > 0) {
		$set = "";
		if ($action_type == "cancel") {
			$set .= ' DEL_FLG = TRUE, '; 
		} else if($action_type == 're_apply'){
			$set .= ' DEL_FLG = FALSE, ';
		}
		
		$update_reorder_sql = "
			UPDATE 
				PRODUCT_REORDER
			SET
				".$set."
				UPDATE_DATE = NOW(),
				UPDATER = '".$member_id."'
			WHERE
				IDX = ".$reorder_idx." AND
				MEMBER_IDX = ".$member_idx."
		";

		$db->query($update_reorder_sql);
	} else {
		$json_result['code'] = 304;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0024', array());
	}
}
	
?>