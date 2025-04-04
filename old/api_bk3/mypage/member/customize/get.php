<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 맞춤 구매 정보
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

if ($country != null && $member_idx != null) {
	$custom_cnt = $db->count("MEMBER_CUSTOM","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx."");
	
	if ($custom_cnt > 0) {
		$select_member_custom_sql = "
			SELECT
				MC.MEMBER_GENDER		AS MEMBER_GENDER,
				MC.UPPER_SIZE_IDX		AS UPPER_SIZE_IDX,
				MC.LOWER_SIZE_IDX		AS LOWER_SIZE_IDX,
				MC.SHOES_SIZE_IDX		AS SHOES_SIZE_IDX
			FROM
				MEMBER_CUSTOM MC
			WHERE
				MC.COUNTRY = '".$country."' AND
				MC.MEMBER_IDX = ".$member_idx."
		";
		
		$db->query($select_member_custom_sql);
		
		foreach($db->fetch() as $custom_data) {
			$json_result['data'] = array(
				'member_gender'			=>$custom_data['MEMBER_GENDER'],
				'upper_size_idx'		=>$custom_data['UPPER_SIZE_IDX'],
				'lower_size_idx'		=>$custom_data['LOWER_SIZE_IDX'],
				'shoes_size_idx'		=>$custom_data['SHOES_SIZE_IDX']
			);
		}
	}
}

?>