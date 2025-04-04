<?php
/*
 +=============================================================================
 | 
 | 공통 : 공지사항 목록 // '/var/www/www/api/common/notice/get.php'
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.02.17
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if ($country != null) {
	$select_board_notice_sql = "
		SELECT 
			BN.IDX				AS NOTICE_IDX,
			BN.COUNTRY			AS COUNTRY,
			BN.BOARD_TITLE		AS BOARD_TITLE,
			BN.BOARD_CONTENTS	AS BOARD_CONTENTS
		FROM 
			BOARD_NOTICE BN
		WHERE 
			BN.COUNTRY = ? AND
			BN.DISPLAY_FLG = TRUE AND
            (NOW() BETWEEN BN.DISPLAY_START_DATE AND BN.DISPLAY_END_DATE) AND
			BN.DEL_FLG = FALSE
		ORDER BY
			BN.FIX_FLG DESC, BN.DISPLAY_NUM ASC
	";
    
	$db->query($select_board_notice_sql,array($country));

	foreach($db->fetch() as $data){
		$board_contents = str_replace('/scripts/smarteditor2/upload/',smart_editor,$data['BOARD_CONTENTS']);
		$json_result['data'][] = array(
			'notice_idx'		=>$data['NOTICE_IDX'],
			'country'			=>$data['COUNTRY'],
			'board_title'		=>$data['BOARD_TITLE'],
			'board_contents'	=>$board_contents
		);
	}
}

