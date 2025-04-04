<?php
/*
 +=============================================================================
 | 
 | 회원 가입 - 신규 회원 가입 // api/account/add.php
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2022.11.30
 | 최종 작성	: 양한빈
 | 최종 수정일	: 2024.04.30
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

include_once $_CONFIG['PATH']['API'].'_legacy/common.php';
include_once $_CONFIG['PATH']['API'].'_legacy/send.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* 1-1. 회원 중복체크 - 동일 ID 중복체크 */
$cnt_id = $db->count("MEMBER","MEMBER_ID = ?",array($member_id));
if ($cnt_id > 0) {
	$json_result['code'] = 303;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0059',array());

	echo json_encode($json_result);
	exit;
}

/* 1-2. 회원 중복체크 - 동일 전화번호 중복체크 */
$cnt_member = $db->count("MEMBER","TEL_MOBILE = ?",array($tel_mobile));
if ($cnt_member > 0) {
	$json_result['code'] = 303;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0146',array());

	echo json_encode($json_result);
	exit;
}

$db->begin_transaction();

try {
	$param_birth = null;
	if (!isset($member_birth)) {
		$param_birth = $member_birth;
	}

	$tmp_flg_r_M = 0;
	if (isset($agree_receive_email) && $agree_receive_email == "on") {
		$tmp_flg_r_M = 1;
	}

	$tmp_flg_r_S = 0;
	if (isset($agree_receive_sms) && $agree_receive_sms == "on") {
		$tmp_flg_r_S = 1;
	}

	$auth_flg = 0;
	if ($_SERVER['HTTP_COUNTRY'] == "KR") {
		$auth_flg == 1;
	}

	/* 2-1. 회원 정보 등록 */
	$db->insert(
		"MEMBER",
		array(
			'COUNTRY'				=>$_SERVER['HTTP_COUNTRY'],
			'MEMBER_STATUS'			=>'NML',
			'MEMBER_ID'				=>$member_id,
			'MEMBER_PW'				=>md5($member_pw),
			'MEMBER_NAME'			=>$member_name,
			'MEMBER_BIRTH'			=>$param_birth,
			'TEL_MOBILE'			=>$tel_mobile,
			'JOIN_DATE'				=>NOW(),

			'RECEIVE_EMAIL_FLG'		=>$tmp_flg_r_M,
			'RECEIVE_EMAIL_DATE'	=>NOW(),
			'RECEIVE_SMS_FLG'		=>$tmp_flg_r_S,
			'RECEIVE_SMS_DATE'		=>NOW(),
			'AUTH_FLG'				=>$auth_flg
		)
	);
	
	$member_idx = $db->last_id();
	if (isset($member_idx)) {
		/* 2-2. 회원 가용 적립금 등록 (신규가입 적립금) */

		/* 한국몰 신규가입 적립금 지급 */
		$db->insert(
			"MILEAGE_INFO",
			array(
				'COUNTRY'				=>"KR",
				'MEMBER_IDX'			=>$member_idx,
				'ID'					=>$member_id,
				'MILEAGE_CODE'			=>'NEW',
				'MILEAGE_UNUSABLE'		=>0,
				'MILEAGE_USABLE_INC'	=>5000,
				'MILEAGE_USABLE_DEC'	=>0,
				'MILEAGE_BALANCE'		=>5,
				'CREATER'				=>'system',
				'UPDATER'				=>'system',
			)
		);

		/* 영문몰 신규가입 적립금 지급 */
		$db->insert(
			"MILEAGE_INFO",
			array(
				'COUNTRY'				=>"EN",
				'MEMBER_IDX'			=>$member_idx,
				'ID'					=>$member_id,
				'MILEAGE_CODE'			=>'NEW',
				'MILEAGE_UNUSABLE'		=>0,
				'MILEAGE_USABLE_INC'	=>5,
				'MILEAGE_USABLE_DEC'	=>0,
				'MILEAGE_BALANCE'		=>5,
				'CREATER'				=>'system',
				'UPDATER'				=>'system',
			)
		);
		
		/* 3. 자동메일 발송설정 체크처리 */
		$mail_setting = checkMAIL_setting($db,$_SERVER['HTTP_COUNTRY'],"MAIL_CODE_0001");
		if ($mail_setting['mail_flg'] == true && ($mail_setting['template_id'] != null && $mail_setting['template_id'] != "00000")) {
			/* PARAM::MAIL */
			$param_mail = array(
				'user_email'		=>$member_id,
				'user_name'			=>$member_name,
				'tel_mobile'		=>$tel_mobile,
				'template_id'		=>$mail_setting['template_id']
			);
			
			/* PARAM::DATA */
			/*
			$mail_data = array(
				'member_name'		=>$member_name,
				'member_id'			=>$member_id
			);
			*/
			
			/* (공통) NCP - 메일 발송 */
			callSEND_mail($db,$param_mail,array());
		}
		
		/* 4. 회원 로그 등록 */
		addMember_log($db,$member_idx,null);
		
		$db->commit();
	}
} catch (mysqli_sql_exception $e) {
	$db->rollback();
	
	print_r($e);
	
	$code = 401;
	$msg = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0101',array());		
}

?>