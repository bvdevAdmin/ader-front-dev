<?php
/*
 +=============================================================================
 | 
 | 매장 찾기 - 지도 표시용 좌표 취득
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.02.15
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

$select_store_location_sql = "
	(
		SELECT
			SPC.IDX			AS STORE_IDX,
			'SPC'			AS STORE_TYPE,
			SPC.LAT			AS LAT,
			SPC.LNG			AS LNG
		FROM
			STORE_SPACE SPC
		WHERE
			SPC.STORE_ADDR IS NOT NULL AND
			SPC.LAT IS NOT NULL AND
			SPC.LNG IS NOT NULL AND
			SPC.DEL_FLG = FALSE
	) UNION (
		SELECT
			PLG.IDX			AS STORE_IDX,
			'PLG'			AS STORE_TYPE,
			PLG.LAT			AS LAT,
			PLG.LNG			AS LNG
		FROM
			STORE_PLUGSHOP PLG
		WHERE
			PLG.STORE_ADDR IS NOT NULL AND
			PLG.LAT IS NOT NULL AND
			PLG.LNG IS NOT NULL AND
			PLG.DEL_FLG = FALSE
	) UNION (
		SELECT
			STC.IDX			AS STORE_IDX,
			'STC'			AS STORE_TYPE,
			STC.LAT			AS LAT,
			STC.LNG			AS LNG
		FROM
			STORE_STOCKIST STC
		WHERE
			STC.STOCKIST_TYPE = 'GLB' AND
			STC.STORE_ADDR IS NOT NULL AND
			STC.LAT IS NOT NULL AND
			STC.LNG IS NOT NULL AND
			STC.DEL_FLG = FALSE
	)
";

$db->query($select_store_location_sql);

foreach($db->fetch() as $location_data) {
	$json_result['data'][] = array(
		'store_idx'		=>$location_data['STORE_IDX'],
		'store_type'	=>$location_data['STORE_TYPE'],
		'lat'			=>$location_data['LAT'],
		'lng'			=>$location_data['LNG'],
	);
}

?>