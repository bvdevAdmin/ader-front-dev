<?php
/*
 +=============================================================================
 | 
 | 회원 가입 - 신규 회원 가입
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
include_once(dir_f_api."/send/send-mail.php");
include_once(dir_f_api."/send/send-kakao.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* 1. 동일 ID 중복체크 */
$check_result = checkDuplicate($db,$member_id);
if ($check_result == true) {
	/* PARAM 회원 ID */
	$param_member_id = array();
	if ($member_id != null) {
		$param_member_id[0] = 'MEMBER_ID';
		$param_member_id[1] = $member_id;
	}
	
	/* PARAM 회원 PW */
	$param_member_pw = array();
	if ($member_pw != null) {
		$param_member_pw[0] = 'MEMBER_PW';
		$param_member_pw[1] = md5($member_pw);
	}
	
	/* PARAM 회원 이름 */
	$param_member_name = array();
	if ($member_name != null) {
		$param_member_name[0] = 'MEMBER_NAME';
		$param_member_name[1] = $member_name;
	}
	
	/* PARAM 회원 주소정보 */
	$param_lot_addr = array();
	$param_road_addr = array();
	$param_detail_addr = array();
	
	/* 쇼핑몰별 주소정보 설정 */
	if($country == 'KR'){
		/* 한국몰 주소정보 설정 */
		if ($lot_addr != null) {
			$param_lot_addr[0] = 'LOT_ADDR';
			$param_lot_addr[1] = $lot_addr;
		}
		
		if ($road_addr != null) {
			$param_road_addr[0] = 'ROAD_ADDR';
			$param_road_addr[1] = $road_addr;
		}
		
		if ($addr_detail != null) {
			$param_detail_addr[0] = 'DETAIL_ADDR';
			$param_detail_addr[1] = $addr_detail;
		}
	} else if ($country == 'EN' || $country == 'CN'){
		/* 영문/중문 몰 주소정보 설정 */
		$country_name = $db->get('COUNTRY_INFO','COUNTRY_CODE = ?', array($country_code))[0]['COUNTRY_NAME'];
		
		$province_name = '';
		if($province_idx != 0){
			$province_name = $db->get('PROVINCE_INFO','IDX = ?', array($province_idx))[0]['PROVINCE_NAME'];
		}
		
		$param_lot_addr[0] = 'LOT_ADDR';
		$param_lot_addr[1] = $country_name." ".$province_name." ".$city;
		
		$param_road_addr[0] = 'ROAD_ADDR';
		$param_road_addr[1] = $country_name." ".$province_name." ".$city;
		
		if ($address != null) {
			$param_detail_addr[0] = 'DETAIL_ADDR';
			$param_detail_addr[1] = $address;
		}
	}
	
	/* PARAM 회원 우편번호 */
	$param_zipcode = array();
	if ($zipcode != null) {
		$param_zipcode[0] = 'ZIPCODE';
		$param_zipcode[1] = $zipcode;
	}
	
	/* PARAM 회원 휴대전화 */
	$param_tel_mobile = array();
	if ($tel_mobile != null) {
		$param_tel_mobile[0] = 'TEL_MOBILE';
		$param_tel_mobile[1] = $tel_mobile;
	}
	
	/* PARAM 회원 생년월일 */
	$param_member_birth = array();
	if($birth_year != null && $birth_month != null && $birth_day != null){
		$param_member_birth[0] = "MEMBER_BIRTH";
		$param_member_birth[1] = $birth_year."-".$birth_month."-".$birth_day;
	}
	
	/* PARAM 회원 성별 */
	$param_gender = array();
	if($gender != null){
		$param_gender[0] = 'MEMBER_GENDER';
		$param_gender[1] = $gender;
	}
	
	/* PARAM 회원 국가코드 */
	$country_code = "NULL";
	if (isset($_POST['country_code'])) {
		$country_code = $_POST['country_code'];
	}
	
	$param_country = array();
	$param_country[0] = 'COUNTRY_CODE';
	$param_country[1] = $country_code;

	$db->begin_transaction();

	try {
		/* 2. 회원 정보 등록처리 */
		$db->insert(
			"MEMBER_".$country,
			array(
				'COUNTRY'				=>$country,
				'MEMBER_STATUS'			=>'NML',
				$param_member_id[0]		=>$param_member_id[1],
				$param_member_pw[0]		=>$param_member_pw[1],
				$param_member_name[0]	=>$param_member_name[1],
				$param_lot_addr[0]		=>$param_lot_addr[1],
				$param_road_addr[0]		=>$param_road_addr[1],
				$param_detail_addr[0]	=>$param_detail_addr[1],
				$param_zipcode[0]		=>$param_zipcode[1],
				$param_country[0]		=>$param_country[1],
				$param_tel_mobile[0]	=>$param_tel_mobile[1],
				$param_gender[0]		=>$param_gender[1],
				$param_member_birth[0]	=>$param_member_birth[1],
				'JOIN_DATE'				=>NOW()
			)
		);
		
		/* 신규 회원 IDX */
		$member_idx = $db->last_id();
		if (!empty($member_idx)) {
			/* 3. 회원 기본 배송지 */
			
			/* 3-1. 회원 기본 배송지 이름 설정 */
			$default_name = '';
			switch($country){
				case 'KR':
					$default_name = '기본 배송지';
					break;
				case 'EN':
					$default_name = 'Default';
					break;
				case 'CN':
					$default_name = '基本配送地';
					break;
			}
			
			/* 3-2. 회원 배송지 정보 등록 PARAM 설정 */
			$param_order_to = null;
			/* 몰별 배송지 등록 */
			if ($country == "KR") {
				/* 한국몰 회원 배송지 정보 등록 PARAM 설정 */
				$param_order_to = array(
					'country'			=>$country,
					'member_idx'		=>$member_idx,
					'default_name'		=>$default_name,
					'member_name'		=>$member_name,
					'tel_mobile'		=>$tel_mobile,
					
					'zipcode'			=>$zipcode,
					'lot_addr'			=>$lot_addr,
					'road_addr'			=>$road_addr,
					'addr_detail'		=>$addr_detail
				);
				
			} else {
				/* 영문/중문 몰 회원 배송지 정보 등록 PARAM 설정 */
				$param_order_to = array(
					'country'			=>$country,
					'member_idx'		=>$member_idx,
					'default_name'		=>$default_name,
					'member_name'		=>$member_name,
					'tel_mobile'		=>$tel_mobile,
					
					'zipcode'			=>$zipcode,
					'country_code'		=>$country_code,
					'province_idx'		=>$province_idx,
					'city'				=>$city,
					'address'			=>$address
				);
			}
			
			/* 3-3. 회원 기본 배송지 등록 */
			addDefaultOrderTo($db,$country,$param_order_to);
			
			/* 4. 가입 적립금 등록 */
			addMileageInfo($db,$country,$member_idx,$member_id);
		}
		
		$db->commit();
		
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
	} catch (mysqli_sql_exception $e) {
		$db->rollback();
		
		print_r($e);
		
		$json_result['code'] = 401;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0101', array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 303;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0059', array());
	
	echo json_encode($json_result);
	exit;
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