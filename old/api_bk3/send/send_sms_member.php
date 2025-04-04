<?php
/*
 +=============================================================================
 | 
 | SENS - SMS 발송 (회원 대량 발송)
 | -----------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.05.13
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/admin/api/send/send-common.php");

/* SMS 발송 대상 회원 PARAM */
$param_member = null;
if (isset($_POST['param_member'])) {
	$param_member = $_POST['param_member'];
}

/* SMS 발송 PARAM */
$param_sms = null;
if (isset($_POST['param_sms'])) {
	$param_sms = $_POST['param_sms'];
}

if ($param_sms != null) {
	$country = $param_sms['country_code'];
	
	/* 1. SMS 발송 대상 회원정보 조회 */
	$member_sms = getMEMBER_sms_member($db,$country,$param_member);
	if ($member_sms != null) {
		$setting_sms = array(
			'domain'			=>$domain_sens,
			'service_id'		=>$service_sms,
			
			'api_access_key'	=>$api_access_key,
			'api_secret_key'	=>$api_secret_key
		);
		
		/* 2. SMS POSTFIELD 설정 */
		$post_field_sms = setPOSTFIELD_sms_member($param_sms,$member_sms,$setting_sms);
		if ($post_field_sms != null) {
			/* 3. SMS 발송 */
			$send_result = false;
			$send_result = sendSMS_member($setting_sms,$post_field_sms);
			if ($send_result != true) {
				$json_result['code'] = 303;
				$json_result['msg'] = "SMS 발송처리중 오류가 발생했습니다.";
			}
		} else {
			$json_result['code'] = 302;
			$json_result['msg'] = "SMS POSTFIELD 설정처리중 오류가 발생했습니다.";
		}
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = "발송 가능한 회원정보가 존재하지 않습니다.<br>SMS 발송대상을 확인해주세요";
	}
} else {
	/* SMS 발송정보 미설정 예외처리 */
	$json_result['code'] = 300;
	$json_result['msg'] = "SMS 발송정보가 설정되어 있지 않습니다. SMS 발송정보를 확인해주세요.";
}

