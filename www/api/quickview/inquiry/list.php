<?php
/*
 +=============================================================================
 | 
 | 퀵뷰 - FAQ 리스트 가져오기 API // /var/www/www/api/quickview/inquiry/list/get.php
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

if (isset($category_idx)) {
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
						FC.IDX
					FROM
						FAQ_CATEGORY FC
					WHERE 
						FC.FATHER_NO = ?
				)
			) AND
            STATUS = 'Y'
	";
	
	$db->query($select_sub_category_sql,array($category_idx,$category_idx));
	
	foreach($db->fetch() as $data){
		$json_result['data'][] = array(
			'category_idx'		=> $data['CATEGORY_IDX'],
			'sub_category'		=> $data['SUB_CATEGORY']
		);
	}
}
