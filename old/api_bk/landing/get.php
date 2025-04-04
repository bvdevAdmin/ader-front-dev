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
} else if (isset($_POST['country'])) {
	$country = $_POST['country'];
}

if ($country == null) {
	$json_result['code'] = 301;
	$json_result['code'] = "부적절한 접근이 감지되었습니다. 사용 언어를 선택 후 다시 시도해주세요.";
}

if ($country != null) {
	$select_main_banner_sql = "
		SELECT
			MB.BANNER_LOCATION		AS BANNER_LOCATION,
			MB.BANNER_LOCATION_MOB	AS BANNER_LOCATION_MOB,
			MB.CONTENT_TYPE			AS CONTENT_TYPE,
			MB.TITLE				AS TITLE,
			MB.SUB_TITLE			AS SUB_TITLE,
			MB.BACKGROUND_COLOR		AS BACKGROUND_COLOR,
			BTN1_NAME				AS BTN1_NAME,
			BTN1_URL				AS BTN1_URL,
			BTN1_DISPLAY_FLG		AS BTN1_DISPLAY_FLG,
			BTN2_NAME				AS BTN2_NAME,
			BTN2_URL				AS BTN2_URL,
			BTN2_DISPLAY_FLG		AS BTN2_DISPLAY_FLG
		FROM
			MAIN_BANNER MB
		WHERE
			MB.COUNTRY = '".$country."' AND
			MB.DEL_FLG = FALSE
		ORDER BY
			MB.DISPLAY_NUM ASC
	";
	
	$db->query($select_main_banner_sql);
	
	$banner_info = array();
	foreach($db->fetch() as $banner_data) {
		$banner_location = '';
		$banner_location_mob = '';
		if($banner_data['CONTENT_TYPE'] == 'MOV'){
			$banner_location = convertMOV($banner_data['BANNER_LOCATION']);
			$banner_location_mob = convertMOV($banner_data['BANNER_LOCATION_MOB']);
		}
		else{
			$banner_location = $banner_data['BANNER_LOCATION'];
			$banner_location_mob = $banner_data['BANNER_LOCATION_MOB'];
		}
		$banner_info[] = array(
			'banner_location'		=>$banner_location,
			'banner_location_mob'	=>$banner_location_mob,
			'content_type'			=>$banner_data['CONTENT_TYPE'],
			'title'					=>$banner_data['TITLE'],
			'sub_title'				=>$banner_data['SUB_TITLE'],
			'background_color'		=>$banner_data['BACKGROUND_COLOR'],
			'btn1_name'				=>$banner_data['BTN1_NAME'],
			'btn1_url'				=>$banner_data['BTN1_URL'],
			'btn1_display_flg'		=>$banner_data['BTN1_DISPLAY_FLG'],
			'btn2_name'				=>$banner_data['BTN2_NAME'],
			'btn2_url'				=>$banner_data['BTN2_URL'],
			'btn2_display_flg'		=>$banner_data['BTN2_DISPLAY_FLG']
		);
	}
	
	$select_main_contents_sql = "
		SELECT
			MC.IMG_LOCATION			AS IMG_LOCATION,
			MC.TITLE				AS TITLE,
			MC.SUB_TITLE			AS SUB_TITLE,
			MC.BACKGROUND_COLOR		AS BACKGROUND_COLOR,
			MC.BTN1_NAME			AS BTN1_NAME,
			MC.BTN1_URL				AS BTN1_URL,
			MC.BTN1_DISPLAY_FLG		AS BTN1_DISPLAY_FLG,
			MC.BTN2_NAME			AS BTN2_NAME,
			MC.BTN2_URL				AS BTN2_URL,
			MC.BTN2_DISPLAY_FLG		AS BTN2_DISPLAY_FLG
		FROM
			MAIN_CONTENTS MC
		WHERE
			MC.COUNTRY = '".$country."' AND
			MC.DEL_FLG = FALSE
	";
	
	$db->query($select_main_contents_sql);
	
	$contents_info = array();
	foreach($db->fetch() as $contents_data) {
		$contents_info = array(
			'img_location'			=>$contents_data['IMG_LOCATION'],
			'title'					=>$contents_data['TITLE'],
			'sub_title'				=>$contents_data['SUB_TITLE'],
			'background_color'		=>$contents_data['BACKGROUND_COLOR'],
			'btn1_name'				=>$contents_data['BTN1_NAME'],
			'btn1_url'				=>$contents_data['BTN1_URL'],
			'btn1_display_flg'		=>$contents_data['BTN1_DISPLAY_FLG'],
			'btn2_name'				=>$contents_data['BTN2_NAME'],
			'btn2_url'				=>$contents_data['BTN2_URL'],
			'btn2_display_flg'		=>$contents_data['BTN2_DISPLAY_FLG']
		);
	}
	
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
			CP.COUNTRY = '".$country."'
		ORDER BY
			CP.DISPLAY_NUM ASC
	";
	
	$db->query($select_contents_product_sql);
	
	$product_info = array();
	foreach($db->fetch() as $product_data) {
		$product_info[] = array(
			'product_idx'			=>$product_data['PRODUCT_IDX'],
			'img_location'			=>$product_data['IMG_LOCATION'],
			'product_name'			=>$product_data['PRODUCT_NAME']
		);
	}
	
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
			MI.COUNTRY = '".$country."' AND
			MI.DEL_FLG = FALSE
		ORDER BY
			MI.DISPLAY_NUM ASC
	";
	
	$db->query($select_main_img_sql);
	
	$img_info = array();
	foreach($db->fetch() as $img_data) {
		$img_info[] = array(
			'img_location'			=>$img_data['IMG_LOCATION'],
			'title'					=>$img_data['TITLE'],
			'btn_name'				=>$img_data['BTN_NAME'],
			'btn_url'				=>$img_data['BTN_URL'],
			'btn_display_flg'		=>$img_data['BTN_DISPLAY_FLG']
		);
	}
	
	$json_result['data'] = array(
		'banner_info'		=>$banner_info,
		'contents_info'		=>$contents_info,
		'product_info'		=>$product_info,
		'img_info'			=>$img_info
	);
}


function convertMOV($path){
	$path_arr = explode('/',$path);
	$path_arr[count($path_arr) - 1] = 'mp4:'.$path_arr[count($path_arr) - 1].'/playlist.m3u8';
	return implode('/',$path_arr);
}
?>