/* 1. SMS 발송 대상 회원정보 조회 */
function getMEMBER_sms_member($db,$country,$param) {
	/*
	발송 대상 회원 PARAM(param_member) 미전송 시
	휴대전화 번호 미등록, 휴면/탈퇴 회원, SMS 수신 거부 회원을 제외 한 전 회원에게 SMS 발송
	*/
	$member_sms = null;
	
	$where_member = "";
	if ($param != null) {
		/* SMS 발송대상 - 회원등급 */
		$member_level = $param['member_level'];
		if ($member_level != null && $member_level != "0") {
			
		}
		
		/* SMS 발송대상 - 최소 주문횟수 / 최대 주문횟수 */
		$min_cnt = $param['min_cnt'];
		$max_cnt = $param['max_cnt'];
		if ($min_cnt != null || $max_cnt != null) {
			if ($min_cnt != null && $max_cnt == null) {
				$where_member .= "
					AND (
						J_OI.ORDER_QTY >= ".$min_qty."
					)
				";
			} else if ($min_cnt == null && $max_cnt != null) {
				$where_member .= "
					AND (
						J_OI.ORDER_QTY <= ".$max_qty."
					)
				";
			} else if ($min_cnt != null && $max_cnt != null) {
				$where_member .= "
					AND (
						J_OI.ORDER_QTY BETWEEN ".$min_cnt." AND ".$max_cnt."
					)
				";
			}
		}
		
		/* SMS 발송대상 - 최소 주문수량 / 최대 주문수량 */
		$min_qty = $param['min_qty'];
		$max_qty = $param['max_qty'];
		if ($min_qty != null || $max_qty != null) {
			if ($min_qty != null && $max_qty == null) {
				$where_member .= "AND (
					J_OP.PRODUCT_QTY >= ".$min_qty."
				)";
			} else if ($min_qty != null && $max_qty == null) {
				$where_member .= "AND (
					J_OP.PRODUCT_QTY <= ".$max_qty."
				)";
			} else if ($min_qty != null && $max_qty != null) {
				$where_member .= "AND (
					J_OP.PRODUCT_QTY BETWEEN ".$min_qty." AND ".$max_qty."
				)";
			}
		}
		
		/* SMS 발송대상 - 가입년도 */
		$regist_year = $param['regist_year'];
		if ($regist_year != null) {
			$where_member .= "
				AND (
					DATE_FORMAT(
						MB.JOIN_DATE,'%Y'
					) = ".$regist_year."
				)
			";
		}
		
		/* SMS 발송대상 - 가입월 */
		$regist_month = $param['regist_month'];
		if ($regist_month != null && $regist_month != "0") {
			$where_member .= "
				AND (
					DATE_FORMAT(
						MB.JOIN_DATE,'%m'
					) = ".$regist_month."
				)
			";
		}
		
		/* SMS 발송대상 - 회원생일 */
		$member_birth = $param['member_birth'];
		if ($member_birth != null && $member_birth != "0") {
			$where_member .= "
				AND (
					DATE_FORMAT(
						MB.MEMBER_BIRTH,'%m'
					) = ".$member_birth."
				)
			";
		}
	}
	
	$select_member_sms_sql = "
		SELECT
			REPLACE(
				MB.TEL_MOBILE,'-',''
			)								AS TEL_MOBILE
		FROM
			MEMBER_".$country." MB
			
			LEFT JOIN (
				SELECT
					S_OI.MEMBER_IDX			AS MEMBER_IDX,
					COUNT(S_OI.IDX)			AS ORDER_QTY
				FROM
					ORDER_INFO S_OI
				WHERE
					S_OI.COUNTRY = ?
				GROUP BY
					S_OI.MEMBER_IDX
			) AS J_OI ON
			MB.IDX = J_OI.MEMBER_IDX
			
			LEFT JOIN (
				SELECT
					S_OI.MEMBER_IDX			AS MEMBER_IDX,
					SUM(S_OP.PRODUCT_QTY)	AS PRODUCT_QTY
				FROM
					ORDER_INFO S_OI
					LEFT JOIN ORDER_PRODUCT S_OP ON
					S_OI.IDX = S_OP.ORDER_IDX
				WHERE
					S_OI.COUNTRY = ? AND
					S_OP.PRODUCT_TYPE NOT IN ('V','D') AND
					S_OP.PRODUCT_QTY > 0
					
			) AS J_OP ON
			MB.IDX = J_OP.MEMBER_IDX
		WHERE
			MB.MEMBER_STATUS NOT IN ('SLP','DRP') AND
			MB.TEL_MOBILE IS NOT NULL AND
			CHAR_LENGTH(MB.TEL_MOBILE) > 0 AND
			MB.RECEIVE_SMS_FLG = TRUE
			".$where_member."
	";
	
	$db->query($select_member_sms_sql,array($country,$country));
	
	foreach($db->fetch() as $data) {
		$member_sms[] = array(
			'tel_mobile'		=>$data['TEL_MOBILE']
		);
	}
	
	return $member_sms;
}

