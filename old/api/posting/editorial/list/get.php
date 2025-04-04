<?php
/*
 +=============================================================================
 | 
 | 에디토리얼 페이지 목록 가져오기
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.31
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

if (isset($size_type)) {
	$select_editorial_page_sql = "
		SELECT
			PP.IDX				AS PAGE_IDX,
			PP.PAGE_TITLE		AS PAGE_TITLE
		FROM
			PAGE_POSTING PP
		WHERE
			PP.COUNTRY = '".$country."' AND
			PP.POSTING_TYPE = 'EDTL' AND
			NOW() BETWEEN PP.DISPLAY_START_DATE AND PP.DISPLAY_END_DATE AND
			PP.DISPLAY_FLG = TRUE AND
			PP.DEL_FLG = FALSE
		ORDER BY
			PP.IDX DESC
	";
	
	$db->query($select_editorial_page_sql);
	
	foreach($db->fetch() as $page_data) {
		$page_idx = $page_data['PAGE_IDX'];
		
		$contents_cnt = $db->count("EDITORIAL_THUMB","PAGE_IDX = ".$page_idx." AND DEL_FLG = FALSE");
		
		if (!empty($page_idx) && $contents_cnt > 0) {
			$contents_location = null;
			
			$select_editorial_contents_sql = "
				SELECT
					EC.CONTENTS_LOCATION		AS CONTENTS_LOCATION
				FROM
					EDITORIAL_THUMB ET
					LEFT JOIN EDITORIAL_CONTENTS EC ON
					ET.IDX = EC.THUMB_IDX
				WHERE
					ET.PAGE_IDX = ".$page_idx." AND
					ET.SIZE_TYPE = '".$size_type."' AND
					ET.DISPLAY_NUM = 1 AND
					ET.DEL_FLG = FALSE
			";
			
			$db->query($select_editorial_contents_sql);
			
			foreach($db->fetch() as $contents_data) {
				$contents_location = $contents_data['CONTENTS_LOCATION'];
			}
		} else {
			continue;
		}
		
		$json_result['data'][] = array(
			'page_idx'				=>$page_data['PAGE_IDX'],
			'page_title'			=>$page_data['PAGE_TITLE'],
			'contents_location'		=>$contents_location,
            'size_type'				=>$size_type
		);
	}
}

?>