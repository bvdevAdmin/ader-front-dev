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

if (isset($_SERVER['HTTP_COUNTRY']) != null) {
	$select_posting_editorial_sql = "
		SELECT
			PE.IDX						AS PAGE_IDX,
			PE.EDITORIAL_TITLE			AS TITLE,
			J_WC.CONTENTS_LOCATION		AS W_LOCATION,
			J_MC.CONTENTS_LOCATION		AS M_LOCATION
		FROM
			POSTING_EDITORIAL PE
			
			LEFT JOIN (
				SELECT
					S_WC.PAGE_IDX			AS PAGE_IDX,
					S_WC.CONTENTS_LOCATION	AS CONTENTS_LOCATION
				FROM
					EDITORIAL_CONTENTS S_WC
				WHERE
					S_WC.SIZE_TYPE = 'W' AND
					S_WC.DEL_FLG = FALSE
				ORDER BY
					S_WC.DISPLAY_NUM ASC
				LIMIT
					0,1
			) AS J_WC ON
			PE.IDX = J_WC.PAGE_IDX
			
			LEFT JOIN (
				SELECT
					S_MC.PAGE_IDX			AS PAGE_IDX,
					S_MC.CONTENTS_LOCATION	AS CONTENTS_LOCATION
				FROM
					EDITORIAL_CONTENTS S_MC
				WHERE
					S_MC.SIZE_TYPE = 'W' AND
					S_MC.DEL_FLG = FALSE
				ORDER BY
					S_MC.DISPLAY_NUM ASC
				LIMIT
					0,1
			) AS J_MC ON
			PE.IDX = J_MC.PAGE_IDX
		WHERE
			PE.COUNTRY = ? AND
			PE.DISPLAY_FLG = TRUE AND
			NOW() BETWEEN PE.DISPLAY_START_DATE AND PE.DISPLAY_END_DATE AND
			PE.DEL_FLG = FALSE
		ORDER BY
			PE.DISPLAY_NUM ASC
	";
	
	$db->query($select_posting_editorial_sql,array($_SERVER['HTTP_COUNTRY']));
	
	foreach($db->fetch() as $data) {
		$json_result['data'][] = array(
			'page_idx'			=>$data['PAGE_IDX'],
			'title'				=>$data['TITLE'],
			'w_location'		=>$data['W_LOCATION'],
			'm_location'		=>$data['M_LOCATION']
		);
	}
}

?>