/* 2. SMS POSTFIELD 설정 */
function setPOSTFIELD_sms_member($param,$member,$setting) {
	$post_field_sms = null;
	
	/* 2-1. SMS message 설정 */
	$message_sms = setMESSAGE_sms_member($member,$param);
	if ($message_sms != null && count($message_sms) > 0) {
		$country_code = "";
		switch ($param['country_code']) {
			case "KR" :
				$country_code = "82";
				break;
				
			case "EN" :
				$country_code = "1";
				break;
				
			case "CN" :
				$country_code = "86";
				break;
		}
		
		$sms_subject	= "";
		$sms_content	= "";
		$sms_file		= "";
		
		/* 2-2. SMS CONTENT TYPE 구분 */
		if ($param['content_type'] == "AD") {
			/* SMS CONTENT TYPE 광고 */
			$txt_ad = "";
			switch ($param['country_code']) {
				case "KR" :
					$txt_ad = "(광고)";
					break;
				
				case "EN" :
					$txt_ad = "(AD)";
					break;
				
				case "CN" :
					$txt_ad = "(广告)";
					break;
			}
			
			if ($param['type'] != "SMS") {
				$sms_subject = $txt_ad." ".$param['sms_message_subject'];
			} else {
				$sms_content = $txt_ad." ".$param['sms_message_content'];
			}
		} else {
			/* SMS CONTENT TYPE 일반 */
			$sms_subject = $param['message_subject'];
			$sms_content = $param['message_content'];
		}
		
		if (strlen($sms_subject) > 0) {
			$sms_subject = '"subject":"'.$sms_subject.'",';
		}
		
		$sms_content = str_replace("\n","\\n",$sms_content);
		$sms_content = str_replace("\t","\\t",$sms_content);
		
		/* 2-3 SMS 파일 업로드 */
		if ($param['type'] != "SMS" && ($param['message_file_name'] != null && $param['message_file_body'] != null)) {
			$file_id = uploadFile_sms($setting,$param['message_file_name'],$param['message_file_body']);
			if ($file_id != null) {
				$sms_file = '
					,"files":[
						{
							"fileId":"'.$file_id.'"
						}
					]
				';
			}
		}
		
		$post_field_sms = '
			{
				"type":"'.$param['type'].'",
				"contentType":"'.$param['content_type'].'",
				"countryCode":"'.$country_code.'",
				"from":"027922232",
				'.$sms_subject.'
				"content":"'.$sms_content.'",
				"messages":[
					'.implode(",",$message_sms).'
				]
				'.$sms_file.'
			}
		';
	}
	
	return $post_field_sms;
}

/* 2-1. SMS message 설정 */
function setMESSAGE_sms_member($member,$param) {
	$message_sms = array();
	
	$message_subject = "";
	$message_content = "";
	
	/* SMS CONTENT TYPE 구분 */
	if ($param['sms_content_type'] == "AD") {
		/* SMS CONTENT TYPE - 광고 */
		$txt_ad = "";
		switch ($param['sms_country_code']) {
			case "KR" :
				$txt_ad = "(광고)";
				break;
			
			case "EN" :
				$txt_ad = "(AD)";
				break;
			
			case "CN" :
				$txt_ad = "(广告)";
				break;
		}
		
		if ($param['sms_type'] != "SMS") {
			$message_subject = $txt_ad." ".$param['message_subject'];
		} else {
			$message_content = $txt_ad." ".$param['message_content'];
		}
	} else {
		/* SMS CONTENT TYPE - 일반 */
		$message_subject = $param['message_subject'];
		$message_content = $param['message_content'];
	}
	
	if (strlen($message_subject) > 0) {
		$message_subject = '"subject":"'.$message_subject.'",';
	}
	
	$message_content = str_replace("\n","\\n",$message_content);
	$message_content = str_replace("\t","\\t",$message_content);
	
	foreach($member as $data) {
		$tmp_message = '
			{
				"to":"'.$data['tel_mobile'].'",
				'.$message_subject.'
				"content":"'.$message_content.'"
			}
		';
		
		array_push($message_sms,$tmp_message);
	}
	
	return $message_sms;
}

/* 3. SMS 발송 */
function sendSMS_member($setting,$post_field) {
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
		if ($result['statusCode'] == 202) {
			$send_result = true;
		} else {
			$send_result = false;
		}
	}
	
	return $send_result;
}

/* 5. SMS 파일 업로드 */
function uploadFile_sms($setting,$file_name,$file_body) {
	$file_id = null;
	
	$tmp_name = explode(".",$file_name);
	$ext = $tmp_name[1];
	
	$method			= "POST";
	$uri			= "/sms/v2/services/".$setting['service_id']."/files";
	$timestamp		= getTimestamp();
	
	$signature		= makeSignature($method,$timestamp,$uri,$setting['api_access_key'],$setting['api_secret_key']);
	
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
				"fileName":"'.date('YmdHis',time()).'.'.$ext.'",
				"fileBody":"'.$file_body.'"
			}
		',
		CURLOPT_HTTPHEADER => [
			"Content-Type:application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$setting['api_access_key'],
			"x-ncp-apigw-signature-v2:".$signature
		],
	]);
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	if (!$err) {
		$result = json_decode($response,true);
		if (isset($result['fileId'])) {
			$file_id = $result['fileId'];
		}
	}
	
	return $file_id;
}
?>