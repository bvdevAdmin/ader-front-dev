<?php
/*
 +=============================================================================
 | 
 | SENS - 한국몰 휴대전화 메일 발송
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

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$member_name = null;
if (isset($_SESSION['MEMBER_NAME'])) {
	$member_name = $_SESSION['MEMBER_NAME'];
}

if ($member_id != null && $member_name) {
	$auth_no = makeAUTH();
	
	$setting_mail = setSETTING_mail($api_access_key,$api_secret_key);
	if ($setting_mail != null) {
		sendMAIL($db,$setting_mail,$member_id,$member_name,$auth_no);
	}
}

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

function sendMAIL($db,$setting,$member_id,$member_name,$auth_no) {
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
		CURLOPT_POSTFIELDS		=>'
			{
				"templateSid" : 13645,
				"recipients": [
					{
						"address":"'.$member_id.'",
						"name":"'.$member_name.'",
						"type":"R",
						"parameters":{
							"member_name":"'.$member_name.'",
							"auth_no":"'.$auth_no.'"
						}
					}
				],
				"individual" : true,
				"advertising" : false
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
		if (isset($result['requestId'])) {
			$status_code = 200;
			
			$country = $_SESSION['COUNTRY'];
			setMEMBER_auth($db,$country,$auth_no);
		}
		
		$json_result['code'] = $status_code;
		
		echo json_encode($json_result);
		exit;
	}
}

function makeAUTH(){
    return mt_rand(100000,999999);
}

function setMEMBER_auth($db,$country,$auth_no) {
	$update_member_sql = "
		UPDATE
			MEMBER_".$country."
		SET
			AUTH_NO		= ?,
			AUTH_DATE	= DATE_ADD(NOW(), INTERVAL 30 SECOND)
		WHERE
			IDX = ?
	";
	
	$db->query($update_member_sql,array($auth_no,$_SESSION['MEMBER_IDX']));
}

?>