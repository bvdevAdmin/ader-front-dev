<?php
/*
 +=============================================================================
 | 
 | SENS - SMS 회원별 자동 발송 (주문)
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
국가코드, 주문 IDX, SMS_CODE를 기준으로 SMS 발송
*/

/* 1. SMS 발송 PARAM */
$param_sms = getPARAM_sms($db,$country,$sms_code);

if ($param_sms != null) {
	/* 2. SMS 발송 대상 회원 PARAM */
	$member_sms = getMEMBER_sms($db,$country,$order_idx);
	if ($member_sms != null) {
		/* 3. SMS 데이터 PARAM 조회 */
		$data_sms = getDATA_sms($db,$country,$order_idx);
		if ($data_sms != null) {
			/* 4. SMS 데이터 항목 치환처리 */
			$param_sms = replaceDATA_sms($data_sms,$param_sms);
			if ($param_sms != null) {
				/* 5. SMS POSTFIELD 설정 */
				$post_field_sms = setPOSTFIELD_sms($param_sms,$member_sms);
				if ($post_field_sms != null) {
					$setting_sms = array(
						'domain'			=>$domain_sens,
						'service_id'		=>$service_sms,
						
						'api_access_key'	=>$api_access_key,
						'api_secret_key'	=>$api_secret_key
					);
					
					/* 6. SMS 발송 */
					sendSMS($setting_sms,$post_field_sms);
				}
			}
		} else {
			/* SMS 발송 데이터 조회 실패 예외처리 */
			$json_result['code'] = 302;
			$json_result['msg'] = "SMS 발송 데이터 조회처리중 오류가 발생했습니다.";
		}
	} else {
		/* SMS 발송 대상 조회 실패 예외처리 */
		$json_result['code'] = 301;
		$json_result['msg'] = "SMS 발송 대상 조회처리중 오류가 발생했습니다.";
	}
} else {
	/* SMS 템플릿 미설정 예외처리 */
	$json_result['code'] = 300;
	$json_result['msg'] = "SMS 템플릿 조회처리중 오류가 발생했습니다.";
}

