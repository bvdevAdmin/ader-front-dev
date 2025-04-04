<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 맞춤정보 조회
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
	$custom_category = getCustom_category($db);
	$gender = array('M'=>"남자",'F'=>"여자");
	
	$select_member_custom_sql = "
		SELECT
			MC.IDX					AS CUSTOM_IDX,
			MC.MEMBER_GENDER		AS MEMBER_GENDER,
			MC.HEIGHT				AS HEIGHT,
			MC.WEIGHT				AS WEIGHT,
			MC.UPPER_SIZE_IDX		AS UPPER_SIZE,
			MC.LOWER_SIZE_IDX		AS LOWER_SIZE,
			MC.SHOES_SIZE_IDX		AS SHOES_SIZE
		FROM
			MEMBER_CUSTOM MC
		WHERE
			MC.COUNTRY = ? AND
			MC.MEMBER_IDX = ?
	";
	
	$db->query($select_member_custom_sql,array($_SERVER['HTTP_COUNTRY'],$member_idx));
	
	foreach($db->fetch() as $data) {
		$upper_size = "";
		if ($data['UPPER_SIZE'] != null && strlen($data['UPPER_SIZE']) > 0) {
			$tmp_size_upper = array();
			
			$tmp_upper = explode(",",$data['UPPER_SIZE']);
			foreach($tmp_upper as $upper) {
				array_push($tmp_size_upper,$custom_category['UPC'][$upper]);
			}
			
			$upper_size = implode(" , ",$tmp_size_upper);
		}
		
		$lower_size = "";
		if ($data['LOWER_SIZE'] != null && strlen($data['LOWER_SIZE']) > 0) {
			$tmp_size_lower = array();
			
			$tmp_lower = explode(",",$data['LOWER_SIZE']);
			foreach($tmp_lower as $lower) {
				array_push($tmp_size_lower,$custom_category['LWC'][$lower]);
			}
			
			$lower_size = implode(" , ",$tmp_size_lower);
		}
		
		$shoes_size = "";
		if ($data['SHOES_SIZE'] != null && strlen($data['SHOES_SIZE']) > 0) {
			$tmp_size_shoes = array();
			
			$tmp_shoes = explode(",",$data['SHOES_SIZE']);
			foreach($tmp_shoes as $shoes) {
				array_push($tmp_size_shoes,$custom_category['SHC'][$shoes]);
			}
			
			$shoes_size = implode(" , ",$tmp_size_shoes);
		}
		
		$json_result['data'] = array(
			'member_gender'		=>$gender[$data['MEMBER_GENDER']],
			'height'			=>$data['HEIGHT'],
			'weight'			=>$data['WEIGHT'],
			'upper_size'		=>$upper_size,
			'lower_size'		=>$lower_size,
			'shoes_size'		=>$shoes_size
		);
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());
}

function getCustom_category($db) {
	$custom_category = array();
	
	$select_custom_category_sql = "
		SELECT
			CC.IDX					AS CATEGORY_IDX,
			CC.CATEGORY_TYPE		AS CATEGORY_TYPE,
			CC.CATEGORY_TXT_KR		AS CATEGORY_TXT_KR,
			CC.CATEGORY_TXT_EN		AS CATEGORY_TXT_EN
		FROM
			CUSTOM_CATEGORY CC
		ORDER BY
			CC.IDX ASC
	";
	
	$db->query($select_custom_category_sql);
	
	foreach($db->fetch() as $data) {
		$custom_category[$data['CATEGORY_TYPE']][$data['CATEGORY_IDX']] = $data['CATEGORY_TXT_'.$_SERVER['HTTP_COUNTRY']];
	}
	
	return $custom_category;
}

?>