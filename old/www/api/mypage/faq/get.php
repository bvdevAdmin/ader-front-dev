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

$country = isset($_SESSION['COUNTRY']) ? $_SESSION['COUNTRY'] : (isset($_SERVER['HTTP_COUNTRY']) ? $_SERVER['HTTP_COUNTRY'] : null);
$category_no = $category_no ?? null;
$keyword = $keyword ?? null;

if (isset($country) && (isset($category_no) || isset($keyword))) {
    $params = [];
    $where = "FC.LANG = ? ";
    $params[] = $country;

    if ($category_no != null) {
        $where .= " AND (FC.IDX = ? OR FC.FATHER_NO = ?)";
        $params[] = $category_no;
        $params[] = $category_no;
    } else if ($keyword != null) {
        $where .= " AND (FAQ.QUESTION LIKE ? OR FAQ.ANSWER LIKE ? OR FC.TITLE LIKE ?)";
        $keyword_param = '%' . $keyword . '%';
        $params[] = $keyword_param;
        $params[] = $keyword_param;
        $params[] = $keyword_param;
    }

    $faq_category_sql = "
        SELECT DISTINCT
            FAQ.CATEGORY_NO    AS CATEGORY_NO,
            FC.TITLE           AS CATEGORY_TITLE
        FROM 
            FAQ FAQ
            LEFT JOIN FAQ_CATEGORY FC ON FAQ.CATEGORY_NO = FC.IDX
        WHERE 
            $where
        ORDER BY 
            CATEGORY_NO
    ";
    $db->query($faq_category_sql, $params);

    $category_data = $db->fetch();
    $category_nos = array_column($category_data, 'CATEGORY_NO');

    if (!empty($category_nos)) {
        $faq_select_sql = "
            SELECT
                CATEGORY_NO,
                QUESTION,
                ANSWER
            FROM
                FAQ
            WHERE
                CATEGORY_NO IN (" . implode(',', array_fill(0, count($category_nos), '?')) . ")
            ORDER BY 
                SEQ ASC
        ";
        $db->query($faq_select_sql, $category_nos);

        $faqs = $db->fetch();
        $faq_info = [];

        foreach ($category_data as $category) {
            $faq_info[$category['CATEGORY_NO']] = [
                'category_title' => $category['CATEGORY_TITLE'],
                'faq_info'       => []
            ];
        }

        foreach ($faqs as $faq) {
            $faq_info[$faq['CATEGORY_NO']]['faq_info'][] = [
                'question'     => $faq['QUESTION'],
                'answer'       => $faq['ANSWER']
            ];
        }

        $json_result['data'] = array_values($faq_info);
    } else {
        $json_result['data'] = [];
    }
} else {
    $json_result['code'] = 300;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0002', array());
}
?>
