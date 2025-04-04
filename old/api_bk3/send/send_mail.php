<?php
/*
 +=============================================================================
 | 
 | COM - MAIL API (메일 단건 발송)
 | -----------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.03.07
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/admin/api/send/send-common.php");

/*
메일유형, 메일코드, 메일 데이터를 기준으로 MAIL 발송
*/

$country = "KR";

$param_receiver = null;

//$mail_type		= $_POST['mail_type'];		//메일 유형 - M : 회원 / A : 관리자
$mail_type = "M";
if ($mail_type == "M") {
	$param_receiver = 19;
	if (isset($_POST['member_idx'])) {
		$param_receiver = $_SESSION['MEMBER_IDX'];
	}
} else if ($mail_type == "A") {	
	if (isset($_POST['url_wcc'])) {
		$param_receiver = $_POST['url_wcc'];
	}
}

//$mail_code		= $_POST['mail_code'];		//메일 코드
$mail_code = "MAIL_CODE_0001";
//$data_mail		= $_POST['data_mail'];		//메일 데이터
$data_mail = array(
	'member_id'		=>"shson@bvdev.co.kr",
	'member_name'	=>"손성환"
);

/* 1. MAIL template id 조회 */
$template_id = getTemplateID($db,$country,$mail_type,$mail_code);
if ($template_id != null && $template_id != "00000") {
	/* 2. MAIL 발송 대상 회원 PARAM */
	$receiver_mail = getRECEIVER_mail($db,$country,$mail_type,$param_receiver);
	if ($receiver_mail != null) {
		/* 3. MAIL 발송용 세팅 설정 */
		$setting_mail = setSETTING_mail($api_access_key,$api_secret_key);
		
		/* 4. MAIL POSTFIELD 설정 */
		$post_field_mail = setPOSTFIELD_mail($template_id,$receiver_mail,$data_mail);
		
		if ($setting_mail != null && ($post_field_mail != null && count($post_field_mail) > 0)) {
			/* 5. MAIL 발송 */
			sendMAIL($setting_mail,$post_field_mail);
		} else {
			/* MAIL 발송 정보 설정 실패 예외처리 */
			$json_result['code'] = 302;
			$json_result['msg'] = "메일 발송 정보 설정중 오류가 발생했습니다.";
		}
	} else {
		/* MAIL 발송 대상 조회 실패 예외처리 */
		$json_result['code'] = 301;
		$json_result['msg'] = "메일 발송 대상 조회처리중 오류가 발생했습니다.";
	}
} else {
	/* MAIL 템플릿 미설정 예외처리 */
	$json_result['code'] = 300;
	$json_result['msg'] = "메일 템플릿 조회처리중 오류가 발생했습니다.";
}

/* 1. MAIL template id 조회 */
function getTemplateID($db,$country,$mail_type,$mail_code) {
	$template_id = null;
	
	$select_template_id_sql = "
		SELECT
			TEMPLATE_MEMBER_".$country."	AS TEMPLATE_MEMBER
		FROM
			MAIL_SETTING MS
		WHERE
			MS.MAIL_CODE = ? AND
			MEMBER_FLG = TRUE
		
		UNION
		
		SELECT
			TEMPLATE_ADMIN_".$country."		AS TEMPLATE_ADMIN
		FROM
			MAIL_SETTING MS
		WHERE
			MS.MAIL_CODE = ? AND
			ADMIN_FLG = TRUE
	";
	
	$db->query($select_template_id_sql,array($mail_code,$mail_code));
	
	foreach($db->fetch() as $data) {
		if ($mail_type == "M") {
			$template_id = $data['TEMPLATE_MEMBER'];
		} else if ($mail_type == "A") {
			$template_id = $data['TEMPLATE_ADMIN'];
		}
	}
	
	return $template_id;
}

/* 2. MAIL 발송 대상 회원 PARAM */
function getRECEIVER_mail($db,$country,$mail_type,$param) {
	$receiver_mail = null;
	
	if ($mail_type == "M") {
		/* 2-1. MAIL 발송 대상 회원 조회 */
		$receiver_mail = getRECEIVER_member($db,$country,$param);
	} else if ($mail_type = "A") {
		/* 2-2. MAIL 발송 대상 관리자 조회 */
		$receiver_mail = getRECEIVER_admin($db,$param);
	}
	
	return $receiver_mail;
}

