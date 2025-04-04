<?php
/*
 +=============================================================================
 | 
 | 게시물_룩북 - 룩북 프로젝트 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.02.10
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY'])) {
	if ($db->count('COLLECTION_PROJECT','COUNTRY = ?',array($_SERVER['HTTP_COUNTRY'])) > 0) {
		$db->query('
			SELECT
				CP.IDX					AS PROJECT_IDX,
				CP.PROJECT_NAME			AS PROJECT_NAME,
				CP.PROJECT_DESC			AS PROJECT_DESC,
				CP.PROJECT_TITLE		AS PROJECT_TITLE,
				CP.THUMB_LOCATION		AS THUMB_LOCATION
			FROM
				COLLECTION_PROJECT CP
			WHERE
				CP.COUNTRY = ?
			AND
				DEL_FLG = FALSE
			ORDER BY
				CP.DISPLAY_NUM DESC
		',array($_SERVER['HTTP_COUNTRY']));
		
		foreach($db->fetch() as $data) {
			$json_result['data'][] = array(
				'project_idx'		=>$data['PROJECT_IDX'],
				'project_name'		=>$data['PROJECT_NAME'],
				'project_desc'		=>$data['PROJECT_DESC'],
				'project_title'		=>$data['PROJECT_TITLE'],
				'thumb_location'	=>$data['THUMB_LOCATION']
			);
		}
	} 
	else {
		$code = 301;
		$msg = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0094', array());
	}
}

?>