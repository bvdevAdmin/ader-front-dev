<?php
/*
 +=============================================================================
 | 
 | A/S신청 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.04.23
 | 최종 수정일	: 
 | 버전		: 1.1
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($country) && isset($as_idx) && $member_idx > 0) {
	$as_cnt = $db->count("MEMBER_AS","IDX = ".$as_idx." AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx."");
	if ($as_cnt > 0) {
		$select_as_price_sql = "
			SELECT
				MA.AS_CODE		AS AS_CODE,
				MA.AS_PRICE		AS AS_PRICE
			FROM
				MEMBER_AS MA
			WHERE
				MA.IDX = ".$as_idx." AND
				MA.COUNTRY = '".$country."' AND
				MA.MEMBER_IDX = ".$member_idx." AND
				MA.AS_STATUS = 'APG'
		";
		
		$db->query($select_as_price_sql);
		
		foreach($db->fetch() as $price_data) {
			$json_result['data'] = array(
				'as_code'		=>$price_data['AS_CODE'],
				'as_price'		=>$price_data['AS_PRICE']
			);
		}
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0006', array());
	}
}

?>