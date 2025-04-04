<?php
/*
 +=============================================================================
 | 
 | notimodal 메세지 정보 취득
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.7.14
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$country        = $_POST['country'];
$msg_code       = $_POST['msg_code'];

$msg_text       = '';
$msg_data = $db->get('MSG_MST', "MSG_CODE = ? ", array($msg_code))[0];
$msg_text = $msg_data['MSG_TEXT_'.$country];

$json_result['data']['msg_text'] = $msg_text;
?>