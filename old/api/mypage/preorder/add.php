<?php
/*
 +=============================================================================
 | 
 | 마이페이지_프리오더 - 프리오더 응모
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.01.15
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
} else {
	if(isset($_POST['country'])){
        $country = $_POST['country'];
	}
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_name = null;
if (isset($_SESSION['MEMBER_NAME'])) {
	$member_name = $_SESSION['MEMBER_NAME'];	
}

if (!isset($country) || $member_idx == 0 || !isset($member_name)) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if (isset($country) && $member_idx > 0 && isset($preorder_idx) && isset($option_idx)) {
	$preorder_cnt = $db->count("PAGE_PREORDER","IDX = ".$preorder_idx." AND COUNTRY = '".$country."' AND DISPLAY_STATUS = TRUE AND ENTRY_START_DATE >= NOW() AND ENTRY_END_DATE < NOW()");
	if ($preorder_cnt == 0) {
		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0096', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	$entry_cnt = $db->count("ENTRY_PREORDER","COUNTRY = '".$country."' AND PREORDER_IDX = ".$preorder_idx." AND MEMBER_IDX = ".$member_idx);
	if ($entry_cnt > 0) {
		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0017', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	$insert_entry_sql = "
		INSERT INTO
			ENTRY_PREORDER
		(
			COUNTRY,
			PREORDER_IDX,
			MEMBER_IDX,
			MEMBER_NAME,
			PRODUCT_IDX,
			OPTION_IDX,
			CREATER,
			UPDATER
		)
		SELECT
			'".$country."'		AS COUNTRY,
			".$preorder_idx."	AS PREORDER_IDX,
			".$member_idx."		AS MEMBER_IDX,
			'".$member_NAME."	AS MEMBER_NAME,
			PP.PRODUCT_IDX		AS PRODUCT_IDX,
			".$option_idx."		AS OPTION_IDX,
			OO.OPTION_NAME		AS OPTION_NAME,
			OO.BARCODE			AS OO.BARCODE,
			'".$member_id."'	AS CREATER,
			'".$member_id."'	AS UPDATER
		FROM
			PAGE_PREORDER PP
			LEFT JOIN SHOP_PRODUCT PR ON
			PP.PRODUCT_IDX = PR.IDX
			LEFT JOIN ORDERSHEET_OPTION OO ON
			PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
		WHERE
			PP.IDX = ".$preorder_idx." AND
			PP.COUNTRY = '".$country."'
	";
	
	$db->query($insert_entry_sql);
}

?>