<?php
/*
 +=============================================================================
 | 
 | 퀵뷰 - FAQ 리스트 가져오기 API
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

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

if (isset($country) && isset($category_idx)) {
	$select_sub_category_sql = "
		SELECT
			FQ.IDX				AS CATEGORY_IDX,
			FQ.SUBCATEGORY		AS SUB_CATEGORY
		FROM
			FAQ FQ
		WHERE
			(
				FQ.CATEGORY_NO = ? OR
				FQ.CATEGORY_NO IN (
					SELECT
						S_FC.IDX
					FROM
						FAQ_CATEGORY S_FC
					WHERE 
						S_FC.FATHER_NO = ?
				)
			) AND
            FQ.STATUS = 'Y'
	";
	
	$db->query($select_sub_category_sql,array($category_idx,$category_idx));
	
	foreach($db->fetch() as $data){
		$json_result['data'][] = array(
			'category_idx'		=> $data['CATEGORY_IDX'],
			'sub_category'		=> $data['SUB_CATEGORY']
		);
	}
}

?>