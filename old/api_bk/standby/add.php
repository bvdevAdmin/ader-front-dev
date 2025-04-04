<?php
/*
 +=============================================================================
 | 
 | 마이페이지_스탠바이 - 스탠바이 응모
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
include_once("/var/www/www/api/common/check.php");

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
$member_name = null;
if (isset($_SESSION['MEMBER_NAME'])) {
	$member_name = $_SESSION['MEMBER_NAME'];	
}
$standby_idx = 0;
if (isset($_POST['standby_idx'])) {
	$standby_idx = $_POST['standby_idx'];
}
$login_chk = checkPromotionLogin($country, $member_idx, $member_name);
if($login_chk['value'] != true){
    $json_result['code'] = 301;
	$json_result['msg'] = $login_chk['msg'];
	return $json_result;
}
$time_result =  checkAccessTime($db, $standby_idx, 'STANDBY');
if($time_result['value'] != true){
	$json_result['code'] = 301;
	$json_result['msg'] = $time_result['msg'];
	return $json_result;
}
$verify_result =  checkVerifyMember($db, $member_idx, $country);
if($verify_result['value'] != true){
	$json_result['code'] = 301;
	$json_result['msg'] = $verify_result['msg'];
	return $json_result;
}
$IpBan_result =  checkIpBanMember($db, $member_idx, $country);
if($IpBan_result['value'] != true){
	$json_result['code'] = 301;
	$json_result['msg'] = $IpBan_result['msg'];
	return $json_result;
}
$duplicate_result =  checkDuplicateEntry($db, $standby_idx, 'STANDBY', $member_idx, $country);
if($duplicate_result['value'] != true){
	$json_result['code'] = 301;
	$json_result['msg'] = $duplicate_result['msg'];
	return $json_result;
}
$level_result =  checkPromotionMemberLevel($db, $standby_idx, 'STANDBY', $member_idx, $country);
if($level_result['value'] != true){
	$json_result['code'] = 301;
	$json_result['msg'] = $level_result['msg'];
	return $json_result;
}
$insert_entry_sql = "
	INSERT INTO
		dev.ENTRY_STANDBY
	(
		COUNTRY,
		STANDBY_IDX,
		MEMBER_IDX,
		MEMBER_NAME,
		CREATER,
		UPDATER
	)
	SELECT
		'".$country."'		AS COUNTRY,
		PS.IDX				AS STANDBY_IDX,
		".$member_idx."		AS MEMBER_IDX,
		'".$member_name."'	AS MEMBER_NAME,
		'".$member_id."'	AS CREATER,
		'".$member_id."'	AS UPDATER
	FROM
		dev.PAGE_STANDBY PS
	WHERE
		PS.IDX = ".$standby_idx." AND
		PS.COUNTRY = '".$country."'
";

$db->query($insert_entry_sql);

?>