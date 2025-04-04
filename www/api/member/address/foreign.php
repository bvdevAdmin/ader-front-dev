<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 영문몰 회원 배송지 국가 및 주/도 정보 조회
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.12
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.28
 | 버전		: 1.1
 | 설명		: 2024.05.28 DB클래스 적용, 중복코드 정리, 필요없는 변수 삭제, 오류 발생시 json 출력이 되도록 수정
 |
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) & isset($_SESSION['MEMBER_IDX'])) {
	$country_info = getAddress_country($db);
	
	$province_info = getAddress_province($db);
	
	if (count($country_info) > 0 && count($province_info) > 0) {
		foreach($country_info as $key => $value) {
			$country_idx = $value['country_idx'];
			
			if (isset($province_info[$country_idx])) {
				$country_info[$key]['province'] = $province_info[$country_idx];
			}
		}
	}
	
	$json_result['data'] = $country_info;
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

function getAddress_country($db) {
	$country_info = array();
	
	$select_country_info_sql = "
		SELECT
			CI.IDX				AS COUNTRY_IDX,
			CI.COUNTRY_CODE		AS COUNTRY_CODE,
			CI.COUNTRY_NAME		AS COUNTRY_NAME,
			CI.PROVINCE_FLG		AS PROVINCE_FLG
		FROM
			COUNTRY_INFO CI
		ORDER BY
			CI.IDX ASC
	";
	
	$db->query($select_country_info_sql);
	
	foreach($db->fetch() as $data) {
		$country_info[] = array(
			'country_idx'		=>$data['COUNTRY_IDX'],
			'country_code'		=>$data['COUNTRY_CODE'],
			'country_name'		=>$data['COUNTRY_NAME'],
			'province_flg'		=>$data['PROVINCE_FLG']
		);
	}
	
	return $country_info;
}

function getAddress_province($db) {
	$province_info = array();
	
	$select_province_info_sql = "
		SELECT
			PI.IDX				AS PROVINCE_IDX,
			PI.COUNTRY_IDX		AS COUNTRY_IDX,
			PI.PROVINCE_NAME	AS PROVINCE_NAME
		FROM
			PROVINCE_INFO PI
		ORDER BY
			PI.IDX ASC
	";
	
	$db->query($select_province_info_sql);
	
	foreach($db->fetch() as $data) {
		$province_info[$data['COUNTRY_IDX']][] = array(
			'province_idx'		=>$data['PROVINCE_IDX'],
			'province_name'		=>$data['PROVINCE_NAME']
		);
	}
	
	return $province_info;
}

?>