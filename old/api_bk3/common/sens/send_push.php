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

function setSENS_PUSH($db,$param_push) {
	$api_access_key = "HCg2ZRAMIocP6NsIWz46";
	$api_secret_key = "TZr46UcJztzTE4D4wMrEMrE706pGdVq2fpv4LotV";
	
	$service_id = "ncp:push:kr:323621061947:ader_shopping";
	
	$reserve_time = date('YYYY-MM-dd HH:mm');
	$timestamp = time($reserve_time);
	
	/*
	$country						= $param_push['country'];
	$push_code						= $param_push['push_code'];
	$user_id						= $param_push['user_id'];
	$language						= $param_push['language'];
	$timezone						= $param_push['timezone'];
	$channel_name					= $param_push['channel_name'];
	$notification_agreement			= $param_push['notification_agreement'];
	$ad_agreement					= $param_push['ad_agreement'];
	$night_ad_agreement				= $param_push['night_ad_agreement'];
	$notification_agreement_time	= $param_push['notification_agreement_time'];
	$ad_agreement_time				= $param_push['ad_agreement_time'];
	$night_ad_agreement_time		= $param_push['night_ad_agreement_time'];
	$create_time					= $param_push['create_time'];
	$update_time					= $param_push['update_time'];
	$device_type					= $param_push['device_type'];
	*/
	
	$country = "KR";
	$push_code = "PUSH_ORD_0001";
	$user_id = "";
	
	/* SENS PUSH - 디바이스 토큰 조회 PARAM */
	$param_get_token = array(
		'service_id'		=>$service_id,
		
		'timestamp'			=>$timestamp,
		'api_access_key'	=>$api_access_key,
		'api_secret_key'	=>$api_secret_key,
		
		'user_id'			=>$user_id
	);
	
	/* SENS PUSH - 디바이스 토큰 조회처리 */
	$device_token = getDeviceToken($param_get_token);
	
	if ($device_token == null) {
		/* SENS PUSH - 디바이스 토큰 등록 PARAM */
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
		$device_token = addDeviceToken($param_add_token);
	}
	
	if ($device_token != null) {
		/* SENS PUSH - PUSH 발송 PARAM */
		$param_send_push = array(
			'service_id'				=>$service_id,
			
			'timestamp'					=>$timestamp,
			'api_access_key'			=>$api_access_key,
			'api_secret_key'			=>$api_secret_key,
		);
		
		/* SENS PUSH - PUSH 발송 PARAM 설정 */
		$param_send_push['device_token'] = $device_token;
		
		/* PUSH 컨텐츠 조회 */
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

/* SENS PUSH 디바이스 토큰 조회 */
function getDeviceToken($param) {
	$device_token = null;
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://sens.apigw.ntruss.com/push/v2/services/".$param['service_id']."/users/".$param['user_id'],
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
			"x-ncp-apigw-signature-v2:".$param['api_secret_key']
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	if (!$err) {
		$device_token = json_encode($response,true);
	}
	
	return $deivce_token;
}

/* SENS PUSH 디바이스 토큰 등록 */
function addDeviceToken($param) {
	$device_token = null;
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://sens.apigw.ntruss.com/push/v2/services/".$param['service_id']."/users",
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

?>