<?php
/*
 +=============================================================================
 | 
 | 해외 국가정보 API
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.6.9
 | 최종 수정일	: 2023.6.9
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

if (isset($country)) {
    $where = '';
	if ($country == "KR") {
		$json_result['code'] = 303;
		
		echo json_encode($json_result);
		exit;
	} else {
		if ($country == 'EN') {
			$where .= " COUNTRY_NAME != 'China' ";
		} else if ($country == 'CN') {
			$where .= " COUNTRY_NAME = 'China' ";
		} else {
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0011', array());
			
			echo json_encode($json_result);
			exit;
		}

		$select_country_info_sql = "
			SELECT 
				CI.COUNTRY_NAME		AS COUNTRY_NAME,
				CI.COUNTRY_CODE		AS COUNTRY_CODE
			FROM
				COUNTRY_INFO CI
			WHERE
				".$where."
		";
		
		$db->query($select_country_info_sql);
		
		foreach($db->fetch() as $data){
			$json_result['country'] = $country;
			$json_result['data'][] = array(
				'label'		=>$data['COUNTRY_NAME'],
				'value'		=>$data['COUNTRY_CODE']
			);
		}
	}
} else {
    $json_result['code'] = 301;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0012', array());
    
    echo json_encode($json_result);
    exit;
}
?>