<?php
/*
 +=============================================================================
 | 
 | 마이페이지_문의내역 - 문의내역 메일링
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.02.27
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");
include_once(dir_f_api."/common/mail.php");

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
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

if (isset($inquiry_title)) {
    $mapping_arr = array();
    
    $mapping_arr[$member_idx]['member_id'] = $member_id;
    $mapping_arr[$member_idx]['inquiry_title'] = $inquiry_title;
    
    checkMailStatus($db,$country,'MAIL_CASE_0017',$member_idx,$member_id,$mapping_arr);
}

?>