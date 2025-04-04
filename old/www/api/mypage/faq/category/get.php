<?php
/*
 +=============================================================================
 | 
 | FAQ 카테고리 취득 api
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

$country = null;
if (isset($_SESSION['COUNTRY'])) {
    $country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
    $country = $_SERVER['HTTP_COUNTRY'];
}

if (!isset($country)) {
    $json_result['code'] = 300;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0002', array());
} else {
	$select_category_sql = "
        SELECT
            FC.IDX         AS FC_IDX,
            FC.TITLE       AS TITLE
        FROM
            dev.FAQ_CATEGORY FC
        WHERE
            FATHER_NO = 0 AND
            LANG = ? AND
            STATUS = 'Y'
        ORDER BY
            SEQ, IDX ASC
    ";

    $db->query($select_category_sql, [$country]);

    foreach($db->fetch() as $data) {
        $json_result['data'][] = array(
            'no'        => intval($data['FC_IDX']),
            'title'     => $data['TITLE']
        );
    }
}
?>