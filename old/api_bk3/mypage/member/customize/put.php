<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 맞춤 구매 정보 등록 / 수정
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.06.02
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($country) && $member_idx > 0) {
	$custom_cnt = $db->count("MEMBER_CUSTOM","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx."");
	
	$set_member_custom_sql = "";
	
	if ($custom_cnt > 0) {
		$db->update(
			"MEMBER_CUSTOM",
			array(
				'MEMBER_GENDER'		=>$member_gender,
				'UPPER_SIZE_IDX'	=>$upper_size_idx,
				'LOWER_SIZE_IDX'	=>$lower_size_idx,
				'SHOES_SIZE_IDX'	=>$shoes_size_idx
			),
			"
				COUNTRY = '".$country."' AND
				MEMBER_IDX = ".$member_idx."
			"
		);
	} else {
		$db->insert(
			"MEMBER_CUSTOM",
			array(
				'COUNTRY'			=>$country,
				'MEMBER_IDX'		=>$member_idx,
				'MEMBER_GENDER'		=>$member_gender,
				
				'UPPER_SIZE_IDX'	=>$upper_size_idx,
				'LOWER_SIZE_IDX'	=>$lower_size_idx,
				'SHOES_SIZE_IDX'	=>$shoes_size_idx
			)
		);
	}
}

?>