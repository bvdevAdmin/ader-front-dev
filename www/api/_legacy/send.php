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

function checkKAKAO_setting($db,$kakao_code) {
	$kakao_setting = array();
	
	$select_kakao_setting_sql = "
		SELECT
			KS.TEMPLATE_ID		AS TEMPLATE_ID,
			KS.USE_FLG			AS USE_FLG
		FROM
			KAKAO_SETTING KS
		WHERE
			KS.KAKAO_CODE = ?
	";
	
	$db->query($select_kakao_setting_sql,array($kakao_code));
	
	foreach($db->fetch() as $data) {
		$kakao_setting = array(
			'kakao_flg'		=>$data['USE_FLG'],
			'template_id'	=>$data['TEMPLATE_ID']
		);
	}
	
	return $kakao_setting;
}

function checkMAIL_setting($db,$country,$mail_code) {
	$mail_setting = array();
	
	$select_mail_setting_sql = "
		SELECT
			MS.TEMPLATE_ID_KR		AS TEMPLATE_ID_KR,
			MS.TEMPLATE_ID_EN		AS TEMPLATE_ID_EN,
			MS.KR_FLG				AS KR_FLG,
			MS.EN_FLG				AS EN_FLG
		FROM
			MAIL_SETTING MS
		WHERE
			MS.MAIL_CODE = ?
	";
	
	$db->query($select_mail_setting_sql,array($mail_code));
	
	foreach($db->fetch() as $data) {
		$mail_setting = array(
			'mail_flg'		=>$data[$country.'_FLG'],
			'template_id'	=>$data['TEMPLATE_ID_'.$country]
		);
	}
	
	return $mail_setting;
}

/* 1. NCP - 알림톡 발송 */
function callSEND_kakao($db,$kakao,$data) {
	/* NCP KAKAO::알림톡 세팅 설정 */
	$kakao_setting = array(
		'domain'			=>"https://sens.apigw.ntruss.com",
		'service_id'		=>"ncp:kkobizmsg:kr:3236210:ader_shopping",
		
		'access_key'		=>"HCg2ZRAMIocP6NsIWz46",
		'secret_key'		=>"TZr46UcJztzTE4D4wMrEMrE706pGdVq2fpv4LotV"
	);
	
	$template = null;
	
	$kakao_id = $kakao['template_id'];
	/* 1-1. 알림톡 template contents 조회 */
	$kakao_contents = getKAKAO_contents($kakao_id,$kakao_setting);
	
	if ($kakao_contents != null && strlen($kakao_contents) > 0) {
		$template = array(
			'template_id'		=>$kakao_id,
			'kakao_message'		=>$kakao_contents
		);
	}
	
	if ($template != null) {
		/* 1-2. ~ 1-3. 알림톡 POSTFIELD 설정 */
		$kakao_post = setKAKAO_post($template,$kakao,$data);
		if ($kakao_post != null) {
			/* 1-4. 알림톡 발송 */
			sendKAKAO($kakao_setting,$kakao_post);
		}
	}
}

/* NCP (공통)::timestamp 생성 */
function getTimestamp() {
  $microtime = microtime();
  $comps = explode(' ', $microtime);
  
  return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
}

