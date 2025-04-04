<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 비밀번호, 휴대전화 번호 수정
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

$member_pw = null;
if (isset($_POST['member_pw'])) {
	$member_pw = md5($_POST['member_pw']);
}

$member_tel = null;
if (isset($_POST['member_tel'])) {
	$member_tel = $_POST['member_tel'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if ($member_pw != null || $member_tel != null) {
	try {
		$member_pw_sql = "";
		if ($member_pw != null) {
			$member_pw_sql = "
				MEMBER_PW = '".$member_pw."',
				PW_DATE = NOW()
			";
		}

		$member_tel_sql = "";
		if ($member_tel != null) {
			$member_tel_sql = " TEL_MOBILE = '".$member_tel."' ";
			if (strlen($member_pw_sql) > 0) {
				$member_tel_sql = " , ".$member_tel_sql;
			}
		}
		
		$prev_member_info = getPrevMemberInfo($db,$country,$member_idx);
		
		$update_member_sql = "
			UPDATE
				MEMBER_".$country."
			SET
				".$member_pw_sql."
				".$member_tel_sql."
			WHERE
				IDX = ".$member_idx."
		";
		
		$db->query($update_member_sql);
		
		$db_result = $db->affectedRows();
		
		if ($db_result > 0) {
			addMemberUpdateLog($db,$country,$member_idx,$prev_member_info);
		}
		
		$db->commit();
	} catch (mysqli_sql_exception $exception) {
		$db->rollback();
		print_r($exception);
		
		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0102', array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0025', array());
	
	echo json_encode($json_result);
	exit;
}

?>