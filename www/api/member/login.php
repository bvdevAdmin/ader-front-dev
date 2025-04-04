<?php
/*
 +=============================================================================
 | 
 | 회원 로그인 // /api/account/login.php
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

$country	= $_SERVER['HTTP_COUNTRY'];
$member_ip	= $_SERVER['REMOTE_ADDR'];

if (!isset($member_id)) {
	/* 로그인 회원 ID 미입력 예외처리 */

	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_WRN_0004',array());

	echo json_encode($json_result);
	exit;
} else if (!isset($member_pw)) {
	/* 로그인 회원 ID 미입력 예외처리 */

	$json_result['code'] = 402;
	$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_WRN_0001',array());
	
	echo json_encode($json_result);
	exit;
} else {
	/* 1. 로그인 회원 ID DB 체크 */
	$cnt_id = $db->count("MEMBER","MEMBER_ID = ?",array($member_id));
	if ($cnt_id > 0) {
		/* 2. 쇼핑몰 별 생일 바우처 정보 조회 */
		$param_voucher = getParam_voucher($db,$country,$member_id);
		
		$param_voucher_sql = "";
		if ($param_voucher != null) {
			$param_voucher_sql = "
				,DATE_FORMAT(
					DATE_SUB(
						MB.MEMBER_BIRTH,
						INTERVAL ".$param_voucher['ago']." DAY
					),
					'%m-%d'
				)						AS START_DATE
				,DATE_FORMAT(
					DATE_ADD(
						MB.MEMBER_BIRTH,
						INTERVAL ".$param_voucher['later']." DAY
					),
					'%m-%d'
				)						AS END_DATE
			";
		}
		
		/* 3. 로그인 회원 세션정보 저장 */
		$select_member_sql = "
			SELECT 
				MB.IDX					AS MEMBER_IDX,
				MB.MEMBER_ID			AS MEMBER_ID,
				MB.MEMBER_PW			AS MEMBER_PW,
				MB.LEVEL_IDX			AS MEMBER_LEVEL,
				MB.MEMBER_NAME			AS MEMBER_NAME,
				MB.TEL_MOBILE			AS TEL_MOBILE,
				MB.MEMBER_STATUS		AS MEMBER_STATUS,
				MB.LOGIN_CNT			AS LOGIN_CNT,
				MB.MEMBER_BIRTH			AS MEMBER_BIRTH,
				MB.IMPROPPER_FLG		AS IMPROPPER_FLG,
				MB.AUTH_FLG				AS AUTH_FLG
				
				".$param_voucher_sql."
			FROM 
				MEMBER MB
			WHERE
				MB.MEMBER_ID = ?
		";
		
		$db->query($select_member_sql,array($member_id));
		
		foreach($db->fetch() as $data){
			$member_idx	= $data['MEMBER_IDX'];
			$db_pw		= $data['MEMBER_PW'];
			
			$login_cnt = $data['LOGIN_CNT'];
			
			$member_birth		= $data['MEMBER_BIRTH'];
			$member_birth_num	= date('Ymd',strtotime($member_birth));
			
			/* 3. 로그인 회원정보 체크 */
			
			/* 3-1. 로그인 회원정보 체크 - 회원 비밀번호 체크 */
			$check_pw = checkMember_pw($member_pw,$db_pw);
			if ($check_pw == true) {
				
				/* 3-2. 로그인 회원정보 체크 - 회원 상태 체크 (휴면회원 / 탈퇴회원) */
				$check_status = checkMember_status($db,$member_idx,$data['MEMBER_STATUS'],$data['IMPROPPER_FLG']);
				if ($check_status == true) {
					
					/* 3-3. 로그인 회원정보 체크 - 로그인 IP */
					$check_ip = checkMember_ip($db,$member_ip);
					if ($check_ip == true) {
						
						/* 4. 로그인 회원정보 세션 저장 */
						$_SESSION['MEMBER_IDX']		= $data['MEMBER_IDX'];
						$_SESSION['MEMBER_ID']		= $data['MEMBER_ID'];
						$_SESSION['LEVEL_IDX']		= $data['MEMBER_LEVEL'];
						$_SESSION['MEMBER_NAME']	= $data['MEMBER_NAME'];
						$_SESSION['TEL_MOBILE']		= $data['TEL_MOBILE'];
						$_SESSION['MEMBER_EMAIL']	= $data['MEMBER_ID'];
						$_SESSION['MEMBER_BIRTH']	= $member_birth_num;
						$_SESSION['AUTH_FLG']		= $data['AUTH_FLG'];
						
						/* 5. 회원정보 로그인 정보 갱신 */
						putMember_login($db,$member_ip,$login_cnt);
						
						/* 6. 생일바우처 지급 여부 체크처리 */
						if (isset($param_voucher['voucher_idx']) && $member_birth != null) {
							$birthday_month	= date('m',strtotime($member_birth));
							
							$now_year		= date('Y');
							$now_month		= date('m');
							
							if ($now_month == $birthday_month) {
								$cnt_issue = $db->count(
									"VOUCHER_ISSUE VI",
									"
										VI.VOUCHER_IDX = ? AND
										VI.COUNTRY = ? AND
										VI.MEMBER_IDX = ? AND
										VI.CREATE_YEAR = ? AND
										VI.CREATE_MONTH = ?
									",
									array($param_voucher['voucher_idx'],$country,$member_idx,$now_year,$now_month)
								);
								
								if ($cnt_issue == 0) {
									if (isset($data['START_DATE']) && isset($data['END_DATE'])) {
										$param_birthday = array(
											'country'		=>$country,
											'member_idx'	=>$member_idx,
											
											'now_year'		=>$now_year,
											'now_month'		=>$now_month,
											
											'voucher_idx'	=>$param_voucher['voucher_idx'],
											'start_date'	=>$data['START_DATE'],
											'end_date'		=>$data['END_DATE']
										);
										
										/* 7. 로그인 회원정보 체크 - 회원 생일 */
										checkMember_birthday($db,$param_birthday);
									}
								}
							}
						}

						$json_result['country'] = $_SERVER['HTTP_COUNTRY'];
						$json_result['auth_flg'] = $data['AUTH_FLG'];
					} else {
						$json_result['code'] = 304;
						$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0103',array());
						
						echo json_encode($json_result);
						exit;
					}
					
					if ($r_url != null) {
						$json_result['data'] = $r_url;
					}
				} else {
					if ($data['IMPROPPER_FLG'] == true) {
						$json_result['code'] = 303;
						$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0103',array());
						
						echo json_encode($json_result);
						exit;
					} else {
						$json_result['code'] = 302;
						$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0067',array());
						
						echo json_encode($json_result);
						exit;
					}
				}
			} else {
				$json_result['code'] = 301;
				$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0040',array());
				
				$json_result['result'] = false;
				
			}
		}
	} else {
		$json_result['code'] = 300;
		$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0100',array());
		
		$json_result['result'] 	= false;
	}
}

