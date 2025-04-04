<?php
/*
 +=============================================================================
 | 
 | 에디토리얼 컨텐츠 
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

if (isset($page_idx)) {
	$e_thumb_V_W	= getEditorial_thumb($db,$page_idx,"VID","W");
	$e_thumb_I_W	= getEditorial_thumb($db,$page_idx,"IMG","W");
	
	$e_thumb_V_M	= getEditorial_thumb($db,$page_idx,"VID","M");
	$e_thumb_I_M	= getEditorial_thumb($db,$page_idx,"IMG","M");
	
	$e_contents_V_W	= getEditorial_contents($db,$page_idx,"VID","W");
	$e_contents_I_W	= getEditorial_contents($db,$page_idx,"IMG","W");
	
	$e_contents_V_M	= getEditorial_contents($db,$page_idx,"VID","M");
	$e_contents_I_M	= getEditorial_contents($db,$page_idx,"IMG","M");
	
	$e_thumb_W		= array_merge($e_thumb_V_W,$e_thumb_I_W);
	$e_contents_W	= array_merge($e_contents_V_W,$e_contents_I_W);
	
	$e_thumb_M		= array_merge($e_thumb_V_M,$e_thumb_I_M);
	$e_contents_M	= array_merge($e_contents_V_M,$e_contents_I_M);
	
	$json_result['data'] = array(
		'e_thumb_W'			=>setDisplay_num($e_thumb_W),
		'e_contents_W'		=>setDisplay_num($e_contents_W),
		
		'e_thumb_M'			=>setDisplay_num($e_thumb_M),
		'e_contents_M'		=>setDisplay_num($e_contents_M)
	);
}

function getEditorial_thumb($db,$page_idx,$type,$size) {
	$e_thumb = array();
	
	$select_editorial_thumb_sql = "
		SELECT
			ET.IDX					AS THUMB_IDX,
			ET.THUMB_LOCATION		AS T_LOCATION
		FROM
			EDITORIAL_THUMB ET
		WHERE
			ET.PAGE_IDX			= ? AND
			ET.THUMB_TYPE		= ? AND
			ET.SIZE_TYPE		= ? AND
			ET.DEL_FLG			= FALSE
		ORDER BY
			ET.DISPLAY_NUM ASC
	";
	
	$db->query($select_editorial_thumb_sql,array($page_idx,$type,$size));
	
	foreach($db->fetch() as $data) {
		$e_thumb[] = array(
			't_idx'			=>$data['THUMB_IDX'],
			't_location'	=>$data['T_LOCATION'],
		);
	}
	
	return $e_thumb;
}

function getEditorial_contents($db,$page_idx,$type,$size) {
	$e_contents = array();
	
	$select_editorial_contents_sql = "
		SELECT
			EC.IDX					AS CONTENTS_IDX,
			EC.CONTENTS_LOCATION	AS C_LOCATION
		FROM
			EDITORIAL_CONTENTS EC
		WHERE
			EC.PAGE_IDX			= ? AND
			EC.CONTENTS_TYPE	= ? AND
			EC.SIZE_TYPE		= ? AND
			EC.DEL_FLG			= FALSE
		ORDER BY
			EC.DISPLAY_NUM ASC
	";
	
	$db->query($select_editorial_contents_sql,array($page_idx,$type,$size));
	
	foreach($db->fetch() as $data) {
		$e_contents[] = array(
			'c_idx'			=>$data['CONTENTS_IDX'],
			'c_location'	=>$data['C_LOCATION']
		);
	}
	
	return $e_contents;
}

function setDisplay_num($editorial) {
	if ($editorial != null && count($editorial) > 0) {
		$display_num = 1;
		
		foreach($editorial as $key => $value) {
			$editorial[$key]['num'] = $display_num;
			$display_num++;
		}
	}
	
	return $editorial;
}

?>