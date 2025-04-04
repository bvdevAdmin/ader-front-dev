<?php
/*
 +=============================================================================
 | 
 | SENS - 알림톡 API
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
$template_code	= "KKO_ORD_0001";

$member_idx		= array(1,2);

/* 알림톡 메시지 PARAM */
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

/* 알림톡 버튼 PARAM */
$param_button	= array();

/* -----> */

if ($member_idx != null && $template_code != null) {
	$param_kakao = array(
		'domain'				=>$domain,
		'service_id'			=>$service_kakao,
		
		'api_access_key'		=>$api_access_key,
		'api_secret_key'		=>$api_secret_key,
		
		'plus_friend_id'		=>$plus_friend_id,
		'template_code'			=>$template_code
	);
	
	$send_result = false;
	
	$param_member = getParamMember($db,$member_idx);
	if ($param_member != null) {
		$send_result = sendKAKAO($param_member,$param_kakao);
	} else {
		$json_result['code'] = 300;
		$json_result['msg'] = "";
	}
} else {
	$json_result['code'] = 300;
	$json_result['msg'] = "";
}

function getParamMember($db,$member_idx) {
	$param_member = null;
	
	$select_param_member_sql = "
		SELECT
			MB.MEMBER_ID		AS MEMBER_ID,
			REPLACE(
				MB.TEL_MOBILE,'-',''
			)					AS TEL_MOBILE
		FROM
			MEMBER_KR MB
		WHERE
	";
	
	$where = null;
	$where_value = null;
	
	if (is_array($member_idx)) {
		$where = "
			MB.IDX IN (?)
		";
		
		$where_value = implode(",",$member_idx);
	} else {
		$where = "
			MB.IDX = ?
		";
		
		$where_value = $member_idx;
	}
	
	$db->query($select_param_member_sql.$where,array($where_value));
	
	foreach($db->fetch() as $data) {
		$param_member[] = array(
			'user_id'			=>$data['MEMBER_ID'],
			'tel_mobile'		=>$data['TEL_MOBILE']
		);
	}
	
	return $param_member;
}

function sendKAKAO($param_member,$param_kakao) {
	$send_result = false;
	
	$method			= "POST";
	$uri			= "/alimtalk/v2/services/".$param_kakao['service_id']."/messages";
	$timestamp		= getTimestamp();
	
	$signature		= makeSignature($method,$timestamp,$uri,$param_kakao['api_access_key'],$param_kakao['api_secret_key']);
	
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL				=>$param_kakao['domain'].$uri,
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"POST",
		CURLOPT_POSTFIELDS		=>'
			{
				"plusFriendId":"ader_shopping",
				"templateCode":"TMP_0001",
				"messages":[
					{
						"to":"01067364537",
						"content":"Hello World I\'m Ader error."
					}
				]
			}
		',
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json",
			"x-ncp-apigw-timestamp:".$timestamp,
			"x-ncp-iam-access-key:".$param_kakao['api_access_key'],
			"x-ncp-apigw-signature-v2:".$param_kakao['api_secret_key']
		],
	]);
}

?>