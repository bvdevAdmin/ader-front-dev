<?php
/*
 +=============================================================================
 | 
 | 메인 랜딩
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.02.13
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

if (isset($country)) {
	/* 메인 페이지 배너 조회처리 */
	$contents_banner = getContents_banner($db,$country);
	
	/* 메인 페이지 컨텐츠 조회처리 */
	$contents_main = getContents_main($db,$country);
	
	/* 메인 페이지 상품정보 조회처리 */
	//$contents_product = getContentsProduct($db,$country);
	
	$json_result['data'] = array(
		'contents_banner'	=>$contents_banner,
		'contents_main'		=>$contents_main
	);
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = "부적절한 접근이 감지되었습니다. 사용 언어를 선택 후 다시 시도해주세요.";
	
	echo json_encode($json_result);
	exit;
}

function convertMOV($path){
	$path_arr = explode('/',$path);
	$path_arr[count($path_arr) - 1] = 'mp4:'.$path_arr[count($path_arr) - 1].'/playlist.m3u8';
	
	return implode('/',$path_arr);
}

/* 메인 페이지 배너 조회처리 */
function getContents_banner($db,$country) {
	$contents_banner = array();
	
	$select_main_banner_sql = "
		SELECT
			MB.BANNER_LOCATION		AS BANNER_LOCATION,
			MB.BANNER_LOCATION_MOB	AS BANNER_LOCATION_MOB,
			MB.`CONTENT_TYPE`		AS BANNER_TYPE,
			MB.TITLE				AS BANNER_TITLE,
			MB.SUB_TITLE			AS BANNER_SUB_TITLE,
			MB.BACKGROUND_COLOR		AS BACKGROUND_COLOR,
			
			MB.BTN1_NAME			AS B1_NAME,
			MB.BTN1_URL				AS B1_URL,
			MB.BTN1_DISPLAY_FLG		AS B1_FLG,
			
			MB.BTN2_NAME			AS B2_NAME,
			MB.BTN2_URL				AS B2_URL,
			MB.BTN2_DISPLAY_FLG		AS B2_FLG
		FROM
			MAIN_BANNER MB
		WHERE
			MB.COUNTRY = ? AND
			MB.DEL_FLG = FALSE
		ORDER BY
			MB.DISPLAY_NUM ASC
	";
	
	$db->query($select_main_banner_sql,array($country));
	
	foreach($db->fetch() as $data) {
		$banner_location_W = "";
		$banner_location_M = "";
		
		if ($data['BANNER_TYPE'] == 'MOV') {
			$banner_location_W	= convertMOV($data['BANNER_LOCATION']);
			$banner_location_M	= convertMOV($data['BANNER_LOCATION_MOB']);
		} else if ($data['BANNER_TYPE'] == "IMG") {
			$banner_location_W	= $data['BANNER_LOCATION'];
			$banner_location_M	= $data['BANNER_LOCATION_MOB'];
		}
		
		$contents_banner[] = array(
			'banner_location_W'		=>$banner_location_W,
			'banner_location_M'		=>$banner_location_M,
			'banner_type'			=>$data['BANNER_TYPE'],
			'title'					=>$data['BANNER_TITLE'],
			'sub_title'				=>$data['BANNER_SUB_TITLE'],
			'background_color'		=>$data['BACKGROUND_COLOR'],
			
			'b1_name'				=>$data['B1_NAME'],
			'b1_url'				=>$data['B1_URL'],
			'b1_flg'				=>$data['B1_FLG'],
			
			'b2_name'				=>$data['B2_NAME'],
			'b2_url'				=>$data['B2_URL'],
			'b2_flg'				=>$data['B2_FLG']
		);
	}
	
	return $contents_banner;
}

