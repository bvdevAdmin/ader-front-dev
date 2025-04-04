<?php
/*
 +=============================================================================
 | 
 | 팝업조회
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2022.10.25
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$country = null;
if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

$non_param_url = explode('?',$url)[0];

if ($country != null) {
	$select_display_popup_sql = "
		SELECT
			DP.IDX				AS POPUP_IDX,
			DP.TITLE			AS POPUP_TITLE,
			DP.CONTENTS			AS POPUP_CONTENTS,
			DP.WIDTH			AS POPUP_WIDTH,
			DP.HEIGHT			AS POPUP_HEIGHT,
			DP.CLOSE_FLG		AS POPUP_CLOSE_FLG,
			PU.URL				AS POPUP_URL,
			PU.POPUP_URL_TYPE	AS POPUP_URL_TYPE
		FROM 
			DISPLAY_POPUP DP
			LEFT JOIN POPUP_URL PU ON
			DP.IDX = PU.POPUP_IDX
		WHERE
			DP.COUNTRY = ? AND
			NOW() BETWEEN DP.DISPLAY_START_DATE AND DP.DISPLAY_END_DATE AND
			DP.DISPLAY_FLG = TRUE
		ORDER BY 
			DP.UPDATE_DATE ASC
	";

	$db->query($select_display_popup_sql,array($country));

	foreach($db->fetch() as $data){
		if($data['POPUP_URL_TYPE'] == 'PRODUCT'){
			if ($data['POPUP_URL'] == $url) {
				$json_result['data'] = array(
					'idx'				=>$data['POPUP_IDX'],
					'title'				=>$data['POPUP_TITLE'],
					'contents'			=>$data['POPUP_CONTENTS'],
					'width'				=>$data['POPUP_WIDTH'],
					'height'			=>$data['POPUP_HEIGHT'],
					'close_flg'			=>$data['POPUP_CLOSE_FLG'],
					'url'				=>$data['POPUP_URL'],
					'popup_url_type'	=>$data['POPUP_URL_TYPE']
				);
			}
		} else {
			if ($data['POPUP_URL'] == $non_param_url) {
				$json_result['data'] = array(
					'idx'				=>$data['POPUP_IDX'],
					'title'				=>$data['POPUP_TITLE'],
					'contents'			=>$data['POPUP_CONTENTS'],
					'width'				=>$data['POPUP_WIDTH'],
					'height'			=>$data['POPUP_HEIGHT'],
					'close_flg'			=>$data['POPUP_CLOSE_FLG'],
					'url'				=>$data['POPUP_URL'],
					'popup_url_type'	=>$data['POPUP_URL_TYPE']
				);
			}
		}
	}
}

?>