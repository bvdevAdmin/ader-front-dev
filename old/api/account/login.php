<?php
/*
 +=============================================================================
 | 
 | 회원 로그인 - 독립몰
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2022.11.30
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");
include_once(dir_f_api."/account/common.php");
include_once(dir_f_api."/send/send-mail.php");

$country = null;
if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

$return_url = null;
if (isset($_POST['r_url'])) {
	$return_url = $r_url;
}

if (!isset($member_id)) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0004', array());
	
	echo json_encode($json_result);
	exit;
}

if (!isset($member_pw)) {
	$json_result['code'] = 402;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0001', array());
	
	echo json_encode($json_result);
	exit;
}

/* 로그인 - 로그인 회원정보 체크 */
$member_cnt = $db->count("MEMBER_".$country,"MEMBER_ID = '".$member_id."'");
if ($member_cnt > 0) {
	$param_select_sql = "
		MB.MEMBER_ID = ?
	";
	
	/* 로그인 - 로그인 회원 생일 바우처 발급일 정보 조회 */
	$param_date = getBirthDateParam($db,$country);
	
	$param_date_sql = "";
	
	if ($param_date != null) {
		$param_date_sql = "
			,DATE_FORMAT(
				DATE_SUB(
					MB.MEMBER_BIRTH,
					INTERVAL ".$param_date['date_ago']." DAY
				),
				'%m-%d'
			)						AS USABLE_START_DATE
			,DATE_FORMAT(
				DATE_ADD(
					MB.MEMBER_BIRTH,
					INTERVAL ".$param_date['date_later']." DAY
				),
				'%m-%d'
			)						AS USABLE_END_DATE
		";
	}
	
	/* 로그인 - 로그인 회원정보 조회 */
	$member = getLoginMember($db,$country,$member_id,$param_select_sql,$param_date_sql);
	if ($member != null) {
		loginMember($db,$country,$member_pw,$member,$return_url);
	}
} else {
	$json_result['code'] = 300;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0100', array());
	
	$json_result['result'] 	= false;
	
	echo json_encode($json_result);
	exit;
}

?>