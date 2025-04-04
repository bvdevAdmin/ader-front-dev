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

include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/common/mail.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else {
	if(isset($_POST['country'])){
        $country = $_POST['country'];
	}
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
	$member_id = $_SESSION['MEMBER_ID'];
}

$inq_img = null;
if (isset($_FILES['inq_img'])) {
	$inq_img = $_FILES['inq_img'];
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}
if (isset($inquiry_type) && isset($inquiry_title) && isset($inquiryTextBox)) {
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
	
	if (!empty($board_idx)) {
		$upload_result = cdn_img_upload($db, $country, null, $inq_img, "/inquiry");
		if ($upload_result != null && count($upload_result) > 0) {
			for ($i=0; $i<count($upload_result); $i++) {
				$db->insert(
					"BOARD_IMAGE",
					array(
						'BOARD_IDX'		=>$board_idx,
						'IMG_LOCATION'	=>$upload_result[$i],
						'CREATER'		=>$member_id,
						'UPDATER'		=>$member_id
					)
				);
			}
		}
	}
}

?>