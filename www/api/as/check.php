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

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($_SERVER['HTTP_COUNTRY']) && $member_idx > 0 && isset($as_idx)) {
	$cnt_as = $db->count("MEMBER_AS","IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",array($as_idx,$_SERVER['HTTP_COUNTRY'],$member_idx));
	if ($cnt_as > 0) {
		$select_as_price_sql = "
			SELECT
				MA.AS_CODE		AS AS_CODE,
				MA.AS_PRICE		AS AS_PRICE
			FROM
				MEMBER_AS MA
			WHERE
				MA.IDX			= ? AND
				MA.COUNTRY		= ? AND
				MA.MEMBER_IDX	= ? AND
				MA.AS_STATUS	= 'APG'
		";
		
		$db->query($select_as_price_sql,array($as_idx,$_SERVER['HTTP_COUNTRY'],$member_idx));
		
		foreach($db->fetch() as $data) {
			$json_result['data'] = array(
				'as_code'		=>$data['AS_CODE'],
				'as_price'		=>$data['AS_PRICE']
			);
		}
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0006',array());
	}
}

?>