/* 2. 쇼핑몰 별 생일 바우처 정보 조회 */
function getParam_voucher($db,$country,$member_id) {
	$param_voucher = null;
	
	$select_voucher_mst_sql = "
		SELECT
			VM.IDX		AS VOUCHER_IDX,
			IFNULL(
				VM.DATE_AGO_PARAM,0
			)			AS AGO,
			IFNULL(
				VM.DATE_LATER_PARAM,0
			)			AS LATER
		FROM
			VOUCHER_MST VM
		WHERE
			VM.COUNTRY = ? AND
			VM.VOUCHER_TYPE = 'BR' AND
			VM.MEMBER_LEVEL = (
				SELECT
					LEVEL_IDX
				FROM
					MEMBER S_MB
				WHERE
					S_MB.MEMBER_ID = ?
			)
	";
	
	$db->query($select_voucher_mst_sql,array($country,$member_id));
	
	foreach($db->fetch() as $data) {
		$param_voucher = array(
			'voucher_idx'	=>$data['VOUCHER_IDX'],
			'ago'			=>$data['AGO'],
			'later'			=>$data['LATER'],
		);
	}
	
	return $param_voucher;
}

/* 3-1. 로그인 회원정보 체크 - 회원 비밀번호 체크 */
function checkMember_pw($param_pw,$db_pw) {
	$check_result = false;
	
	if ($db_pw == md5($param_pw)) {
		$check_result = true;
	}
	
	return $check_result;
}