/* 메인 페이지 컨텐츠 조회처리 */
function getContents_main($db,$country) {
	$contents_main = array();
	
	$select_main_contents_sql = "
		SELECT
			MC.CONTENTS_LOCATION_W	AS CONTENTS_LOCATION_W,
			MC.CONTENTS_LOCATION_M	AS CONTENTS_LOCATION_M,
			MC.TITLE				AS MAIN_TITLE,
			MC.SUB_TITLE			AS MAIN_SUB_TITLE,
			MC.BACKGROUND_COLOR		AS BACKGROUND_COLOR,
			MC.BTN1_NAME			AS B1_NAME,
			MC.BTN1_URL				AS B1_URL,
			MC.BTN1_DISPLAY_FLG		AS B1_FLG,
			MC.BTN2_NAME			AS B2_NAME,
			MC.BTN2_URL				AS B2_URL,
			MC.BTN2_DISPLAY_FLG		AS B2_FLG
		FROM
			MAIN_CONTENTS MC
		WHERE
			MC.COUNTRY = ? AND
			MC.DEL_FLG = FALSE
	";
	
	$db->query($select_main_contents_sql,array($country));
	
	foreach($db->fetch() as $data) {
		$contents_main[] = array(
			'contents_location_W'	=>$data['CONTENTS_LOCATION_W'],
			'contents_location_M'	=>$data['CONTENTS_LOCATION_M'],
			
			'main_title'			=>$data['MAIN_TITLE'],
			'main_sub_title'		=>$data['MAIN_SUB_TITLE'],
			
			'background_color'		=>$data['BACKGROUND_COLOR'],
			
			'b1_name'				=>$data['B1_NAME'],
			'b1_url'				=>$data['B1_URL'],
			'b1_flg'				=>$data['B1_FLG'],
			
			'b2_name'				=>$data['B2_NAME'],
			'b2_url'				=>$data['B2_URL'],
			'b2_flg'				=>$data['B2_FLG']
		);
	}
	
	return $contents_main;
}

/* 메인 페이지 상품정보 조회처리 */
function getContentsProduct($db,$country) {
	$contents_product = array();
	
	$select_contents_product_sql = "
		SELECT
			CP.PRODUCT_IDX			AS PRODUCT_IDX,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = PR.IDX AND
					IMG_TYPE = 'P' AND
					IMG_SIZE = 'M'
				ORDER BY
					IDX ASC
				LIMIT
					0,1
			)						AS IMG_LOCATION,
			PR.PRODUCT_NAME			AS PRODUCT_NAME
		FROM
			CONTENTS_PRODUCT CP
			LEFT JOIN SHOP_PRODUCT PR ON
			CP.PRODUCT_IDX = PR.IDX
		WHERE
			CP.COUNTRY = ? AND
			PR.DEL_FLG = FALSE
		ORDER BY
			CP.DISPLAY_NUM ASC
	";
	
	$db->query($select_contents_product_sql,array($country));
	
	foreach($db->fetch() as $data) {
		$contents_product[] = array(
			'product_idx'			=>$data['PRODUCT_IDX'],
			'img_location'			=>$data['IMG_LOCATION'],
			'product_name'			=>$data['PRODUCT_NAME']
		);
	}
	
	return $contents_product;
}

/* 메인 페이지 이미지 조회처리 */
function getMainImages($db,$country) {
	$main_img = array();
	
	$select_main_img_sql = "
		SELECT
			MI.IMG_LOCATION			AS IMG_LOCATION,
			MI.TITLE				AS TITLE,
			MI.BTN_NAME				AS BTN_NAME,
			MI.BTN_URL				AS BTN_URL,
			MI.BTN_DISPLAY_FLG		AS BTN_DISPLAY_FLG
		FROM
			MAIN_IMG MI
		WHERE
			MI.COUNTRY = ? AND
			MI.DEL_FLG = FALSE
		ORDER BY
			MI.DISPLAY_NUM ASC
	";
	
	$db->query($select_main_img_sql,array($country));
	
	foreach($db->fetch() as $img_data) {
		$main_img[] = array(
			'img_location'			=>$img_data['IMG_LOCATION'],
			
			'title'					=>$img_data['TITLE'],
			
			'btn_name'				=>$img_data['BTN_NAME'],
			'btn_url'				=>$img_data['BTN_URL'],
			'btn_display_flg'		=>$img_data['BTN_DISPLAY_FLG']
		);
	}
	
	return $main_img;
}

?>