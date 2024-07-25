<?php
/*
 +=============================================================================
 | 
 | 퀵뷰 - FAQ 가져오기 API // /var/www/www/api/quickview/inquiry/get.php
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.03.14
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
$faq_idx = $_POST['faq_idx'];

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

if (isset($country) && isset($faq_idx)) {
	$get_faq_sql = "
		SELECT
			SUBCATEGORY,
			QUESTION,
			ANSWER
		FROM
			FAQ
		WHERE
			IDX = ".$faq_idx."
	";
	
	$db->query($get_faq_sql);
	
	foreach($db->fetch() as $data){
		$json_result['data'] = array(
			'subcategory'	=> $data['SUBCATEGORY'],
			'question'		=> $data['QUESTION'],
			'answer'		=> $data['ANSWER']
		);
	}
}
