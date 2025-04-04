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
include_once("/var/www/www/api/common/mail.php");
include_once("/var/www/www/api/common.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$country = null;
if (isset($_POST['country'])) {
	$country = $_POST['country'];
}

$member_id = null;
if (isset($_POST['member_id'])) {
	$member_id = $_POST['member_id'];
}

$member_pw = null;
if (isset($_POST['member_pw'])) {
	$member_pw = $_POST['member_pw'];
}

$member_name = null;
if (isset($_POST['member_name'])) {
	$member_name = $_POST['member_name'];
}

$zipcode = null;
if (isset($_POST['zipcode'])) {
	$zipcode = $_POST['zipcode'];
}

$lot_addr = null;
if (isset($_POST['lot_addr'])) {
	$lot_addr = $_POST['lot_addr'];
}

$road_addr = null;
if (isset($_POST['road_addr'])) {
	$road_addr = $_POST['road_addr'];
}

$addr_detail = null;
if (isset($_POST['addr_detail'])) {
	$addr_detail = $_POST['addr_detail'];
}
$country_code = null;
if (isset($_POST['country_code'])) {
	$country_code = $_POST['country_code'];
}
$province_idx = null;
if (isset($_POST['province_idx'])) {
	$province_idx = $_POST['province_idx'];
}
$city = null;
if (isset($_POST['city'])) {
	$city = $_POST['city'];
}
$address = null;
if (isset($_POST['address'])) {
	$address = $_POST['address'];
}
$tel_mobile = null;
if (isset($_POST['tel_mobile'])) {
	$tel_mobile = $_POST['tel_mobile'];
}

$birth_year = null;
if (isset($_POST['birth_year'])) {
	$birth_year = $_POST['birth_year'];
}

$birth_month = null;
if (isset($_POST['birth_month'])) {
	$birth_month = $_POST['birth_month'];
}

$birth_day = null;
if (isset($_POST['birth_day'])) {
	$birth_day = $_POST['birth_day'];
}

$gender = null;
if (isset($_POST['gender'])) {
	$gender = $_POST['gender'];
}

//동일 ID 중복체크
$member_kr_cnt = 0;
$member_en_cnt = 0;
$member_cn_cnt = 0;
$member_total_cnt = 0;

$member_kr_cnt = $db->count('MEMBER_KR', 'MEMBER_ID = "'.$member_id.'" ');
$member_en_cnt = $db->count('MEMBER_EN', 'MEMBER_ID = "'.$member_id.'" ');
$member_cn_cnt = $db->count('MEMBER_CN', 'MEMBER_ID = "'.$member_id.'" ');

$member_total_cnt = $member_kr_cnt + $member_en_cnt + $member_cn_cnt;

if($member_total_cnt > 0){
	$json_result['code'] = 303;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0059', array());
	
	echo json_encode($json_result);
	exit;
}

$member_id_arr = array();
if ($member_id != null) {
	$member_id_arr[0] = 'MEMBER_ID';
	$member_id_arr[1] = $member_id;
}

$member_pw_arr = array();
if ($member_pw != null) {
	$member_pw_arr[0] = 'MEMBER_PW';
	$member_pw_arr[1] = md5($member_pw);
}

$member_name_arr = array();
if ($member_name != null) {
	$member_name_arr[0] = 'MEMBER_NAME';
	$member_name_arr[1] = $member_name;
}

$lot_addr_arr = array();
$road_addr_arr = array();
$addr_detail_arr = array();

if($country == 'KR'){
	if ($lot_addr != null) {
		$lot_addr_arr[0] = 'LOT_ADDR';
		$lot_addr_arr[1] = $lot_addr;
	}
	
	if ($road_addr != null) {
		$road_addr_arr[0] = 'ROAD_ADDR';
		$road_addr_arr[1] = $road_addr;
	}
	
	if ($addr_detail != null) {
		$addr_detail_arr[0] = 'DETAIL_ADDR';
		$addr_detail_arr[1] = $addr_detail;
	}
}
else if($country == 'EN' || $country == 'CN'){
	$country_name = $db->get('COUNTRY_INFO','COUNTRY_CODE = ?', array($country_code))[0]['COUNTRY_NAME'];
	$province_name = '';
	if($province_idx != 0){
		$province_name = $db->get('PROVINCE_INFO','IDX = ?', array($province_idx))[0]['PROVINCE_NAME'];
	}

	$lot_addr_arr[0] = 'LOT_ADDR';
	$lot_addr_arr[1] = $country_name." ".$province_name." ".$city;
	
	$road_addr_arr[0] = 'ROAD_ADDR';
	$road_addr_arr[1] = $country_name." ".$province_name." ".$city;
	
	if ($address != null) {
		$addr_detail_arr[0] = 'DETAIL_ADDR';
		$addr_detail_arr[1] = $address;
	}
}

$zipcode_arr = array();
if ($zipcode != null) {
	$zipcode_arr[0] = 'ZIPCODE';
	$zipcode_arr[1] = $zipcode;
}


$tel_mobile_arr = array();
if ($tel_mobile != null) {
	$tel_mobile_arr[0] = 'TEL_MOBILE';
	$tel_mobile_arr[1] = $tel_mobile;
}

$birth_arr = array();
if($birth_year != null && $birth_month != null && $birth_day != null){
	$birth_arr[0] = 'MEMBER_BIRTH';
	$birth_arr[1] = "DATE('".$birth_year."-".$birth_month."-".$birth_day."')";
}

$gender_arr = array();
if($gender != null){
	$gender_arr[0] = 'MEMBER_GENDER';
	$gender_arr[1] = $gender;
}

$country_code_arr = array();
$country_code_arr[0] = 'COUNTRY_CODE';
$country_code_arr[1] = $country_code;

$db->begin_transaction();

try {
	/*
	$insert_member_sql = "
		INSERT INTO
			MEMBER_".$country."
		(   
			COUNTRY,
			MEMBER_STATUS,
			".$member_id_arr[0]."
			".$member_pw_arr[0]."
			".$member_name_arr[0]."
			".$lot_addr_arr[0]."
			".$road_addr_arr[0]."
			".$addr_detail_arr[0]."
			".$zipcode_arr[0]."
			".$country_code_arr[0]."
			".$tel_mobile_arr[0]."
			".$gender_arr[0]."
			".$birth_arr[0].",
			JOIN_DATE
		)
		VALUES
		(
			'".$country."',
			'NML',
			".$member_id_arr[1]."
			".$member_pw_arr[1]."
			".$member_name_arr[1]."
			".$lot_addr_arr[1]."
			".$road_addr_arr[1]."
			".$addr_detail_arr[1]."
			".$zipcode_arr[1]."
			".$country_code_arr[1]."
			".$tel_mobile_arr[1]."
			".$gender_arr[1]."
			".$birth_arr[1].",
			NOW()
		)
	";
	
	$db->query($insert_member_sql);
	*/
	
	//회원 정보 등록
	$db->insert(
		"MEMBER_".$country,
		array(
			'COUNTRY'				=>$country,
			'MEMBER_STATUS'			=>'NML',
			$member_id_arr[0]		=>$member_id_arr[1],
			$member_pw_arr[0]		=>$member_pw_arr[1],
			$member_name_arr[0]		=>$member_name_arr[1],
			$lot_addr_arr[0]		=>$lot_addr_arr[1],
			$road_addr_arr[0]		=>$road_addr_arr[1],
			$addr_detail_arr[0]		=>$addr_detail_arr[1],
			$zipcode_arr[0]			=>$zipcode_arr[1],
			$country_code_arr[0]	=>$country_code_arr[1],
			$tel_mobile_arr[0]		=>$tel_mobile_arr[1],
			$gender_arr[0]			=>$gender_arr[1],
			$birth_arr[0]			=>$birth_arr[1],
			'JOIN_DATE'				=>NOW()
		)
	);
	
	$member_idx = $db->last_id();
	
	if (!empty($member_idx)) {
		$defaultStr = '';
		switch($country){
			case 'KR':
				$defaultStr = '기본 배송지';
				break;
			case 'EN':
				$defaultStr = 'Default';
				break;
			case 'CN':
				$defaultStr = '基本配送地';
				break;
		}
		
		$insert_order_to_sql = "
			INSERT INTO
				ORDER_TO
			(
				COUNTRY,
				MEMBER_IDX,
				TO_PLACE,
				TO_NAME,
				TO_MOBILE,
				TO_ZIPCODE,
		";
		
		if($country == "KR"){
			$insert_order_to_sql .= "
				TO_LOT_ADDR,
				TO_ROAD_ADDR,
				TO_DETAIL_ADDR,
			";
		}
		else{
			$insert_order_to_sql .= "
				TO_COUNTRY_CODE,
				TO_PROVINCE_IDX,
				TO_CITY,
				TO_ADDRESS,
			";
		}
		
		$insert_order_to_sql .= "
				DEFAULT_FLG
			) VALUES (
				'".$country."',
				".$member_idx.",
				'".$defaultStr."',
				'".$member_name."',
				'".$tel_mobile."',
				'".$zipcode."',";
		if($country == "KR"){
			$insert_order_to_sql .= "
				'".$lot_addr."',
				'".$road_addr."',
				'".$addr_detail."',
			";
		}
		else{
			$insert_order_to_sql .= "
				'".$country_code."',
				".$province_idx.",
				'".$city."',
				'".$address."',
			";
		}
		$insert_order_to_sql .= "
				TRUE
			);
		";
		$db->query($insert_order_to_sql);

		$insert_join_mileage_sql = "
			INSERT INTO
				MILEAGE_INFO
			(
				COUNTRY,
				MEMBER_IDX,
				ID,
				MILEAGE_CODE,
				MILEAGE_UNUSABLE,
				MILEAGE_USABLE_INC,
				MILEAGE_USABLE_DEC,
				MILEAGE_BALANCE,
				CREATER,
				UPDATER
			) 
			VALUES(
				'".$country."',
				".$member_idx.",
				'".$member_id."',
				'NEW',
				0,
				5000,
				0,
				5000,
				'System',
				'System'
			)
		";
		$db->query($insert_join_mileage_sql);
	}
	$mapping_arr = array();
    $mapping_arr[$member_idx]['member_id'] = $member_id;
    $mapping_arr[$member_idx]['member_name'] = $member_name;
    checkMailStatus($db, $country, 'MAIL_CASE_0001', $member_idx, $member_id, $mapping_arr);
	
	
	//joinMailSet($member_id, $member_name);
	$db->commit();
} catch (mysqli_sql_exception $exception) {
	$db->rollback();
	print_r($exception);
	
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0101', array());
	
	echo json_encode($json_result);
	exit;
}

?>