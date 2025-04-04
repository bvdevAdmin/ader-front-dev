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
include_once("/var/www/www/api/common.php");
$country = null;
if(isset($_POST['country'])){
    $country = $_POST['country'];
}

$category_no = null;
if(isset($_POST['category_no'])){
    $category_no = $_POST['category_no'];
}

$keyword = null;
if(isset($_POST['keyword'])){
    $keyword = $_POST['keyword'];
}

if($country != null && ($category_no != null || $keyword != null)) {
    $where = "";
	if($category_no != null){
        $where .= " AND (FC.IDX = ".$category_no." OR FC.FATHER_NO = ".$category_no.")";
    } else if($keyword != null){
        $where .= " AND (FAQ.QUESTION LIKE '%".$keyword."%' OR FC.TITLE LIKE '%".$keyword."%' OR FAQ.ANSWER LIKE '%".$keyword."%')";
    }
    
	$select_faq_sql = "
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
			".$where."
		WHERE
			FAQ.STATUS = 'Y' AND
			FC.IDX IS NOT NULL
		ORDER BY
			FAQ.CATEGORY_NO, FAQ.SEQ, FAQ.IDX
    ";
    
    $db->query($select_faq_sql);
    
    foreach($db->fetch() as $data){
        $json_result['data'][] = array(
            'idx'               => $data['FAQ_IDX'],
            'seq'               => $data['SEQ'],
            'category_no'       => $data['CATEGORY_NO'],
            'subcategory'       => $data['SUBCATEGORY'],
            'question'          => $data['QUESTION'],
            'answer'            => $data['ANSWER'],
            'title'           	=> $data['TITLE']
        );
    }
} else {
    $json_result['code'] = 300;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0002', array());
	
	echo json_encode($json_result);
	exit;
}

?>