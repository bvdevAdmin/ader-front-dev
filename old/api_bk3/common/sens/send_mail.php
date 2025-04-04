<?php
/*
 +=============================================================================
 | 
 | Cloud Outboard Mailer - 메일 발송
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

$api_access_key = "HCg2ZRAMIocP6NsIWz46";
$api_secret_key = "TZr46UcJztzTE4D4wMrEMrE706pGdVq2fpv4LotV";

$service_id = "ncp:mail:kr:323621048250:main";

$timestamp = time();
print_r(" [ TIMESTAMP : ".$timestamp." ] ");
print_r(" [ DATE : ".date('y-m-d h:i:s',$timestamp)." ] ");

$signature = makeSignature($timestamp,$api_access_key,$api_secret_key);

function makeSignature($timestamp,$api_access_key,$api_secret_key) {
    $space = " ";					//공백
    $newLine = "\n";				//줄바꿈
    $method = "POST";				//HTTP 메소드
    $uri= "/api/v1/mails";  		//도메인을 제외한 "/" 아래 전체 url (쿼리스트링 포함)
    $timestamp = $timestamp;		//현재 타임스탬프 (epoch, millisecond)
    $accessKey = $api_access_key;	//access key id (from portal or sub account)
    $secretKey = $api_secret_key;	//secret key (from portal or sub account)

    $hmac = $method.$space.$uri.$newLine.$timestamp.$newLine.$accessKey;
    $signautue = base64_encode(hash_hmac('sha256', $hmac, $secretKey,true));

    return $signautue;
}

$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL				=>"https://mail.apigw.ntruss.com/api/v1/mails",
	CURLOPT_RETURNTRANSFER	=>true,
	CURLOPT_ENCODING		=>"",
	CURLOPT_MAXREDIRS		=>10,
	CURLOPT_TIMEOUT			=>30,
	//CURLOPT_SSL_VERIFYHOST	=>false,
	//CURLOPT_SSL_VERIFYPEER	=>false,
	CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST	=>"POST",
	CURLOPT_POSTFIELDS		=>'
		{
			"senderName":"ADER",
			"templateSid":"12571",
			"parameters":{
				"member_id":"shson@bvdev.co.kr",
				"member_name":"손성환"
			},
		}
	',
	CURLOPT_HTTPHEADER => [
		"Content-Type:application/json",
		"x-ncp-apigw-timestamp:".$timestamp,
		"x-ncp-iam-access-key:".$api_access_key,
		"x-ncp-apigw-signature-v2:".$signature,
		"x-ncp-lang:ko-KR"
	],
]);

$response = curl_exec($curl);
print_r($response);

$err = curl_error($curl);
$err = json_encode($err,true);
print_r($err);

/*
function sendMail($param) {
	
}

function getMailTemplate() {
	
}

function checkMailStatus($db,$country,$mail_code,$member_idx,$member_id,$mapping_arr){
    $select_mail_setting_sql = "
		SELECT
			MS.MEMBER_FLG		AS MEMBER_FLG,
			MS.ADMIN_FLG		AS ADMIN_FLG
		FROM
			MAIL_SETTING MS
		WHERE
			MS.MAIL_CODE = '".$mail_code."'
	";
	
	$db->query($select_mail_setting_sql);
	
	foreach($db->fetch() as $setting_data){
		$member_flg = $setting_data['MEMBER_FLG'];
		$admin_flg = $setting_data['ADMIN_FLG'];
		
		if ($member_flg == true) {
			$select_mail_template_sql = "
				SELECT
					MT.MAIL_TITLE		AS MAIL_TITLE,
					MT.MAIL_BODY		AS MAIL_BODY
				FROM
					MAIL_TEMPLATE MT
				WHERE
					MT.COUNTRY = '".$country."' AND
					MT.TEMPLATE_TYPE = 'M' AND
					MT.MAIL_CODE = '".$mail_code."'
			";
			
			$db->query($select_mail_template_sql);
			
			foreach($db->fetch() as $template_data){
				$mail_title = convertMapping($template_data['MAIL_TITLE'],$mapping_arr[$member_idx]);
				$mail_body = convertMapping($template_data['MAIL_BODY'],$mapping_arr[$member_idx]);
				
				commonSendMail($member_id, $mail_title, $mail_body);
			}
		}
		
		if ($admin_flg == true) {
			$select_mail_template_sql = "
				SELECT
					MT.MAIL_TITLE		AS MAIL_TITLE,
					MT.MAIL_BODY		AS MAIL_BODY
				FROM
					MAIL_TEMPLATE MT
				WHERE
					MT.COUNTRY = '".$country."' AND
					MT.TEMPLATE_TYPE = 'A' AND
					MT.MAIL_CODE = '".$mail_code."'
			";
			
			$db->query($select_mail_template_sql);
			
			foreach($db->fetch() as $template_data){
				$mail_title = convertMapping($template_data['MAIL_TITLE'],$mapping_arr[$member_idx]);
				$mail_body = convertMapping($template_data['MAIL_BODY'],$mapping_arr[$member_idx]);
				
				commonSendMail($member_id, $mail_title, $mail_body);
			}
		}
	}
}

function convertMapping($tag, $mapping){
    $keys_arr = array_keys($mapping);
	foreach($keys_arr as $key){
		$tag = str_replace('{'.$key.'}',$mapping[$key],$tag);
	}
	
	return $tag;
}
*/
?>