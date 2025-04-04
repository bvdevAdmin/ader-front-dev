<?php
/*
 +=============================================================================
 | 
 | 회원 로그인 - 간편 로그인
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
include_once(dir_f_api."/send/send-kakao.php");

$country = null;
if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

$return_url = null;
if (isset($_POST['r_url'])) {
	$return_url = $r_url;
}

if ($sns_type != null && $account_key != null) {
	if ($sns_type == "NAVER" || $sns_type == "KAKAO") {
		$tel_mobile = str_replace("+82 ","0",$tel_mobile);
	}
	
	$column_key = "";	
	
	switch ($sns_type) {
		case "NAVER" :
			$column_key = "NAVER_ACCOUNT_KEY";
			
			$cnt_member = $db->count("MEMBER_KR","NAVER_ACCOUNT_KEY = ? AND TEL_MOBILE = ?",array($account_key,$tel_mobile));
			if ($cnt_member == 0) {
				$db->update(
					"MEMBER_KR",
					array(
						'NAVER_ACCOUNT_KEY'		=>$account_key
					),
					"TEL_MOBILE = ?",
					array($tel_mobile)
				);
			}
			
			break;
		
		case "KAKAO" :
			$column_key = "KAKAO_ACCOUNT_KEY";
			
			$cnt_member = $db->count("MEMBER_KR","KAKAO_ACCOUNT_KEY = ? AND TEL_MOBILE = ?",array($account_key,$tel_mobile));
			if ($cnt_member == 0) {
				$db->update(
					"MEMBER_KR",
					array(
						'KAKAO_ACCOUNT_KEY'		=>$account_key
					),
					"TEL_MOBILE = ?",
					array($tel_mobile)
				);
			}
			
			break;
		
		case "GOOGLE" :
			$column_key = "GOOGLE_ACCOUNT_KEY";
			
			$cnt_member = $db->count("MEMBER_KR","KAKAO_ACCOUNT_KEY = ? AND MEMBER_ID = ?",array($account_key,$member_id));
			if ($cnt_member == 0) {
				$db->update(
					"MEMBER_".$country,
					array(
						'GOOGLE_ACCOUNT_KEY'	=>$account_key
					),
					"MEMBER_ID = ?",
					array($member_id)
				);
			}
			break;
	}
	
	$param_select_sql = $column_key." = ?";
	
	/* 로그인 - 로그인 회원 생일 바우처 발급일 정보 조회 */
	$param_date = getBirthDateParam($db,$country);
	
	$param_date_sql = "";
	
	if ($param_date != null) {
		$param_date_sql = "
			, CASE
				WHEN MB.MEMBER_BIRTH = '0000-00-00'
				THEN NULL
				ELSE DATE_FORMAT(
					DATE_SUB(
						MB.MEMBER_BIRTH,
						INTERVAL {$param_date['date_ago']} DAY
					),
					'%m-%d'
				)
			END AS USABLE_START_DATE
			, CASE
				WHEN MB.MEMBER_BIRTH = '0000-00-00'
				THEN NULL
				ELSE DATE_FORMAT(
					DATE_ADD(
						MB.MEMBER_BIRTH,
						INTERVAL {$param_date['date_later']} DAY
					),
					'%m-%d'
				)
			END AS USABLE_END_DATE
		";
	}
	
	/* 휴대전화 기준 회원정보 조회 */
	$cnt_member = $db->count("MEMBER_".$country,$column_key." = ?",array($account_key));
	if ($cnt_member > 0) {
		$member = getLoginMember($db,$country,$account_key,$param_select_sql,$param_date_sql);
		if ($member != null) {
			loginMember($db,$country,null,$member,$return_url);
		}
	} else {
		$table = "MEMBER_".$country;
		
		$db->insert(
			$table,
			array(
				'COUNTRY'			=>$country,
				'MEMBER_STATUS'		=>'NML',
				$column_key			=>$account_key,
				'MEMBER_ID'			=>$member_id,
				'MEMBER_NAME'		=>$member_name,
				'MEMBER_GENDER'		=>$gender?$gender:'N',
				'TEL_MOBILE'		=>$tel_mobile,
				'MEMBER_BIRTH'		=>$member_birth?$member_birth:null,
				'JOIN_DATE'			=>NOW()
			)
		);
		
		$member_idx = $db->last_id();
		if (!empty($member_idx)) {
			$member = getLoginMember($db,$country,$account_key,$param_select_sql,$param_date_sql);
			if ($member != null) {
				loginMember($db,$country,null,$member,$return_url);
			}
			
			/* 4. 가입 적립금 등록 */
			addMileageInfo($db,$country,$member_idx,$member_id);
			
			/* ========== NAVER CLOUD PLATFORM::신규가입 메일 발송 ========== */
			/* PARAM::MAIL */
			$param_mail = array(
				'country'		=>$country,
				'mail_type'		=>"M",
				'mail_code'		=>"MAIL_CODE_0001",
				
				'param_member'	=>$member_idx,
				'param_admin'	=>null
			);
			
			/* PARAM::MAIL DATA */
			$param_data = array(
				'member_name'	=>$member_name,
				'member_id'		=>$member_id
			);
			
			/* 신규가입 메일 발송 */
			callSEND_mail($db,$param_mail,$param_data);
			
			/* ========== NAVER CLOUD PLATFORM::신규가입 알림톡 발송 ========== */
			if ($country == "KR") {
				/* PARAM::KAKAO */
				$param_kakao = array(
					'kakao_code'	=>"KAKAO_CODE_0011",
					'member_idx'	=>$member_idx
				);
				
				/* PARAM::KAKAO DATA */
				$param_data = array(
					'data_type'		=>"MEMBER",
					'member_idx'	=>$member_idx
				);
				
				/* PARAM::DATA */
				$data_kakao = getDATA_kakao($db,$param_data);
				
				/* 신규가입 알림톡 발송 */
				callSEND_kakao($db,$param_kakao,$data_kakao);
			}
		}
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0004', array());
	
	echo json_encode($json_result);
	exit;
}

/* 4. 가입 적립금 등록 */
function addMileageInfo($db,$country,$member_idx,$member_id) {
	$mileage = 0;
	$mileage = "5000";
	if ($country != "KR") {
		$mileage = "5";
	}
	
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
}

?>
