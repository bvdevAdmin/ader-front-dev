<?php
/*
 +=============================================================================
 | 
 | 회원 공통함수
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.06.19
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

/* 회원 로그인 처리 */
function loginMember($db,$country,$param_pw,$member,$return_url) {
	$member_ip = '0.0.0.0';
	if (isset($_SERVER['REMOTE_ADDR'])) {
		$member_ip = $_SERVER['REMOTE_ADDR'];
	}

	$member_birth_num = "";
	if ($member['member_birth'] != null) {
		$member_birth_num = date('Ymd',strtotime($member['member_birth']));
	}
	
	$member['country']			= $country;
	$member['member_birth_num']	= $member_birth_num;
	$member['member_ip']		= $member_ip;
	
	/* 로그인 - 로그인 회원 비밀번호 체크 */
	$check_pw = true;
	if ($param_pw != null) {
		$check_pw = checkLoginMember_pw($param_pw,$member);
	}
	
	if ($check_pw) {
		/* 로그인 - 로그인 회원 회원상태 체크 */
		$check_status = checkLoginMember_status($db,$country,$member);
		if ($check_status) {
			/* 로그인 - 로그인 회원 IP 차단 여부 체크 */
			$check_ip = checkLoginMember_ip($db,$member);
			if ($check_ip) {
				/* 로그인 - 로그인 회원 세션 설정 */
				setMemberSession($member);
				
				putLoginMember($db,$country,$member);
				
				if ($return_url != null) {
					$json_result['data'] = $return_url;
				}
				
				if ($member['usable_start_date'] != null && $member['usable_end_date'] != null) {
					/* 로그인 - 로그인 회원 생일 바우처 발급 가능여부 체크 */
					$check_birthday = checkLoginMember_birthday($db,$country,$member);
					if ($check_birthday) {
						/* 로그인 - 로그인 회원 생일바우처 발급 전 체크처리 */
						$check_voucher = checkLoginMember_voucher($db,$country,$member);
						if ($check_voucher) {
							/* ========== NAVER CLOUD PLATFORM::생일 바우처 발급 안내 메일 발송 ========== */
							/* PARAM::MAIL */
							$param_mail = array(
								'country'		=>$country,
								'mail_type'		=>"M",
								'mail_code'		=>"MAIL_CODE_0010",
								
								'param_member'	=>$member['member_idx'],
								'param_admin'	=>null
							);
							
							/* PARAM::MAIL DATA */
							$param_data = array(
								'member_name'	=>$member['member_name'],
								'member_id'		=>$member['member_id']
							);
							
							/* 생일 바우처 발급 안내 메일 발송 */
							callSEND_mail($db,$param_mail,$param_data);
						}
					}
				}
			} else {
				/* IP 차단 회원 로그인 시도 예외처리 */
				$json_result['code'] = 303;
				$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0103', array());
				
				echo json_encode($json_result);
				exit;
			}
		} else {
			/* 휴면 / 탈퇴 회원 로그인 시도 예외처리 */
			$json_result['code'] = 302;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0067', array());
			
			echo json_encode($json_result);
			exit;
		}
	} else {
		/* 로그인 입력 비밀번호 불일치 예외처리 */
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0040', array());
		
		$json_result['result'] = false;
		
		echo json_encode($json_result);
		exit;
	}
}

/* 로그인 - 로그인 회원 생일 바우처 발급일 정보 조회 */
function getBirthDateParam($db,$country) {
	$param_date = null;
	
	$select_voucher_mst_sql = "
		SELECT
			VM.DATE_AGO_PARAM		AS DATE_AGO_PARAM,
			VM.DATE_LATER_PARAM		AS DATE_LATER_PARAM
		FROM
			VOUCHER_MST VM
		WHERE
			VM.COUNTRY = ? AND
			VM.VOUCHER_TYPE = 'BR'
	";
	
	$db->query($select_voucher_mst_sql,array($country));
	
	foreach($db->fetch() as $data) {
		$param_date = array(
			'date_ago'			=>$data['DATE_AGO_PARAM'],
			'date_later'		=>$data['DATE_LATER_PARAM'],
		);
	}
	
	return $param_date;
}

