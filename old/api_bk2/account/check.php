<?php


/*
 +=============================================================================
 | 
 | 가입메일 체크
 | -------
 |
 | 최초 작성    : 박성혁
 | 최초 작성일   : 2022.11.30
 | 최종 수정일   : 
 | 버전       : 1.0
 | 설명       : 
 |            
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common/mail.php");
include_once("/var/www/www/api/common.php");
$country = null;
if(isset($_POST['country'])){
	$country = $_POST['country'];
}
else{
	$result = false;
	$code = 300;
	$msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0072', array());
}
$member_id = $_POST['member_id'];

if ($member_id == null || $member_id == '') {
    $result = false;
    $code = 401;
    $msg = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0003', array());
} else {
    $member_count = 0;
    $sql = "
        SELECT 
            COUNT(0) AS MEMBER_CNT,
            IDX,
            MEMBER_NAME
        FROM 
            MEMBER_" . $country . "
        WHERE
            MEMBER_ID = '" . $member_id . "'
    ";
    $db->query($sql);

    $result_arr = array();
    foreach ($db->fetch() as $data) {
        $member_count = $data['MEMBER_CNT'];
        $member_idx = $data['IDX'];
        $member_name = $data['MEMBER_NAME'];
    }

    if ($member_count > 0) {
        $tmp_pw = makeTmpPw();

        $update_sql = "
            UPDATE
                MEMBER_".$country."
            SET
                MEMBER_PW = '".md5($tmp_pw)."'
            WHERE
                IDX = ".$member_idx." 
        ";
        $db->query($update_sql);

        $mapping_arr = array();
        $mapping_arr[$member_idx]['member_id'] = $member_id;
        $mapping_arr[$member_idx]['member_name'] = $member_name;
        $mapping_arr[$member_idx]['tmp_pw'] = $tmp_pw;
        checkMailStatus($db, $country, 'MAIL_CASE_0016', $member_idx, $member_id, $mapping_arr);
    } else {
        $result = false;
        $code = 300;
        $msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0080', array());
    }
}

function makeTmpPw(){
    return mt_rand(1000000,9999999);
}
?>
