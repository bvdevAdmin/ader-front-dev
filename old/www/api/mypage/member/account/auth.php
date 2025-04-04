<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 인증번호,인증기간 설정/체크처리
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

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
    $member_idx = $_SESSION['MEMBER_IDX'];
}

if ($member_idx > 0 && $auth_no != null) {
    $cnt_member = $db->count(
        "MEMBER_KR",
        "IDX = ? AND AUTH_NO = ? AND NOW() <= AUTH_DATE",
        array($member_idx, $auth_no)
    );
    if ($cnt_member == 0) {
        $json_result['code'] = 400;
    }
}
?>
