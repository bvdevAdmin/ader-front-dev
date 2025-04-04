<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 맞춤 구매 정보 등록 / 수정
 | -------
 |
 | 최초 작성    : 윤재은
 | 최초 작성일  : 2023.06.02
 | 최종 수정일  : 
 | 버전      : 1.0
 | 설명      : 
 | 
 +=============================================================================
*/

$country = null;
if (isset($_SESSION['COUNTRY'])) {
    $country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
    $member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($country) && $member_idx > 0) {
    // Using parameterized query for count
    $custom_cnt = $db->count(
        "MEMBER_CUSTOM",
        "COUNTRY = ? AND MEMBER_IDX = ?",
        [$country, $member_idx]
    );
    
    if ($custom_cnt > 0) {
        // Perform update
        $db->update(
            "MEMBER_CUSTOM",
            array(
                'MEMBER_GENDER'    => $member_gender,
                'UPPER_SIZE_IDX'   => $upper_size_idx,
                'LOWER_SIZE_IDX'   => $lower_size_idx,
                'SHOES_SIZE_IDX'   => $shoes_size_idx
            ),
            "COUNTRY = ? AND MEMBER_IDX = ?",
            [$country, $member_idx]
        );
    } else {
        // Perform insert
        $db->insert(
            "MEMBER_CUSTOM",
            array(
                'COUNTRY'         => $country,
                'MEMBER_IDX'      => $member_idx,
                'MEMBER_GENDER'   => $member_gender,
                'UPPER_SIZE_IDX'  => $upper_size_idx,
                'LOWER_SIZE_IDX'  => $lower_size_idx,
                'SHOES_SIZE_IDX'  => $shoes_size_idx
            )
        );
    }
}
?>