/* 2-1. MAIL 발송 대상 회원 조회 */
function getRECEIVER_member($db,$country,$param) {
	$receiver_mail = null;
	
	$select_receiver_member_sql = "
		SELECT
			MB.MEMBER_ID	AS MEMBER_ID,
			MB.MEMBER_NAME	AS MEMBER_NAME,
			REPLACE(
				MB.TEL_MOBILE,'-',''
			)				AS TEL_MOBILE
		FROM
			MEMBER_".$country." MB
		WHERE
			MB.IDX = ?
	";
	
	$db->query($select_receiver_member_sql,array($param));
	
	foreach($db->fetch() as $data) {
		$receiver_mail[] = array(
			'user_email'		=>$data['MEMBER_ID'],
			'user_name'			=>$data['MEMBER_NAME'],
			'tel_mobile'		=>$data['TEL_MOBILE']
		);
	}
	
	return $receiver_mail;
}

/* 2-2. MAIL 발송 대상 관리자 조회 */
function getRECEIVER_admin($db,$param) {
	$receiver_mail = null;
	
	$select_receiver_admin_sql = "
		SELECT
			AD.ADMIN_EMAIL		AS ADMIN_EMAIL,
			AD.ADMIN_NAME		AS ADMIN_NAME,
			REPLACE(
				AD.TEL_MOBILE,'-',''
			)					AS TEL_MOBILE
		FROM
			PERMITION_MAPPING PM
			LEFT JOIN ADMIN AD ON
			PM.ADMIN_IDX = AD.IDX
			
			LEFT JOIN ADMIN_PERMITION AP ON
			PM.PERMITION_IDX = AP.IDX
		WHERE
			AP.PERMITION_URL = ?
	";
	
	$db->query($select_receiver_admin_sql,array($param_receiver));
	
	foreach($db->fetch() as $data) {
		$receiver_mail[] = array(
			'user_email'		=>$data['ADMIN_EMAIL'],
			'user_name'			=>$data['ADMIN_NAME'],
			'tel_mobile'		=>$data['TEL_MOBILE']
		);
	}
	
	return $receiver_mail;
}

/* 3. MAIL 발송용 세팅 설정 */
function setSETTING_mail($access_key,$secret_key) {
	$setting_mail = null;
	
	/* NAVER CLOUD 채널 정보 조회 */
	$channel	= checkChannel();
	
	/* NAVER CLOUD 콜백 엔드포인트 */
	$end_point = $channel['callbackEndpoint'];
	if ($end_point != null) {
		/* 메일 발송용 세팅 설정 */
		$setting_mail = array(
			'domain'			=>"https://livestation.apigw.ntruss.com",
			'end_point'			=>$end_point,
			'api_access_key'	=>$access_key,
			'api_secret_key'	=>$secret_key
		);
	}
	
	return $setting_mail;
}

/* 4. MAIL POSTFIELD 설정 */
function setPOSTFIELD_mail($template_id,$receiver,$data) {
	$post_field_mail = array();
	
	$recipients_mail = setRECIPIENTS_mail($receiver,$data);
	
	$post_field_mail = '
		{
			"templateSid" : '.$template_id.',
			"recipients": [
				'.implode(",",$recipients_mail).'
			],
			"individual" : true,
			"advertising" : false
		}
	';
	
	return $post_field_mail;
}

/* 4-1. MAIL recipients 설정 */
function setRECIPIENTS_mail($receiver,$data) {
	$recipients_mail = array();
	
	$parameters = "";
	
	$mail_param = array();
	if ($data != null && count($data) > 0) {
		$key_data = array_keys($data);
		for ($i=0; $i<count($key_data); $i++) {
			$key = $key_data[$i];
			$val = $data[$i];
			
			$tmp_param = '"'.$key.'" : "'.$val.'"';
			array_push($mail_param,$tmp_param);
		}
	}
	
	if (count($mail_param) > 0) {
		$parameters = '
			,"parameters":{
				'.implode(",",$mail_param).'
			}
		';
	}
	
	foreach($receiver as $member) {
		$recipients = '
			{
				"address":"'.$member['user_email'].'",
				"name":"'.$member['user_name'].'",
				"type":"R"
				'.$parameters.'
			}
		';
		
		array_push($recipients_mail,$recipients);
	}
	
	return $recipients_mail;
}

/* 5. MAIL 발송 */
function sendMAIL($setting,$post_field) {
	$method			= "POST";
	$uri			= "/api/v1/mails";
	$timestamp		= getTimestamp();
	
	$signature		= makeSignature($method,$timestamp,$uri,$setting['api_access_key'],$setting['api_secret_key']);
	
	$curl = curl_init();
		
	curl_setopt_array($curl, [
		CURLOPT_URL				=>$setting['end_point'],
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>$method,
		CURLOPT_POSTFIELDS		=>$post_field,
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$setting['api_access_key'],
			"x-ncp-apigw-signature-v2:".$signature
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	if (!$err) {
		$result = json_decode($response,true);
		if (isset($result['requestId']) && isset($result['count']) && $result['count'] > 0) {
			$send_result = true;
		}
	}
}

?>