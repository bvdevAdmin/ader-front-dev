<?php
/*
 +=============================================================================
 | 
 | 매장 찾기 - 매장 정보 개별 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.02.15
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.27
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

$current_lat = null;
if (isset($lat)) {
	$current_lat = floatval($lat);
}

$current_lng = null;
if (isset($lng)) {
	floatval($lng);
}

if (isset($_SERVER['HTTP_COUNTRY'])) {
	$where = "
		SI.DEL_FLG = FALSE
	";
	
	$param_bind = array();
	
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

	$distance = setDistance($current_lat,$current_lng);
	
	/* 매장 정보 조회 - 스페이스 */
	$store_space = getStore_space($db,$country,$where,$param_bind,$distance);
	$contents_space = getContents_space($db);
	if (sizeof($store_space) > 0 && sizeof($contents_space) > 0) {
		foreach($store_space as $key => $space) {
			$store_idx = $space['store_idx'];
			
			$store_space[$key]['contents_info'] = $contents_space[$store_idx];
		}
	}
	
	/* 매장 정보 조회 - 플러그샵 */
	$store_plugshop = getStore_plugshop($db,$country,$where,$param_bind,$distance);
	$contents_plugshop = getContents_plugshop($db);
	if (sizeof($store_plugshop) && sizeof($contents_plugshop) > 0) {
		foreach($store_plugshop as $key => $plugshop) {
			$store_idx = $plugshop['store_idx'];
			
			$store_plugshop[$key]['contents_info'] = $contents_plugshop[$store_idx];
		}
	}
	
	/* 매장 정보 조회 - 스톡키스트 */
	$store_stockist = getStore_stockist($db,$country,$where,$param_bind);
	
	$json_result['data'] = array(
		'space_info'		=>$store_space,
		'plugshop_info'		=>$store_plugshop,
		'stockist_info'		=>$store_stockist
	);
} else {
	$json_encode['code'] = 301;
	$json_encode['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0072', array());

	echo json_encode($json_result);
	exit;
}

function getStore_space($db,$country,$where,$param,$distance) {
	$store_space = array();
	
	$column_distance	= "";
	$order_distance		= "";
	$distance_bind		= array();

	if ($distance != null) {
		$column_distance	= $distance['column'];
		$order_distance		= $distance['order'];
		$distance_bind		= $distance['bind'];
	}

	$select_store_space_sql = "
		SELECT
			SI.IDX					AS SPACE_IDX,
			SI.COUNTRY_KR			AS COUNTRY_KR,
			SI.COUNTRY_EN			AS COUNTRY_EN,
			SI.STORE_NAME			AS STORE_NAME,
			SI.STORE_ADDR			AS STORE_ADDR_KR,
			SI.STORE_ADDR_EN		AS STORE_ADDR_EN,
			SI.STORE_TEL			AS STORE_TEL,
			SI.STORE_SALE_DATE		AS STORE_SALE_DATE_KR,
			SI.STORE_SALE_DATE_EN	AS STORE_SALE_DATE_EN,
			SI.STORE_LINK			AS STORE_LINK,
			SI.INSTAGRAM_ID			AS INSTAGRAM_ID,
			SI.LAT					AS LAT,
			SI.LNG					AS LNG,
			SI.LINK_MAP				AS LINK_MAP

			".$column_distance."
		FROM
			STORE_SPACE SI
		WHERE
			".$where."
		ORDER BY
			".$order_distance."SI.DISPLAY_NUM ASC
	";
	
	$param_bind = null;
	if (count($distance_bind) > 0 || count($param) > 0) {
		$param_bind = array_merge($distance_bind,$param);
	}

	if ($param_bind != null) {
		$db->query($select_store_space_sql,$param_bind);
	} else {
		$db->query($select_store_space_sql);
	}
	
	foreach ($db->fetch() as $data) {
		$store_space[] = array(
			'store_idx'			=>$data['SPACE_IDX'],
			'store_type'		=>"SPC",
			'country'			=>$data['COUNTRY_'.$country],
			'store_name'		=>$data['STORE_NAME'],
			'store_addr'		=>$data['STORE_ADDR_'.$country],
			'store_tel'			=>$data['STORE_TEL'],
			'store_sale_date'	=>$data['STORE_SALE_DATE_'.$country],
			'store_link'		=>$data['STORE_LINK'],
			'instagram_id'		=>$data['INSTAGRAM_ID'],
			'lat'				=>$data['LAT'],
			'lng'				=>$data['LNG'],
			'link_map'			=>$data['LINK_MAP']
		);
	}
	
	return $store_space;
}

function getContents_space($db) {
	$contents_space = array();
	
	$select_contents_space_sql = "
		SELECT
			CI.STORE_IDX			AS STORE_IDX,
			CI.CONTENTS_LOCATION	AS CONTENTS_LOCATION
		FROM
			CONTENTS_SPACE CI
		WHERE
			CI.DEL_FLG = FALSE
	";

	$db->query($select_contents_space_sql);

	foreach($db->fetch() as $data) {
		$contents_space[$data['STORE_IDX']][] = array(
			'contents_location'		=>$data['CONTENTS_LOCATION']
		);
	}
	
	return $contents_space;
}