/* 3-2. 로그인 회원정보 체크 - 회원 상태 체크 (휴면회원 / 탈퇴회원) */
function checkMember_status($db,$member_idx,$param_status,$impropper_flg) {
	$check_result = false;
	
	if ($param_status == 'NML') {
		if ($impropper_flg != true) {
			$check_result = true;
		}
	} else if ($param_status == 'SLP') {
		$db->update(
			"MEMBER",
			array(
				'SLEEP_OFF_DATE'	=>NOW(),
				'MEMBER_STATUS'		=>"NML"
			),
			"IDX = ?",
			array($member_idx)
		);
		
		$check_result = true;
	}
	
	return $check_result;
}

/* 3-3. 로그인 회원정보 체크 - 로그인 IP */
function checkMember_ip($db,$member_ip) {
	$check_result = false;
	
	$cnt_ip = $db->count("IP_BAN","IP = ?",array($member_ip));
	if ($cnt_ip == 0) {
		$check_result = true;
	}
	
	return $check_result;
}

/* 5. 회원정보 로그인 정보 갱신 */
function putMember_login($db,$member_ip,$login_cnt) {
	$db->update(
		"MEMBER",
		array(
			'LOGIN_IP'		=>$member_ip,
			'LOGIN_CNT'		=>($login_cnt + 1),
			'LOGIN_DATE'	=>NOW(),
		),
		"IDX = ?",
		array($_SESSION['MEMBER_IDX'])
	);
	
	$db->insert(
		"MEMBER_LOGIN_HISTORY",
		array(
			'COUNTRY'		=>$_SERVER['HTTP_COUNTRY'],
			'MEMBER_IDX'	=>$_SESSION['MEMBER_IDX'],
			'MEMBER_IP'		=>$member_ip,
			'MEMBER_ID'		=>$_SESSION['MEMBER_ID'],
			'MEMBER_NAME'	=>$_SESSION['MEMBER_NAME'],
			'CREATE_DATE'	=>NOW()
		)
	);
}

/* 7. 로그인 회원정보 체크 - 회원 생일 */
function checkMember_birthday($db,$param) {
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
	
	$usable_start_date	= $year_start."-".$param['start_date'];
	$usable_end_date	= $year_end."-".$param['end_date'];
	
	$param_issue = array(
		'country'			=>$param['country'],
		'member_idx'		=>$param['member_idx'],
		'voucher_idx'		=>$param['voucher_idx'],
		'usable_start_date'	=>$usable_start_date,
		'usable_end_date'	=>$usable_end_date
	);
	
	/* 생일 바우처 지급 처리 */
	issueVoucher_birthday($db,$param_issue);
}

/* 생일 바우처 지급 처리 */
function issueVoucher_birthday($db,$param) {
	$voucher_code = makeVoucher_code();
	
	/* 생일바우처 발급 이력이 존재하지 않는 경우 */
	$insert_voucher_issue_sql = "
		INSERT INTO
			VOUCHER_ISSUE
		(
			COUNTRY,
			VOUCHER_IDX,
			VOUCHER_ISSUE_CODE,
			
			VOUCHER_ADD_DATE,
			USABLE_START_DATE,
			USABLE_END_DATE,
			
			CREATE_YEAR,
			CREATE_MONTH,
			
			MEMBER_IDX,
			MEMBER_ID,
			
			CREATER,
			UPDATER
		)
		SELECT 
			?				AS COUNTRY,
			?				AS VOUCHER_IDX,
			?				AS VOUCHER_ISSUE_CODE,
			
			NOW(),
			?				AS USABLE_START_DATE,
			?				AS USABLE_END_DATE,
			
			DATE_FORMAT(
				NOW(),'%Y'
			)				AS CREATE_YEAR,
			DATE_FORMAT(
				NOW(),'%m'
			)				AS CREATE_MONTH,
			
			MB.IDX			AS MEMBER_IDX,
			MB.MEMBER_ID	AS MEMBER_ID,
			
			'system'		AS CREATER,
			'system'		AS UPDATER
		FROM
			MEMBER MB
		WHERE
			MB.IDX = ?
	";
	
	$db->query(
		$insert_voucher_issue_sql,
		array($param['country'],$param['voucher_idx'],$voucher_code,$param['usable_start_date'],$param['usable_end_date'],$param['member_idx'])
	);
}

function makeVoucher_code(){
	$micro_now      = microtime(true);
	$micro_now_dex  = str_replace('.','',$micro_now);
	$micro_now_hex  = dechex($micro_now_dex);

	return strtoupper($micro_now_hex);
}

?>