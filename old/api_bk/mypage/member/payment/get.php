<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 결제수단 조회
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.03.16
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	exit;
}

if ($country != null && $member_idx > 0) {
	$select_payment_to_sql = "
		SELECT
			MC.CARD_NAME AS CARD_NAME,
			MC.CARD_NUMBER AS CARD_NUMBER,
			MC.CARD_VALID_YEAR AS CARD_VALID_YEAR,
			MC.CARD_VALID_MONTH AS CARD_VALID_MONTH,
			MC.CARD_DEFAULT_FLG AS CARD_DEFAULT_FLG
		FROM
			MEMBER_".$country." MC
		WHERE
			IDX = ".$member_idx."
	";

	$db->query($select_payment_to_sql);

	foreach($db->fetch() as $data) {
		$json_result['data'][] = array(
			'card_name'				 =>$data['CARD_NAME'],
			'card_number'			 =>$data['CARD_NUMBER'],
			'card_valid_year'	 =>$data['CARD_VALID_YEAR'],
			'card_valid_month' =>$data['CARD_VALID_MONTH'],
			'card_default_flg' =>$data['CARD_DEFAULT_FLG']
		);
	}
}
?>