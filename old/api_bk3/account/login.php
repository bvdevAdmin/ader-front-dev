<?php
/*
 +=============================================================================
 | 
 | 회원 로그인
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

include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/account/common.php");

$country = null;
if (isset($_POST['country'])) {
	$country = $_POST['country'];
}

$member_ip = '0.0.0.0';
if (isset($_SERVER['REMOTE_ADDR'])) {
	$member_ip = $_SERVER['REMOTE_ADDR'];
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

/* 로그인 회원정보 체크 */
$member_cnt = $db->count("MEMBER_".$country,"MEMBER_ID = '".$member_id."'");
if ($member_cnt > 0) {
	/* 로그인 회원 생일 바우처 정보 조회 */
	$param_date = getVoucherDateParam($db,$country,"BR");
	
	$param_date_sql = "";
	if ($param_date != null) {
		$param_date_sql = "
			,DATE_FORMAT(
				DATE_SUB(
					MB.MEMBER_BIRTH,
					INTERVAL ".$param_date['param_ago']." DAY
				),
				'%m-%d'
			)						AS USABLE_START_DATE
			,DATE_FORMAT(
				DATE_ADD(
					MB.MEMBER_BIRTH,
					INTERVAL ".$param_date['param_later']." DAY
				),
				'%m-%d'
			)						AS USABLE_END_DATE
		";
	}
	
	$select_member_sql = "
		SELECT 
			MB.IDX					AS MEMBER_IDX,
			MB.COUNTRY				AS COUNTRY,
			MB.MEMBER_ID			AS MEMBER_ID,
			MB.MEMBER_PW			AS MEMBER_PW,
			MB.LEVEL_IDX			AS MEMBER_LEVEL,
			MB.MEMBER_NAME			AS MEMBER_NAME,
			MB.TEL_MOBILE			AS TEL_MOBILE,
			MB.MEMBER_STATUS		AS MEMBER_STATUS,
			MB.LOGIN_CNT			AS LOGIN_CNT,
			
			MB.MEMBER_BIRTH			AS MEMBER_BIRTH
			
			".$param_date_sql."
		FROM 
			MEMBER_".$country." MB
		WHERE
			MB.MEMBER_ID = '".$member_id."'
	";
	
	$db->query($select_member_sql);
	
	foreach($db->fetch() as $data){
		$member_idx = $data['MEMBER_IDX'];
		$db_pw = $data['MEMBER_PW'];
		
		$member_status = $data['MEMBER_STATUS'];
		
		$login_cnt = $data['LOGIN_CNT'];
		
		$member_birth		= $data['MEMBER_BIRTH'];
		$member_birth_num	= date('Ymd',strtotime($member_birth));
		
		/* 회원 비밀번호 체크 */
		$check_pw = checkMemberPW($member_pw,$db_pw);
		if ($check_pw == true) {
			/* 회원 상태 체크 (휴면회원 / 탈퇴회원) */
			$check_status = checkMemberStatus($db,$country,$member_idx,$member_status);
			if ($check_status == true) {
				$check_ip = checkMemberIP($db,$member_ip);
				if ($check_ip == true) {
					/* 로그인 회원정보 세션 저장 */
					$_SESSION['MEMBER_IDX']		= $data['MEMBER_IDX'];
					$_SESSION['COUNTRY']		= $data['COUNTRY'];
					$_SESSION['MEMBER_ID']		= $data['MEMBER_ID'];
					$_SESSION['LEVEL_IDX']		= $data['MEMBER_LEVEL'];
					$_SESSION['MEMBER_NAME']	= $data['MEMBER_NAME'];
					$_SESSION['TEL_MOBILE']		= $data['TEL_MOBILE'];
					$_SESSION['MEMBER_EMAIL']	= $data['MEMBER_ID'];
					$_SESSION['MEMBER_BIRTH']	= $member_birth_num;
					
					/* 회원정보 로그인 정보 갱신 */
					putMemberLogin($db,$country,$member_idx,$member_ip,$login_cnt);
					
					/* 생일바우처 체크처리 */
					if ($member_birth != null) {
						$birthday_month	= date('m',strtotime($member_birth));
						
						$now_year		= date('Y');
						$now_month		= date('m');
						
						if ($now_month == $birthday_month) {
							$cnt_birth = $db->count(
								"VOUCHER_ISSUE VI",
								"
									VI.VOUCHER_IDX = (
										SELECT
											S_VM.IDX
										FROM
											VOUCHER_MST S_VM
										WHERE
											S_VM.COUNTRY = '".$country."' AND
											S_VM.VOUCHER_TYPE = 'BR'
									) AND
									VI.COUNTRY = '".$country."' AND
									VI.MEMBER_IDX = ".$member_idx." AND
									VI.CREATE_YEAR = '".$now_year."' AND
									VI.CREATE_MONTH = '".$now_month."'
								"
							);
							
							if ($cnt_birth == 0) {
								if (isset($data['USABLE_START_DATE']) && isset($data['USABLE_END_DATE'])) {
									$param_birthday = array(
										'country'			=>$country,
										'member_idx'		=>$member_idx,
										
										'now_year'			=>$now_year,
										'now_month'			=>$now_month,
										
										'usable_start_date'	=>$data['USABLE_START_DATE'],
										'usable_end_date'	=>$data['USABLE_END_DATE']
									);
									
									checkMemberBirthday($db,$param_birthday);
								}
								
							}
						}
					}
				} else {
					$json_result['code'] = 305;
					$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0103', array());
					
					echo json_encode($json_result);
					exit;
				}
				
				if ($r_url != null) {
					$json_result['data'] = $r_url;
				}
			} else {
				$json_result['code'] = 305;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0067', array());
				
				echo json_encode($json_result);
				exit;
			}
		} else {
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0040', array());
			
			$json_result['result'] = false;
			
			echo json_encode($json_result);
			exit;
		}
	}
} else {
	$json_result['code'] = 300;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0100', array());
	
	$json_result['result'] 	= false;
	
	echo json_encode($json_result);
	exit;
}

