<?php
/*
 +=============================================================================
 | 
 | SENS - 공통함수
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

$api_access_key		= "HCg2ZRAMIocP6NsIWz46";
$api_secret_key		= "TZr46UcJztzTE4D4wMrEMrE706pGdVq2fpv4LotV";

$domain_sens		= "https://sens.apigw.ntruss.com";
$domain_mail		= "https://livestation.apigw.ntruss.com";

$service_sms		= "ncp:sms:kr:323621039649:ader_shopping";
$service_push		= "ncp:push:kr:323621061947:ader_shopping";
$service_kakao		= "ncp:kkobizmsg:kr:3236210:ader_shopping";

$project_id			= "ader_shopping";
$plus_friend_id		= "";

/* 1. 타임스탬프 값 취득 */
function getTimestamp() {
	$date = date('Y-m-d H:i:s',time());
	$microtime = microtime();
	$comps = explode(' ', $microtime);
	
	return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
}

/* 2. HEADER signature 인코딩 */
function makeSignature($method,$timestamp,$service_uri,$api_access_key,$api_secret_key) {
    $space		= " ";				//공백
    $newLine	= "\n";				//줄바꿈
    $method		= $method;			//HTTP 메소드
	$uri		= $service_uri;		//도메인을 제외한 "/" 아래 전체 url (쿼리스트링 포함)
    $timestamp	= $timestamp;		//현재 타임스탬프 (epoch, millisecond)
    $accessKey	= $api_access_key;	//access key id (from portal or sub account)
    $secretKey	= $api_secret_key;	//secret key (from portal or sub account)

    $hmac = $method.$space.$uri.$newLine.$timestamp.$newLine.$accessKey;
    $signautue = base64_encode(hash_hmac('sha256',$hmac,$secretKey,true));
	
    return $signautue;
}

/* 3. NAVER CLOUD 채널 정보 체크 */
function checkChannel() {
	$param_channel = array(
		'domain'			=>"https://livestation.apigw.ntruss.com",
		'uri'				=>"/api/v2/channels",
		'channel_name'		=>"ader_shopping",
		
		'api_access_key'	=>"HCg2ZRAMIocP6NsIWz46",
		'api_secret_key'	=>"TZr46UcJztzTE4D4wMrEMrE706pGdVq2fpv4LotV"
	);
	
	/* 3-1. NAVER CLOUD 채널 정보 체크 */
	$channel = getChannel($param_channel);
	if ($channel == null) {
		/* 3-2. NAVER CLOUD 채널 정보 등록 */
		$channel = addChannel($param_channel);
	}
	
	return $channel;
}

/* 3-1. NAVER CLOUD 채널 정보 체크 */
function getChannel($param) {
	$channel = null;
	
	$method			= "GET";
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
		CURLOPT_CUSTOMREQUEST	=>$method,
		CURLOPT_POSTFIELDS		=>"",
		CURLOPT_HTTPHEADER => [
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$param['api_access_key'],
			"x-ncp-apigw-signature-v2:".$signature,
			"x-ncp-region_code:KR"
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	if (!$err) {
		$result = json_decode($response,true);
		if (isset($result['content']) && $result['total'] > 0) {
			$content = $result['content'];
			$channel_name = $param['channel_name'];
			
			for ($i=0; $i<count($content); $i++) {
				if ($channel_name == $content[$i]['channelName']) {
					$channel = $content[$i];
				}
			}
		}
	}
	
	return $channel;
}

/* 3-2. NAVER CLOUD 채널 정보 등록 */
function addChannel($param) {
	$channel = null;
	
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
		CURLOPT_CUSTOMREQUEST	=>$method,
		CURLOPT_POSTFIELDS		=>'
			{
				"channelName":"'.$param['channel_name'].'",
				"cdn":{
					"createCdn":false,
					"cdnType":"GLOBAL_CDN",
					"cdnInstanceNo":123456
				},
				"qualitySetId":6,
				"useDvr":false,
				"envType":"DEV",
				"outputProtocol":"HLS",
				"record":{
					"type":"NO_RECORD"
				},
				"drmEnabledYn":false
			}
		',
		CURLOPT_HTTPHEADER => [
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$param['api_access_key'],
			"x-ncp-apigw-signature-v2:".$signature,
			"Content-type:application/json",
			"x-ncp-region_code:KR"
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	if (!$err) {
		$result = json_decode($response,true);
		if (isset($result['content'])) {
			$channel = $result['content'];
		}
	}
	
	return $channel;
}

/* NAVER CLOUD PLATFORM end point 최초 등록시에만 사용 */

/* 4. NAVER CLOUD end point 정보 체크 */
/*
function checkEndPoint($channel) {
	$param_end_point = array(
		'domain'			=>"https://livestation.apigw.ntruss.com",
		'uri_get'			=>"/api/v2/events/callbackEndpoint",
		'uri_add'			=>"/api/v2/channels/".$channel['channelId']."/callbackEndpoint",
		
		'id'				=>"ader_shopping",
		'log_level'			=>"INFO",
		'channel_id'		=>$channel['channelId'],
		
		'api_access_key'	=>"HCg2ZRAMIocP6NsIWz46",
		'api_secret_key'	=>"TZr46UcJztzTE4D4wMrEMrE706pGdVq2fpv4LotV"
	);
	
	$end_point = getEndpoint($param_end_point);
	if ($end_point == null) {
		$end_point = addEndPoint($param_end_point);
	}
	
	return $end_point;
}
*/

/* 4-1. NAVER CLOUD end point 정보 조회 */
/*
function getEndpoint($param) {
	$end_point = null;
	
	$method			= "GET";
	$timestamp		= getTimestamp();
	
	$signature		= makeSignature($method,$timestamp,$param['uri_get'],$param['api_access_key'],$param['api_secret_key']);
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>$param['domain'].$param['uri_get'],
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>$method,
		CURLOPT_HTTPHEADER => [
			"Content-Type:application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$param['api_access_key'],
			"x-ncp-apigw-signature-v2:".$signature,
			"x-ncp-region_code:KR"
		],
	]);
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	if (!$err) {
		$result = json_decode($response,true);
	}
	
	return $end_point;
}
*/

/* 4-2. NAVER CLOUD end point 정보 등록 */
/*
function addEndPoint($param) {
	$end_point = null;
	
	$method			= "POST";
	$timestamp		= getTimestamp();
	
	$signature		= makeSignature($method,$timestamp,$param['uri_add'],$param['api_access_key'],$param['api_secret_key']);
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>$param['domain'].$param['uri_add'],
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>$method,
		CURLOPT_POSTFIELDS		=>'
			{
				"callbackEndpoint":"https://mail.apigw.ntruss.com/api/v1/mails"
			}
		',
		CURLOPT_HTTPHEADER => [
			"Content-Type:application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$param['api_access_key'],
			"x-ncp-apigw-signature-v2:".$signature,
			"x-ncp-region_code:KR"
		],
	]);
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	if (!$err) {
		$result = json_decode($response,true);
		if (isset($result['content'])) {
			$end_point = $result['content'];
		}
	}
	
	return $end_point;
}
*/

?>