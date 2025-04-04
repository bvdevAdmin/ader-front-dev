<?php
/*
 +=============================================================================
 | 
 | 1:1문의 삭제
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
}

$board_idx = 0;
if (isset($_POST['board_idx'])) {
	$board_idx = $_POST['board_idx'];
}

if ($member_idx == 0 || $country == NULL) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	return $json_result;
}

if($country != null && $board_idx != null){
    $delete_page_board_inq_sql = "
        DELETE FROM
            PAGE_BOARD
        WHERE
            IDX = ".$board_idx." AND
			COUNTRY = '".$country."' AND
			MEMBER_IDX = ".$member_idx."
    ";

    $db->query($delete_page_board_inq_sql);
}

?>