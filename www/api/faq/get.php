<?php
/*
 +=============================================================================
 | 
 | FAQ 카테고리 취득 api // '/var/www/www/api/mypage/faq/get.php'
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.09
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if(isset($country) && (isset($category_no) || isset($keyword))) {
    $where = '';
	if($category_no != null) {
        $where .= ' AND (FC.IDX = ? OR FC.FATHER_NO = ?)';
        $where_values = array($category_no,$category_no);
    } 
    else if($keyword != null) {
        $where .= ' AND (FAQ.QUESTION LIKE ? OR FC.TITLE LIKE ? OR FAQ.ANSWER LIKE ?)';
        $where_values = array('%'.$keyword.'%','%'.$keyword.'%','%'.$keyword.'%');
    }
    
    $db->query('
        SELECT 
            FAQ.IDX				AS FAQ_IDX,
            FAQ.SEQ				AS SEQ,
            FAQ.CATEGORY_NO		AS CATEGORY_NO,
            FAQ.SUBCATEGORY		AS SUBCATEGORY,
            FAQ.QUESTION		AS QUESTION,
            FAQ.ANSWER			AS ANSWER,
            FC.TITLE			AS TITLE
        FROM 
            FAQ  FAQ
            LEFT JOIN FAQ_CATEGORY FC ON
            FAQ.CATEGORY_NO = FC.IDX
            '.$where.'
        WHERE
            FAQ.STATUS = "Y" AND
            FC.IDX IS NOT NULL
        ORDER BY
            FAQ.CATEGORY_NO, FAQ.SEQ, FAQ.IDX
    ',$where_values);
    
    foreach($db->fetch() as $data){
        $json_result['data'][] = array(
            'idx'               => intval($data['FAQ_IDX']),
            'seq'               => intval($data['SEQ']),
            'category_no'       => intval($data['CATEGORY_NO']),
            'subcategory'       => $data['SUBCATEGORY'],
            'question'          => $data['QUESTION'],
            'answer'            => $data['ANSWER'],
            'title'           	=> $data['TITLE']
        );
    }
} else {
    $json_result['code'] = 300;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0002', array());
}

