<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 마케팅 정보 조회
 | -------
 |
 | 최초 작성    : 윤재은
 | 최초 작성일  : 2023.01.13
 | 최종 수정일  : 
 | 버전      : 1.0
 | 설명      : 
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

if (!isset($country) || $member_idx == 0) {
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
    
    echo json_encode($json_result);
    exit;
}

if (isset($country) && $member_idx > 0) {
    $select_marketing_sql = "
        SELECT
            MB.RECEIVE_TEL_FLG AS RECEIVE_TEL_FLG,
            MB.RECEIVE_SMS_FLG AS RECEIVE_SMS_FLG,
            MB.RECEIVE_EMAIL_FLG AS RECEIVE_EMAIL_FLG
        FROM 
            MEMBER_".$country." MB
        WHERE 
            MB.IDX = ?
    ";

    $db->query($select_marketing_sql, [$member_idx]);

    foreach($db->fetch() as $data) {
        $json_result['data'][] = array(
            'receive_tel_flg'  => $data['RECEIVE_TEL_FLG'],
            'receive_sms_flg'  => $data['RECEIVE_SMS_FLG'],
            'receive_email_flg'=> $data['RECEIVE_EMAIL_FLG'],
        );
    }
}
?>
