<?php
/*
 +=============================================================================
 | 
 | 공통 : 공지사항 목록 
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.02.17
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$country = null;
if (isset($_POST['country'])) {
	$country = $_POST['country'];
}
if($country != null){
	$select_notice_sql = "
		SELECT 
			CM.CODE_NAME	AS CODE_NAME,
			PB.IDX			AS NOTICE_IDX,
			PB.COUNTRY		AS COUNTRY,
			PB.TITLE		AS TITLE,
			REPLACE(
				PB.CONTENTS,
				'/scripts/smarteditor2/upload/',
				'http://116.124.128.246:81/scripts/smarteditor2/upload/'
			)				AS CONTENTS,
			PB.FIX_FLG		AS FIX_FLG
		FROM 
			PAGE_BOARD PB
			LEFT JOIN CODE_MST CM ON 	
			PB.CATEGORY = CM.CODE_VALUE
		WHERE 
			PB.DEL_FLG = FALSE AND
			PB.BOARD_TYPE = 'NTC' AND
			PB.COUNTRY = '".$country."'
	";
    
	$db->query($select_notice_sql);

	foreach($db->fetch() as $data){
		$json_result['data'][] = array(
			'code_name'         => $data['CODE_NAME'],
			'notice_idx'        => $data['NOTICE_IDX'],
			'country'           => $data['COUNTRY'],
			'title'             => $data['TITLE'],
			'contents'          => $data['CONTENTS'],
			'fix_flg'           => $data['FIX_FLG'],
		);
	}
}

?>