/* 로그인 - 로그인 회원정보 조회 */
function getLoginMember($db,$country,$param_member,$select_sql,$date_sql) {
	$member = null;
	
	/* 로그인 회원정보 조회 */
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
			
			CASE
				WHEN
					MB.MEMBER_BIRTH = '0000-00-00'
				THEN
					NULL
				ELSE
					DATE_FORMAT(MB.MEMBER_BIRTH,'%Y-%m-%d')
			END						AS MEMBER_BIRTH
			
			".$date_sql."
		FROM 
			MEMBER_".$country." MB
		WHERE
			".$select_sql."
	";
	
	$db->query($select_member_sql,array($param_member));
	
	foreach($db->fetch() as $data) {
		$member_birth = null;
		if ($data['MEMBER_BIRTH'] != null) {
			$member_birth = $data['MEMBER_BIRTH'];
		}
		
		$pw_status = "F";
		if ($data['MEMBER_PW'] != null && strlen($data['MEMBER_PW']) > 0) {
			$pw_status = "T";
		}
		
		$member = array(
			'member_idx'		=>$data['MEMBER_IDX'],
			'country'			=>$data['COUNTRY'],
			'member_id'			=>$data['MEMBER_ID'],
			'member_pw'			=>$data['MEMBER_PW'],
			'member_level'		=>$data['MEMBER_LEVEL'],
			'member_name'		=>$data['MEMBER_NAME'],
			'tel_mobile'		=>$data['TEL_MOBILE'],
			'member_status'		=>$data['MEMBER_STATUS'],
			'login_cnt'			=>$data['LOGIN_CNT'],
			'member_birth'		=>$member_birth,
			'pw_status'			=>$pw_status,
			
			'usable_start_date'	=>$data['USABLE_START_DATE'],
			'usable_end_date'	=>$data['USABLE_END_DATE']
		);
	}
	
	return $member;
}

/* 로그인 - 로그인 회원 비밀번호 체크 */
function checkLoginMember_pw($member_pw,$member) {
	$check_result = false;
	
	if ($member['member_pw'] == md5($member_pw)) {
		$check_result = true;
	}
	
	return $check_result;
}

/* 로그인 - 로그인 회원 휴면/탈퇴 여부 체크 */
function checkLoginMember_status($db,$country,$member) {
	$check_result = false;
	
	if ($member['member_status'] == 'NML') {
		$check_result = true;
	} else if ($member['member_status'] == 'SLP') {
		$db->update(
			"MEMBER_".$country,
			array(
				'SLEEP_OFF_DATE'	=>NOW(),
				'MEMBER_STATUS'		=>"NML"
			),
			"IDX = ".$member['member_idx']
		);
		
		$check_result = true;
	}
	
	return $check_result;
}

/* 로그인 - 로그인 회원 IP 차단 여부 체크 */
function checkLoginMember_ip($db,$member) {
	$check_result = true;
	
	$cnt_ip = $db->count("IP_BAN","IP = '".$member['member_ip']."'");
	if ($cnt_ip > 0) {
		$check_result = false;
	}
	
	return $check_result;
}

/* 로그인 - 로그인 회원 세션 설정 */
function setMemberSession($member) {
	$_SESSION['MEMBER_IDX']		= $member['member_idx'];
	$_SESSION['COUNTRY']		= $member['country'];
	$_SESSION['MEMBER_ID']		= $member['member_id'];
	$_SESSION['LEVEL_IDX']		= $member['member_level'];
	$_SESSION['MEMBER_NAME']	= $member['member_name'];
	$_SESSION['TEL_MOBILE']		= $member['tel_mobile'];
	$_SESSION['MEMBER_EMAIL']	= $member['member_id'];
	$_SESSION['MEMBER_BIRTH']	= $member['member_birth_num'];
	$_SESSION['PW_STATUS']		= $member['pw_status'];
}

/* 로그인 - 로그인 회원 정보 갱신 */
function putLoginMember($db,$country,$member) {
	$db->update(
		"MEMBER_".$country,
		array(
			'IP'			=>$member['member_ip'],
			'LOGIN_CNT'		=>($member['login_cnt'] + 1),
			'LOGIN_DATE'	=>NOW(),
		),
		"IDX = ".$member['member_idx']
	);	
}

