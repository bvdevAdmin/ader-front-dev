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

include_once $_CONFIG['PATH']['API'].'_legacy/mail.php';
include_once $_CONFIG['PATH']['API'].'_legacy/send/common.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* 1. 동일 ID 중복체크 */
$check_result = checkDuplicate($db,$member_id);
if ($check_result == true) {
	$db->begin_transaction();

	try {
		/* 2. 회원 정보 등록처리 */
		$db->insert(
			"MEMBER_".$country,
			array(
				'COUNTRY'				=> $country,
				'MEMBER_STATUS'			=> 'NML',
				'MEMBER_ID'				=> $member_id,
				'MEMBER_PW'				=> md5($member_pw),
				'MEMBER_NAME'			=> $member_name,
				//'COUNTRY_CODE'			=> $country_code,
				'TEL_MOBILE'			=> $tel_mobile,
				//'MEMBER_GENDER'			=> $gender,
				//'MEMBER_BIRTH'			=> $birth_year."-".$birth_month."-".$birth_day,
				'JOIN_DATE'				=> NOW()
			)
		);

		/* 신규 회원 IDX */
		$member_idx = $db->last_id();	

        /* 4. 가입 적립금 등록 */
        $mileage = ($country != "KR") ? 5 : 5000;        
        $db->insert(
            "MILEAGE_INFO",
            array(
                'COUNTRY'				=>$country,
                'MEMBER_IDX'			=>$member_idx,
                'ID'					=>$member_id,
                'MILEAGE_CODE'			=>'NEW',
                'MILEAGE_UNUSABLE'		=>0,
                'MILEAGE_USABLE_INC'	=>$mileage,
                'MILEAGE_USABLE_DEC'	=>0,
                'MILEAGE_BALANCE'		=>$mileage,
                'CREATER'				=>'system',
                'UPDATER'				=>'system',
            )
        );

		/* 메일 데이터 PARAM 설정처리 */
		$param_mail_data = array(
			'member_name'	=>$member_name,
			'member_id'		=>$member_id
		);
		
		/* 자동메일 발송설정 체크처리 */
		$mail_setting = checkMailSetting($db,$country,"MAIL_CODE_0001");
		if (isset($mail_setting['template_member']) && $mail_setting['template_member'] != null) {
			/* 메일 발송 */
			//sendMail($db,$country,"M",$member_idx,null,$mail_setting['template_member'],$param_mail_data);
		}
		
		if (isset($mail_setting['template_admin']) && $mail_setting['template_admin'] != null) {
			/* 메일 발송 */
			//sendMail($db,$country,"A","/member/info",null,$mail_setting['template_member'],$param_mail_data);
		}
		
		$db->commit();
	} catch (mysqli_sql_exception $e) {
		$db->rollback();
		
		print_r($e);
		
		$code = 401;
		$msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0101', array());		
	}
} 
else {
	$code = 303;
	$msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0059', array());	
}

/* 1. 동일 ID 중복체크 */
function checkDuplicate($db,$member_id) {
	$check_result = false;
	
	$cnt_member = 0;
	
	$cnt_member += $db->count("MEMBER_KR","MEMBER_ID = '".$member_id."'");
	$cnt_member += $db->count("MEMBER_EN","MEMBER_ID = '".$member_id."'");
	$cnt_member += $db->count("MEMBER_CN","MEMBER_ID = '".$member_id."'");
	
	if ($cnt_member == 0) {
		$check_result = true;
	}
	
	return $check_result;
}

/* 3-3. 회원 기본 배송지 등록 */
function addDefaultOrderTo($db,$country,$param) {
	if ($country == "KR") {
		/* 국내몰 배송지 등록 */
		$db->insert(
			"ORDER_TO",
			array(
				'COUNTRY'			=>$country,
				'MEMBER_IDX'		=>$param['member_idx'],
				'TO_PLACE'			=>$param['default_name'],
				'TO_NAME'			=>$param['member_name'],
				'TO_MOBILE'			=>$param['tel_mobile'],
				'TO_ZIPCODE'		=>$param['zipcode'],
				
				'TO_LOT_ADDR'		=>$param['lot_addr'],
				'TO_ROAD_ADDR'		=>$param['road_addr'],
				'TO_DETAIL_ADDR'	=>$param['addr_detail'],
				
				'DEFAULT_FLG'		=>1
			)
		);
	} else {
		/* 영문몰,중문몰 배송지 등록 */
		$db->insert(
			"ORDER_TO",
			array(
				'COUNTRY'			=>$country,
				'MEMBER_IDX'		=>$param['member_idx'],
				'TO_PLACE'			=>$param['default_name'],
				'TO_NAME'			=>$param['member_name'],
				'TO_MOBILE'			=>$param['tel_mobile'],
				'TO_ZIPCODE'		=>$param['zipcode'],
				
				'TO_COUNTRY_CODE'	=>$param['country_code'],
				'TO_PROVINCE_IDX'	=>$param['province_idx'],
				'TO_CITY'			=>$param['city'],
				'TO_ADDRESS'		=>$param['address'],
				
				'DEFAULT_FLG'		=>1
			)
		);
	}
}
