<?php
/*
 +=============================================================================
 | 
 | 공지사항 목록 
 | -------
 |
 | 최초 작성    : 박성혁
 | 최초 작성일  : 2023.01.09
 | 최종 수정일  : 
 | 버전      : 1.0
 | 설명      : 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

$country = isset($_SESSION['COUNTRY']) ? $_SESSION['COUNTRY'] : null;
$member_idx = isset($_SESSION['MEMBER_IDX']) ? $_SESSION['MEMBER_IDX'] : 0;

if (!$country || $member_idx == 0) {
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
    
    echo json_encode($json_result);
    exit;
}

$select_notice_sql = "
    SELECT 
        CM.CODE_NAME AS CODE_NAME,
        PB.IDX AS NOTICE_IDX,
        PB.COUNTRY AS COUNTRY,
        PB.TITLE AS TITLE,
        REPLACE(
            PB.CONTENTS,
            '/scripts/smarteditor2/upload/',
            'https://wcc.fivespace.zone/scripts/smarteditor2/upload/'
        ) AS CONTENTS,
        PB.FIX_FLG AS FIX_FLG
    FROM 
        PAGE_BOARD PB
        LEFT JOIN CODE_MST CM ON PB.CATEGORY = CM.CODE_VALUE
    WHERE 
        PB.DEL_FLG = FALSE AND
        PB.BOARD_TYPE = 'NTC' AND
        PB.COUNTRY = ?
    ORDER BY
        PB.FIX_FLG DESC, DISPLAY_NUM ASC
";

$db->query($select_notice_sql, [$country]);

foreach ($db->fetch() as $data) {
    $json_result['data'][] = array(
        'code_name' => $data['CODE_NAME'],
        'notice_idx' => $data['NOTICE_IDX'],
        'country' => $data['COUNTRY'],
        'title' => $data['TITLE'],
        'contents' => htmlspecialchars_decode($data['CONTENTS'], ENT_QUOTES),
        'fix_flg' => $data['FIX_FLG'],
    );
}
?>
