<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 마케팅 정보 수정
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

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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
    exit;
}

if (isset($receive_tel_flg) || isset($receive_sms_flg) || isset($receive_email_flg)) {
    try {
        $receive_tel_flg_bool = ($receive_tel_flg === 'true') ? 1 : 0;
        $receive_sms_flg_bool = ($receive_sms_flg === 'true') ? 1 : 0;
        $receive_email_flg_bool = ($receive_email_flg === 'true') ? 1 : 0;

        $accept_marketing_flg = ($receive_tel_flg === 'true' || $receive_sms_flg === 'true' || $receive_email_flg === 'true') ? 1 : 0;

        $member_prev = getMEMBER_prev($db, $country, $member_idx);

        // Update array
        $update_data = array(
            'RECEIVE_TEL_FLG' => $receive_tel_flg_bool,
            'RECEIVE_TEL_DATE' => ($receive_tel_flg === 'true') ? NOW() : null,
            'RECEIVE_SMS_FLG' => $receive_sms_flg_bool,
            'RECEIVE_SMS_DATE' => ($receive_sms_flg === 'true') ? NOW() : null,
            'RECEIVE_EMAIL_FLG' => $receive_email_flg_bool,
            'RECEIVE_EMAIL_DATE' => ($receive_email_flg === 'true') ? NOW() : null,
            'ACCEPT_MARKETING_FLG' => $accept_marketing_flg
        );

        // Remove null values to avoid updating those fields
        $update_data = array_filter($update_data, function($value) { return $value !== null; });

        // Perform update
        $db->update(
            "MEMBER_$country",
            $update_data,
            "IDX = ".$member_idx
        );

        if ($db->affectedRows() > 0) {
            addMEMBER_log($db, $member_prev);
        }

        $db->commit();
    } catch (mysqli_sql_exception $exception) {
        $db->rollback();
        print_r($exception);
        
        $json_result['code'] = 302;
        $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0019', array());
        
        echo json_encode($json_result);
        exit;
    }
}

?>
