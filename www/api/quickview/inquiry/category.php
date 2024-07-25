<?php
/*
 +=============================================================================
 | 
 | 퀵뷰 - FAQ 카테고리 불러오기 API // /var/www/www/api/quickview/inquiry/category/get.php
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

if (isset($country) && isset($category_type)) {
	if ($category_type == 'FAQ') {
		$faq_category_get_sql = "
			SELECT
				FC.IDX			AS CATEGORY_IDX,
				FC.TITLE		AS CATEGORY_TITLE
			FROM 
				FAQ_CATEGORY FC
			WHERE
				FC.FATHER_NO = 0 AND
				FC.STATUS = 'Y' AND
				FC.LANG = '".$country."'
			ORDER BY 
				FC.SEQ ASC
		";
		
		$db->query($faq_category_get_sql);
		
		foreach($db->fetch() as $data){
			$json_result['data'][] = array(
				'category_idx'		=>$data['CATEGORY_IDX'],
				'category_title'	=>$data['CATEGORY_TITLE']
			);
		}
	} else if ($category_type == 'INQ') {
		$inquiry_category_get_sql = "
			SELECT 
				DISTINCT CODE_VALUE,
				CODE_NAME
			FROM
				CODE_MST
			WHERE
				CODE_TYPE = 'BOARD_CATEGORY'
			AND 
				CODE_VALUE IN ('CAR','OAP','FAD','RAE','AFS','DAE','RST','PIQ','BGP','VUC','OSV')
		";
		
		$db->query($inquiry_category_get_sql);
		
		foreach($db->fetch() as $data){
			$json_result['data'][] = array(
				'code_value' 	=> $data['CODE_VALUE'],
				'code_name' 	=> $data['CODE_NAME']
			);
		}
	}
}