/* 1. SMS 발송 PARAM */
function getPARAM_sms($db,$country,$sms_code) {
	$param_sms = null;
	
	$country_code = "";
	switch ($country) {
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
	
	$select_template_sms_sql = "
		SELECT
			ST.SMS_TYPE								AS SMS_TYPE,
			ST.SMS_CONTENT_TYPE						AS SMS_CONTENT_TYPE,
			ST.SMS_SUBJECT_".$country."				AS SMS_SUBJECT,
			ST.SMS_CONTENT_".$country."				AS SMS_CONTENT,
			ST.SMS_MESSAGE_SUBJECT_".$country."		AS SMS_MESSAGE_SUBJECT,
			ST.SMS_MESSAGE_CONTENT_".$country."		AS SMS_MESSAGE_CONTENT
		FROM
			SMS_TEMPLATE ST
		WHERE
			ST.SMS_CODE = ?
	";
	
	$db->query($select_template_sms_sql,array($sms_code));
	
	foreach($db->fetch() as $data) {
		$param_sms = array(
			'sms_type'				=>$data['SMS_TYPE'],
			'sms_content_type'		=>$data['SMS_CONTENT_TYPE'],
			'sms_country_code'		=>$country_code,
			'sms_subject'			=>$data['SMS_SUBJECT'],
			'sms_content'			=>$data['SMS_CONTENT'],
			'sms_message_subject'	=>$data['SMS_MESSAGE_SUBJECT'],
			'sms_message_content'	=>$data['SMS_MESSAGE_CONTENT']
		);
	}
	
	return $param_sms;
}

/* 2. SMS 발송 대상 회원 PARAM */
function getMEMBER_sms($db,$country,$order_idx) {
	$member_sms = null;
	
	$select_member_sms_sql = "
		SELECT
			REPLACE(
				MB.TEL_MOBILE,'-',''
			)				AS TEL_MOBILE
		FROM
			MEMBER_".$country." MB
			LEFT JOIN ORDER_INFO OI ON
			MB.IDX = OI.MEMBER_IDX
		WHERE
			OI.IDX = ?
	";
	
	$db->query($select_member_sms_sql,array($order_idx));
	
	foreach($db->fetch() as $data) {
		$member_sms = array(
			'tel_mobile'		=>$data['TEL_MOBILE']
		);
	}
	
	return $member_sms;
}

/* 3. SMS 데이터 PARAM 조회 */
function getDATA_sms($db,$country,$order_idx) {
	$data_sms = null;
	
	$select_param_sms_sql = "
		SELECT
			MB.MEMBER_NAME		AS MEMBER_NAME,
			DATE_FORMAT(
				OI.CREATE_DATE,
				'%Y-%m-%d %H:%i:%s'
			)					AS ORDER_DATE,
			OI.ORDER_CODE		AS ORDER_CODE,
			OI.ORDER_TITLE		AS ORDER_TITLE,
			J_OP.PRODUCT_QTY	AS PRODUCT_QTY,
			OI.PRICE_TOTAL		AS PRICE_TOTAL,
			OI.PG_PRICE			AS PG_PRICE,
			DC.COMPANY_NAME		AS COMPANY_NAME,
			OI.DELIVERY_NUM		AS DELIVERY_NUM
		FROM
			ORDER_INFO OI
			LEFT JOIN ORDER_PRODUCT OP ON
			OI.IDX = OP.ORDER_IDX
			
			LEFT JOIN (
				SELECT
					S_OP.ORDER_IDX			AS ORDER_IDX,
					SUM(S_OP.PRODUCT_QTY)	AS PRODUCT_QTY
				FROM
					ORDER_PRODUCT S_OP
				WHERE
					S_OP.PRODUCT_TYPE NOT IN ('D','V') AND
					S_OP.PRODUCT_QTY > 0
				GROUP BY
					S_OP.ORDER_IDX
			) AS J_OP ON
			OI.IDX = J_OP.ORDER_IDX
			
			LEFT JOIN MEMBER_".$country." MB ON
			OI.MEMBER_IDX = MB.IDX
			
			LEFT JOIN DELIVERY_COMPANY DC ON
			OI.DELIVERY_IDX = DC.IDX
		WHERE
			OI.IDX = ?
	";
	
	$db->query($select_param_sms_sql,array($order_idx));
	
	foreach($db->fetch() as $data) {
		$data_sms = array(
			'member_name'		=>$data['MEMBER_NAME'],
			'order_date'		=>$data['ORDER_DATE'],
			'order_code'		=>$data['ORDER_CODE'],
			'order_title'		=>$data['ORDER_TITLE'],
			'product_qty'		=>$data['PRODUCT_QTY'],
			'price_total'		=>$data['PRICE_TOTAL'],
			'pg_price'			=>$data['PG_PRICE'],
			'company_name'		=>$data['COMPANY_NAME'],
			'delivery_num'		=>$data['DELIVERY_NUM']
		);
	}
	
	return $data_sms;
}

/* 4. SMS 데이터 항목 치환처리 */
function replaceDATA_sms($data,$param) {
	$sms_content			= $param['sms_content'];
	$sms_message_content	= $param['sms_message_content'];
	
	/* 치환 대상 데이터 항목 */
	$param_replace = array(
		'[이름]',
		'[년월일 및 시분초]',
		'[주문 번호]',
		'[제품명]',
		'[총 주문수량]',
		'[총 결제 금액]',
		'[배송회사명]',
		'[운송장번호]',
		'[결제 대기 금액]',
		'[입금 확인 금액]'
	);
	
	for ($i=0; $i<count($param_replace); $i++) {
		$tmp_replace	= $param_replace[$i];
		$tmp_data		= $data[$i];
		
		if ($tmp_replace != null && $tmp_data != null) {
			$sms_content			= str_replace($tmp_replace,$tmp_data,$sms_content);
			$sms_message_content	= str_replace($tmp_replace,$tmp_data,$sms_message_content);
		}
	}
	
	$param['sms_content']			= $sms_content;
	$param['sms_message_content']	= $sms_message_content;
	
	return $param;
}

/* 5. SMS POSTFIELD 설정 */
function setPOSTFIELD_sms($param,$member) {
	$post_field_sms = null;
	
	/* 5-1. SMS message_content 설정 */
	$sms_message_content = setMESSAGE_sms($member,$param['sms_message_content']);
	if ($sms_message_content != null && count($sms_message_content) > 0) {
		/* 5-2. LMS/MMS 기본 메시지 제목 설정 */
		$sms_subject = "";
		if ($param['sms_type'] != "SMS") {
			$sms_subject = '"subject":"'.$param['sms_subject'].'",';
		}
		
		/* 5-3. 기본 메시지 내용 설정 */
		$sms_content = "";
		if ($param['sms_content_type'] != "AD") {
			$sms_content = $param['sms_content'];
		} else {
			/* 메시지 타입 광고 - 문자열 설정 */
			switch ($param['sms_country_code']) {
				case "" :
					$sms_content = "(광고)".$param['sms_content'];
					break;
				
				case "" :
					$sms_content = "(AD)".$param['sms_content'];
					break;
				
				case "" :
					$sms_content = "(广告)".$param['sms_content'];
					break;
			}
		}
		
		$post_field_sms = '
			{
				"type":"'.$param['sms_type'].'",
				"contentType":"'.$param['sms_content_type'].'",
				"countryCode":"'.$param['sms_country_code'].'",
				"from":"027922232",
				'.$sms_subject.'
				"content":"'.$sms_content.'",
				"messages":[
					'.implode(",",$sms_message_content).'
				]
			}
		';
	}
	
	return $post_field_sms;
}

/* 5-1. SMS message 설정 */
function setMESSAGE_sms($member,$message_content) {
	$message_sms = array();
	
	foreach($member as $data) {
		$tmp_message = '
			{
				"to":"'.$data['tel_mobile'].'",
				"content":"'.$message_content.'"
			}
		';
		
		array_push($message_sms,$tmp_message);
	}
	
	return $message_sms;
}

/* 6. SMS 발송 */
function sendSMS($setting,$post_field) {
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
		print_r($result);
		if ($result['status'] == 200) {
			$send_result = true;
		} else {
			$send_result = false;
		}
	}
	
	return $send_result;
}

?>