function getStore_plugshop($db,$country,$where,$param,$distance) {
	$store_plugshop = array();

	$column_distance	= "";
	$order_distance		= "";
	$distance_bind		= array();

	if ($distance != null) {
		$column_distance	= $distance['column'];
		$order_distance		= $distance['order'];
		$distance_bind		= $distance['bind'];
	}

	$select_store_plugshop_sql = "
		SELECT
			SI.IDX					AS PLUGSHOP_IDX,
			SI.COUNTRY_KR			AS COUNTRY_KR,
			SI.COUNTRY_EN			AS COUNTRY_EN,
			SI.STORE_NAME			AS STORE_NAME,
			SI.STORE_ADDR			AS STORE_ADDR_KR,
			SI.STORE_ADDR_EN		AS STORE_ADDR_EN,
			SI.STORE_TEL			AS STORE_TEL,
			SI.STORE_SALE_DATE		AS STORE_SALE_DATE_KR,
			SI.STORE_SALE_DATE_EN	AS STORE_SALE_DATE_EN,
			SI.STORE_LINK			AS STORE_LINK,
			SI.INSTAGRAM_ID			AS INSTAGRAM_ID,
			SI.LAT					AS LAT,
			SI.LNG					AS LNG

			".$column_distance."
		FROM
			STORE_PLUGSHOP SI
		WHERE
			".$where."
		ORDER BY
			".$order_distance."SI.DISPLAY_NUM ASC
	";
	
	$param_bind = null;
	if (count($distance_bind) > 0 || count($param) > 0) {
		$param_bind = array_merge($distance_bind,$param);
	}

	if ($param_bind != null) {
		$db->query($select_store_plugshop_sql,$param_bind);
	} else {
		$db->query($select_store_plugshop_sql);
	}
	
	foreach ($db->fetch() as $data) {
		$store_plugshop[] = array(
			'store_idx'			=>$data['PLUGSHOP_IDX'],
			'store_type'		=>"PLG",
			'country'			=>$data['COUNTRY_'.$country],
			'store_name'		=>$data['STORE_NAME'],
			'store_addr'		=>$data['STORE_ADDR_'.$country],
			'store_tel'			=>$data['STORE_TEL'],
			'store_sale_date'	=>$data['STORE_SALE_DATE_'.$country],
			'store_link'		=>$data['STORE_LINK'],
			'instagram_id'		=>$data['INSTAGRAM_ID'],
			'lat'				=>$data['LAT'],
			'lng'				=>$data['LNG']
		);
	}
	
	return $store_plugshop;
}

function getContents_plugshop($db) {
	$contents_plugshop = array();
	
	$select_contents_plugshop_sql = "
		SELECT
			CI.STORE_IDX			AS STORE_IDX,
			CI.CONTENTS_LOCATION	AS CONTENTS_LOCATION
		FROM
			CONTENTS_PLUGSHOP CI
		WHERE
			CI.DEL_FLG = FALSE
	";
	
	$db->query($select_contents_plugshop_sql);
	
	foreach($db->fetch() as $data) {
		$contents_plugshop[$data['STORE_IDX']][] = array(
			'contents_location'		=>$data['CONTENTS_LOCATION']
		);
	}
	
	return $contents_plugshop;
}

function getStore_stockist($db,$country,$where,$param) {
	$store_stockist = array();
	
	$select_store_stockist_sql = "
		SELECT
			SI.IDX					AS STORE_IDX,
			SI.COUNTRY_KR			AS COUNTRY_KR,
			SI.COUNTRY_EN			AS COUNTRY_EN,
			SI.STORE_NAME			AS STORE_NAME,
			SI.STORE_TEL			AS STORE_TEL,
			SI.STORE_LINK			AS STORE_LINK,
			SI.INSTAGRAM_ID			AS INSTAGRAM_ID,
			SI.LAT					AS LAT,
			SI.LNG					AS LNG
		FROM
			STORE_STOCKIST SI
		WHERE
			".$where."
		ORDER BY
			SI.STOCKIST_TYPE,
			SI.DISPLAY_NUM ASC
	";
	
	if (count($param) > 0) {
		$db->query($select_store_stockist_sql,$param);
	} else {
		$db->query($select_store_stockist_sql);
	}
	
	
	foreach ($db->fetch() as $data) {
		$store_stockist[] = array(
			'store_idx'			=>$data['STORE_IDX'],
			'store_type'		=>"STC",
			'country'			=>$data['COUNTRY_'.$country],
			'store_name'		=>$data['STORE_NAME'],
			'store_tel'			=>$data['STORE_TEL'],
			'store_link'		=>$data['STORE_LINK'],
			'instagram_id'		=>$data['INSTAGRAM_ID'],
			'lat'				=>$data['LAT'],
			'lng'				=>$data['LNG']
		);
	}
	
	return $store_stockist;
}

function setDistance($lat,$lng) {
	$distance = null;

	if ($lat != null && $lng != null) {
		$column_distance = "
			,(
				6371 * acos(cos(radians(?)) * cos(radians(SI.LAT)) * cos(radians(SI.LNG) - radians(?)) + sin(radians(?)) * sin(radians(SI.LAT)))
			) AS DISTANCE
		";

		$bind_distance = array($lat,$lng,$lat);

		$order_distance = " DISTANCE ASC, ";

		$distance = array(
			'column'		=>$column_distance,
			'bind'			=>$bind_distance,
			'order'			=>$order_distance
		);
	}

	return $distance;
}

?>