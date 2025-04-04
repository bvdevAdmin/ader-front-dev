<?php
/*
 +=============================================================================
 | 
 | 퀵뷰 - FAQ 가져오기 API
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
} else if (isset($_POST['country'])) {
	$country = $_POST['country'];
}

if ($country != null && $faq_idx != null) {
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

?>