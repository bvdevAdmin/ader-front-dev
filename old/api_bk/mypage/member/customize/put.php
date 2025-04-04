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

$member_gender = null;
if (isset($_POST['member_gender'])) {
	$member_gender = $_POST['member_gender'];
}

$upper_size_idx = 0;
if (isset($_POST['upper_size_idx'])) {
	$upper_size_idx = $_POST['upper_size_idx'];
}

$lower_size_idx = 0;
if (isset($_POST['lower_size_idx'])) {
	$lower_size_idx = $_POST['lower_size_idx'];
}

$shoes_size_idx = 0;
if (isset($_POST['shoes_size_idx'])) {
	$shoes_size_idx = $_POST['shoes_size_idx'];
}

if ($country != null && $member_idx != null) {
	$custom_cnt = $db->count("MEMBER_CUSTOM","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx."");
	
	$set_member_custom_sql = "";
	
	if ($custom_cnt > 0) {
		$set_member_custom_sql = "
			UPDATE
				MEMBER_CUSTOM
			SET
				MEMBER_GENDER = '".$member_gender."',
				UPPER_SIZE_IDX = ".$upper_size_idx.",
				LOWER_SIZE_IDX = ".$lower_size_idx.",
				SHOES_SIZE_IDX = ".$shoes_size_idx."
			WHERE
				COUNTRY = '".$country."' AND
				MEMBER_IDX = ".$member_idx."
		";
	} else {
		$set_member_custom_sql = "
			INSERT INTO
				MEMBER_CUSTOM
			(
				COUNTRY,
				MEMBER_IDX,
				MEMBER_GENDER,
				UPPER_SIZE_IDX,
				LOWER_SIZE_IDX,
				SHOES_SIZE_IDX
			) VALUES (
				'".$country."',
				".$member_idx.",
				'".$member_gender."',
				'".$upper_size_idx."',
				'".$lower_size_idx."',
				'".$shoes_size_idx."'
			)
		";
	}
	
	$db->query($set_member_custom_sql);
}

?>