<?php
/*
 +=============================================================================
 | 
 | SENS - SMS 발송
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

function setSENS_SMS($db,$param_sms) {
	$api_access_key = "HCg2ZRAMIocP6NsIWz46";
	$api_secret_key = "TZr46UcJztzTE4D4wMrEMrE706pGdVq2fpv4LotV";

	$service_id = "ncp:sms:kr:323621039649:ader_shopping";
	
	$reserve_time = date('YYYY-MM-dd HH:mm');
	$timestamp = time($reserve_time);
	
	/*
	$country	= $param_sms['country'];
	$sms_code	= $param_sms['sms_code'];
	$sms_from	= $param_sms['sms_from'];
	$tel_mobile	= $param_sms['tel_mobile'];
	*/
	
	$country = "KR";
	$sms_code = "SMS_ORD_0001";
	$sms_from = "";
	$tel_mobile = "";
	
	/* SENS SMS - SMS 발송 PARAM */
	$param_send_sms = array(
		'service_id'		=>$service_id,
		
		'timestamp'			=>$timestamp,
		'api_access_key'	=>$api_access_key,
		'api_secret_key'	=>$api_secret_key,
		
		'sms_from'			=>$sms_from,
		'tel_mobile'		=>$tel_mobile,
		'reserve_time'		=>$reserve_time
	);
	
	/* SMS 타이틀/컨텐츠 조회 */
	$sms_info = $db->get(
		"SMS_INFO",
		"
			SMS_CODE = ?
		",
		array(
			$sms_code
		)
	)[0];
	
	$sms_title = $sms_info["SMS_TITLE_".$country];
	if (strlen($sms_title) > 0) {
		$param_send_sms['sms_title']		= $sms_title;
	}
	
	/* SENS SMS - 회원 SMS 발송 PARAM 설정 */
	$sms_content_mem = $sms_info['SMS_CONTENT_MEM_'.$country];
	if (strlen($sms_title) > 0 && strlen($sms_content_mem) > 0) {
		$param_send_sms['sms_content'] = $sms_content_mem;
		
		$result_mem = sendSENS_SMS($param_send_sms);
	}
	
	/* SENS SMS - 관리자 SMS 발송 PARAM 설정 */
	$sms_content_adm = $sms_info['SMS_CONTENT_MEM_'.$country];
	if (strlen($sms_title) > 0 && strlen($sms_content_adm) > 0) {
		$param_send_sms['sms_content'] = $sms_content_adm;
		
		$result_adm = sendSENS_SMS($param_send_sms);
	}
}

function sendSENS_SMS($param) {
	$send_result = false;
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://sens.apigw.ntruss.com/sms/v2/services/".$param['service_id']."/messages",
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"POST",
		CURLOPT_POSTFIELDS		=>'
			{
				"type":"SMS",
				"contentType":"COMM",
				"countryCode":"82",
				"from":"'.$param['sms_from'].'",
				"subject":"'.$param['sms_title'].'",
				"content":"'.$param['sms_content'].'",
				"messages":[
					{
						"to":"'.$param['tel_mobile'].'"
					}
				],
				"reserveTime": "'.$param['reserve_time'].'",
				"reserveTimeZone": "Asia/Seoul"
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
	print_r(" [ RESPONSE START ----- ] ");
	print_r($response);
	print_r(" [ RESPONSE END ----- ] ");
	
	$err = curl_error($curl);
	print_r(" [ ERR START ----- ] ");
	print_r($err);
	print_r(" [ ERR END ----- ] ");
	
	if (!$err) {
		$result = json_encode($response,true);
		print_r($result);
	}
	
	return $send_result;
}

?>