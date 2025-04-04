<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 맞춤정보 수정
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2024.10.15
 | 최종 수정	: 
 | 최종 수정일	: 
 | 버전		: 
 | 설명		: 
 |
 +=============================================================================
*/

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$upper_size = null;
	if (isset($topsize) && is_array($topsize)) {
		$upper_size = implode(",",$topsize);
	}
	
	$lower_size = null;
	if (isset($bottomsize) && is_array($bottomsize)) {
		$lower_size = implode(",",$bottomsize);
	}
	
	$shoes_size = null;
	if (isset($shoesize) && is_array($shoesize)) {
		$shoes_size = implode(",",$shoesize);
	}
	
	$cnt_custom = $db->count("MEMBER_CUSTOM","COUNTRY = ? AND MEMBER_IDX = ?",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	if ($cnt_custom > 0) {
		$db->update(
			"MEMBER_CUSTOM",
			array(
				'MEMBER_GENDER'		=>$gender,
				'HEIGHT'			=>$height,
				'WEIGHT'			=>$weight,
				'UPPER_SIZE_IDX'	=>$upper_size,
				'LOWER_SIZE_IDX'	=>$lower_size,
				'SHOES_SIZE_IDX'	=>$shoes_size
			),
			"COUNTRY = ? AND MEMBER_IDX = ?",
			array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'])
		);
	} else {
		$db->insert(
			"MEMBER_CUSTOM",
			array(
				'COUNTRY'			=>$_SERVER['HTTP_COUNTRY'],
				'MEMBER_IDX'		=>$_SESSION['MEMBER_IDX'],
				'MEMBER_GENDER'		=>$gender,
				'HEIGHT'			=>$height,
				'WEIGHT'			=>$weight,
				'UPPER_SIZE_IDX'	=>$upper_size,
				'LOWER_SIZE_IDX'	=>$lower_size,
				'SHOES_SIZE_IDX'	=>$shoes_size
			)
		);
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

?>