function checkMemberPW($param_pw,$db_pw) {
	$check_result = false;
	
	if ($db_pw == md5($param_pw)) {
		$check_result = true;
	}
	
	return $check_result;
}

function checkMemberStatus($db,$country,$member_idx,$param_status) {
	$check_result = array();
	
	if ($param_status == 'NML') {
		$check_result = true;
	} else if ($param_status == 'SLP') {
		$db->update(
			"MEMBER_".$country,
			array(
				'SLEEP_OFF_DATE'	=>NOW(),
				'MEMBER_STATUS'		=>"NML"
			),
			"IDX = ".$member_idx
		);
		
		$check_result = true;
	}
	
	return $check_result;
}

function checkMemberIP($db,$member_ip) {
	$check_result = true;
	
	$cnt_ip = $db->count("IP_BAN","IP = '".$member_ip."'");
	if ($cnt_ip > 0) {
		$check_result = false;
	}
	
	return $check_result;
}

function putMemberLogin($db,$country,$member_idx,$member_ip,$login_cnt) {
	$db->update(
		"MEMBER_".$country,
		array(
			'IP'			=>$member_ip,
			'LOGIN_CNT'		=>($login_cnt + 1),
			'LOGIN_DATE'	=>NOW(),
		),
		"IDX = ".$member_idx
	);
}

function getVoucherDateParam($db,$country,$voucher_type) {
	$param_date = null;
	
	$select_voucher_mst_sql = "
		SELECT
			VM.DATE_AGO_PARAM		AS DATE_AGO_PARAM,
			VM.DATE_LATER_PARAM		AS DATE_LATER_PARAM
		FROM
			VOUCHER_MST VM
		WHERE
			VM.COUNTRY = '".$country."' AND
			VM.VOUCHER_TYPE = '".$voucher_type."'
	";
	
	$db->query($select_voucher_mst_sql);
	
	foreach($db->fetch() as $data) {
		$param_date = array(
			'param_ago'			=>$data['DATE_AGO_PARAM'],
			'param_later'		=>$data['DATE_LATER_PARAM'],
		);
	}
	
	return $param_date;
}

function checkMemberBirthday($db,$param) {
	$now_year	= $param['now_year'];
	$now_month	= $param['now_month'];
	
	if ($now_month == '01') {
		$year_start = intval($now_year) - 1;
		$year_end = $now_year;
	} else if ($now_month == '12') {
		$year_start = $now_year;
		$year_end = $now_year + 1;
	} else {
		$year_start = $now_year;
		$year_end = $now_year;
	}
	
	$param_start_date	= $year_start."-".$param['usable_start_date'];
	$param_end_date		= $year_end."-".$param['usable_end_date'];
	
	issueBirthVoucher($db,$param['country'],$param['member_idx'],$param_start_date,$param_end_date);
}

?>