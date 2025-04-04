<?php
/*
 +=============================================================================
 | 
 | 퀵뷰 - FAQ 카테고리 불러오기 API
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

if (isset($country) && isset($category_type)) {
	if ($category_type == 'FAQ') {
		$select_faq_category_sql = "
			SELECT
				FC.IDX			AS CATEGORY_IDX,
				FC.TITLE		AS CATEGORY_TITLE
			FROM 
				FAQ_CATEGORY FC
			WHERE
				FC.FATHER_NO = 0 AND
				FC.STATUS = 'Y' AND
				FC.LANG = ?
			ORDER BY 
				FC.SEQ ASC
		";
		
		$db->query($select_faq_category_sql,array($country));
		
		foreach($db->fetch() as $data){
			$json_result['data'][] = array(
				'category_idx'		=>$data['CATEGORY_IDX'],
				'category_title'	=>$data['CATEGORY_TITLE']
			);
		}
	} else if ($category_type == 'INQ') {
		$select_code_mst_sql = "
			SELECT 
				DISTINCT CM.CODE_VALUE	AS CODE_VALUE,
				CM.CODE_NAME			AS CODE_NAME
			FROM
				CODE_MST CM
			WHERE
				CM.CODE_TYPE = 'BOARD_CATEGORY' AND
				CM.CODE_VALUE IN (
					'CAR','OAP','FAD','RAE','AFS','DAE','RST','PIQ','BGP','VUC','OSV'
				)
		";
		
		$db->query($select_code_mst_sql);
		
		foreach($db->fetch() as $data){
			$json_result['data'][] = array(
				'code_value' 	=> $data['CODE_VALUE'],
				'code_name' 	=> $data['CODE_NAME']
			);
		}
	}
}

?>