<?php
/*
 +=============================================================================
 | 
 | 마이페이지_드로우 - 드로우 응모
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

$member_name = null;
if (isset($_SESSION['MEMBER_NAME'])) {
	$member_name = $_SESSION['MEMBER_NAME'];	
}

$draw_idx = 0;
if (isset($_POST['draw_idx'])) {
	$draw_idx = $_POST['draw_idx'];
}

$option_idx = 0;
if (isset($_POST['option_idx'])) {
	$option_idx = $_POST['option_idx'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if ($country != null && $member_idx > 0 && $draw_idx > 0 && $option_idx > 0) {
	$draw_cnt = $db->count("PAGE_DRAW","IDX = ".$draw_idx." AND COUNTRY = '".$country."' AND DISPLAY_STATUS = TRUE AND ENTRY_START_DATE >= NOW() AND ENTRY_END_DATE < NOW()");
	if ($draw_cnt == 0) {
		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0090', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	$entry_cnt = $db->count("ENTRY_DRAW","COUNTRY = '".$country."' AND DRAW_IDX = ".$draw_idx." AND MEMBER_IDX = ".$member_idx);
	if ($entry_cnt > 0) {
		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0015', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	$insert_entry_sql = "
		INSERT INTO
			ENTRY_DRAW
		(
			COUNTRY,
			DRAW_IDX,
			MEMBER_IDX,
			MEMBER_NAME,
			PRODUCT_IDX,
			OPTION_IDX,
			CREATER,
			UPDATER
		)
		SELECT
			'".$country."'		AS COUNTRY,
			".$draw_idx."		AS DRAW_IDX,
			".$member_idx."		AS MEMBER_IDX,
			'".$member_NAME."	AS MEMBER_NAME,
			PD.PRODUCT_IDX		AS PRODUCT_IDX,
			".$option_idx."		AS OPTION_IDX,
			OO.OPTION_NAME		AS OPTION_NAME,
			OO.BARCODE			AS OO.BARCODE,
			'".$member_id."'	AS CREATER,
			'".$member_id."'	AS UPDATER
		FROM
			PAGE_DRAW PD
			LEFT JOIN SHOP_PRODUCT PR ON
			PD.PRODUCT_IDX = PR.IDX
			LEFT JOIN ORDERSHEET_OPTION OO ON
			PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
		WHERE
			PD.IDX = ".$draw_idx." AND
			PD.COUNTRY = '".$country."'
	";
	
	$db->query($insert_entry_sql);
}

?>