/* 1-1. 알림톡 template contents 조회 */
function getKAKAO_contents($kakao_id,$setting) {
	$kakao_message = null;
	
	$method			= "GET";
	$uri			= "/alimtalk/v2/services/".$setting['service_id']."/templates?channelId=@ader&templateCode=".$kakao_id;
	$timestamp		= getTimestamp();

	$signature		= setKAKAO_signature($method,$timestamp,$uri,$setting['access_key'],$setting['secret_key']);

	$curl = curl_init();
		
	curl_setopt_array($curl, [
		CURLOPT_URL				=>$setting['domain'].$uri,
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>$method,
		CURLOPT_POSTFIELDS		=>"",
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$setting['access_key'],
			"x-ncp-apigw-signature-v2:".$signature
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	if (!$err) {
		$result = json_decode($response,true);
		if (isset($result[0]['content'])) {
			$kakao_message = $result[0]['content'];
		}
	}
	
	return $kakao_message;
}

/* 1-2. 알림톡 POSTFIELD 설정 */
function setKAKAO_post($template,$kakao,$data) {
	$kakao_post = null;
	
	/* 1-3. 알림톡 데이터 치환 */
	$kakao_message = setKAKAO_data($template['kakao_message'],$data);
	$kakao_message = str_replace("\n","\\n",$kakao_message);
	
	$kakao_post = '
		{
			"plusFriendId":"@ader",
			"templateCode":"'.$template['template_id'].'",
			"messages":[
				{
					"to":"'.$kakao['tel_mobile'].'",
					"content":"'.$kakao_message.'"
				}
			]
		}
	';
	
	return $kakao_post;
}

/* 1-3. 알림톡 데이터 치환 */
function setKAKAO_data($message,$data) {
	$param_replace = array(
		'#{member_id}',
		'#{member_name}',
		'#{param_date}',
		'#{param_time}',
		'#{order_code}',
		'#{order_title}',
		'#{price_total}',
		'#{product_name}',
		'#{option_name}',
		'#{company_name}',
		'#{delivery_num}',
		'#{delivery_end_date}',
		'#{pg_payment}',
		'#{e_product_name}',
		'#{e_option_name}',
		'#{member_id}',
		'#{member_level}',
		'#{tmp_pw}',
		'#{mileage_balance}',
		'#{m_expired_date}',
		'#{auth_no}',
		'#{level_next_name}',
		'#{level_prev_name}',
		'#{title_inquiry}'
	);
	
	$data_key = array_keys($data);
	
	foreach($data_key as $key => $tmp_key) {
		$replace_key = strval("#{".$tmp_key."}");
		
		if (in_array($replace_key,$param_replace)) {
			$message = str_replace($replace_key,$data[$tmp_key],$message);
		}
	}
	
	return $message;
}

/* 1-4. 알림톡 발송 */
function sendKAKAO($setting,$post) {
	$method			= "POST";
	$uri			= "/alimtalk/v2/services/".$setting['service_id']."/messages";
	$timestamp		= getTimestamp();
	
	$signature		= setKAKAO_signature($method,$timestamp,$uri,$setting['access_key'],$setting['secret_key']);
	
	$curl = curl_init();
		
	curl_setopt_array($curl, [
		CURLOPT_URL				=>$setting['domain'].$uri,
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>$method,
		CURLOPT_POSTFIELDS		=>$post,
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$setting['access_key'],
			"x-ncp-apigw-signature-v2:".$signature
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
		
	if (!$err) {
		$result = json_decode($response,true);
		if (isset($result['statusCode']) && $result['statusCode'] == 202) {
			$send_result = true;
		}
	}
}

/* 2. NCP - 메일 발송 */
function callSEND_mail($db,$mail,$data) {
	$access_key		= "HCg2ZRAMIocP6NsIWz46";
	$secret_key		= "TZr46UcJztzTE4D4wMrEMrE706pGdVq2fpv4LotV";
		
	$mail_setting = array(
		'access_key'	=>$access_key,
		'secret_key'	=>$secret_key
	);
	
	/* 2-1. MAIL POSTFIELD 설정 */
	$mail_post = setMAIL_postfield($mail,$data);
		
	if ($mail_post != null) {
		/* 2-2. MAIL 발송 */
		sendMAIL($mail_setting,$mail_post);
	} else {
		/* MAIL 발송 정보 설정 실패 예외처리 */
		$json_result['code'] = 302;
		$json_result['msg'] = "메일 발송 정보 설정중 오류가 발생했습니다.";
		
		echo json_encode($json_result);
		exit;
	}
}

/* 2-1. MAIL POSTFIELD 설정 */
function setMAIL_postfield($mail,$data) {
	$mail_post = null;
	
	$mail_param = array();
	if ($data != null && count($data) > 0) {
		$key_data = array_keys($data);
		foreach($key_data as $key => $a_key) {
			$value = $data[$a_key];
			
			$tmp_param = '"'.$a_key.'" : "'.$value.'"';
			array_push($mail_param,$tmp_param);
		}
	}
	
	$parameters = "";
	if (count($mail_param) > 0) {
		$parameters = '
			,"parameters":{
				'.implode(",",$mail_param).'
			}
		';
	}
	
	$recipients = '
		{
			"address":"'.$mail['user_email'].'",
			"name":"'.$mail['user_name'].'",
			"type":"R"
			'.$parameters.'
		}
	';
	
	$mail_post = '
		{
			"templateSid" : '.$mail['template_id'].',
			"recipients": [
				'.$recipients.'
			],
			"individual" : true,
			"advertising" : false
		}
	';
	
	return $mail_post;
}

/* 2-2. MAIL 발송 */
function sendMAIL($setting,$post) {
	$method			= "POST";
	$domain			= "https://mail.apigw.ntruss.com";
	$uri			= "/api/v1/mails";
	$timestamp		= getTimestamp();
	
	$signature		= setMAIL_signature($timestamp,$setting['access_key'],$setting['secret_key']);
	
	$curl = curl_init();
		
	curl_setopt_array($curl, [
		CURLOPT_URL				=>$domain.$uri,
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>$method,
		CURLOPT_POSTFIELDS		=>$post,
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$setting['access_key'],
			"x-ncp-apigw-signature-v2:".$signature
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
		
	if (!$err) {
		$result = json_decode($response,true);
		if (isset($result['requestId']) && isset($result['count']) && $result['count'] > 0) {
			$send_result = true;
		}
	}
}

function setMAIL_signature($timestamp,$access_key,$secret_key) {
    $space = " ";					//공백
    $newLine = "\n";				//줄바꿈
    $method = "POST";				//HTTP 메소드
    $uri= "/api/v1/mails";  		//도메인을 제외한 "/" 아래 전체 url (쿼리스트링 포함)

    $hmac = $method.$space.$uri.$newLine.$timestamp.$newLine.$access_key;
    $signautue = base64_encode(hash_hmac('sha256',$hmac,$secret_key,true));

    return $signautue;
}

function setKAKAO_signature($method,$timestamp,$service_uri,$access_key,$secret_key) {
    $space		= " ";				//공백
    $newLine	= "\n";				//줄바꿈
    $method		= $method;			//HTTP 메소드
	$uri		= $service_uri;		//도메인을 제외한 "/" 아래 전체 url (쿼리스트링 포함)
    $timestamp	= $timestamp;		//현재 타임스탬프 (epoch, millisecond)
    $accessKey	= $access_key;	//access key id (from portal or sub account)
    $secretKey	= $secret_key;	//secret key (from portal or sub account)

    $hmac = $method.$space.$uri.$newLine.$timestamp.$newLine.$accessKey;
    $signautue = base64_encode(hash_hmac('sha256',$hmac,$secretKey,true));
	
    return $signautue;
}

?>

