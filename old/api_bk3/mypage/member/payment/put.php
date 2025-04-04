<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 결제수단 수정
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.03.16
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common.php");

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

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

$card_name = null;
if (isset($_POST['CARD_NAME'])) {
	$card_name = $_POST['CARD_NAME'];
}
$card_number = null;
if (isset($_POST['CARD_NUMBER'])) {
	$card_number = $_POST['CARD_NUMBER'];
}
$card_valid_year = null;
if (isset($_POST['CARD_VALID_YEAR'])) {
	$card_valid_year = $_POST['CARD_VALID_YEAR'];
}
$card_valid_month = null;
if (isset($_POST['CARD_VALID_MONTH'])) {
	$card_valid_month = $_POST['CARD_VALID_MONTH'];
}
$card_default_flg = $_POST['CARD_DEFAULT_FLG'];
$card_default_flg_sql = "";
if ($card_default_flg == 'true') {
	$card_default_flg_sql = "
		CARD_DEFAULT_FLG = TRUE
	";
} else {
	$card_default_flg_sql = "
		CARD_DEFAULT_FLG = FALSE
	";
}

if($country != null && $member_idx > 0) {
	$update_payment_sql = "
		UPDATE
			MEMBER_".$country."
		SET
			CARD_NAME = ".$card_name.",
			CARD_NUMBER = ".$card_number.",
			CARD_VALID_YEAR = ".$card_valid_year.",
			CARD_VALID_MONTH = ".$card_valid_month.",
			".$card_default_flg_sql."
		WHERE
			IDX = ".$member_idx."
	";
	
	$db->query($update_payment_sql);
}
?>