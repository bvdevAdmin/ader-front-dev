<?php
/*
 +=============================================================================
 | 
 | SENS - KAKAO 회원별 자동 발송
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

/*
KAKAO CODE를 기준으로 알림톡 발송
*/

function callSEND_kakao($db,$kakao,$data) {
	$api_access_key		= "HCg2ZRAMIocP6NsIWz46";
	$api_secret_key		= "TZr46UcJztzTE4D4wMrEMrE706pGdVq2fpv4LotV";
	
	$domain_sens		= "https://sens.apigw.ntruss.com";
	$service_kakao		= "ncp:kkobizmsg:kr:3236210:ader_shopping";
	
	/* 1. KAKAO 발송용 세팅 설정 */
	$setting_kakao = array(
		'domain'			=>$domain_sens,
		'service_id'		=>$service_kakao,
		
		'api_access_key'	=>$api_access_key,
		'api_secret_key'	=>$api_secret_key
	);
	
	/* 2. KAKAO template 조회 */
	$template_kakao = getTemplate_kakao($db,$kakao['kakao_code'],$setting_kakao);
	if ($template_kakao != null) {
		if (
			($template_kakao['template_id'] != null && $template_kakao['template_id'] != "00000") &&
			strlen($template_kakao['kakao_message']) > 0
		) {
			/* 3. KAKAO 발송 대상 회원 PARAM */
			$member_kakao = getMEMBER_kakao($db,$kakao['member_idx']);
			if ($member_kakao != null) {
				/* 4. KAKAO POSTFIELD 설정 */
				$post_field_kakao = setPOSTFIELD_kakao($template_kakao,$member_kakao,$data);
				
				if ($setting_kakao != null && $post_field_kakao != null) {
					/* 5. KAKAO 발송 */
					sendKAKAO($setting_kakao,$post_field_kakao);
				} else {
					/* KAKAO 발송 정보 설정 실패 예외처리 */
					$json_result['code'] = 302;
					$json_result['msg'] = "메일 발송 정보 설정중 오류가 발생했습니다.";
					
					echo json_encode($json_result);
					exit;
				}
			} else {
				/* KAKAO 발송 대상 조회 실패 예외처리 */
				$json_result['code'] = 301;
				$json_result['msg'] = "알림톡 발송 대상 조회처리중 오류가 발생했습니다.";
				
				echo json_encode($json_result);
				exit;
			}
		}
	}
}

/* 2. KAKAO template 조회 */
function getTemplate_kakao($db,$kakao_code,$setting) {
	$template_kakao = null;
	
	$select_template_id_sql = "
		SELECT
			KS.TEMPLATE_ID		AS TEMPLATE_ID
		FROM
			KAKAO_SETTING KS
		WHERE
			KS.KAKAO_CODE = ? AND
			KS.USE_FLG = TRUE
	";
	
	$db->query($select_template_id_sql,array($kakao_code));
	
	foreach($db->fetch() as $data) {
		$template_id = $data['TEMPLATE_ID'];
		
		/* KAKAO template content 조회 */
		$kakao_message = getTemplateContent($template_id,$setting);
		
		$template_kakao = array(
			'template_id'		=>$template_id,
			'kakao_message'		=>$kakao_message
		);
	}
	
	return $template_kakao;
}

/* KAKAO template content 조회 */
function getTemplateContent($template_id,$setting) {
	$kakao_message = null;
	
	$method			= "GET";
	$uri			= "/alimtalk/v2/services/".$setting['service_id']."/templates?channelId=@ader&templateCode=".$template_id;
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
		CURLOPT_POSTFIELDS		=>"",
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
		if (isset($result[0]['content'])) {
			$kakao_message = $result[0]['content'];
		}
	}
	
	return $kakao_message;
}

/* 3. KAKAO 발송 대상 회원 PARAM */
function getMEMBER_kakao($db,$member_idx) {
	$member_kakao = null;
	
	$select_member_sql = "
		SELECT
			MB.MEMBER_ID	AS MEMBER_ID,
			MB.MEMBER_NAME	AS MEMBER_NAME,
			REPLACE(
				MB.TEL_MOBILE,'-',''
			)				AS TEL_MOBILE
		FROM
			MEMBER_KR MB
		WHERE
			MB.IDX = ?
	";
	
	$db->query($select_member_sql,array($member_idx));
	
	foreach($db->fetch() as $data) {
		$member_kakao = array(
			'user_email'		=>$data['MEMBER_ID'],
			'user_name'			=>$data['MEMBER_NAME'],
			'tel_mobile'		=>$data['TEL_MOBILE']
		);
	}
	
	return $member_kakao;
}

/* 4. KAKAO POSTFIELD 설정 */
function setPOSTFIELD_kakao($template,$member,$data) {
	$post_field_kakao = null;
	
	$kakao_message = replaceDATA_kakao($template['kakao_message'],$data);
	$kakao_message = str_replace("\n","\\n",$kakao_message);
	
	$post_field_kakao = '
		{
			"plusFriendId":"@ader",
			"templateCode":"'.$template['template_id'].'",
			"messages":[
				{
					"to":"'.$member['tel_mobile'].'",
					"content":"'.$kakao_message.'"
				}
			]
		}
	';
	
	return $post_field_kakao;
}

