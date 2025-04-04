<?php

/*
 +=============================================================================
 | 
 | 휴대전화 인증처리
 | -------
 |
 | 최초 작성    : 박성혁
 | 최초 작성일   : 2022.11.30
 | 최종 수정일   : 
 | 버전       : 1.0
 | 설명       : 
 |            
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/send/send-common.php");

if ($tel_mobile != null && $sms_code != null && strlen($sms_code) > 0) {
	$param_non_user[] = array(
		'tel_mobile'	=>$tel_mobile
	);
	
	$sms_info = checkSMSInfo($db,$country,$sms_code);
	if ($sms_info != null) {
		$param_sms_content = array(
			'sms_title'		=>$sms_info['sms_title'],
			'sms_content'	=>$sms_info['sms_content_mem']
		);
		
		/* 휴대전화 인증 SMS 발송 */
		$send_result = sendSMS($db,$country,"M",null,$param_non_user,$param_sms_content,null);
		if ($send_result != true) {
			$json_result['code'] = 300;
			$json_result['msg'] = "";
			
			echo json_encode($json_result);
			exit;
		}
	}
}

?>