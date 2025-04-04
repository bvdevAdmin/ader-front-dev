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

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
	$member_id = $_SESSION['MEMBER_ID'];
}

$board_idx = null;
if (isset($_POST['board_idx'])) {
	$board_idx = $_POST['board_idx'];
}

$inquiry_type = null;
if (isset($_POST['inquiry_type'])) {
	$inquiry_type = $_POST['inquiry_type'];
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

$priview_location = null;
if (isset($_POST['priview_location'])) {
	$priview_location = $_POST['priview_location'];
}

if ($member_idx == 0 || $country == NULL) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	return $json_result;
}

if($inquiry_type != null && $inquiry_title != null && $inquiryTextBox != null){
	$reply_cnt = $db->count('PAGE_BOARD', 'IDX = '.$board_idx.' AND ANSWER_STATE = "NAS" ');
	
	if($reply_cnt > 0){
		try {
			$update_page_board_inq_sql = "
				UPDATE PAGE_BOARD
				SET
					CATEGORY = '".$inquiry_type."',
					TITLE = '".$inquiry_title."',
					CONTENTS = '".$inquiryTextBox."',
					UPDATER = '".$member_id."'
				WHERE
					IDX = ".$board_idx."
				AND
					ANSWER_STATE = 'NAS'
			";

			$db->query($update_page_board_inq_sql);

			$db->query('DELETE FROM BOARD_IMAGE WHERE BOARD_IDX = '.$board_idx.' ');
			for ($i=0; $i<count($priview_location); $i++) {
				if(strlen($priview_location[$i]) > 0){
					$old_board_img_sql = "
						INSERT INTO
							BOARD_IMAGE
						(
							BOARD_IDX,
							IMG_LOCATION,
							CREATER,
							UPDATER
						) VALUES (
							".$board_idx.",
							'".$priview_location[$i]."',
							'".$member_id."',
							'".$member_id."'
						)
					";
					$db->query($old_board_img_sql);
				}
			}

			$upload_result = cdn_img_upload($db, $country, null, $inq_img, "/inquiry");
			
			if ($upload_result != null && count($upload_result) > 0) {
				for ($i=0; $i<count($upload_result); $i++) {
					$new_board_img_sql = "
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
					
					$db->query($new_board_img_sql);
				}
			}
			$db->commit();
		} 
		catch(mysqli_sql_exception $exception){
			$json_result['code'] = 301;
			$db->rollback();
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0020', array());
			return $json_result;
		}
		
	}
	else{
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0058', array());
		
		return $json_result;
	}
}

?>