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

if (isset($member_pw) || isset($member_tel)) {
	try {
		$update_param = array();
		
		$member_pw_sql = "";
		if (isset($member_pw)) {
			$update_param = array(
				'MEMBER_PW'		=>md5('".$member_pw."'),
				'PW_DATE'		=>NOW()
			);
		}

		$member_tel_sql = "";
		if (isset($member_tel)) {
			$update_param = array(
				'TEL_MOBILE'		=>$member_tel,
			);
		}
		
		$prev_member_info = getPrevMemberInfo($db,$country,$member_idx);
		
		$db->update(
			"MEMBER_".$country,
			$update_param,
			"IDX = ".$member_idx
		);
		
		$db_result = $db->affectedRows();
		
		if ($db_result > 0) {
			addMemberUpdateLog($db,$country,$member_id,$member_idx);
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