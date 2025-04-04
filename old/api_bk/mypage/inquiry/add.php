<?php
/*
 +=============================================================================
 | 
 | 1:1문의 추가
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.02.26
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common/common.php");
include_once("/var/www/www/api/common/mail.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
	$member_id = $_SESSION['MEMBER_ID'];
}

$inquiry_type = null;
if (isset($_POST['inquiry_type'])) {
	$inquiry_type = $_POST['inquiry_type'];
}

$inquiry_title = null;
if (isset($_POST['inquiry_title'])) {
	$inquiry_title = $_POST['inquiry_title'];
}

$inquiryTextBox = null;
if (isset($_POST['inquiryTextBox'])) {
	$inquiryTextBox = $_POST['inquiryTextBox'];
}

$inq_img = null;
if (isset($_FILES['inq_img'])) {
	$inq_img = $_FILES['inq_img'];
}

if ($member_idx == 0 || $country == NULL) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	return $json_result;
}
if($inquiry_type != null && $inquiry_title != null && $inquiryTextBox != null){
	$insert_page_board_inq_sql = "
		INSERT INTO
			PAGE_BOARD
		(
			COUNTRY,
			BOARD_TYPE,
			CATEGORY,
			MEMBER_IDX,
			MEMBER_ID,
			MEMBER_NAME,
			TITLE,
			CONTENTS,
			ANSWER_STATE,
			CREATER,
			UPDATER
		)
		SELECT
			'".$country."',
			'ONE',
			'".$inquiry_type."',
			IDX,
			MEMBER_ID,
			MEMBER_NAME,
			'".$inquiry_title."',
			'".$inquiryTextBox."',
			'NAS',
			MEMBER_ID,
			MEMBER_ID
		FROM
			MEMBER_".$country."
		WHERE
			IDX = '".$member_idx."'
	";

	$db->query($insert_page_board_inq_sql);

	$board_idx = $db->last_id();
	if(!empty($board_idx)){
		$upload_result = cdn_img_upload($db, $country, null, $inq_img, "/inquiry");
		if ($upload_result != null && count($upload_result) > 0) {
			for ($i=0; $i<count($upload_result); $i++) {
				$insert_board_img_sql = "
					INSERT INTO
						BOARD_IMAGE
					(
						BOARD_IDX,
						IMG_LOCATION,
						CREATER,
						UPDATER
					) VALUES (
						".$board_idx.",
						'".$upload_result[$i]."',
						'".$member_id."',
						'".$member_id."'
					)
				";
				
				$db->query($insert_board_img_sql);
			}
		}
	}
	$mapping_arr = array();
	$mapping_arr[$member_idx]['member_id'] = $member_id;
	$mapping_arr[$member_idx]['inquiry_title'] = $inquiry_title;
	checkMailStatus($db, $country, 'MAIL_CASE_0017', $member_idx, $member_id, $mapping_arr);
}

?>