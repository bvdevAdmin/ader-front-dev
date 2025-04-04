<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 맞춤정보 카테고리 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2024.10.15
 | 최종 수정	: 
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
  $member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($_SERVER['HTTP_COUNTRY']) && $member_idx > 0) {
	$gender = null;
	$height = null;
	$weight = null;
	
	$size_upper = array();
	$size_lower = array();
	$size_shoes = array();
	
	$member_custom = getMember_custom($db,$member_idx);
	if ($member_custom != null) {
		$gender = $member_custom['member_gender'];
		$height = $member_custom['height'];
		$weight = $member_custom['weight'];
		
		$size_upper = $member_custom['upper_size'];
		$size_lower = $member_custom['lower_size'];
		$size_shoes = $member_custom['shoes_size'];
	}
	
	$category_txt = "CATEGORY_TXT_".$_SERVER['HTTP_COUNTRY'];
	
	$select_custom_category_sql = "
		SELECT
			CC.IDX				AS CATEGORY_IDX,
			CC.CATEGORY_TYPE	AS CATEGORY_TYPE,
			CC.CATEGORY_TXT_KR	AS CATEGORY_TXT_KR,
			CC.CATEGORY_TXT_EN	AS CATEGORY_TXT_EN
		FROM
			CUSTOM_CATEGORY CC
		ORDER BY
			CC.IDX ASC
	";
	
	$upper_size = array();
	$lower_size = array();
	$shoes_size = array();
	
	$db->query($select_custom_category_sql);
	
	foreach($db->fetch() as $data) {
		$checked = false;
		
		switch ($data['CATEGORY_TYPE']) {
			case "UPC" :
				if (in_array($data['CATEGORY_IDX'],$size_upper)) {
					$checked = true;
				}
				
				$upper_size[] = array(
					'category_idx'		=>$data['CATEGORY_IDX'],
					'category_txt'		=>$data['CATEGORY_TXT_'.$_SERVER['HTTP_COUNTRY']],
					'checked'			=>$checked
				);
				
				break;
			
			case "LWC" :
				if (in_array($data['CATEGORY_IDX'],$size_lower)) {
					$checked = true;
				}
				
				$lower_size[] = array(
					'category_idx'		=>$data['CATEGORY_IDX'],
					'category_txt'		=>$data['CATEGORY_TXT_'.$_SERVER['HTTP_COUNTRY']],
					'checked'			=>$checked
				);
				
				break;
			
			case "SHC" :
				if (in_array($data['CATEGORY_IDX'],$size_shoes)) {
					$checked = true;
				}
				
				$shoes_size[] = array(
					'category_idx'		=>$data['CATEGORY_IDX'],
					'category_txt'		=>$data['CATEGORY_TXT_'.$_SERVER['HTTP_COUNTRY']],
					'checked'			=>$checked
				);
				
				break;
		}
	}
	
	$json_result['data'] = array(
		'gender'			=>$gender,
		'height'			=>$height,
		'weight'			=>$weight,
		
		'upper_size'		=>$upper_size,
		'lower_size'		=>$lower_size,
		'shoes_size'		=>$shoes_size
	);
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());
}

function getMember_custom($db,$member_idx) {
	$member_custom = null;
	
	$select_member_custom_sql = "
		SELECT
			MC.MEMBER_GENDER	AS MEMBER_GENDER,
			MC.HEIGHT			AS HEIGHT,
			MC.WEIGHT			AS WEIGHT,
			MC.UPPER_SIZE_IDX	AS UPPER_SIZE,
			MC.LOWER_SIZE_IDX	AS LOWER_SIZE,
			MC.SHOES_SIZE_IDX	AS SHOES_SIZE
		FROM
			MEMBER_CUSTOM MC
		WHERE
			COUNTRY		= ? AND
			MEMBER_IDX	= ?
	";
	
	$db->query($select_member_custom_sql,array($_SERVER['HTTP_COUNTRY'],$member_idx));
	
	foreach($db->fetch() as $data) {
		$upper_size = array();
		if ($data['UPPER_SIZE'] != null && strlen($data['UPPER_SIZE']) > 0) {
			$upper_size = explode(",",$data['UPPER_SIZE']);
		}
		
		$lower_size = array();
		if ($data['LOWER_SIZE'] != null && strlen($data['LOWER_SIZE']) > 0) {
			$lower_size = explode(",",$data['LOWER_SIZE']);
		}
		
		$shoes_size = array();
		if ($data['SHOES_SIZE'] != null && strlen($data['SHOES_SIZE']) > 0) {
			$shoes_size = explode(",",$data['SHOES_SIZE']);
		}
		
		$member_custom = array(
			'member_gender'		=>$data['MEMBER_GENDER'],
			'height'			=>$data['HEIGHT'],
			'weight'			=>$data['WEIGHT'],
			'upper_size'		=>$upper_size,
			'lower_size'		=>$lower_size,
			'shoes_size'		=>$shoes_size
		);
	}
	
	return $member_custom;
}

?>