<?php
/*
 +=============================================================================
 | 
 | 간편 회원가입 본인인증 처리
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2024.01.16
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$db->update(
		"MEMBER",
		array(
			'MEMBER_NAME'		=>$member_name,
			'TEL_MOBILE'		=>$tel_mobile,
			'MEMBER_BIRTH'		=>$member_birth,
			'AUTH_FLG'			=>1,
		),
		"IDX = ?",
		array($_SESSION['MEMBER_IDX'])
	);

	$gender = "F";
	if ($member_gender == 1) {
		$gender = "M";
	}

	$db->update(
		"MEMBER_CUSTOM",
		array(
			'MEMBER_GENDER'		=>$gender
		),
		"MEMBER_IDX = ?",
		array($_SESSION['MEMBER_IDX'])
	);
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>