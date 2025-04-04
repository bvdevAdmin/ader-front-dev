<?php
/*
 +=============================================================================
 | 
 | SENS - 알림톡 발송
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

function setSENS_KAKAO($db,$param_kakao) {
	$api_access_key = "HCg2ZRAMIocP6NsIWz46";
	$api_secret_key = "TZr46UcJztzTE4D4wMrEMrE706pGdVq2fpv4LotV";
	
	$service_id = "ncp:kkobizmsg:kr:3236210:ader_shopping";
	
	$reserve_time = date('YYYY-MM-dd HH:mm');
	$timestamp = time($reserve_time);
	
	/*
	$country			= $param_kakao['country'];
	$plus_friend_id		= $param_kakao['plus_friend_id'];
	$template_code		= $param_kakao['template_code'];
	$messages			= $param_kakao['message'];
	
	$message_to			= $param_kakao['message_to'];
	$message_content	= $param_kakao['message_content'];
	*/
	
	$country = "KR";
	$kakao_code = "KAKAO_ORD_0001";
	
	/* SENS PUSH - 디바이스 토큰 조회 PARAM */
	$param_send_kakao = array(
		'country'			=>$country,
		'plus_friend_id'	=>$plus_friend_id,
		'template_code'		=>$template_code,
		'message'			=>$message,
		
		'message_to'		=>$message_to,
		'message_content'	=>$message_content
	);
	
	/* 알림톡 컨텐츠 조회 */
	$kakao_info = $db->get(
		"KAKAO_INFO",
		"
			KAKAO_CODE = ?
		",
		array(
			$kakao_code
		)
	)[0];
	
	$kakao_title = $kakao_info['KAKAO_ID_'.$country];
	$kakao_content = $kakao_info['KAKAO_CONTENT_'.$country];
	
	if (strlen($kakao_title) > 0 && strlen($kakao_content) > 0) {
		$param_send_kakao['kakao_content'] = $kakao_content;
		
		$result = sendSENS_KAKAO($param_send_kakao);
	}
}

function sendSENS_KAKAO($param) {
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://sens.apigw.ntruss.com/alimtalk/v2/services/".$param['service_id']."/messages",
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"POST",
		CURLOPT_POSTFIELDS		=>'
			{
				"plusFriendId":"'.$param['plus_friend_id'].'",
				"templateCode":"'.$param['template_code'].'",
				"messages":[
					{
						"countryCode":"string",
						"to":"string",
						"title":"string",
						"content":"string",
						"headerContent":"string",
						"itemHighlight":{
							"title":"string",
							"description":"string"
						},
						"item":{
							"list":[
								{
									"title":"string",
									"description":"string"
								}
							],
							"summary":{
								"title":"string",
								"description":"string"
							}
						},
						"buttons":[
							{
								"type":"string",
								"name":"string",
								"linkMobile":"string",
								"linkPc":"string",
								"schemeIos":"string",
								"schemeAndroid":"string"
							}
						],
						"useSmsFailover": "boolean",
						"failoverConfig": {
							"type": "string",
							"from": "string",
							"subject": "string",
							"content": "string"
						}
					}
				],
				"reserveTime": "yyyy-MM-dd HH:mm",
				"reserveTimeZone": "string"
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