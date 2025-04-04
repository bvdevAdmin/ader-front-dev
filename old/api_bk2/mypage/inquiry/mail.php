<?php
include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/common/mail.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}
else{
	if(isset($_POST['country'])){
        $country = $_POST['country'];
	}
}
$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
	$member_id = $_SESSION['MEMBER_ID'];
}
if(isset($_POST['inquiry_title'])){
    $inquiry_title = $_POST['inquiry_title'];

    $mapping_arr = array();
    $mapping_arr[$member_idx]['member_id'] = $member_id;
    $mapping_arr[$member_idx]['inquiry_title'] = $inquiry_title;
    checkMailStatus($db, $country, 'MAIL_CASE_0017', $member_idx, $member_id, $mapping_arr);
}
?>