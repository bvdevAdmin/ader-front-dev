<?php
/*
 +=============================================================================
 | 
 | SENS - 한국몰 휴대전화 SMS 발송
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

include_once(dir_f_api."/send/send-common.php");

if ($tel_mobile != null) {
	$tel_mobile = str_replace("-","",$tel_mobile);
	
	$auth_no = makeAUTH();
	
	$setting_sms = array(
		'domain'			=>$domain_sens,
		'service_id'		=>$service_sms,
		
		'api_access_key'	=>$api_access_key,
		'api_secret_key'	=>$api_secret_key
	);
	
	sendSMS($db,$setting_sms,$tel_mobile,$auth_no);
}

function sendSMS($db,$setting,$tel_mobile,$auth_no) {
	$send_result = false;
	
	$method			= "POST";
	$uri			= "/sms/v2/services/".$setting['service_id']."/messages";
	$timestamp		= getTimestamp();
	
	$signature		= makeSignature($method,$timestamp,$uri,$setting['api_access_key'],$setting['api_secret_key']);
	
	$send_date		= date('Y-m-d H:i');
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>$setting['domain'].$uri,
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>$method,
		CURLOPT_POSTFIELDS		=>'
			{
				"type":"SMS",
				"contentType":"COMM",
				"countryCode":"82",
				"from":"027922232",
				"content":"[ADER] 확인 인증번호는 ['.$auth_no.']입니다. 정확히 입력해주세요.",
				"messages":[
					{
						"to":"'.$tel_mobile.'"
					}
				]
			}
		',
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$setting['api_access_key'],
			"x-ncp-apigw-signature-v2:".$signature
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	curl_close($curl);
	
	if (!$err) {
		$result = json_decode($response,true);
		
		$status_code = 400;
		if (isset($result['statusCode'])) {
			$status_code = $result['statusCode'];
			
			$country = "KR";
			setMEMBER_auth($db,$auth_no);
		}
		
		$json_result['code'] = $status_code;
		
		echo json_encode($json_result);
		exit;
	}
}

function makeAUTH(){
    return mt_rand(100000,999999);
}

function setMEMBER_auth($db,$auth_no) {
	$update_member_sql = "
		UPDATE
			MEMBER_KR
		SET
			AUTH_NO		= ?,
			AUTH_DATE	= DATE_ADD(NOW(), INTERVAL 10 MINUTE)
		WHERE
			IDX = ?
	";
	
	$db->query($update_member_sql,array($auth_no,$_SESSION['MEMBER_IDX']));
}

?>