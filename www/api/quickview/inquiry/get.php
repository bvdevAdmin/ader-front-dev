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

if (isset($faq_idx)) {
	$selec_faq_sql = "
		SELECT
			FQ.SUBCATEGORY	AS SUB_CATEGORY,
			FQ.QUESTION		AS QUESTION,
			FQ.ANSWER		AS ANSWER
		FROM
			FAQ FQ
		WHERE
			FQ.IDX = ?
	";
	
	$db->query($selec_faq_sql,array($faq_idx));
	
	foreach($db->fetch() as $data){
		$json_result['data'] = array(
			'subcategory'	=> $data['SUB_CATEGORY'],
			'question'		=> $data['QUESTION'],
			'answer'		=> $data['ANSWER']
		);
	}
}
