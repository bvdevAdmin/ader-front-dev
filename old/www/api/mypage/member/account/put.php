<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 비밀번호, 휴대전화 번호 수정
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
    
    echo json_encode($json_result);
    exit;
}

if ($member_pw != null || $member_tel_mobile != null) {
    try {
        $update_param = array();
        $update_sql_parts = array();
        
        if ($member_pw != null) {
            $update_param['MEMBER_PW'] = md5($member_pw);
            $update_sql_parts[] = "PW_DATE = NOW()";
        }

        if ($member_tel_mobile != null) {
            $update_param['TEL_MOBILE'] = $member_tel_mobile;
        }
        
        $member_prev = getMEMBER_prev($db, $country, $member_idx);
        
        $set_clause = [];
        foreach ($update_param as $key => $value) {
            $set_clause[] = "$key = ?";
        }
        $set_clause = implode(", ", $set_clause);
        if (!empty($update_sql_parts)) {
            $set_clause .= ", " . implode(", ", $update_sql_parts);
        }
        
        $sql = "UPDATE MEMBER_" . $country . " SET " . $set_clause . " WHERE IDX = ?";
        $params = array_values($update_param);
        $params[] = $member_idx;

        $db->query($sql, $params);
        
        $db_result = $db->affectedRows();
        
        if ($db_result > 0) {
            addMEMBER_log($db, $member_prev);
            
            if (isset($member_tel_mobile)) {
                $_SESSION['TEL_MOBILE'] = $member_tel_mobile;
            }
        }
        
        $db->commit();
    } catch (mysqli_sql_exception $e) {
        $db->rollback();
        
        print_r($e);
        
        $json_result['code'] = 302;
        $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0102', array());
        
        echo json_encode($json_result);
        exit;
    }
} else {
    $json_result['code'] = 301;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0025', array());
    
    echo json_encode($json_result);
    exit;
}
?>