/* 4-1. KAKAO 데이터 항목 치환처리 */
function replaceDATA_kakao($message,$data) {
	$kakao_message = $message;
	
	if ($data != null && count($data) > 0) {
		/* 치환 대상 데이터 항목 */
		$param_replace = array(
			'#{member_name}',
			'#{param_date}',
			'#{param_time}',
			'#{order_code}',
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
		
		$key = array_keys($data);
		for ($i=0; $i<count($key); $i++) {
			$param_key = $key[$i];
			$tmp_key = strval("#{".$param_key."}");
			
			for ($j=0; $j<count($param_replace); $j++) {
				$tmp_replace = $param_replace[$j];
				if ($tmp_key == $tmp_replace) {
					if (isset($data[$param_key])) {
						$kakao_message = str_replace($tmp_key,$data[$param_key],$kakao_message);
					}
				}
			}
		}
	}
	
	return $kakao_message;
}

/* 5. KAKAO 발송 */
function sendKAKAO($setting,$post_field) {
	$method			= "POST";
	$uri			= "/alimtalk/v2/services/".$setting['service_id']."/messages";
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
		if (isset($result['requestId']) && isset($result['count']) && $result['count'] > 0) {
			$send_result = true;
		}
	}
}

function getDATA_kakao($db,$param) {
	$data_kakao = null;
	
	switch ($param['data_type']) {
		/* 알림톡 데이터 조회 - 주문 정보 */
		case "ORDER" :
			$data_kakao = getDATA_k_order($db,$param);
			break;
		
		/* 알림톡 데이터 조회 - 주문 취소 정보 */
		case "ORDER_CANCEL" :
			$data_kakao = getDATA_k_cancel($db,$param);
			break;
		
		/* 알림톡 데이터 조회 - 주문 교환 접수 정보 */
		case "ORDER_EXCHANGE" :
			$data_kakao = getDATA_k_exchange($db,$param);
			break;
		
		/* 알림톡 데이터 조회 - 주문 반품 정보 */
		case "ORDER_REFUND" :
			$data_kakao = getDATA_k_refund($db,$param);
			break;
		
		/* 알림톡 데이터 조회 - 회원정보 */
		case "MEMBER" :
			$data_kakao = getDATA_k_member($db,$param);
			break;
	}
	return $data_kakao;
}

/* 알림톡 데이터 조회 - 주문 정보 */
function getDATA_k_order($db,$param) {
	$data_kakao = null;
	
	$select_order_info_sql = "
		SELECT
			MB.MEMBER_NAME				AS MEMBER_NAME,
			
			DATE_FORMAT(
				OI.CREATE_DATE,
				'%Y-%m-%d'
			)							AS PARAM_DATE,
			DATE_FORMAT(
				OI.CREATE_DATE,
				'%H:%i'
			)							AS PARAM_TIME,
			
			OI.ORDER_CODE				AS ORDER_CODE,
			OI.ORDER_TITLE				AS ORDER_TITLE,
			
			OI.PRICE_TOTAL				AS PRICE_TOTAL,
			
			DC.COMPANY_NAME				AS COMPANY_NAME,
			OI.DELIVERY_NUM				AS DELIVERY_NUM,
			IFNULL(
				DATE_FORMAT(
					OI.DELIVERY_START_DATE,
					'%Y-%m-%d %H:%i'
				),'-'
			)							AS DELIVERY_START_DATE,
			IFNULL(
				DATE_FORMAT(
					OI.DELIVERY_START_DATE,
					'%Y-%m-%d %H:%i'
				),'-'
			)							AS DELIVERY_END_DATE,
			
			OI.PG_PAYMENT				AS PG_PAYMENT
		FROM
			ORDER_INFO OI
			LEFT JOIN MEMBER_KR MB ON
			OI.MEMBER_IDX = MB.IDX
			LEFT JOIN DELIVERY_COMPANY DC ON
			OI.DELIVERY_IDX = DC.IDX
		WHERE
			OI.IDX = ?
	";
	
	$db->query($select_order_info_sql,array($param['order_idx']));
	
	foreach($db->fetch() as $data) {
		$data_kakao = array(
			'member_name'			=>$data['MEMBER_NAME'],
			
			'param_date'			=>$data['PARAM_DATE'],
			'param_time'			=>$data['PARAM_TIME'],
			
			'order_code'			=>$data['ORDER_CODE'],
			'product_name'			=>$data['ORDER_TITLE'],
			
			'price_total'			=>number_format($data['PRICE_TOTAL']),
			
			'company_name'			=>$data['COMPANY_NAME'],
			'delivery_num'			=>$data['DELIVERY_NUM'],
			'delivery_start_date'	=>$data['DELIVERY_START_DATE'],
			'delivery_end_date'		=>$data['DELIVERY_END_DATE'],
			
			'pg_payment'			=>$data['PG_PAYMENT']
		);
	}
	
	return $data_kakao;
}

