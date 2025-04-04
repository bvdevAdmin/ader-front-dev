<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 마케팅 정보 수정
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.13
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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

$receive_tel_flg = null;
if (isset($_POST['receive_tel_flg'])) {
	$receive_tel_flg = $_POST['receive_tel_flg'];
}

$receive_sms_flg = null;
if (isset($_POST['receive_sms_flg'])) {
	$receive_sms_flg = $_POST['receive_sms_flg'];
}

$receive_email_flg = null;
if (isset($_POST['receive_email_flg'])) {
	$receive_email_flg = $_POST['receive_email_flg'];
}



if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	exit;
}

if ($receive_tel_flg != null || $receive_sms_flg != null || $receive_email_flg != null) {
	try {
		$receive_tel_date_sql = "";
		if ($receive_tel_flg == true) {
			$receive_tel_date_sql = "
				RECEIVE_TEL_DATE = NOW(),
			";
		}

		$receive_sms_date_sql = "";
		if ($receive_sms_flg == true) {
			$receive_sms_date_sql = "
				RECEIVE_SMS_DATE = NOW(),
			";
		}

		$receive_email_date_sql = "";
		if ($receive_email_flg == true) {
			$receive_email_date_sql = "
				,RECEIVE_EMAIL_DATE = NOW()
			";
		}

		$accept_marketing_flg_sql = "";
		if ($receive_tel_flg == 'true' || $receive_sms_flg == 'true' || $receive_email_flg == 'true') {
			$accept_marketing_flg_sql = "
				,ACCEPT_MARKETING_FLG = TRUE
			";
		}
		
		if($receive_tel_flg == 'false' && $receive_sms_flg == 'false' && $receive_email_flg == 'false') {
		  $accept_marketing_flg_sql = "
				,ACCEPT_MARKETING_FLG = FALSE
			";
		}
		
		$prev_member_info = getPrevMemberInfo($db,$country,$member_idx);
		
		$update_marketing_sql = "
			UPDATE
				MEMBER_".$country."
			SET
				RECEIVE_TEL_FLG = ".$receive_tel_flg.",
				".$receive_tel_date_sql."
				RECEIVE_SMS_FLG = ".$receive_sms_flg.",
				".$receive_sms_date_sql."
				RECEIVE_EMAIL_FLG = ".$receive_email_flg."
				".$receive_email_date_sql."
				".$accept_marketing_flg_sql."
			WHERE
				IDX = ".$member_idx."
		";

		$db->query($update_marketing_sql);
		
		$db_result = $db->affectedRows();
		
		if ($db_result > 0) {
			addMemberUpdateLog($db,$country,$member_idx,$prev_member_info);
		}
		
		$db->commit();
	} catch (mysqli_sql_exception $exception) {
		$db->rollback();
		print_r($exception);
		
		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0019', array());
		
		echo json_encode($json_result);
		exit;
	}
}

?>