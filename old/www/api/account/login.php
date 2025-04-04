<?php
/*
 +=============================================================================
 | 
 | 회원 로그인 - 독립몰
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

include_once(dir_f_api."/common.php");
include_once(dir_f_api."/account/common.php");
include_once(dir_f_api."/send/send-mail.php");

$country = $_SERVER['HTTP_COUNTRY'] ?? null;
$return_url = $_POST['r_url'] ?? null;
$member_id = $_POST['member_id'] ?? null;
$member_pw = $_POST['member_pw'] ?? null;

if (!$member_id) {
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0004', array());
    
    echo json_encode($json_result);
    exit;
}

if (!$member_pw) {
    $json_result['code'] = 402;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0001', array());
    
    echo json_encode($json_result);
    exit;
}

/* 로그인 - 로그인 회원정보 체크 */
$member_cnt = $db->count("MEMBER_".$country, "MEMBER_ID = ?", [$member_id]);
if ($member_cnt > 0) {
    $param_select_sql = "
        MB.MEMBER_ID = ?
    ";
    
    /* 로그인 - 로그인 회원 생일 바우처 발급일 정보 조회 */
    $param_date = getBirthDateParam($db, $country);
    
    $param_date_sql = "";
    
    if ($param_date != null) {
        $param_date_sql = "
            , CASE
                WHEN MB.MEMBER_BIRTH IS NULL
                THEN NULL
                ELSE DATE_FORMAT(
                    DATE_SUB(
                        MB.MEMBER_BIRTH,
                        INTERVAL {$param_date['date_ago']} DAY
                    ),
                    '%m-%d'
                )
            END AS USABLE_START_DATE
            , CASE
                WHEN MB.MEMBER_BIRTH IS NULL
                THEN NULL
                ELSE DATE_FORMAT(
                    DATE_ADD(
                        MB.MEMBER_BIRTH,
                        INTERVAL {$param_date['date_later']} DAY
                    ),
                    '%m-%d'
                )
            END AS USABLE_END_DATE
        ";
    }
    
    /* 로그인 - 로그인 회원정보 조회 */
    $member = getLoginMember($db, $country, $member_id, $param_select_sql, $param_date_sql);
    if ($member != null) {
        loginMember($db, $country, $member_pw, $member, $return_url);
    }
} else {
    $json_result['code'] = 300;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0100', array());
    
    $json_result['result'] = false;
    
    echo json_encode($json_result);
    exit;
}

?>
