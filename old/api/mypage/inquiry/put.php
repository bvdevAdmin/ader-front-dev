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

include_once(dir_f_api."/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
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

if(isset($inquiry_type) && isset($inquiry_title) && isset($inquiryTextBox)) {
	$reply_cnt = $db->count('PAGE_BOARD', 'IDX = '.$board_idx.' AND ANSWER_STATE = "NAS" ');
	
	if ($reply_cnt > 0) {
		try {
			$db->update(
				"PAGE_BOARD",
				array(
					'CATEGORY'	=>$inquiry_type,
					'TITLE'		=>$inquiry_title,
					'CONTENTS'	=>$inquiryTextBox,
					'UPDATER'	=>$member_id
				),
				"IDX = ".$board_idx." AND ANSWER_STATE = 'NAS' "
			);
			
			$db->delete(
				"BOARD_IMAGE",
				"BOARD_IDX = ?",
				array($board_idx)
			);
			
			for ($i=0; $i<count($priview_location); $i++) {
				if(strlen($priview_location[$i]) > 0){
					$db->insert(
						"BOARD_IMAGE",
						array(
							'BOARD_IDX'		=>$board_idx,
							'IMG_LOCATION'	=>$priview_location[$i],
							'CREATER'		=>$member_id,
							'UPDATER'		=>$member_id
						)
					);
				}
			}
			
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
			
			$db->commit();
  		} catch(mysqli_sql_exception $exception) {
			$db->rollback();
			
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0020', array());
			
			echo json_encode($json_result);
			exit;
		}
		
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0058', array());
		
		echo json_encode($json_result);
		exit;
	}
}

?>