<?php
/*
 +=============================================================================
 | 
 | FAQ 카테고리 취득 api
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.09
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if(isset($_SERVER['HTTP_COUNTRY'])){
    $country = $_SERVER['HTTP_COUNTRY'];
}

$category_no = null;
if (isset($_POST['category_no'])) {
	$category_no = $_POST['category_no'];
}

$keyword = null;
if (isset($_POST['keyword'])) {
	$keyword = $_POST['keyword'];
}

if (isset($country) && (isset($category_no) || isset($keyword))) {
    $where = " FC.LANG = '".$country."' ";
	
	if($category_no != null){
        $where .= ' AND (FC.IDX = '.$category_no.' OR FC.FATHER_NO = '.$category_no.') ';
    } else if($keyword != null){
        $where .= " AND (FAQ.QUESTION LIKE '%".$keyword."%' OR FAQ.ANSWER LIKE '%".$keyword."%' OR FC.TITLE LIKE '%".$keyword."%') ";
    }
    
	$faq_category_sql = "
        SELECT
            distinct FAQ.CATEGORY_NO    AS CATEGORY_NO,
            FC.TITLE                    AS CATEGORY_TITLE
        FROM 
            FAQ FAQ LEFT JOIN
            FAQ_CATEGORY FC
        ON
            FAQ.CATEGORY_NO = FC.IDX
        WHERE 
            ".$where."
        ORDER BY 
            CATEGORY_NO
    ";
    $db->query($faq_category_sql);
    
    foreach($db->fetch() as $category_data) {
        $faq_select_sql = "
            SELECT
                IDX,
                SEQ,
                CATEGORY_NO,
                SUBCATEGORY,
                QUESTION,
                ANSWER,
                REG_DATE
            FROM
                FAQ
            WHERE
                CATEGORY_NO = ".$category_data['CATEGORY_NO']."
            ORDER BY 
                SEQ ASC
        ";
        $db->query($faq_select_sql);

        $faq_info = array();
        foreach($db->fetch() as $faq_data){
            $faq_info[] = array(
                'no' => intval($faq_data['IDX']),
                'seq' => intval($faq_data['SEQ']),
                'category_no' => intval($faq_data['CATEGORY_NO']),
                'subcategory' => $faq_data['SUBCATEGORY'],
                'question' => $faq_data['QUESTION'],
                'answer' => $faq_data['ANSWER']
            );
        }
    
        $json_result['data'][] = array(
            'category_no'=>intval($category_data['CATEGORY_NO']),
            'category_title'=>$category_data['CATEGORY_TITLE'],
            'faq_info'=>$faq_info
        );
    }
} else{
    $json_result['code'] = 300;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0002', array());
	
	echo json_encode($json_result);
	exit;
}

?>