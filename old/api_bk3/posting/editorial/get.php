<?php
/*
 +=============================================================================
 | 
 | 에디토리얼 컨텐츠 리스트 가져오기
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

$cdn_img = "https://cdn-ader-orig.fastedge.net";
$cdn_vid = "https://media-ader.fastedge.net/adervod/_definst_";

$editorial_info = array();
$product_info = array();

if (isset($page_idx) && isset($size_type)) {
	$select_thumb_sql = "
		SELECT
			ET.IDX				AS THUMB_IDX,
			ET.THUMB_TYPE		AS THUMB_TYPE,
			ET.THUMB_LOCATION	AS IMG_LOCATION
		FROM
			EDITORIAL_THUMB ET
		WHERE
			ET.PAGE_IDX = " . $page_idx . " AND
			ET.SIZE_TYPE = '" . $size_type . "' AND
			ET.DEL_FLG = FALSE
		ORDER BY
			ET.DISPLAY_NUM ASC
	";
	
	$db->query($select_thumb_sql);
	
	$thumb_info = array();
	foreach($db->fetch() as $thumb_data) {
		$thumb_idx = $thumb_data['THUMB_IDX'];
			
		$contents_info = array();
		if (!empty($thumb_idx)) {
			$select_contents_sql = "
				SELECT
					EC.IDX					AS CONTENTS_IDX,
					EC.CONTENTS_TYPE		AS CONTENTS_TYPE,
					EC.CONTENTS_LOCATION	AS CONTENTS_URL
				FROM
					EDITORIAL_CONTENTS EC
				WHERE
					EC.THUMB_IDX = " . $thumb_idx . " AND
					SIZE_TYPE = '" . $size_type . "'
			";
			
			$db->query($select_contents_sql);

			foreach ($db->fetch() as $contents_data) {
				$contents_location = '';
				$contents_location_mob = '';
				if($contents_data['CONTENTS_TYPE'] == 'VID'){
					$contents_location = convertMOV($contents_data['CONTENTS_URL']);
				}
				else{
					$contents_location = $contents_data['CONTENTS_URL'];
				}
				$contents_info[] = array(
					'contents_idx' => $contents_data['CONTENTS_IDX'],
					'contents_type' => $contents_data['CONTENTS_TYPE'],
					'contents_url' => $contents_location,
				);
			}
		}

		$select_editorial_product_sql = "
			SELECT
				RP.PRODUCT_IDX		AS PRODUCT_IDX
			FROM
				EDITORIAL_PRODUCT RP
			WHERE
				RP.PAGE_IDX = ".$page_idx."
		";
		
		$db->query($select_editorial_product_sql);
		
		foreach($db->fetch() as $product_data) {
			$product_info[] = $product_data['PRODUCT_IDX'];
		}

		$editorial_info[] = array(
			'editorial_idx' => $thumb_idx,
			'thumb_type' => $thumb_data['THUMB_TYPE'],
			'img_location' => $thumb_data['IMG_LOCATION'],
			'contents_info' => $contents_info
		);

		$json_result['data']['editorial_info'] = $editorial_info;
		$json_result['data']['product_info'] = $product_info;
	}
}

function convertMOV($path){
	$path_arr = explode('/',$path);
	$path_arr[count($path_arr) - 1] = 'mp4:'.$path_arr[count($path_arr) - 1].'/playlist.m3u8';
	
	return implode('/',$path_arr);
}

?>