<?php
/*
 +=============================================================================
 | 
 | SENS - PUSH 발송
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

include_once("/var/www/www/api/send/send-common.php");

/* 발송 테스트용 임시 PARAM */
$country = "KR";
$push_code = "PUSH_ORD_0001";
$user_id = "shson@bvdev.co.kr";

/* PUSH 정보 조회 */
$push_info = $db->get(
	"PUSH_INFO",
	"
		PUSH_CODE = ?
	",
	array(
		$push_code
	)
)[0];

$push_title			= $push_info["PUSH_TITLE_".$country];
$push_content_mem	= $push_info['PUSH_CONTENT_MEM_'.$country];
$push_content_adm	= $push_info['PUSH_CONTENT_MEM_'.$country];

/*
$param_push = array(
	'domain'			=>$domain,
	'service_id'		=>$service_push,
	
	'user_id'			=>$user_id,
	'push_title'		=>$push_title,
	
	'api_access_key'	=>$api_access_key,
	'api_secret_key'	=>$api_secret_key
);

if ($push_content_mem != null) {
	$param_push['push_content'] = $push_content_mem;
	
	sendPUSH("01067364537",$param_push);
}

if ($push_content_adm != null) {
	$param_push['push_content'] = $push_content_adm;
	
	sendPUSH("01067364537",$param_push);
}
*/

$uri 			= "/push/v2/services/ncp:push:kr:323621061947:ader_shopping/users/shson@bvdev.co.kr";
$timestamp		= getTimestamp();

$signature		= makeSignature($timestamp,"/push/v2/services/ncp:push:kr:323621061947:ader_shopping/users",$api_access_key,$api_secret_key);

$param_check_token = array(
	'domain'			=>$domain,
	'uri'				=>$uri,
	
	'user_id'			=>$user_id,
	'timestamp'			=>$timestamp,
	
	'api_access_key'	=>$api_access_key,
	'api_secret_key'	=>$api_secret_key,
	'signature'			=>$signature
);

$device_token = checkDeviceToken($param_check_token);

/*
function sendPUSH($tel_mobile,$param) {
	$send_result = false;
	
	$uri			= "/push/v2/services/".$param['service_id']."/users";
	$timestamp		= getTimestamp();
	
	$reserve_time	= date('Y-m-d H:i:s');
	
	if ($device_token != null) {
		/* SENS PUSH - PUSH 발송 PARAM */
		/*
		$param_send_push = array(
			'service_id'				=>$service_id,
			
			'timestamp'					=>$timestamp,
			'api_access_key'			=>$api_access_key,
			'api_secret_key'			=>$api_secret_key,
		);
		
		/* SENS PUSH - PUSH 발송 PARAM 설정 */
		//$param_send_push['device_token'] = $device_token;
		
		/* PUSH 컨텐츠 조회 */
		/*
		$push_info = $db->get(
			"PUSH_INFO",
			"
				PUSH_CODE = ?
			",
			array(
				$push_code
			)
		)[0];
		
		$push_content = $push_info['PUSH_CONTENT_'.$country];
		if (strlen($push_content) > 0) {
			$param_send_push['push_content']	= $push_content;
			
			$result = sendSENS_PUSH($param_send_push);
		}
	}
}
*/

function checkDeviceToken($param) {
	$device_token = null;
	
	/* SENS PUSH - 디바이스 토큰 조회처리 */
	$device_token = getDeviceToken($param);
	
	/*
	if ($device_token == null) {
		/* SENS PUSH - 디바이스 토큰 등록 PARAM */
		/*
		$param_add_token = array(
			'service_id'				=>$service_id,
			
			'timestamp'					=>$timestamp,
			'api_access_key'			=>$api_access_key,
			'api_secret_key'			=>$api_secret_key,
			
			'user_id'					=>$user_id,
			
			'device_type'				=>$device_type,
			'device_token'				=>md5($user_id),
			
			'notification_agreement'	=>$notification_agreement,
			'ad_agreement'				=>$ad_agreement,
			'night_ad_agreement'		=>$night_ad_agreement
		);
		
		/* SENS PUSH - 디바이스 토큰 등록처리 */
		/*
		$device_token = addDeviceToken($param_add_token);
	}
	*/
	
	return $device_token;
}

/* SENS PUSH 디바이스 토큰 조회 */
function getDeviceToken($param) {
	$device_token = null;
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://sens.apigw.ntruss.com/push/v2/services/ncp:push:kr:323621061947:ader_shopping/users/shson@bvdev.co.kr",
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"GET",
		CURLOPT_POSTFIELDS		=>'',
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$param['timestamp'],
			"x-ncp-iam-access-key:".$param['api_access_key'],
			"x-ncp-apigw-signature-v2:".$param['signature']
		],
	]);

	$response = curl_exec($curl);
	print_r($response);
	$err = curl_error($curl);
	print_r($err);
	
	if (!$err) {
		$device_token = json_encode($response,true);
	}
	
	return $device_token;
}

/* SENS PUSH 디바이스 토큰 등록 */
/*
function addDeviceToken($param) {
	$device_token = null;
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>$param['domain'].$param['uri'],
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"POST",
		CURLOPT_POSTFIELDS		=>'
			{
				"userId":"'.$param['user_id'].'",
				
				"deviceType":"'.$param['device_type'].'",
				"deviceToken":"'.$param['device_token'].'",
				
				"isNotificationAgreement":'.$param['notification_agreement'].',
				"isAdAgreement":'.$param['ad_agreement'].',
				"isNightAdAgreement":'.$param['night_ad_agreement'].'
			}
		',
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$param['timestamp'],
			"x-ncp-iam-access-key:".$param['api_access_key'],
			"x-ncp-apigw-signature-v2:".$param['api_secret_key']
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	if (!$err) {
		/* SENS PUSH - 디바이스 토큰 조회 PARAM */
		/*
		$param_get_token = array(
			'service_id'		=>$param['service_id'],
			
			'timestamp'			=>$param['timestamp'],
			'api_access_key'	=>$param['api_access_key'],
			'api_secret_key'	=>$param['api_secret_key'],
			
			'user_id'			=>$param['user_id']
		);
		
		$device_token = getDeviceToken($param_get_token);
	}
	
	return $device_token;
}

function sendSENS_PUSH($param) {
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://sens.apigw.ntruss.com/push/v2/services/".$param['service_id']."/messages",
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"POST",
		CURLOPT_POSTFIELDS		=>'
			{
				"target": {
					"type": "USER",
				},
				"message": {
					"default": {
						"content": "'.$param['push_content'].'"
					}
				}
			}
		',
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$param['timestamp'],
			"x-ncp-iam-access-key:".$param['api_access_key'],
			"x-ncp-apigw-signature-v2:".$param['api_secret_key']
		],
	]);
}
*/

?>