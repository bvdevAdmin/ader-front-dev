<?php
/*
 +=============================================================================
 | 
 | 비밀번호 변경
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2022.11.30
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");

$country = null;
if(isset($_SERVER['HTTP_COUNTRY'])){
	$country = $_SERVER['HTTP_COUNTRY'];
}

/* member_idx는 현재 비밀번호 변경->이메일로 링크 전달->해당 링크로 비밀번호 변경창으로 이동하는 파라미터이다.*/
/* 추후 변경 가능*/

if(!isset($member_idx) || !isset($country)){
    $result = false;
	$code	= 401;
	
	$msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0072', array());
} else {
	$db->update(
		"MEMBER_".$country,
		array(
			'MEMBER_PW'		=>md5($member_pw),
			PW_DATE			=>NOW
		),
		'MEMBER_IDX = '.$member_idx
	);
}

?>