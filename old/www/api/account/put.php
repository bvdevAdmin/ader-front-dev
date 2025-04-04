<?php
/*
 +=============================================================================
 | 
 | 비밀번호 변경
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

$country = $_SERVER['HTTP_COUNTRY'] ?? null;
$member_idx = $_POST['member_idx'] ?? null;
$member_pw = $_POST['member_pw'] ?? null;

if (!$country || !$member_idx || !$member_pw) {
    $json_result = [
        'result' => false,
        'code'   => 401,
        'msg'    => getMsgToMsgCode($db, $country, 'MSG_B_ERR_0072', [])
    ];
    
    echo json_encode($json_result);
    exit;
} else {
    $update_data = [
        'MEMBER_PW' => md5($member_pw),
        'PW_DATE'   => NOW()
    ];

    $db->update(
        "MEMBER_$country",
        $update_data,
        'IDX = ?',
        [$member_idx]
    );

    $json_result = [
        'result' => true,
        'code'   => 200,
        'msg'    => 'Password updated successfully'
    ];
    
    echo json_encode($json_result);
}
?>
