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

/* <----- 발송 테스트용 임시 PARAM */
$country	= "KR";
$push_type	= "MEM";
$push_code	= "PUSH_ORD_0001";

$member_idx	= array(1,2);

/* 회원 SMS 파라미터 */
$param_message = array(
	array(
		'member_name'	=>"손성환",
		'date'			=>"2024-04-09",
		'price'			=>"1,000,000",
		'order_code'	=>"202404091203",
		'order_title'	=>"테스트용 주문 타이틀",
		'delivery_num'	=>"111122223333456789000"
	),
	array(
		'member_name'	=>"손성환",
		'date'			=>"2024-04-09",
		'price'			=>"1,000,000",
		'order_code'	=>"202404091203",
		'order_title'	=>"테스트용 주문 타이틀",
		'delivery_num'	=>"111122223333456789000"
	)
);

/* 관리자 SMS 파라미터 */
/*
$param_message = array(
	'member_name'	=>"손성환",
	'date'			=>"2024-04-09",
	'price'			=>"1,000,000",
	'order_code'	=>"202404091203",
	'order_title'	=>"테스트용 주문 타이틀",
	'delivery_num'	=>"111122223333456789000"
);
*/

/* -----> */

if ($country != null && $push_type != null && $push_code != null) {
	$param_token = array(
		'domain'			=>$domain,
		'uri'				=>"/push/v2/services/".$service_push."/users",
		
		'user_id'			=>"shson",
		'device_type'		=>"APNS",
		
		'api_access_key'	=>$api_access_key,
		'api_secret_key'	=>$api_secret_key
	);
	
	checkDeviceToken($param_token);
}

function checkDeviceToken($param) {
	$device_token = null;
	
	/* SENS PUSH - 디바이스 토큰 조회처리 */
	getDeviceToken($param);
	//addDeviceToken($param);
}

function getDeviceToken($param) {
	print_r($param);
	$method			= "GET";
	$timestamp		= getTimestamp();
	
	$signature		= makeSignature($method,$timestamp,$param['uri'],$param['api_access_key'],$param['api_secret_key']);
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>$param['domain'].$param['uri']."/shson",
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"GET",
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$param['api_access_key'],
			"x-ncp-apigw-signature-v2:".$signature
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	print_r($response);
}

function addDeviceToken($param) {
	$method			= "POST";
	$timestamp		= getTimestamp();
	
	$signature		= makeSignature($method,$timestamp,$param['uri'],$param['api_access_key'],$param['api_secret_key']);
	
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
				"userId":"shson",
				"deviceType":"APNS",
				"deviceToken":"'.md5("shson").'",
				"isNotificationAgreement":true,
				"isAdAgreement":true,
				"isNightAdAgreement":true
			}
		',
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$param['api_access_key'],
			"x-ncp-apigw-signature-v2:".$signature
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	print_r($response);
	print_r($err);
}

?>