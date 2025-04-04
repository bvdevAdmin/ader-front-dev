<?php
/*
 +=============================================================================
 | 
 | notimodal 메세지 정보 취득
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.7.14
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.6.26
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($msg_code)) {
	$msg_text = '';
	
	$msg_data = $db->get('MSG_MST',"MSG_CODE = ? ",array($msg_code));
	if (sizeof($msg_data) > 0) {
		$data = $msg_data[0];
		
		$msg_text = null;
		if (isset($data['MSG_TEXT_'.$_SERVER['HTTP_COUNTRY']])) {
			$msg_text = $data['MSG_TEXT_'.$_SERVER['HTTP_COUNTRY']];
		}
		
		$json_result['data']['msg_text'] = $msg_text;
	}
}

?>
