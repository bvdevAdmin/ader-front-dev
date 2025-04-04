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

include_once(dir_f_api."/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

if (!isset($country)) {
	$json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0072', array());
	
	echo json_encode($json_result);
	exit;
}

if (isset($country)) {
	$paramCountry = '';
	if($country != 'KR'){
		$paramCountry .= "_".$country;
	}
	
	$where = " SI.DEL_FLG = FALSE ";
	
	if (isset($store_keyword)) {
		$where .= "
			AND (
				COUNTRY_KR REGEXP '".$store_keyword."' OR
				COUNTRY_EN REGEXP '".$store_keyword."' OR
				COUNTRY_CN REGEXP '".$store_keyword."' OR
				SI.STORE_NAME REGEXP '".$store_keyword."' OR
				SI.STORE_ADDR REGEXP '".$store_keyword."' OR
				SI.STORE_KEYWORD REGEXP '".$store_keyword."' OR
				SI.INSTAGRAM_ID REGEXP '".$store_keyword."'
			)
		";
	}
	
	$select_store_space_sql = "
		SELECT
			SI.IDX								AS SPACE_IDX,
			SI.COUNTRY_".$country."				AS COUNTRY,
			SI.STORE_NAME						AS STORE_NAME,
			SI.STORE_ADDR".$paramCountry."		AS STORE_ADDR,
			SI.STORE_TEL						AS STORE_TEL,
			SI.STORE_SALE_DATE".$paramCountry."	AS STORE_SALE_DATE,
			SI.STORE_LINK						AS STORE_LINK,
			SI.INSTAGRAM_ID						AS INSTAGRAM_ID,
			SI.LAT								AS LAT,
			SI.LNG								AS LNG
		FROM
			STORE_SPACE SI
		WHERE
			".$where."
		ORDER BY
			SI.DISPLAY_NUM ASC
	";
	
	$db->query($select_store_space_sql);
	
	$space_info = array();
	foreach ($db->fetch() as $space_data) {
		$space_idx = $space_data['SPACE_IDX'];
		
		$contents_info = array();
		if (!empty($space_idx)) {
			$select_contents_sql = "
				SELECT
					CI.CONTENTS_LOCATION		AS CONTENTS_LOCATION
				FROM
					CONTENTS_SPACE CI
				WHERE
					CI.STORE_IDX = ".$space_idx." AND
					CI.DEL_FLG = FALSE
			";
			
			$db->query($select_contents_sql);
			
			foreach($db->fetch() as $space_contents_data) {
				$contents_info[] = array(
					'contents_location'		=>$space_contents_data['CONTENTS_LOCATION']
				);
			}
		}
		
		$space_info[] = array(
			'store_idx'			=>$space_idx,
			'store_type'		=>"SPC",
			'country'			=>$space_data['COUNTRY'],
			'store_name'		=>$space_data['STORE_NAME'],
			'store_addr'		=>$space_data['STORE_ADDR'],
			'store_tel'			=>$space_data['STORE_TEL'],
			'store_sale_date'	=>$space_data['STORE_SALE_DATE'],
			'store_link'		=>$space_data['STORE_LINK'],
			'instagram_id'		=>$space_data['INSTAGRAM_ID'],
			'lat'				=>$space_data['LAT'],
			'lng'				=>$space_data['LNG'],
			
			'contents_info'		=>$contents_info
		);
	}
	
	$select_store_plugshop_sql = "
		SELECT
			SI.IDX								AS PLUGSHOP_IDX,
			SI.COUNTRY_".$country."				AS COUNTRY,
			SI.STORE_NAME						AS STORE_NAME,
			SI.STORE_ADDR".$paramCountry."		AS STORE_ADDR,
			SI.STORE_TEL						AS STORE_TEL,
			SI.STORE_SALE_DATE".$paramCountry."	AS STORE_SALE_DATE,
			SI.STORE_LINK						AS STORE_LINK,
			SI.INSTAGRAM_ID						AS INSTAGRAM_ID,
			SI.LAT								AS LAT,
			SI.LNG								AS LNG
		FROM
			STORE_PLUGSHOP SI
		WHERE
			".$where."
		ORDER BY
			SI.DISPLAY_NUM ASC
	";
	
	$db->query($select_store_plugshop_sql);
	
	$plugshop_info = array();
	
	foreach ($db->fetch() as $plugshop_data) {
		$plugshop_idx = $plugshop_data['PLUGSHOP_IDX'];
		
		$contents_info = array();
		if (!empty($plugshop_idx)) {
			$select_contents_sql = "
				SELECT
					CI.CONTENTS_LOCATION		AS CONTENTS_LOCATION
				FROM
					CONTENTS_PLUGSHOP CI
				WHERE
					CI.STORE_IDX = ".$plugshop_idx." AND
					CI.DEL_FLG = FALSE
			";
			
			$db->query($select_contents_sql);
			
			foreach($db->fetch() as $plugshop_contents_data) {
				$contents_info[] = array(
					'contents_location'		=>$plugshop_contents_data['CONTENTS_LOCATION']
				);
			}
		}
		
		$plugshop_info[] = array(
			'store_idx'			=>$plugshop_idx,
			'store_type'		=>"PLG",
			'country'			=>$plugshop_data['COUNTRY'],
			'store_name'		=>$plugshop_data['STORE_NAME'],
			'store_addr'		=>$plugshop_data['STORE_ADDR'],
			'store_tel'			=>$plugshop_data['STORE_TEL'],
			'store_sale_date'	=>$plugshop_data['STORE_SALE_DATE'],
			'store_link'		=>$plugshop_data['STORE_LINK'],
			'instagram_id'		=>$plugshop_data['INSTAGRAM_ID'],
			'lat'				=>$plugshop_data['LAT'],
			'lng'				=>$plugshop_data['LNG'],
			
			'contents_info'		=>$contents_info
		);
	}
	
	$json_result['data'] = array(
		'space_info'		=>$space_info,
		'plugshop_info'		=>$plugshop_info
	);
}

?>