<?php
/*
 +=============================================================================
 | 
 | 매장 찾기 - 매장 정보 개별 조회
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

if (isset($_SERVER['HTTP_COUNTRY'])) {
	$where = "
		SI.DEL_FLG = FALSE
	";
	
	$param_bind = null;
	
	if (isset($keyword) && trim($keyword) != '') {
		$where .= "
			AND (
				COUNTRY_KR LIKE			? OR
				COUNTRY_EN LIKE			? OR
				
				SI.STORE_NAME LIKE		? OR
				SI.STORE_ADDR LIKE		? OR
				SI.STORE_ADDR_EN LIKE	? OR
				SI.STORE_KEYWORD LIKE	? OR
				SI.INSTAGRAM_ID REGEXP	?
			)
		";
		
		$param_bind = array(
			"%".$keyword."%",
			"%".$keyword."%",
			
			"%".$keyword."%",
			"%".$keyword."%",
			"%".$keyword."%",
			"%".$keyword."%",
			"%".$keyword."%"
		);
	}
	
	$store_space	= getStore_space($db,$where,$param_bind);
	$store_plugshop	= getStore_plugshop($db,$where,$param_bind);
	
	$json_result['data'] = array(
		'space_info'		=>$store_space,
		'plugshop_info'		=>$store_plugshop
	);
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0072', array());
	
	echo json_encode($json_result);
	exit;
}

function getStore_space($db,$where,$param_bind) {
	$store_space = array();

	$select_store_space_sql = "
		SELECT
			SI.IDX						AS STORE_IDX,
			SI.COUNTRY_KR				AS COUNTRY_KR,
			SI.COUNTRY_EN				AS COUNTRY_EN,
			SI.STORE_NAME				AS STORE_NAME,
			SI.STORE_ADDR				AS STORE_ADDR_KR,
			SI.STORE_ADDR_EN			AS STORE_ADDR_EN,
			SI.STORE_TEL				AS STORE_TEL,
			SI.STORE_SALE_DATE			AS STORE_SALE_DATE_KR,
			SI.STORE_SALE_DATE_EN		AS STORE_SALE_DATE_EN,
			SI.STORE_LINK				AS STORE_LINK,
			SI.INSTAGRAM_ID				AS INSTAGRAM_ID,
			SI.LAT						AS LAT,
			SI.LNG						AS LNG,
			SI.LINK_MAP					AS LINK_MAP
		FROM
			STORE_SPACE SI
		WHERE
			".$where."
		ORDER BY
			SI.DISPLAY_NUM ASC
	";
	
	if ($param_bind != null) {
		$db->query($select_store_space_sql,$param_bind);
	} else {
		$db->query($select_store_space_sql);
	}
	
	$store_idx = array();

	foreach ($db->fetch() as $data) {
		array_push($store_idx,$data['STORE_IDX']);

		$store_space[] = array(
			'store_idx'			=>$data['STORE_IDX'],
			'store_type'		=>"SPC",
			'country'			=>$data['COUNTRY_'.$_SERVER['HTTP_COUNTRY']],
			'store_name'		=>$data['STORE_NAME'],
			'store_addr'		=>$data['STORE_ADDR_'.$_SERVER['HTTP_COUNTRY']],
			'store_tel'			=>$data['STORE_TEL'],
			'store_sale_date'	=>$data['STORE_SALE_DATE_'.$_SERVER['HTTP_COUNTRY']],
			'store_link'		=>$data['STORE_LINK'],
			'instagram_id'		=>$data['INSTAGRAM_ID'],
			'lat'				=>$data['LAT'],
			'lng'				=>$data['LNG'],
			'link_map'			=>$data['LINK_MAP']
		);
	}

	if (sizeof($store_idx) > 0) {
		$store_contents = getStore_contents($db,"SPC",$store_idx);
		if (sizeof($store_contents) > 0) {
			foreach($store_space as $key => $space) {
				$param_idx = $space['store_idx'];
				
				if (isset($store_contents[$param_idx])) {
					$store_space[$key]['contents_info'] = $store_contents[$param_idx];
				}
			}
		}
	}

	return $store_space;
}

function getStore_plugshop($db,$where,$param_bind) {
	$store_plugshop = array();

	$select_store_plugshop_sql = "
		SELECT
			SI.IDX						AS STORE_IDX,
			SI.COUNTRY_KR				AS COUNTRY_KR,
			SI.COUNTRY_EN				AS COUNTRY_EN,
			SI.STORE_NAME				AS STORE_NAME,
			SI.STORE_ADDR				AS STORE_ADDR_KR,
			SI.STORE_ADDR_EN			AS STORE_ADDR_EN,
			SI.STORE_TEL				AS STORE_TEL,
			SI.STORE_SALE_DATE			AS STORE_SALE_DATE_KR,
			SI.STORE_SALE_DATE_EN		AS STORE_SALE_DATE_EN,
			SI.STORE_LINK				AS STORE_LINK,
			SI.INSTAGRAM_ID				AS INSTAGRAM_ID,
			SI.LAT						AS LAT,
			SI.LNG						AS LNG,
			SI.LINK_MAP					AS LINK_MAP
		FROM
			STORE_PLUGSHOP SI
		WHERE
			".$where."
		ORDER BY
			SI.DISPLAY_NUM ASC
	";
	
	if ($param_bind != null) {
		$db->query($select_store_plugshop_sql,$param_bind);
	} else {
		$db->query($select_store_plugshop_sql);
	}

	$store_idx = array();
	
	foreach ($db->fetch() as $data) {
		array_push($store_idx,$data['STORE_IDX']);

		$store_plugshop[] = array(
			'store_idx'			=>$data['STORE_IDX'],
			'store_type'		=>"PLG",
			'country'			=>$data['COUNTRY_'.$_SERVER['HTTP_COUNTRY']],
			'store_name'		=>$data['STORE_NAME'],
			'store_addr'		=>$data['STORE_ADDR_'.$_SERVER['HTTP_COUNTRY']],
			'store_tel'			=>$data['STORE_TEL'],
			'store_sale_date'	=>$data['STORE_SALE_DATE_'.$_SERVER['HTTP_COUNTRY']],
			'store_link'		=>$data['STORE_LINK'],
			'instagram_id'		=>$data['INSTAGRAM_ID'],
			'lat'				=>$data['LAT'],
			'lng'				=>$data['LNG'],
			'link_map'			=>$data['LINK_MAP']
		);
	}

	if (sizeof($store_idx) > 0) {
		$store_contents = getStore_contents($db,"PLG",$store_idx);
		if (sizeof($store_contents) > 0) {
			foreach($store_plugshop as $key => $plugshop) {
				$param_idx = $plugshop['store_idx'];

				if (isset($store_contents[$param_idx])) {
					$store_plugshop[$key]['contents_info'] = $store_contents[$param_idx];
				}
			}
		}
	}

	return $store_plugshop;
}

function getStore_contents($db,$store_type,$store_idx) {
	$store_contents = array();

	$table = array(
		'SPC'		=>"CONTENTS_SPACE",
		'PLG'		=>"CONTENTS_PLUGSHOP"
	);

	$select_store_contents_sql = "
		SELECT
			CI.STORE_IDX			AS STORE_IDX,
			CI.CONTENTS_LOCATION	AS CONTENTS_LOCATION
		FROM
			".$table[$store_type]." CI
		WHERE
			CI.STORE_IDX IN (".implode(',',array_fill(0,count($store_idx),'?')).") AND
			CI.DEL_FLG = FALSE
	";
	
	$db->query($select_store_contents_sql,$store_idx);
	
	foreach($db->fetch() as $data) {
		$store_contents[$data['STORE_IDX']][] = array(
			'contents_location'		=>$data['CONTENTS_LOCATION']
		);
	}

	return $store_contents;
}

?>