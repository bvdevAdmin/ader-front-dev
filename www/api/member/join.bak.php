<?php
/*
 +=============================================================================
 | 
 | 회원 가입 - 신규 회원 가입
 | -------
 |
 | 최초 작성	: 양한빈
 | 최초 작성일	: 2023.09.06
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common/mail.php");
include_once("/var/www/www/api/common/common.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


//동일 ID 중복체크
$member_total_cnt = $db->count('MEMBER_KR', 'MEMBER_ID = ?', array($member_id))
	+ $db->count('MEMBER_EN', 'MEMBER_ID = ?', array($member_id))
	+ $db->count('MEMBER_CN', 'MEMBER_ID = ?', array($member_id));
if($member_total_cnt > 0){
	$code = 303;
	$msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0059', array());
}

else {
	$values = array(
		'COUNTRY' => $country,
		'MEMBER_STATUS' => 'NML',
		'MEMBER_ID' => $member_id,
		'MEMBER_PW' => md5($member_pw),
		'MEMBER_NAME' => $member_name,
		'TEL_MOBILE' => $tel_mobile,
		'JOIN_DATE' => now()
	);
	if(isset($country_code) && $country != 'KR'){
		$values['COUNTRY_CODE'] = $country_code;
	}

	$db->begin_transaction();

	try {
		// 회원 정보 입력
		$db->insert('MEMBER_'.$country,$values);
		
		// 회원 키인덱스 가져오기
		$member_idx = $db->last_id();
		
		// 마일리지 정보 입력
		$db->insert('MILEAGE_INFO',array(
			'COUNTRY' => $country,
			'MEMBER_IDX' => $member_idx,
			'ID' => $member_id,
			'MILEAGE_CODE' => 'NEW',
			'MILEAGE_UNUSABLE' => 0,
			'MILEAGE_USABLE_INC' => 5000,
			'MILEAGE_USABLE_DEC' => 0,
			'MILEAGE_BALANCE' => 5000,
			'CREATER' => 'System',
			'UPDATER' => 'System'
		));

		$mapping_arr = array();
		$mapping_arr[$member_idx]['member_id'] = $member_id;
		$mapping_arr[$member_idx]['member_name'] = $member_name;
		//checkMailStatus($db, $country, 'MAIL_CASE_0001', $member_idx, $member_id, $mapping_arr);

		//joinMailSet($member_id, $member_name);
		$db->commit();
	} catch (mysqli_sql_exception $exception) {
		$db->rollback();
		print_r($exception);
		
		$code = 401;
		$msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0101', array());
	}
}
