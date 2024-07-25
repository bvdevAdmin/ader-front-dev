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

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

$category_idx = 0;
if (isset($_POST['category_idx'])) {
	$category_idx = $_POST['category_idx'];
}

if (isset($country) && isset($category_idx)) {
	$select_sub_category_sql = "
		SELECT
			IDX				AS CATEGORY_IDX,
			SUBCATEGORY		AS SUB_CATEGORY
		FROM
			FAQ
		WHERE
			(
				CATEGORY_NO = ".$category_idx." OR
				CATEGORY_NO IN (
					SELECT
						IDX
					FROM
						FAQ_CATEGORY
					WHERE 
						FATHER_NO = ".$category_idx."
				)
			)
        AND
            STATUS = 'Y'
	";
	
	$db->query($select_sub_category_sql);
	
	foreach($db->fetch() as $data){
		$json_result['data'][] = array(
			'category_idx'		=> $data['CATEGORY_IDX'],
			'sub_category'		=> $data['SUB_CATEGORY']
		);
	}
}