/* 로그인 - 로그인 회원 생일 바우처 발급 가능여부 체크 */
function checkLoginMember_birthday($db,$country,$member) {
	$check_result = false;
	if ($member['member_birth'] != null) {
		$birth_m		= date('m',strtotime($member['member_birth']));
		
		$now_y			= date('Y');
		$now_m			= date('m');
		
		/* 로그인 회원 생일이 이번달인 경우 */
		if ($now_m == $birth_m) {
			/* 생일 바우처 발급 여부 체크 */
			$cnt_issue = $db->count(
				"
					VOUCHER_ISSUE VI
					LEFT JOIN VOUCHER_MST VM ON
					VI.VOUCHER_IDX = VM.IDX
				",
				"
					VM.VOUCHER_TYPE = 'BR' AND
					
					VI.COUNTRY = '".$country."' AND
					VI.MEMBER_IDX = ".$member['member_idx']." AND
					VI.CREATE_YEAR = '".$now_y."'
				"
			);
			
			if ($cnt_issue == 0) {
				$check_result = true;
			}
		}
	}
	
	return $check_result;
}

/* 로그인 - 로그인 회원 생일바우처 발급 전 체크처리 */
function checkLoginMember_voucher($db,$country,$member) {
	$check_voucher = false;
	
	$now_y		= date('Y');
	$now_m		= date('m');
	
	$start_y	= "";
	$end_y		= "";
	
	if ($now_m == '01') {
		$start_y	= intval($now_y) - 1;
		$end_y		= $now_y;
	} else if ($now_m == '12') {
		$start_y	= $now_y;
		$end_y		= $now_y + 1;
	} else {
		$start_y	= $now_y;
		$end_y		= $now_y;
	}
	
	$start_date		= $start_y."-".$member['usable_start_date'];
	$end_date		= $end_y."-".$member['usable_end_date'];
	
	$param_issue = array(
		'country'		=>$country,
		'member_idx'	=>$meber['member_idx'],
		'start_date'	=>$start_date,
		'end_date'		=>$end_date
	);
	
	$voucher_idx = issueBirthVoucher($db,$param_issue);
	if ($voucher_idx > 0) {
		$check_voucher = true;
	}
	
	return $check_voucher;
}

/* 생일 바우처 발급처리 */
function issueBirthVoucher($db,$param) {
	$voucher_issue_idx = 0;
	
	$select_voucher_mst_sql = "
		SELECT
			VM.IDX					AS VOUCHER_IDX
		FROM
			VOUCHER_MST VM
		WHERE
			VOUCHER_TYPE = 'BR'
		AND
			VM.COUNTRY = ?
	";
	
	$db->query($select_voucher_mst_sql,array($param['country']));
	
	foreach($db->fetch() as $data) {
		$voucher_idx = $data['VOUCHER_IDX'];
		
		$voucher_issue_code = makeVoucherCode();
		
		if (!empty($voucher_idx)) {
			/* 생일바우처 발급 이력 체크 */
			$issue_cnt = $db->count(
				"VOUCHER_ISSUE VI",
				"
					VI.VOUCHER_IDX = ".$voucher_idx." AND
					VI.MEMBER_IDX = ".$param['member_idx']." AND
					VI.CREATE_YEAR = DATE_FORMAT(NOW(),'%Y') AND
					VI.CREATE_MONTH = DATE_FORMAT(NOW(),'%m')
				"
			);
			
			if ($issue_cnt == 0) {
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
						'".$param['country']."',
						".$voucher_idx.",
						'".$voucher_issue_code."',
						
						NOW(),
						'".$param['start_date']."',
						'".$param['end_date']."',
						
						DATE_FORMAT(NOW(), '%Y'),
						DATE_FORMAT(NOW(), '%m'),
						MB.IDX,
						MB.MEMBER_ID,
						'system',
						'system'
					FROM
						MEMBER_".$param['country']." MB
					WHERE
						MB.IDX = ?
				";
				
				$db->query($insert_voucher_issue_sql,array($param['member_idx']));
				
				$voucher_issue_idx = $db->last_id();
				if (!empty($voucher_issue_idx)) {
					/* 생일 바우처 발급수량 갱신처리 */
					$update_voucher_mst_sql = "
						UPDATE 
							VOUCHER_MST
						SET 
							TOT_ISSUE_NUM = (
								SELECT 
									COUNT(0)
								FROM
									VOUCHER_ISSUE
								WHERE
									VOUCHER_IDX = ".$voucher_idx."
							)
						WHERE
							IDX = ".$voucher_idx."
					";
					
					$db->query($update_voucher_mst_sql);
				}
			}
		}
	}
	
	return $voucher_issue_idx;
}

/* 바우처 코드 생성 */
function makeVoucherCode(){
	$micro_now      = microtime(true);
	$micro_now_dex  = str_replace('.','',$micro_now);
	$micro_now_hex  = dechex($micro_now_dex);

	return strtoupper($micro_now_hex);
}

?>