/* 알림톡 데이터 조회 - 주문 취소 정보 */
function getDATA_k_cancel($db,$param) {
	$data_kakao = null;
	
	$select_order_cancel_sql = "
		SELECT
			OC.MEMBER_NAME				AS MEMBER_NAME,
			OC.ORDER_CODE				AS ORDER_CODE,
			OC.PRICE_CANCEL				AS CANCEL_PRICE,
			OC.PG_PAYMENT				AS PG_PAYMENT
		FROM
			ORDER_CANCEL OC
			LEFT JOIN MEMBER_".$param['country']." MB ON
			OC.MEMBER_IDX = MB.IDX
		WHERE
			OC.ORDER_UPDATE_CODE = ?
	";
	
	$db->query($select_order_cancel_sql,array($param['order_update_code']));
	
	foreach($db->fetch() as $data) {
		$data_kakao = array(
			'member_name'		=>$data['MEMBER_NAME'],
			'order_code'		=>$data['ORDER_CODE'],
			'cancel_price'		=>number_format($data['CANCEL_PRICE']),
			'pg_payment'		=>$data['PG_PAYMENT']
		);
	}
	
	return $data_kakao;
}

/* 알림톡 데이터 조회 - 주문 교환 정보 */
function getDATA_k_exchange($db,$param) {
	$data_kakao = null;
	
	$select_order_exchange_sql = "
		SELECT
			T_OE.MEMBER_NAME			AS MEMBER_NAME,
			OPE.ORDER_CODE				AS ORDER_CODE,
			OPE.PRODUCT_NAME			AS PRODUCT_NAME,
			(
				SELECT
					S_OO.OPTION_NAME	AS OPTION_NAME
				FROM
					ORDERSHEET_OPTION S_OO
				WHERE
					S_OO.IDX = OPE.PREV_OPTION_IDX
			)							AS PREV_OPTION_NAME,
			OPE.OPTION_NAME				AS OPTION_NAME
		FROM
			ORDER_PRODUCT_EXCHANGE OPE
			LEFT JOIN ORDER_EXCHANGE T_OE ON
			OPE.ORDER_IDX = T_OE.IDX
		WHERE
			OPE.ORDER_UPDATE_CODE = ?
	";
	
	$db->query($select_order_exchange_sql,array($param['order_update_code']));
	
	foreach($db->fetch() as $data) {
		$data_kakao = array(
			'member_name'		=>$data['MEMBER_NAME'],
			'order_code'		=>$data['ORDER_CODE'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'prev_option_name'	=>$data['PREV_OPTION_NAME'],
			'option_name'		=>$data['OPTION_NAME']
		);
	}
	
	return $data_kakao;
}

/* 알림톡 데이터 조회 - 주문 반품 정보 */
function getDATA_k_refund($db,$param) {
	$data_kakao = null;
	
	$select_order_refund_sql = "
		SELECT
			T_OF.MEMBER_NAME			AS MEMBER_NAME,
			OPF.ORDER_CODE				AS ORDER_CODE,
			OPF.PRODUCT_NAME			AS PRODUCT_NAME,
			OPF.OPTION_NAME				AS OPTION_NAME
		FROM
			ORDER_PRODUCT_REFUND OPF
			LEFT JOIN T_OF ON
			OPF.ORDER_IDX = T_OF.IDX
			LEFT JOIN MEMBER_".$param['country']." MB ON
			T_OF.MEMBER_IDX = MB.IDX
		WHERE
			OPF.ORDER_UPDATE_CODE = ?
	";
	
	$db->query($select_order_refund_sql,array($param['order_update_code']));
	
	foreach($db->fetch() as $data) {
		$data_kakao = array(
			'member_name'		=>$data['MEMBER_NAME'],
			'order_code'		=>$data['ORDER_CODE'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'option_name'		=>$data['OPTION_NAME']
		);
	}
	
	return $data_kakao;
}

/* 알림톡 데이터 조회 - 회원 정보 */
function getDATA_k_member($db,$param) {
	$data_kakao = null;
	
	$select_member_sql = "
		SELECT
			MB.MEMBER_ID				AS MEMBER_ID,
			MB.MEMBER_NAME				AS MEMBER_NAME,
			ML.TITLE					AS MEMBER_LEVEL
		FROM
			MEMBER_KR MB
			LEFT JOIN MEMBER_LEVEL ML ON
			MB.LEVEL_IDX = ML.IDX
		WHERE
			MB.IDX = ?
	";
	
	$db->query($select_member_sql,array($param['member_idx']));
	
	foreach($db->fetch() as $data) {
		$data_kakao = array(
			'member_id'			=>$data['MEMBER_ID'],
			'member_name'		=>$data['MEMBER_NAME'],
			'member_level'		=>$data['MEMBER_LEVEL'],
		);
		
		if (isset($param['auth_no'])) {
			$data_kakao['auth_no'] = $param['auth_no'];
		}
		
		if (isset($param['tmp_pw'])) {
			$data_kakao['tmp_pw'] = $param['tmp_pw'];
		}
	}
	
	return $data_kakao;
}

?>